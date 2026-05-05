<?php

namespace App\Http\Controllers;

use App\Exports\OperationalReportExport;
use App\Models\Broker;
use App\Models\Claim;
use App\Models\Policy;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->endOfMonth()->toDateString()))->endOfDay();

        $production = Policy::query()
            ->selectRaw('type, COUNT(*) as policies_count, SUM(net_premium) as premium_total')
            ->whereBetween('issued_at', [$from, $to])
            ->groupBy('type')
            ->get();

        $claims = Claim::query()
            ->selectRaw('status, COUNT(*) as claims_count, SUM(approved_amount) as approved_total')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('status')
            ->get();

        $brokers = Broker::query()
            ->withSum(['policies as premium_total' => fn ($q) => $q->whereBetween('issued_at', [$from, $to])], 'net_premium')
            ->withCount(['policies as production_count' => fn ($q) => $q->whereBetween('issued_at', [$from, $to])])
            ->orderByDesc('premium_total')
            ->limit(10)
            ->get();

        $premiumsTotal = (float) $production->sum('premium_total');
        $claimsTotal = (float) $claims->sum('approved_total');
        $lossRatio = $premiumsTotal > 0 ? round(($claimsTotal / $premiumsTotal) * 100, 2) : 0;

        return view('reports.index', compact('production', 'claims', 'brokers', 'from', 'to', 'premiumsTotal', 'claimsTotal', 'lossRatio'));
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        return Excel::download(new OperationalReportExport($request), 'operational_report_'.now()->format('Ymd_His').'.xlsx');
    }

    public function exportPdf(Request $request): Response|BinaryFileResponse
    {
        $viewData = $this->index($request)->getData();
        $pdf = Pdf::loadView('reports.pdf', (array) $viewData);

        return $pdf->download('operational_report_'.now()->format('Ymd_His').'.pdf');
    }
}
