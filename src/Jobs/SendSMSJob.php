<?php

namespace Ibraheem\AramexIntegration\Jobs;

use Ibraheem\AramexIntegration\Models\AramexShipment;
use Ibraheem\AramexIntegration\Services\SMSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $shipment;

    public function __construct(AramexShipment $shipment)
    {
        $this->shipment = $shipment;
    }

    /**
     * Execute the job.
     */
    public function handle(SMSService $smsService): void
    {
        try {
            $smsService->sendShipmentCreatedSMS($this->shipment);
        } catch (\Exception $e) {
            Log::error('SendSMSJob failed', [
                'shipment_id' => $this->shipment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

