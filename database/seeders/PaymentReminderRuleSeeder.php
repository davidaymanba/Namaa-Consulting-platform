<?php

namespace Database\Seeders;

use App\Models\PaymentReminderRule;
use Illuminate\Database\Seeder;

class PaymentReminderRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['code' => 'email_7_before', 'label' => 'Email reminder 7 days before due date', 'config' => ['channel' => 'email', 'days_offset' => 7], 'is_active' => true],
            ['code' => 'sms_due_day', 'label' => 'SMS reminder on due date', 'config' => ['channel' => 'sms', 'days_offset' => 0], 'is_active' => true],
            ['code' => 'whatsapp_7_overdue', 'label' => 'WhatsApp reminder 7 days overdue', 'config' => ['channel' => 'whatsapp', 'days_offset' => -7], 'is_active' => true],
            ['code' => 'email_30_overdue', 'label' => 'Email reminder 30 days overdue', 'config' => ['channel' => 'email', 'days_offset' => -30], 'is_active' => true],
        ];

        foreach ($rules as $rule) {
            PaymentReminderRule::updateOrCreate(
                ['code' => $rule['code']],
                $rule
            );
        }
    }
}
