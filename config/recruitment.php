<?php

return [
    'age_min' => 18,
    'age_max_regular' => 25,
    'age_max_tradesmen' => 27,
    'age_max_officer' => 30,
    'height_min_male' => 1.65,
    'height_min_female' => 1.58,
    'nationality' => 'Ghanaian',
    'voucher_costs' => [
        'regular' => 50,
        'tradesmen' => 100,
        'officer' => 200,
    ],
    'categories' => ['regular', 'tradesmen', 'officer'],
    'application_stages' => [
        'registered',
        'application_submitted',
        'eligibility_passed',
        'shortlisted',
        'appointment_scheduled',
        'screening_completed',
        'final_decision',
    ],
];
