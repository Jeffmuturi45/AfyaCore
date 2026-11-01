<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HMIS Application Configuration
    |--------------------------------------------------------------------------
    */

    'name' => env('HMIS_NAME', 'Afya Core HMIS'),
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Branch Configuration
    |--------------------------------------------------------------------------
    */
    'branches' => [
        'NYR' => [
            'name' => 'Prime Opticals',
            'location' => 'Nyeri',
            'type' => 'eye_clinic',
            'modules' => ['eye_clinic', 'optical_pos', 'insurance'],
            'mrn_prefix' => 'NYR',
        ],
        'KRT' => [
            'name' => 'Best Wing Opticals',
            'location' => 'Karatina',
            'type' => 'eye_clinic',
            'modules' => ['eye_clinic', 'optical_pos', 'insurance'],
            'mrn_prefix' => 'KRT',
        ],
        'HOS' => [
            'name' => 'Afya Hospital',
            'location' => 'TBD',
            'type' => 'opd',
            'modules' => ['opd', 'pharmacy', 'pos', 'insurance'],
            'mrn_prefix' => 'HOS',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */
    'roles' => [
        'super_admin' => 'Super Administrator',
        'branch_admin' => 'Doctor / Branch Administrator',
        'receptionist' => 'Receptionist',
        'nurse' => 'Nurse',
        'doctor' => 'Doctor',
        'optometrist' => 'Optometrist',
        'pharmacist' => 'Pharmacist',
        'cashier' => 'Cashier',
        'inventory_officer' => 'Inventory Officer',
        'records_officer' => 'Records Officer',
        'auditor' => 'Auditor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Encounter Configuration
    |--------------------------------------------------------------------------
    */
    'encounter' => [
        'opd_statuses' => [
            'registered',
            'triaged',
            'in_queue',
            'in_progress',
            'consultation_complete',
            'pre_auth_pending',
            'pre_auth_approved',
            'billed',
            'closed',
            'void',
        ],
        'eye_clinic_statuses' => [
            'registered',
            'in_progress',
            'consultation_complete',
            'pre_auth_pending',
            'pre_auth_approved',
            'billed',
            'closed',
            'void',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    */
    'payment' => [
        'methods' => ['cash', 'mpesa', 'card', 'insurance', 'waiver'],
        'statuses' => [
            'initiated', 'pending', 'confirmed',
            'failed', 'expired', 'cancelled', 'reversed',
        ],
        'currency' => 'KES',
        'currency_symbol' => 'KSh',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */
    'security' => [
        'max_failed_logins' => 5,
        'lockout_minutes' => 30,
        'session_lifetime' => 480,
        'password_min_length' => 10,
        'require_mfa_roles' => ['super_admin', 'branch_admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'retain_days' => 2555,
    ],

];
