<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Broker;
use App\Models\ChartAccount;
use App\Models\Claim;
use App\Models\Client;
use App\Models\ApprovalFlowRule;
use App\Models\JournalEntry;
use App\Models\Policy;
use App\Models\PolicyInstallment;
use App\Models\ReinsuranceTreaty;
use App\Models\TariffRate;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoreInsuranceSeeder extends Seeder
{
    public function run(): void
    {
        $approvalRules = [
            ['module' => 'policies', 'request_type' => 'premium_discount', 'required_role' => 'BRANCH_MANAGER', 'min_amount' => null, 'max_amount' => 4999.99, 'sla_hours' => 8, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'policies', 'request_type' => 'premium_discount', 'required_role' => 'SUPER_ADMIN', 'min_amount' => 5000, 'max_amount' => null, 'sla_hours' => 12, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'policies', 'request_type' => 'cancellation', 'required_role' => 'BRANCH_MANAGER', 'min_amount' => null, 'max_amount' => 20000, 'sla_hours' => 12, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'policies', 'request_type' => 'cancellation', 'required_role' => 'SUPER_ADMIN', 'min_amount' => 20000.01, 'max_amount' => null, 'sla_hours' => 24, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'claims', 'request_type' => 'large_claim_escalation', 'required_role' => 'BRANCH_MANAGER', 'min_amount' => null, 'max_amount' => 25000, 'sla_hours' => 6, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'claims', 'request_type' => 'large_claim_escalation', 'required_role' => 'SUPER_ADMIN', 'min_amount' => 25000.01, 'max_amount' => null, 'sla_hours' => 12, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'claims', 'request_type' => 'senior_claim_approval', 'required_role' => 'BRANCH_MANAGER', 'min_amount' => null, 'max_amount' => 10000, 'sla_hours' => 8, 'total_stages' => 1, 'is_active' => true],
            ['module' => 'claims', 'request_type' => 'senior_claim_approval', 'required_role' => 'SUPER_ADMIN', 'min_amount' => 10000.01, 'max_amount' => null, 'sla_hours' => 12, 'total_stages' => 1, 'is_active' => true],
        ];

        foreach ($approvalRules as $rule) {
        if (Policy::query()->exists()) {
            return;
        }

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

        $amman = Branch::create(['name' => 'فرع الرياض', 'code' => 'RUH', 'city' => 'Riyadh']);
        $irbid = Branch::create(['name' => 'فرع جدة', 'code' => 'JED', 'city' => 'Jeddah']);

        $brokers = collect([
            ['name' => 'وسيط الأمان للتأمين', 'code' => 'BRK-101', 'contact' => '+966500001111', 'email' => 'alaman@broker.sa'],
            ['name' => 'مركز الوسيط السعودي', 'code' => 'BRK-102', 'contact' => '+966500002222', 'email' => 'saudibroker@broker.sa'],
            ['name' => 'التميز لخدمات التأمين', 'code' => 'BRK-103', 'contact' => '+966500003333', 'email' => 'tamayoz@broker.sa'],
        ])->map(fn ($broker) => Broker::create([
            ...$broker,
            'commission_rates' => [
                'health' => 10,
                'car' => 8,
                'fire' => 12,
                'marine' => 14,
                'engineering' => 11,
                'liability' => 9,
            ],
        ]));

        $clients = collect([
            ['name' => 'شركة النخبة للتجارة', 'type' => 'company', 'national_id' => '20001234567', 'contact' => '+96656001001', 'address' => 'الرياض - الشميسي'],
            ['name' => 'محمد خالد العياصرة', 'type' => 'individual', 'national_id' => '9876543210', 'contact' => '+96655002002', 'address' => 'جدة - الحي الشرقي'],
            ['name' => 'شركة الميناء للشحن', 'type' => 'company', 'national_id' => '20007654321', 'contact' => '+96654003003', 'address' => 'الدمام - المنطقة الصناعية'],
            ['name' => 'ليان سامر الخطيب', 'type' => 'individual', 'national_id' => '2233445566', 'contact' => '+96657004004', 'address' => 'الرياض - العليا'],
            ['name' => 'مقاولات البناء الحديث', 'type' => 'company', 'national_id' => '20006543219', 'contact' => '+96659005005', 'address' => 'الخبر - الوسط التجاري'],
            ['name' => 'أحمد يونس الشوابكة', 'type' => 'individual', 'national_id' => '1122334455', 'contact' => '+96656006006', 'address' => 'المدينة المنورة - وسط البلد'],
            ['name' => 'مصنع البتراء للصناعات', 'type' => 'company', 'national_id' => '20002221111', 'contact' => '+96650007007', 'address' => 'مكة - المنطقة الصناعية'],
            ['name' => 'ديما نزار الزعبي', 'type' => 'individual', 'national_id' => '5544332211', 'contact' => '+96653008008', 'address' => 'أبها - الحي الجنوبي'],
        ])->map(fn ($client) => Client::create($client + ['meta' => ['country' => 'Saudi Arabia']]));

        $roleUsers = [
            ['name' => 'مدير النظام', 'email' => 'admin@namaa.sa', 'role' => 'SUPER_ADMIN', 'branch_id' => $amman->id, 'permissions' => null],
            ['name' => 'مدير فرع الرياض', 'email' => 'branch.manager@namaa.sa', 'role' => 'BRANCH_MANAGER', 'branch_id' => $amman->id, 'permissions' => null],
            ['name' => 'مكتتب رئيسي', 'email' => 'underwriter@namaa.sa', 'role' => 'UNDERWRITER', 'branch_id' => $amman->id, 'permissions' => null],
            ['name' => 'مسؤول مطالبات', 'email' => 'claims@namaa.sa', 'role' => 'CLAIMS_OFFICER', 'branch_id' => $irbid->id, 'permissions' => null],
            ['name' => 'محاسب النظام', 'email' => 'accountant@namaa.sa', 'role' => 'ACCOUNTANT', 'branch_id' => $amman->id, 'permissions' => null],
            ['name' => 'وسيط الأمان - مستخدم', 'email' => 'broker@namaa.sa', 'role' => 'BROKER', 'branch_id' => $amman->id, 'permissions' => ['broker_id' => $brokers[0]->id]],
            ['name' => 'عميل تجريبي', 'email' => 'client@namaa.sa', 'role' => 'CLIENT', 'branch_id' => $amman->id, 'permissions' => ['client_id' => $clients[0]->id]],
        ];

        foreach ($roleUsers as $roleUser) {
            User::updateOrCreate(
                ['email' => $roleUser['email']],
                [
                    'name' => $roleUser['name'],
                    'password' => Hash::make('Password@123'),
                    'role' => $roleUser['role'],
                    'branch_id' => $roleUser['branch_id'],
                    'permissions' => $roleUser['permissions'],
                    'email_verified_at' => now(),
                    'locale' => 'ar',
                    'theme' => 'light',
                ]
            );
        }

        TariffRate::insert([
            ['insurance_type' => 'health', 'base_rate' => 0.0180, 'min_premium' => 250, 'rules' => json_encode(['age_loading' => true]), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['insurance_type' => 'car', 'base_rate' => 0.0250, 'min_premium' => 120, 'rules' => json_encode(['young_driver_loading' => 0.75]), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['insurance_type' => 'fire', 'base_rate' => 0.0150, 'min_premium' => 300, 'rules' => json_encode(['sprinklers_discount' => 0.10]), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['insurance_type' => 'marine', 'base_rate' => 0.0110, 'min_premium' => 400, 'rules' => json_encode(['packing_loading' => 0.08]), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['insurance_type' => 'engineering', 'base_rate' => 0.0130, 'min_premium' => 500, 'rules' => json_encode(['project_type_factor' => true]), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['insurance_type' => 'liability', 'base_rate' => 0.0090, 'min_premium' => 220, 'rules' => json_encode(['limit_based' => true]), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        ReinsuranceTreaty::create([
            'reinsurer' => 'Saudi Re',
            'type' => 'quota_share',
            'share_pct' => 25,
            'retention' => 5000,
            'effective_from' => now()->subYear()->toDateString(),
            'effective_to' => null,
            'is_active' => true,
        ]);

        ReinsuranceTreaty::create([
            'reinsurer' => 'Arab Re',
            'type' => 'excess_of_loss',
            'share_pct' => 10,
            'retention' => 10000,
            'effective_from' => now()->subYear()->toDateString(),
            'effective_to' => null,
            'is_active' => true,
        ]);

        $accounts = [
            ['code' => '1000', 'name' => 'Cash / Bank', 'category' => 'asset'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'category' => 'asset'],
            ['code' => '1200', 'name' => 'Investments', 'category' => 'asset'],
            ['code' => '2100', 'name' => 'Unearned Premiums', 'category' => 'liability'],
            ['code' => '2200', 'name' => 'Claims Reserve', 'category' => 'liability'],
            ['code' => '3100', 'name' => 'Owner Equity', 'category' => 'equity'],
            ['code' => '4100', 'name' => 'Earned Premiums', 'category' => 'revenue'],
            ['code' => '4200', 'name' => 'Commission Income', 'category' => 'revenue'],
            ['code' => '5100', 'name' => 'Claims Expense', 'category' => 'expense'],
            ['code' => '5200', 'name' => 'Commission Paid', 'category' => 'expense'],
            ['code' => '5300', 'name' => 'Operations Expense', 'category' => 'expense'],
        ];

        foreach ($accounts as $account) {
            ChartAccount::create($account + ['currency' => 'SAR']);
        }

        $policyTypes = ['health', 'car', 'fire', 'marine', 'engineering', 'liability'];
        $statuses = ['active', 'active', 'active', 'renewed', 'expired'];

        for ($i = 0; $i < 60; $i++) {
            $type = $policyTypes[$i % count($policyTypes)];
            $client = $clients[$i % $clients->count()];
            $broker = $brokers[$i % $brokers->count()];
            $issuedAt = now()->subMonths(rand(0, 11))->subDays(rand(1, 27));
            $netPremium = rand(180, 6500);
            $status = $statuses[$i % count($statuses)];
            $branch = $i % 2 === 0 ? $amman : $irbid;

            $policy = Policy::create([
                'policy_no' => 'POL-'.str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                'client_id' => $client->id,
                'broker_id' => $broker->id,
                'branch_id' => $branch->id,
                'issued_by' => 1,
                'type' => $type,
                'status' => $status,
                'premium' => $netPremium,
                'discount_pct' => rand(0, 12),
                'loading_pct' => rand(0, 10),
                'net_premium' => $netPremium,
                'start_date' => $issuedAt->copy()->toDateString(),
                'end_date' => $issuedAt->copy()->addYear()->toDateString(),
                'issued_at' => $issuedAt,
                'data' => $type === 'car' ? [
                    'plate_number' => 'SA-'.rand(10000, 99999),
                    'make' => ['Toyota', 'Kia', 'Hyundai', 'Nissan'][rand(0, 3)],
                    'model' => ['Corolla', 'Sportage', 'Elantra', 'Sunny'][rand(0, 3)],
                    'year' => rand(2015, 2025),
                    'market_value' => rand(7000, 35000),
                    'engine_cc' => rand(1200, 3000),
                    'chassis_number' => 'CH'.rand(100000, 999999),
                    'driver_name' => 'سائق '.($i + 1),
                    'driver_age' => rand(21, 55),
                    'license_type' => 'خصوصي',
                    'insurance_type' => rand(0, 1) ? 'mandatory' : 'comprehensive',
                    'installment_mode' => ['monthly', 'quarterly', 'annual'][rand(0, 2)],
                    'base_rate' => 0.025,
                ] : ['generic' => true],
            ]);

            for ($ins = 1; $ins <= 4; $ins++) {
                PolicyInstallment::create([
                    'policy_id' => $policy->id,
                    'sequence' => $ins,
                    'due_date' => $policy->start_date->copy()->addMonths(($ins - 1) * 3),
                    'amount' => round($netPremium / 4, 2),
                    'status' => $ins <= 2 ? 'paid' : 'pending',
                    'paid_at' => $ins <= 2 ? now()->subDays(rand(1, 60)) : null,
                ]);
            }

            Transaction::create([
                'transaction_no' => 'TXN-SD-'.str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                'policy_id' => $policy->id,
                'type' => 'policy_issue',
                'status' => 'completed',
                'amount' => $netPremium,
                'currency' => 'SAR',
                'transaction_date' => $issuedAt,
            ]);

            JournalEntry::create([
                'date' => $issuedAt->toDateString(),
                'debit_account' => '1100-Accounts Receivable',
                'credit_account' => '4100-Earned Premiums',
                'amount' => $netPremium,
                'currency' => 'SAR',
                'reference' => $policy->policy_no,
                'entry_type' => 'policy_issuance',
            ]);

            if ($i % 3 === 0) {
                $claimAmount = rand(200, 12000);
                $claim = Claim::create([
                    'claim_no' => 'CLM-'.str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                    'policy_id' => $policy->id,
                    'status' => ['registered', 'under_investigation', 'report_received', 'approved', 'paid'][rand(0, 4)],
                    'incident_date' => $issuedAt->copy()->addDays(rand(20, 220))->toDateString(),
                    'report_date' => $issuedAt->copy()->addDays(rand(21, 225))->toDateString(),
                    'description' => 'مطالبة تجريبية ناتجة عن حادث مؤمّن عليه',
                    'claimed_amount' => $claimAmount,
                    'estimated_loss' => round($claimAmount * 0.85, 2),
                    'approved_amount' => round($claimAmount * 0.70, 2),
                    'paid_amount' => round($claimAmount * 0.55, 2),
                    'is_large_claim' => $claimAmount > 10000,
                    'escalated_at' => $claimAmount > 10000 ? now()->subDays(rand(1, 15)) : null,
                    'last_status_update_at' => now()->subDays(rand(1, 40)),
                    'workflow_notes' => [['status' => 'registered', 'at' => now()->subDays(10)->toDateTimeString(), 'by' => 2]],
                ]);

                Transaction::create([
                    'transaction_no' => 'TXN-CLM-'.str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                    'policy_id' => $policy->id,
                    'claim_id' => $claim->id,
                    'type' => 'claim_payment',
                    'status' => $claim->status === 'paid' ? 'completed' : 'pending',
                    'amount' => $claim->paid_amount,
                    'currency' => 'SAR',
                    'transaction_date' => $claim->created_at,
                ]);
            }
        }
    }
}
