<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function create(Request $request): View
    {
        $categories = Category::where('slug', '!=', 'sos')->orderBy('sort')->get();
        $selected = $request->query('type');

        return view('public.report', compact('categories', 'selected'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->whereNot('slug', 'sos')],
            'subtype' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'min:5', 'max:150'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'severity' => ['required', Rule::in(array_keys(config('lsw.severities')))],
            'location_name' => ['required', Rule::in(array_keys(config('lsw.locations')))],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'occurred_at' => ['nullable', 'date', 'before_or_equal:now'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'is_anonymous' => ['nullable', 'boolean'],
            'reporter_contact' => ['nullable', 'string', 'max:100'],
        ], [
            'location_name.required' => 'Please choose where on campus this happened.',
        ]);

        $category = Category::findOrFail($data['category_id']);
        // Confidential categories (Harassment & GBV) are ALWAYS anonymous, whatever the form sends
        $anonymous = $category->is_confidential || $request->boolean('is_anonymous');

        // Use the landmark's coordinates when the user did not drop a pin
        if (empty($data['latitude']) && ($point = config('lsw.locations')[$data['location_name']] ?? null)) {
            [$data['latitude'], $data['longitude']] = $point;
        }

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('reports', 'public');
        }

        unset($data['photo']);
        $report = Report::create([
            ...$data,
            'is_anonymous' => $anonymous,
            'reporter_id' => $anonymous ? null : $request->user()?->id,
            'status' => 'pending',
        ]);

        $report->updates()->create(['to_status' => 'pending', 'note' => 'Report submitted.']);

        return redirect()->route('report.submitted', $report->reference);
    }

    public function submitted(string $reference): View
    {
        $report = Report::with('category')->where('reference', $reference)->firstOrFail();

        return view('public.submitted', compact('report'));
    }

    /** SOS panic button: one tap, no login, sends GPS position to Campus Security. */
    public function sos(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'note' => ['nullable', 'string', 'max:300'],
        ]);

        $exact = isset($data['latitude'], $data['longitude']);
        $center = config('lsw.campus_center');

        $report = Report::create([
            'category_id' => Category::where('slug', 'sos')->value('id'),
            'title' => 'SOS ALERT',
            'description' => trim(($data['note'] ?? '') ?: 'Emergency SOS button pressed. Immediate assistance needed.')
                .($exact ? '' : ' (GPS unavailable: location is approximate)'),
            'severity' => 'critical',
            'status' => 'pending',
            'location_name' => $exact ? 'GPS location' : 'Approximate (campus centre)',
            'latitude' => $data['latitude'] ?? $center['lat'],
            'longitude' => $data['longitude'] ?? $center['lng'],
            'occurred_at' => now(),
            'is_sos' => true,
            'is_anonymous' => ! $request->user(),
            'reporter_id' => $request->user()?->id,
        ]);
        $report->updates()->create(['to_status' => 'pending', 'note' => 'SOS alert raised.']);

        return response()->json(['reference' => $report->reference, 'exact' => $exact]);
    }

    public function mine(Request $request): View
    {
        $reports = $request->user()->reports()->with('category')->latest()->paginate(10);

        return view('public.mine', compact('reports'));
    }
}
