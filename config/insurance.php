<?php

return [
    'roles' => [
        'SUPER_ADMIN',
        'BRANCH_MANAGER',
        'UNDERWRITER',
        'CLAIMS_OFFICER',
        'ACCOUNTANT',
        'BROKER',
        'CLIENT',
    ],

    'policy_statuses' => ['draft', 'active', 'renewed', 'cancelled', 'expired'],

    'claim_statuses' => [
        'registered',
        'under_investigation',
        'surveyor_assigned',
        'report_received',
        'approved',
        'rejected',
        'paid',
    ],

    'insurance_types' => [
        'health' => 'التأمين الصحي',
        'car' => 'تأمين السيارات',
        'fire' => 'تأمين الحريق',
        'marine' => 'التأمين البحري',
        'engineering' => 'التأمين الهندسي',
        'liability' => 'تأمين المسؤولية',
    ],
];
