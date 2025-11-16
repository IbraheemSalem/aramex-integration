<?php

namespace Ibraheem\AramexIntegration\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Ibraheem\AramexIntegration\Services\AramexService createShipment(array $data)
 * @method static \Ibraheem\AramexIntegration\Services\AramexService trackShipment(string $trackingNumber)
 * @method static \Ibraheem\AramexIntegration\Services\AramexService calculateRate(array $data)
 * 
 * @see \Ibraheem\AramexIntegration\Services\AramexService
 */
class AramexIntegration extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'aramex';
    }
}

