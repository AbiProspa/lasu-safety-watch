<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true; // categories + staff accounts

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DemoReportSeeder::class); // 20 sample reports
    }

    public function test_public_pages_load(): void
    {
        foreach (['/', '/report', '/map', '/map/data', '/safety', '/track', '/login', '/register'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_anyone_can_submit_an_anonymous_report_and_track_it(): void
    {
        $response = $this->post('/report', [
            'category_id' => Category::where('slug', 'facility')->value('id'),
            'title' => 'Broken desk in LT1',
            'description' => 'Several desks in LT1 are broken and sharp.',
            'severity' => 'low',
            'location_name' => 'Faculty of Law',
            'is_anonymous' => 1,
        ]);

        $report = Report::latest('id')->first();
        $response->assertRedirect(route('report.submitted', $report->reference));
        $this->assertTrue($report->is_anonymous);
        $this->assertNull($report->reporter_id);
        $this->assertSame('pending', $report->status);
        $this->get('/track?ref='.$report->reference)->assertSee('Report submitted.');
    }

    public function test_harassment_reports_are_always_anonymous_and_hidden_from_map(): void
    {
        $this->actingAs(User::where('role', 'student')->first())->post('/report', [
            'category_id' => Category::where('slug', 'harassment')->value('id'),
            'title' => 'Being followed after lectures',
            'description' => 'Someone keeps following me after evening lectures.',
            'severity' => 'high',
            'location_name' => 'Faculty of Arts',
            'is_anonymous' => 0,
        ]);

        $report = Report::latest('id')->first();
        $this->assertTrue($report->is_anonymous);
        $this->assertNull($report->reporter_id);
        $report->update(['status' => 'verified']);
        $this->get('/map/data')->assertDontSee($report->reference);
    }

    public function test_sos_creates_a_critical_alert(): void
    {
        $this->postJson('/sos', ['latitude' => 6.4677, 'longitude' => 3.1989])
            ->assertOk()->assertJson(['exact' => true]);

        $sos = Report::where('is_sos', true)->latest('id')->first();
        $this->assertSame('critical', $sos->severity);
        $this->assertSame('pending', $sos->status);
    }

    public function test_units_only_see_their_own_reports(): void
    {
        $security = User::where('email', 'security@lasu.test')->first();
        $hostel = Report::whereHas('category', fn ($c) => $c->where('unit', 'works'))->first();
        $theft = Report::whereHas('category', fn ($c) => $c->where('unit', 'security'))->first();

        $this->actingAs($security)->get(route('staff.reports.show', $theft))->assertOk();
        $this->actingAs($security)->get(route('staff.reports.show', $hostel))->assertForbidden();
        $this->actingAs($security)->get('/admin/users')->assertForbidden();
        $this->actingAs(User::where('role', 'student')->first())->get('/dashboard')->assertForbidden();
    }

    public function test_staff_can_update_a_report_and_it_is_logged(): void
    {
        $security = User::where('email', 'security@lasu.test')->first();
        $report = Report::whereHas('category', fn ($c) => $c->where('unit', 'security'))->where('status', 'pending')->first();

        $this->actingAs($security)->patch(route('staff.reports.update', $report), [
            'status' => 'resolved', 'severity' => 'high', 'assigned_to' => $security->id,
            'note' => 'Suspects handed to police.', 'is_internal' => 0,
        ])->assertRedirect();

        $report->refresh();
        $this->assertSame('resolved', $report->status);
        $this->assertNotNull($report->resolved_at);
        $this->assertDatabaseHas('report_updates', ['report_id' => $report->id, 'to_status' => 'resolved']);
    }

    public function test_dashboard_and_admin_pages_load_for_admin(): void
    {
        $admin = User::where('role', 'admin')->first();
        foreach (['/dashboard', '/dashboard/reports', '/dashboard/reports/export', '/admin/users', '/dashboard/sos-feed'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
