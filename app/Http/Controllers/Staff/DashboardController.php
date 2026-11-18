<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $base = fn () => Report::visibleTo($user);

        $kpis = [
            'total' => $base()->count(),
            'pending' => $base()->where('status', 'pending')->count(),
            'active' => $base()->whereIn('status', ['verified', 'in_progress'])->count(),
            'resolved_month' => $base()->where('status', 'resolved')->where('resolved_at', '>=', now()->startOfMonth())->count(),
            'critical_open' => $base()->where('severity', 'critical')->whereNotIn('status', ['resolved', 'rejected'])->count(),
        ];

        $resolved = $base()->whereNotNull('resolved_at')->get(['created_at', 'resolved_at']);
        $kpis['avg_hours'] = $resolved->isEmpty() ? null
            : (int) round($resolved->avg(fn ($r) => $r->created_at->diffInHours($r->resolved_at)));

        // Chart: reports per day for the last 14 days
        $days = collect(range(13, 0))->map(fn ($i) => now()->subDays($i)->startOfDay());
        $recent = $base()->where('created_at', '>=', $days->first())->get(['created_at']);
        $trend = [
            'labels' => $days->map->format('d M')->values(),
            'data' => $days->map(fn ($d) => $recent->filter(fn ($r) => $r->created_at->isSameDay($d))->count())->values(),
        ];

        // Chart: by category
        $byCategory = Category::orderBy('sort')->get()
            ->map(fn ($c) => ['name' => $c->name, 'color' => $c->color, 'count' => $base()->where('category_id', $c->id)->count()])
            ->filter(fn ($c) => $c['count'] > 0)->values();

        // Chart: by status
        $byStatus = collect(config('lsw.statuses'))
            ->map(fn ($label, $key) => ['label' => $label, 'count' => $base()->where('status', $key)->count()])->values();

        // Hotspots: top campus locations
        $hotspots = $base()->selectRaw('location_name, count(*) as total')
            ->whereNotNull('location_name')->groupBy('location_name')->orderByDesc('total')->take(6)->get();

        $urgent = $base()->with('category')->whereNotIn('status', ['resolved', 'rejected'])
            ->orderByRaw("case severity when 'critical' then 0 when 'high' then 1 when 'medium' then 2 else 3 end")
            ->latest()->take(6)->get();

        return view('staff.dashboard', compact('kpis', 'trend', 'byCategory', 'byStatus', 'hotspots', 'urgent'));
    }

    /** Polled every 15 seconds by the dashboard to raise live SOS alerts. */
    public function sosFeed(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->isAdmin() && $user->unit() !== 'security') {
            return response()->json([]);
        }

        return response()->json(
            Report::where('is_sos', true)->where('status', 'pending')->latest()->take(5)->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'ref' => $r->reference,
                    'when' => $r->created_at->diffForHumans(),
                    'location' => $r->location_name,
                    'url' => route('staff.reports.show', $r),
                ])
        );
    }
}
