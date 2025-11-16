<?php

namespace Ibraheem\AramexIntegration\Http\Controllers;

use Illuminate\Http\Request;
use Ibraheem\AramexIntegration\Http\Controllers\Controller;
use Ibraheem\AramexIntegration\Models\AramexShipment;
use Ibraheem\AramexIntegration\Models\WebhookLog;
use Ibraheem\AramexIntegration\Events\ShipmentUpdated;
use Ibraheem\AramexIntegration\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    use ApiResponseTrait;

    /**
     * Handle webhook from Aramex.
     */
    public function handle(Request $request, $merchantId = null)
    {
        $merchantId = $merchantId ?? $request->input('merchant_id') ?? $request->header('X-Merchant-Id');

        // Log webhook
        $webhookLog = WebhookLog::create([
            'merchant_id' => $merchantId,
            'event_type' => $request->input('UpdateCode') ?? 'unknown',
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
            'ip_address' => $request->ip(),
            'processed' => false,
        ]);

        try {
            $trackingNumber = $request->input('WaybillNumber') 
                ?? $request->input('waybill_number')
                ?? $request->input('ShipmentNumber')
                ?? $request->input('shipment_number');

            if (!$trackingNumber) {
                throw new \Exception('Tracking number not found in webhook data');
            }

            $shipment = AramexShipment::where('tracking_number', $trackingNumber)
                ->where('merchant_id', $merchantId)
                ->first();

            if (!$shipment) {
                Log::warning('Webhook received for unknown shipment', [
                    'merchant_id' => $merchantId,
                    'tracking_number' => $trackingNumber,
                ]);

                $webhookLog->update([
                    'processed' => true,
                    'error_message' => 'Shipment not found',
                ]);

                return $this->error('Shipment not found', 'ARAMEX_WEBHOOK_SHIPMENT_NOT_FOUND', 404);
            }

            // Update shipment
            $oldStatus = $shipment->status;
            $shipment->update([
                'status' => $this->mapTrackingStatus($request->input('UpdateCode') ?? $request->input('Status') ?? $shipment->status),
                'webhook_data' => $request->all(),
                'tracking_data' => array_merge($shipment->tracking_data ?? [], $request->all()),
                'last_tracking_update' => now(),
            ]);

            // Update webhook log
            $webhookLog->update([
                'shipment_id' => $shipment->id,
                'processed' => true,
                'processed_at' => now(),
            ]);

            // Fire event if status changed
            if ($oldStatus !== $shipment->status) {
                event(new ShipmentUpdated($shipment, $oldStatus));
            }

            return $this->success($shipment, 'Webhook processed successfully');
        } catch (\Exception $e) {
            $webhookLog->update([
                'processed' => true,
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Webhook processing error', [
                'merchant_id' => $merchantId,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Webhook processing failed: ' . $e->getMessage(), 'ARAMEX_WEBHOOK_ERROR', 500);
        }
    }

    /**
     * Map tracking status.
     */
    protected function mapTrackingStatus($aramexStatus)
    {
        $statusMap = [
            'SH001' => 'created',
            'SH002' => 'picked_up',
            'SH003' => 'in_transit',
            'SH004' => 'out_for_delivery',
            'SH005' => 'delivered',
            'SH006' => 'exception',
            'SH007' => 'returned',
            'SH008' => 'cancelled',
        ];

        return $statusMap[$aramexStatus] ?? strtolower($aramexStatus);
    }
}

