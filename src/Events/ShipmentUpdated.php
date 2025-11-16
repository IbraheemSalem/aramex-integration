<?php

namespace Ibraheem\AramexIntegration\Events;

use Ibraheem\AramexIntegration\Models\AramexShipment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipmentUpdated
{
    use Dispatchable, SerializesModels;

    public $shipment;
    public $oldStatus;

    public function __construct(AramexShipment $shipment, $oldStatus)
    {
        $this->shipment = $shipment;
        $this->oldStatus = $oldStatus;
    }
}

