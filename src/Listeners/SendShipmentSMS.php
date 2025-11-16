<?php

namespace Ibraheem\AramexIntegration\Listeners;

use Ibraheem\AramexIntegration\Events\ShipmentCreated;
use Ibraheem\AramexIntegration\Jobs\SendSMSJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendShipmentSMS implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ShipmentCreated $event): void
    {
        SendSMSJob::dispatch($event->shipment);
    }
}

