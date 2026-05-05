<?php

namespace App\Exports;

use App\Models\ReinsuranceDistribution;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReinsuranceBordereauxExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return ReinsuranceDistribution::query()
            ->with(['policy.client', 'treaty'])
            ->get()
            ->map(fn ($row) => [
                'policy_no' => $row->policy?->policy_no,
                'insured' => $row->policy?->client?->name,
                'type' => $row->policy?->type,
                'premium' => $row->policy_premium,
                'ri_share' => $row->ri_share_amount,
                'claims_recovered' => $row->claims_recovered,
                'reinsurer' => $row->treaty?->reinsurer,
            ]);
    }

    public function headings(): array
    {
        return ['policy_no', 'insured', 'type', 'premium', 'RI_share', 'claims_recovered', 'reinsurer'];
    }
}
