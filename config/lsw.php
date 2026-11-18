<?php

/*
|--------------------------------------------------------------------------
| LASU Safety Watch settings
|--------------------------------------------------------------------------
| Edit this file to change campus locations, emergency numbers and units.
*/

return [

    'name' => 'LASU Safety Watch',
    // Set SEED_DEMO_REPORTS=true on Railway to load the 20 sample reports on first deploy
    'seed_demo' => (bool) env('SEED_DEMO_REPORTS', false),

    'tagline' => 'See something? Report it. Safely and anonymously.',
    'university' => 'Lagos State University',

    // Centre of LASU Ojo main campus (map default)
    'campus_center' => ['lat' => 6.4677, 'lng' => 3.1989, 'zoom' => 16],

    // Responder units. Each report category belongs to one unit.
    'units' => [
        'security' => 'Campus Security',
        'works' => 'Works & Services',
        'medical' => 'Medical Centre',
        'counselling' => 'Student Affairs & Counselling',
    ],

    // User roles => [label, unit they handle (null = all / none)]
    'roles' => [
        'admin' => ['Administrator', null],
        'security' => ['Security Officer', 'security'],
        'works' => ['Works Officer', 'works'],
        'medical' => ['Medical Officer', 'medical'],
        'counsellor' => ['Counsellor', 'counselling'],
        'student' => ['Student / Staff', null],
    ],

    'severities' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ],

    'statuses' => [
        'pending' => 'Pending review',
        'verified' => 'Verified',
        'in_progress' => 'In progress',
        'resolved' => 'Resolved',
        'rejected' => 'Rejected',
    ],

    // Statuses shown publicly on the map / home page
    'public_statuses' => ['verified', 'in_progress', 'resolved'],

    'locations' => [
        'Main Gate' => [6.4706, 3.2003],
        'Senate Building' => [6.4672, 3.1993],
        'University Library' => [6.4664, 3.1978],
        'Student Union Building' => [6.4683, 3.1971],
        'Faculty of Science' => [6.4655, 3.1999],
        'Faculty of Arts' => [6.4690, 3.1985],
        'Faculty of Social Sciences' => [6.4669, 3.2012],
        'Faculty of Management Sciences' => [6.4648, 3.1985],
        'Faculty of Law' => [6.4641, 3.1968],
        'Faculty of Education' => [6.4697, 3.1962],
        'Medical Centre' => [6.4686, 3.2017],
        'Sports Centre' => [6.4628, 3.2006],
        'On-campus Hostels' => [6.4621, 3.1979],
        'Car Park' => [6.4700, 3.1990],
        'Chapel / Mosque area' => [6.4656, 3.2022],
        'Off-campus (Iba / Ojo)' => [6.4750, 3.1920],
        'Other' => null,
    ],

    // Replace the 0800... numbers with the official LASU numbers before going live.
    'contacts' => [
        ['name' => 'LASU Campus Security (Control Room)', 'desc' => 'Theft, fights, cult activity, suspicious persons', 'phone' => '08000000001', 'level' => 'critical'],
        ['name' => 'LASU Medical Centre', 'desc' => 'Injuries and medical emergencies', 'phone' => '08000000002', 'level' => 'critical'],
        ['name' => 'Lagos State Emergency', 'desc' => 'Fire, accidents, rescue (toll-free)', 'phone' => '112', 'level' => 'critical'],
        ['name' => 'Lagos Emergency Short Code', 'desc' => 'Alternative Lagos emergency line', 'phone' => '767', 'level' => 'high'],
        ['name' => 'Student Affairs / Counselling', 'desc' => 'Harassment, GBV, welfare', 'phone' => '08000000003', 'level' => 'high'],
        ['name' => 'Works & Services', 'desc' => 'Power, water, broken facilities, flooding', 'phone' => '08000000004', 'level' => 'normal'],
    ],

    'tips' => [
        'Personal security' => [
            'Avoid walking alone through dark or isolated parts of campus at night.',
            'Keep phones, laptops and ID cards out of sight in crowded places.',
            'Tell a friend your movement when returning late to the hostel.',
        ],
        'Cultism & harassment' => [
            'Never accept invitations to secret meetings or initiation "parties".',
            'Report threats early. You can report anonymously.',
            'Harassment by anyone (staff or student) should be reported to Counselling.',
        ],
        'Hostel safety' => [
            'Lock your room every time you leave, even briefly.',
            'Do not overload sockets or cook with open flames in rooms.',
            'Report unknown occupants (squatters) to the porter.',
        ],
        'Flooding & environment' => [
            'Avoid walking or driving through flooded roads during heavy rain.',
            'Report blocked drains early. They cause flooding around lecture halls.',
            'Do not dump refuse in gutters or open spaces.',
        ],
    ],
];
