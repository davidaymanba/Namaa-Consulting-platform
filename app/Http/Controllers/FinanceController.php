<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(): View
    {
        $entries = JournalEntry::query()->latest('date')->limit(100)->get();

        $income = (float) JournalEntry::query()->where('credit_account', 'like', '41%')->sum('amount');
        $claimsExpense = (float) JournalEntry::query()->where('debit_account', 'like', '51%')->sum('amount');
        $cashMovement = (float) JournalEntry::query()->where('debit_account', 'like', '1000%')->sum('amount')
            - (float) JournalEntry::query()->where('credit_account', 'like', '1000%')->sum('amount');

        $balances = JournalEntry::query()
            ->select('debit_account as account', DB::raw('SUM(amount) as total'))
            ->groupBy('debit_account')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('finance.index', compact('entries', 'income', 'claimsExpense', 'cashMovement', 'balances'));
    }
}
