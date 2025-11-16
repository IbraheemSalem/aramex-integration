<?php

namespace Ibraheem\AramexIntegration\Events;

use Ibraheem\AramexIntegration\Models\AramexShipment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipmentCreated
{
    use Dispatchable, SerializesModels;

    public $shipment;

    public function __construct(AramexShipment $shipment)
    {
        $this->shipment = $shipment;
    }
}

