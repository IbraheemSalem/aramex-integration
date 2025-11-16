<?php

namespace Ibraheem\AramexIntegration\Events;

use Ibraheem\AramexIntegration\Models\MerchantTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BillingCharged
{
    use Dispatchable, SerializesModels;

    public $transaction;

    public function __construct(MerchantTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}

