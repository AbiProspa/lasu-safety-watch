<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        $stats = [
            'total' => Report::count(),
            'resolved' => Report::where('status', 'resolved')->count(),
            'open' => Report::whereNotIn('status', ['resolved', 'rejected'])->count(),
            'this_week' => Report::where('created_at', '>=', now()->subDays(7))->count(),
            'avg_hours' => $this->averageResolutionHours(),
            'last_report' => Report::latest()->value('created_at'),
        ];

        $recent = Report::public()->with('category')->latest()->take(6)->get();
        $categories = Category::where('slug', '!=', 'sos')->orderBy('sort')->get();

        // Open cases per category for the live status card
        $openByCategory = Report::whereNotIn('status', ['resolved', 'rejected'])
            ->selectRaw('category_id, count(*) as total')->groupBy('category_id')->pluck('total', 'category_id');

        return view('public.home', compact('stats', 'recent', 'categories', 'openByCategory'));
    }

    public function map(): View
    {
        $categories = Category::where('is_confidential', false)->orderBy('sort')->get();

        return view('public.map', compact('categories'));
    }

    /** JSON used by the Leaflet map. Only verified, non-confidential reports are exposed. */
    public function mapData(Request $request): JsonResponse
    {
        $reports = Report::public()->with('category')
            ->whereNotNull('latitude')
            ->when($request->integer('days'), fn ($q, $d) => $q->where('created_at', '>=', now()->subDays($d)))
            ->latest()->limit(500)->get()
            ->map(fn (Report $r) => [
                'ref' => $r->reference,
                'title' => $r->title,
                'category' => $r->category->name,
                'category_id' => $r->category_id,
                'color' => $r->category->color,
                'status' => $r->status,
                'status_label' => $r->statusLabel(),
                'severity' => $r->severity,
                'location' => $r->location_name,
                'lat' => $r->latitude,
                'lng' => $r->longitude,
                'when' => $r->created_at->diffForHumans(),
            ]);

        return response()->json($reports);
    }

    public function safety(): View
    {
        return view('public.safety');
    }

    /** Anyone with a reference code can follow the progress of a report. */
    public function track(Request $request): View
    {
        $report = null;
        $ref = strtoupper(trim((string) $request->query('ref')));

        if ($ref !== '') {
            $report = Report::with(['category', 'updates' => fn ($q) => $q->where('is_internal', false)->oldest()])
                ->where('reference', $ref)->first();
        }

        return view('public.track', compact('report', 'ref'));
    }

    private function averageResolutionHours(): ?int
    {
        $resolved = Report::whereNotNull('resolved_at')->get(['created_at', 'resolved_at']);
        if ($resolved->isEmpty()) {
            return null;
        }

        return (int) round($resolved->avg(fn ($r) => $r->created_at->diffInHours($r->resolved_at)));
    }
}
