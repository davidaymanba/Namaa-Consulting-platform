<?php

namespace App\Http\Controllers;

use App\Exports\ReinsuranceBordereauxExport;
use App\Models\ReinsuranceDistribution;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReinsuranceController extends Controller
{
    public function index(): View
    {
        $rows = ReinsuranceDistribution::query()
            ->with(['policy.client', 'treaty'])
            ->latest()
            ->paginate(20);

        return view('reinsurance.index', compact('rows'));
    }

    public function exportMonthly(): BinaryFileResponse
    {
        $name = 'bordereaux_'.now()->format('Y_m').'.xlsx';

        return Excel::download(new ReinsuranceBordereauxExport(), $name);
    }
}
