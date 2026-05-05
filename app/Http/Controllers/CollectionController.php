<?php

namespace App\Http\Controllers;

use App\Services\PaymentCollectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function __construct(private readonly PaymentCollectionService $service)
    {
    }

    public function aging(): JsonResponse
    {
        return response()->json([
            'buckets' => $this->service->agingBuckets(),
        ]);
    }

    public function scheduleReminders(): JsonResponse
    {
        $count = $this->service->scheduleReminders();

        return response()->json([
            'message' => 'Payment reminders scheduled.',
            'count' => $count,
        ]);
    }
}
