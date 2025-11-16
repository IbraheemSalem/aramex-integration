<?php

namespace Ibraheem\AramexIntegration\Listeners;

use Ibraheem\AramexIntegration\Events\ShipmentCreated;
use Ibraheem\AramexIntegration\Services\BillingService;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecordBillingTransaction implements ShouldQueue
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Handle the event.
     */
    public function handle(ShipmentCreated $event): void
    {
        $this->billingService->chargeForShipment(
            $event->shipment->merchant_id,
            $event->shipment->id
        );
    }
}

