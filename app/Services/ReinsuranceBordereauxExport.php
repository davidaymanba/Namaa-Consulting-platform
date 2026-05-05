<?php

namespace App\Exports;

use App\Models\ReinsuranceDistribution;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReinsuranceBordereauxExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly ?string $startDate = null, private readonly ?string $endDate = null)
    {
    }

    public function collection()
    {
        $query = ReinsuranceDistribution::query()
            ->with(['policy.client', 'treaty']);

        if ($this->startDate && $this->endDate) {
            $query->whereHas('policy', fn ($policyQuery) => $policyQuery->whereBetween('issued_at', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()]));
        }

        return $query->get()->map(fn ($row) => [
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
