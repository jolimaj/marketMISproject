<?php
return [
    [
        'type' => 'One Time Clearance Fee',
        'amount' => 20.00,
        'is_daily' => false,
        'is_monthly' => false,
        'is_styro' => false,
        'is_per_kilo' => false,
    ],
    [
        'type' => 'Fee per styrofoam',
        'amount' => 10.00,
        'is_daily' => true,
        'is_monthly' => false,
        'is_styro' => true,
        'is_per_kilo' => false,
    ],
    [
        'type' => 'Fee per Kilo',
        'amount' => 0.50,
        'is_daily' => true,
        'is_monthly' => false,
        'is_styro' => false,
        'is_per_kilo' => true,
    ],
    [
        'type' => 'Fee per Table',
        'amount' => 500.00,
        'is_daily' => false,
        'is_monthly' => true,
        'is_styro' => false,
        'is_per_kilo' => false,
    ],
    [
        'type' => 'Occupancy Permit Fee',
        'amount' => 10.00,
        'is_daily' => true,
        'is_monthly' => false,
        'is_styro' => false,
        'is_per_kilo' => false,
    ],
];