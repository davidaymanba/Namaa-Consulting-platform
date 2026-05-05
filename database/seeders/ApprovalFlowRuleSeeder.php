<?php

namespace Database\Seeders;

use App\Models\ApprovalFlowRule;
use Illuminate\Database\Seeder;

class ApprovalFlowRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['module' => 'policies', 'request_type' => 'premium_discount', 'required_role' => 'BRANCH_MANAGER', 'min_amount' => null, 'max_amount' => 4999.99, 'sla_hours' => 8, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'policies', 'request_type' => 'premium_discount', 'required_role' => 'SUPER_ADMIN', 'min_amount' => 5000, 'max_amount' => null, 'sla_hours' => 12, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'policies', 'request_type' => 'cancellation', 'required_role' => 'BRANCH_MANAGER', 'min_amount' => null, 'max_amount' => 20000, 'sla_hours' => 12, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'policies', 'request_type' => 'cancellation', 'required_role' => 'SUPER_ADMIN', 'min_amount' => 20000.01, 'max_amount' => null, 'sla_hours' => 24, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'claims', 'request_type' => 'large_claim_escalation', 'required_role' => 'BRANCH_MANAGER', 'min_amount' => null, 'max_amount' => 25000, 'sla_hours' => 6, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'claims', 'request_type' => 'large_claim_escalation', 'required_role' => 'SUPER_ADMIN', 'min_amount' => 25000.01, 'max_amount' => null, 'sla_hours' => 12, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'claims', 'request_type' => 'senior_claim_approval', 'required_role' => 'BRANCH_MANAGER', 'min_amount' => null, 'max_amount' => 10000, 'sla_hours' => 8, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'claims', 'request_type' => 'senior_claim_approval', 'required_role' => 'SUPER_ADMIN', 'min_amount' => 10000.01, 'max_amount' => null, 'sla_hours' => 12, 'total_stages' => 1, 'is_active' => true],
        ];

        foreach ($rules as $rule) {
            ApprovalFlowRule::updateOrCreate(
                [
                    'module' => $rule['module'],
                    'request_type' => $rule['request_type'],
                    'required_role' => $rule['required_role'],
                    'min_amount' => $rule['min_amount'],
                    'max_amount' => $rule['max_amount'],
                ],
                [
                    'sla_hours' => $rule['sla_hours'],
                    'total_stages' => $rule['total_stages'],
                    'is_active' => $rule['is_active'],
                ]
            );
        }
    }
}
