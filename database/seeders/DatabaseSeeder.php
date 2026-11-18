<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * php artisan migrate --seed
     * Demo accounts (change passwords before real use):
     *   admin@lasu.test / Admin@1234 ... see README.md
     */
    public function run(): void
    {
        $this->categories();
        $this->users();

        if (app()->environment('local') || config('lsw.seed_demo')) {
            $this->call(DemoReportSeeder::class);
        }
    }

    private function categories(): void
    {
        $cats = [
            ['Security Incident', 'security', 'security', '#dc2626', 'shield',
                'Theft, robbery, cult activity, fights, suspicious persons and break-ins.',
                ['Theft / Robbery', 'Cult activity', 'Fight / Assault', 'Suspicious person or vehicle', 'Break-in / Burglary', 'Kidnapping attempt', 'Vandalism', 'Other'], false],
            ['Harassment & GBV', 'harassment', 'counselling', '#9333ea', 'heart',
                'Sexual harassment, gender-based violence, bullying or intimidation. Always confidential.',
                ['Sexual harassment', 'Assault', 'Bullying / Intimidation', 'Exploitation (e.g. marks for favour)', 'Other'], true],
            ['Hostel Issue', 'hostel', 'works', '#0891b2', 'home',
                'Problems in on-campus or off-campus hostels.',
                ['Unauthorised occupant (squatter)', 'Water failure', 'Power failure', 'Sanitation / toilets', 'Pest infestation', 'Other'], false],
            ['Facility Damage', 'facility', 'works', '#f59e0b', 'wrench',
                'Broken lights, faulty doors, leaking roofs, damaged furniture or equipment.',
                ['Lighting / electrical', 'Building / roof', 'Furniture', 'Toilets / plumbing', 'Road / walkway', 'Lab equipment', 'Other'], false],
            ['Environment & Flooding', 'environment', 'works', '#2563eb', 'droplet',
                'Flooding, blocked drains, refuse dumps and overgrown bush.',
                ['Flooding', 'Blocked drainage', 'Refuse / waste dump', 'Bush overgrowth', 'Fallen tree', 'Erosion', 'Other'], false],
            ['Health & Emergency', 'health', 'medical', '#db2777', 'plus',
                'Accidents, fire outbreaks and medical emergencies.',
                ['Medical emergency', 'Road accident', 'Fire outbreak', 'Electric shock', 'Other'], false],
            ['Emergency SOS', 'sos', 'security', '#b91c1c', 'siren',
                'Sent automatically by the SOS panic button.', [], false],
        ];

        foreach ($cats as $i => [$name, $slug, $unit, $color, $icon, $desc, $subtypes, $conf]) {
            Category::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'unit' => $unit, 'color' => $color, 'icon' => $icon,
                'description' => $desc, 'subtypes' => $subtypes, 'is_confidential' => $conf, 'sort' => $i,
            ]);
        }
    }

    private function users(): void
    {
        $users = [
            ['System Administrator', 'admin@lasu.test', 'Admin@1234', 'admin'],
            ['Chief Security Officer', 'security@lasu.test', 'Security@123', 'security'],
            ['Officer Bello Adewale', 'bello@lasu.test', 'Security@123', 'security'],
            ['Works Department', 'works@lasu.test', 'Works@1234', 'works'],
            ['Medical Centre', 'medical@lasu.test', 'Medical@123', 'medical'],
            ['Counselling Unit', 'counsel@lasu.test', 'Counsel@123', 'counsellor'],
            ['Demo Student', 'student@lasu.test', 'Student@123', 'student'],
        ];

        foreach ($users as [$name, $email, $password, $role]) {
            // firstOrCreate: never overwrite a password the admin has changed
            User::firstOrCreate(['email' => $email], [
                'name' => $name, 'password' => $password, 'role' => $role, 'is_active' => true,
            ]);
        }
    }
}
