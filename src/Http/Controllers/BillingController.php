<?php

namespace Ibraheem\AramexIntegration\Http\Controllers;

use Illuminate\Http\Request;
use Ibraheem\AramexIntegration\Http\Controllers\Controller;
use Ibraheem\AramexIntegration\Services\BillingService;
use Ibraheem\AramexIntegration\Models\MerchantBilling;
use Ibraheem\AramexIntegration\Models\MerchantTransaction;
use Ibraheem\AramexIntegration\Traits\ApiResponseTrait;

class BillingController extends Controller
{
    use ApiResponseTrait;

    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Get billing history.
     */
    public function getBillingHistory(Request $request)
    {
        $merchantId = $request->input('merchant_id');
        $billings = MerchantBilling::where('merchant_id', $merchantId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->success($billings, 'Billing history retrieved successfully');
    }

    /**
     * Get transactions.
     */
    public function getTransactions(Request $request)
    {
        $merchantId = $request->input('merchant_id');
        $transactions = MerchantTransaction::where('merchant_id', $merchantId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->success($transactions, 'Transactions retrieved successfully');
    }

    /**
     * Get free quota status.
     */
    public function getFreeQuota(Request $request)
    {
        $merchantId = $request->input('merchant_id');
        $remaining = $this->billingService->getRemainingFreeQuota($merchantId);
        $total = config('aramex.billing.free_shipment_quota');

        return $this->success([
            'total_quota' => $total,
            'remaining_quota' => $remaining,
            'used_quota' => $total - $remaining,
        ], 'Free quota retrieved successfully');
    }
}

