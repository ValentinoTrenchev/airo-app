<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Models\Quotation;
use App\Services\QuotationCalculator;
use Illuminate\Http\JsonResponse;

class QuotationController extends Controller
{
    public function __construct(private readonly QuotationCalculator $calculator) {}

    public function store(StoreQuotationRequest $request): JsonResponse
    {
        $ages = array_map('intval', explode(',', $request->age));

        $total = $this->calculator->calculate($ages, $request->start_date, $request->end_date);

        $quotation = Quotation::create([
            'ages'        => $request->age,
            'currency_id' => $request->currency_id,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'total'       => $total,
        ]);

        return response()->json([
            'quotation_id' => $quotation->id,
            'total'        => $quotation->total,
            'currency_id'  => $quotation->currency_id,
        ], 201);
    }
}
