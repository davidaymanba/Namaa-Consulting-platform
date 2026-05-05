<?php

namespace App\Services;

use App\Models\JournalEntry;

class AccountingService
{
    public function onPolicyIssued(string $ref, float $amount): void
    {
        JournalEntry::create([
            'date' => now()->toDateString(),
            'debit_account' => '1100-Accounts Receivable',
            'credit_account' => '4100-Earned Premiums',
            'amount' => $amount,
            'reference' => $ref,
            'entry_type' => 'policy_issuance',
            'currency' => 'SAR',
        ]);
    }

    public function onPremiumCollected(string $ref, float $amount): void
    {
        JournalEntry::create([
            'date' => now()->toDateString(),
            'debit_account' => '1000-Cash/Bank',
            'credit_account' => '1100-Accounts Receivable',
            'amount' => $amount,
            'reference' => $ref,
            'entry_type' => 'premium_collection',
            'currency' => 'SAR',
        ]);
    }

    public function onClaimPaid(string $ref, float $amount): void
    {
        JournalEntry::create([
            'date' => now()->toDateString(),
            'debit_account' => '5100-Claims Expense',
            'credit_account' => '1000-Cash/Bank',
            'amount' => $amount,
            'reference' => $ref,
            'entry_type' => 'claim_payment',
            'currency' => 'SAR',
        ]);
    }
}
