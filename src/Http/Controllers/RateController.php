<?php

namespace Ibraheem\AramexIntegration\Http\Controllers;

use Illuminate\Http\Request;
use Ibraheem\AramexIntegration\Http\Controllers\Controller;
use Ibraheem\AramexIntegration\Services\AramexService;
use Ibraheem\AramexIntegration\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;

class RateController extends Controller
{
    use ApiResponseTrait;

    protected $aramexService;

    public function __construct(AramexService $aramexService)
    {
        $this->aramexService = $aramexService;
    }

    /**
     * Calculate shipping rate.
     */
    public function calculateRate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'required|array',
            'origin.line1' => 'required|string',
            'origin.city' => 'required|string',
            'origin.country_code' => 'required|string|size:2',
            'destination' => 'required|array',
            'destination.line1' => 'required|string',
            'destination.city' => 'required|string',
            'destination.country_code' => 'required|string|size:2',
            'weight' => 'required|numeric|min:0.1',
            'weight_unit' => 'nullable|in:KG,LB',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 'ARAMEX_VALIDATION_01', 422, $validator->errors());
        }

        try {
            $merchantId = $request->input('merchant_id');
            $result = $this->aramexService->calculateRate($merchantId, $request->all());

            return $this->success($result, 'Rate calculated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 'ARAMEX_RATE_ERROR', 500);
        }
    }
}

