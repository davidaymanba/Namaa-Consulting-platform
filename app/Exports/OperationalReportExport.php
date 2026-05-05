<?php

namespace App\Exports;

use App\Models\Claim;
use App\Models\Policy;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;

class OperationalReportExport implements FromArray
{
    public function __construct(private readonly Request $request)
    {
    }

    public function array(): array
    {
        $from = $this->request->date('from', now()->startOfMonth());
        $to = $this->request->date('to', now()->endOfMonth());

        $rows = [['Operational Report', ''], ['From', $from->toDateString()], ['To', $to->toDateString()], ['Type', 'Policies', 'Premiums']];

        $production = Policy::query()
            ->selectRaw('type, COUNT(*) as policies_count, SUM(net_premium) as premium_total')
            ->whereBetween('issued_at', [$from, $to])
            ->groupBy('type')
            ->get();

        foreach ($production as $item) {
            $rows[] = [$item->type, $item->policies_count, (float) $item->premium_total];
        }

        $rows[] = [];
        $rows[] = ['Claim Status', 'Claims', 'Approved Amount'];

        $claims = Claim::query()
            ->selectRaw('status, COUNT(*) as claims_count, SUM(approved_amount) as approved_total')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('status')
            ->get();

        foreach ($claims as $item) {
            $rows[] = [$item->status, $item->claims_count, (float) $item->approved_total];
        }

        return $rows;
    }
}
