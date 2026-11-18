<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Sample reports spread over the last 30 days. Run: php artisan db:seed --class=DemoReportSeeder */
class DemoReportSeeder extends Seeder
{
    public function run(): void
    {
        if (Report::count() > 0) {
            return;
        }

        $cat = Category::pluck('id', 'slug');
        $student = User::where('email', 'student@lasu.test')->first();
        $staff = User::whereIn('role', ['security', 'works', 'medical', 'counsellor'])->get()->groupBy('role');

        $rows = [
            // [category, subtype, title, description, severity, status, location, days ago, anonymous]
            ['security', 'Theft / Robbery', 'Phone snatched near Faculty of Arts', 'Two young men on a motorcycle snatched a student\'s phone at about 7pm and sped off towards the main gate.', 'high', 'in_progress', 'Faculty of Arts', 1, false],
            ['security', 'Cult activity', 'Suspected cult meeting behind Sports Centre', 'About 15 people wearing the same colour gathered behind the sports centre after 10pm with loud chanting.', 'critical', 'pending', 'Sports Centre', 0, true],
            ['security', 'Fight / Assault', 'Fight at Student Union Building', 'Two students fought over a seat during an event. One was slightly injured.', 'medium', 'resolved', 'Student Union Building', 9, false],
            ['security', 'Suspicious person or vehicle', 'Unknown car parked overnight', 'A tinted car without a plate number has been parked at the car park for 3 days.', 'low', 'verified', 'Car Park', 4, false],
            ['security', 'Break-in / Burglary', 'Office break-in at Faculty of Law', 'The door to the departmental office was forced open overnight; two laptops are missing.', 'high', 'resolved', 'Faculty of Law', 18, false],
            ['security', 'Theft / Robbery', 'Laptop stolen in the library', 'A laptop was stolen from a reading table on the second floor while the owner went to the toilet.', 'medium', 'verified', 'University Library', 6, false],
            ['security', 'Vandalism', 'Notice boards torn at Senate', 'Notice boards in front of the Senate building have been torn and defaced.', 'low', 'resolved', 'Senate Building', 22, true],
            ['harassment', 'Bullying / Intimidation', 'Repeated harassment by a senior student', 'A senior student keeps following me to my hostel and sending threatening messages.', 'high', 'in_progress', 'Off-campus (Iba / Ojo)', 2, true],
            ['hostel', 'Water failure', 'No water in Block C for 3 days', 'Block C has had no running water since Monday. Students are buying water outside.', 'medium', 'in_progress', 'On-campus Hostels', 3, false],
            ['hostel', 'Unauthorised occupant (squatter)', 'Squatters in Room 14, Block A', 'Two people who are not students are living in room 14 and threatening the occupants.', 'high', 'pending', 'On-campus Hostels', 1, true],
            ['hostel', 'Power failure', 'Hostel transformer fault', 'No light in the hostels for two nights. Students are reading with phone torches.', 'medium', 'resolved', 'On-campus Hostels', 14, false],
            ['facility', 'Lighting / electrical', 'Street lights off along library road', 'All street lights between the library and Faculty of Science are off, making the road very dark at night.', 'high', 'verified', 'University Library', 5, false],
            ['facility', 'Building / roof', 'Leaking roof in lecture theatre', 'Rain water pours into the main lecture theatre of the Faculty of Social Sciences.', 'medium', 'resolved', 'Faculty of Social Sciences', 15, false],
            ['facility', 'Furniture', 'Broken chairs in LT2', 'More than 30 chairs in lecture theatre 2 are broken; students stand during lectures.', 'low', 'pending', 'Faculty of Management Sciences', 2, false],
            ['environment', 'Flooding', 'Flooded road at Main Gate after rain', 'The road at the main gate is flooded knee-deep after this morning\'s rain. Cars cannot pass.', 'high', 'in_progress', 'Main Gate', 2, false],
            ['environment', 'Refuse / waste dump', 'Refuse dump behind Faculty of Science', 'Refuse has not been evacuated for two weeks and now smells.', 'medium', 'verified', 'Faculty of Science', 6, false],
            ['environment', 'Blocked drainage', 'Blocked gutter near Chapel', 'The gutter beside the chapel is blocked with sand and plastic bottles.', 'low', 'resolved', 'Chapel / Mosque area', 26, false],
            ['health', 'Medical emergency', 'Student collapsed during exam', 'A student collapsed in the exam hall and was taken to the Medical Centre.', 'critical', 'resolved', 'Faculty of Education', 11, false],
            ['health', 'Fire outbreak', 'Small fire at food vendor stall', 'A gas cylinder caught fire at a food stall near the Student Union Building. It was put out quickly.', 'high', 'resolved', 'Student Union Building', 20, false],
            ['sos', null, 'SOS alert', 'Emergency SOS button pressed. Immediate assistance needed.', 'critical', 'resolved', 'Faculty of Arts', 7, true],
        ];

        $locations = config('lsw.locations');
        $roleFor = ['security' => 'security', 'sos' => 'security', 'harassment' => 'counsellor', 'hostel' => 'works', 'facility' => 'works', 'environment' => 'works', 'health' => 'medical'];

        foreach ($rows as [$slug, $subtype, $title, $desc, $sev, $status, $loc, $days, $anon]) {
            [$lat, $lng] = $locations[$loc] ?? [6.4677, 3.1989];
            $created = now()->subDays($days)->subHours(random_int(1, 9));
            $officer = $staff[$roleFor[$slug]]?->random();

            $report = Report::create([
                'category_id' => $cat[$slug], 'subtype' => $subtype, 'title' => $title, 'description' => $desc,
                'severity' => $sev, 'status' => $status, 'location_name' => $loc,
                'latitude' => $lat + random_int(-15, 15) / 100000, 'longitude' => $lng + random_int(-15, 15) / 100000,
                'occurred_at' => $created->copy()->subMinutes(random_int(10, 90)),
                'is_anonymous' => $anon, 'is_sos' => $slug === 'sos',
                'reporter_id' => $anon ? null : $student?->id,
                'assigned_to' => $status === 'pending' ? null : $officer?->id,
                'verified_at' => $status === 'pending' ? null : $created->copy()->addHours(1),
                'resolved_at' => $status === 'resolved' ? $created->copy()->addHours(random_int(5, 72)) : null,
                'created_at' => $created, 'updated_at' => $created,
            ]);

            // Timeline
            $report->updates()->create(['to_status' => 'pending', 'note' => 'Report submitted.', 'created_at' => $created]);
            if ($status !== 'pending') {
                $report->updates()->create(['user_id' => $officer?->id, 'from_status' => 'pending', 'to_status' => 'verified',
                    'note' => 'Report verified and assigned to '.$officer?->name.'.', 'created_at' => $created->copy()->addHours(1)]);
            }
            if (in_array($status, ['in_progress', 'resolved'])) {
                $report->updates()->create(['user_id' => $officer?->id, 'from_status' => 'verified', 'to_status' => 'in_progress',
                    'note' => 'Team dispatched to the location.', 'created_at' => $created->copy()->addHours(2)]);
                $report->updates()->create(['user_id' => $officer?->id, 'note' => 'Internal: follow up with the unit head.',
                    'is_internal' => true, 'created_at' => $created->copy()->addHours(3)]);
            }
            if ($status === 'resolved') {
                $report->updates()->create(['user_id' => $officer?->id, 'from_status' => 'in_progress', 'to_status' => 'resolved',
                    'note' => 'Issue handled and closed.', 'created_at' => $report->resolved_at]);
            }
        }
    }
}
