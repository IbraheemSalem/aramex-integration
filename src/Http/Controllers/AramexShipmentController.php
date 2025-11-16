<?php

namespace Ibraheem\AramexIntegration\Http\Controllers;

use Illuminate\Http\Request;
use Ibraheem\AramexIntegration\Http\Controllers\Controller;
use Ibraheem\AramexIntegration\Services\AramexService;
use Ibraheem\AramexIntegration\Jobs\ProcessShipment;
use Ibraheem\AramexIntegration\Models\AramexShipment;
use Ibraheem\AramexIntegration\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AramexShipmentController extends Controller
{
    use ApiResponseTrait;

    protected $aramexService;

    public function __construct(AramexService $aramexService)
    {
        $this->aramexService = $aramexService;
    }

    /**
     * Create shipment (queued).
     */
    public function createShipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'nullable|string',
            'receiver' => 'required|array',
            'receiver.name' => 'required|string',
            'receiver.phone' => 'required|string',
            'receiver.email' => 'required|email',
            'receiver.address' => 'required|array',
            'receiver.address.city' => 'required|string',
            'receiver.address.country_code' => 'required|string|size:2',
            'receiver.address.line1' => 'required|string',
            'receiver.address.postal_code' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
            'product_name' => 'required|string',
            'cod_amount' => 'nullable|numeric|min:0',
            'payment_type' => 'nullable|in:P,C,3',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 'ARAMEX_VALIDATION_01', 422, $validator->errors());
        }

        try {
            $merchantId = $request->input('merchant_id');
            
            // Dispatch job to queue
            ProcessShipment::dispatch($merchantId, $request->all());

            return $this->success([
                'message' => 'Shipment creation queued successfully',
                'merchant_id' => $merchantId,
            ], 'Shipment creation queued');
        } catch (\Exception $e) {
            Log::error('Error queuing shipment', [
                'merchant_id' => $request->input('merchant_id'),
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to queue shipment: ' . $e->getMessage(), 'ARAMEX_QUEUE_ERROR', 500);
        }
    }

    /**
     * Get shipments list.
     */
    public function getShipments(Request $request)
    {
        try {
            $merchantId = $request->input('merchant_id');
            $query = AramexShipment::where('merchant_id', $merchantId);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('tracking_number')) {
                $query->where('tracking_number', $request->tracking_number);
            }

            $shipments = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return $this->success($shipments, 'Shipments retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve shipments: ' . $e->getMessage(), 'ARAMEX_LIST_ERROR', 500);
        }
    }

    /**
     * Track shipment.
     */
    public function trackShipment($trackingNumber, Request $request)
    {
        try {
            $merchantId = $request->input('merchant_id');
            $result = $this->aramexService->trackShipment($merchantId, $trackingNumber);

            return $this->success($result, 'Tracking information retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 'ARAMEX_TRACK_ERROR', 500);
        }
    }
}

