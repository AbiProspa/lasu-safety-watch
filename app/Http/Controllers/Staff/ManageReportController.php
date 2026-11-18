<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManageReportController extends Controller
{
    public function index(Request $request): View
    {
        $reports = $this->filtered($request)->with(['category', 'assignee'])->paginate(15)->withQueryString();
        $categories = Category::orderBy('sort')->get()
            ->when(! $request->user()->isAdmin(), fn ($c) => $c->where('unit', $request->user()->unit()));

        return view('staff.reports.index', compact('reports', 'categories'));
    }

    public function show(Request $request, Report $report): View
    {
        abort_unless($request->user()->canManage($report), 403);

        $report->load(['category', 'reporter', 'assignee', 'updates.user']);
        $officers = User::where('is_active', true)
            ->whereIn('role', $this->rolesForUnit($report->category->unit))->orderBy('name')->get();

        return view('staff.reports.show', compact('report', 'officers'));
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        abort_unless($request->user()->canManage($report), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('lsw.statuses')))],
            'severity' => ['required', Rule::in(array_keys(config('lsw.severities')))],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')],
            'note' => ['nullable', 'string', 'max:2000'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        $from = $report->status;
        $notes = [];

        if ($data['status'] !== $from) {
            $notes[] = 'Status changed to '.config("lsw.statuses.{$data['status']}").'.';
            if ($data['status'] === 'verified' && ! $report->verified_at) {
                $report->verified_at = now();
            }
            $report->resolved_at = $data['status'] === 'resolved' ? now() : null;
        }

        if ((int) ($data['assigned_to'] ?? 0) !== (int) $report->assigned_to && ! empty($data['assigned_to'])) {
            $notes[] = 'Assigned to '.User::find($data['assigned_to'])->name.'.';
        }

        $report->fill([
            'status' => $data['status'],
            'severity' => $data['severity'],
            'assigned_to' => $data['assigned_to'] ?? null,
        ])->save();

        if ($notes || ! empty($data['note'])) {
            $report->updates()->create([
                'user_id' => $request->user()->id,
                'from_status' => $data['status'] !== $from ? $from : null,
                'to_status' => $data['status'] !== $from ? $data['status'] : null,
                'note' => trim(implode(' ', $notes).' '.($data['note'] ?? '')),
                'is_internal' => $request->boolean('is_internal'),
            ]);
        }

        return back()->with('success', 'Report '.$report->reference.' updated.');
    }

    /** Download the filtered list as a CSV file (opens in Excel). */
    public function export(Request $request): StreamedResponse
    {
        $reports = $this->filtered($request)->with(['category', 'assignee', 'reporter'])->get();

        return response()->streamDownload(function () use ($reports) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reference', 'Date', 'Category', 'Type', 'Title', 'Severity', 'Status', 'Location', 'Latitude', 'Longitude', 'Reporter', 'Assigned to', 'Resolved at']);
            foreach ($reports as $r) {
                fputcsv($out, [
                    $r->reference, $r->created_at->format('Y-m-d H:i'), $r->category->name, $r->subtype, $r->title,
                    $r->severityLabel(), $r->statusLabel(), $r->location_name, $r->latitude, $r->longitude,
                    $r->reporterName(), $r->assignee?->name, $r->resolved_at?->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, 'lasu-reports-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function filtered(Request $request): Builder
    {
        return Report::visibleTo($request->user())
            ->when($request->query('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->query('category'), fn ($q, $v) => $q->where('category_id', $v))
            ->when($request->query('severity'), fn ($q, $v) => $q->where('severity', $v))
            ->when($request->query('from'), fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->query('to'), fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($request->query('q'), fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$v}%")->orWhere('reference', 'like', "%{$v}%")
                ->orWhere('description', 'like', "%{$v}%")->orWhere('location_name', 'like', "%{$v}%")))
            ->latest();
    }

    private function rolesForUnit(string $unit): array
    {
        return collect(config('lsw.roles'))->filter(fn ($r) => $r[1] === $unit)->keys()->push('admin')->all();
    }
}
