<?php

namespace Ibraheem\AramexIntegration\Jobs;

use Ibraheem\AramexIntegration\Services\AramexService;
use Ibraheem\AramexIntegration\Events\ShipmentCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessShipment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $merchantId;
    public $data;

    public function __construct($merchantId, array $data)
    {
        $this->merchantId = $merchantId;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(AramexService $aramexService): void
    {
        try {
            $result = $aramexService->createShipment($this->merchantId, $this->data);

            if ($result['success'] && isset($result['shipment'])) {
                event(new ShipmentCreated($result['shipment']));
            }
        } catch (\Exception $e) {
            Log::error('ProcessShipment job failed', [
                'merchant_id' => $this->merchantId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

