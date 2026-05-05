<?php

namespace App\Http\Controllers;

use App\Exports\ReinsuranceBordereauxExport;
use App\Models\ReinsuranceAlert;
use App\Models\ReinsuranceDistribution;
use App\Services\ReinsuranceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReinsuranceController extends Controller
{
    public function __construct(private readonly ReinsuranceService $service)
    {
    }

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

    public function alerts(): JsonResponse
    {
        return response()->json($this->service->alerts());
    }

    public function generateBordereaux(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $batch = $this->service->generateBordereauxBatch(
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null,
        );

        return response()->json([
            'message' => 'Bordereaux batch generated.',
            'data' => $batch,
        ], 201);
    }

    public function resolveAlert(Request $request, ReinsuranceAlert $alert): JsonResponse
    {
        $updated = $this->service->resolveAlert($alert, (int) $request->user()->id);

        return response()->json([
            'message' => 'Alert resolved.',
            'data' => $updated,
        ]);
    }
}
