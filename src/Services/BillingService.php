<?php

namespace Ibraheem\AramexIntegration\Services;

use Ibraheem\AramexIntegration\Models\MerchantBilling;
use Ibraheem\AramexIntegration\Models\MerchantTransaction;
use Ibraheem\AramexIntegration\Models\AramexShipment;
use Illuminate\Support\Facades\Log;

class BillingService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('aramex.billing');
    }

    /**
     * Charge merchant for shipment creation.
     */
    public function chargeForShipment($merchantId, $shipmentId)
    {
        try {
            $shipment = AramexShipment::find($shipmentId);
            if (!$shipment || $shipment->merchant_id != $merchantId) {
                throw new \Exception('Shipment not found', 404);
            }

            // Check if free quota available
            $freeQuota = $this->getRemainingFreeQuota($merchantId);
            $shouldCharge = $freeQuota <= 0;

            if ($shouldCharge) {
                $amount = $this->config['per_shipment_fee'];
                $currency = $this->config['currency'];

                // Create transaction
                $transaction = MerchantTransaction::create([
                    'merchant_id' => $merchantId,
                    'shipment_id' => $shipmentId,
                    'transaction_type' => 'shipment_fee',
                    'amount' => $amount,
                    'currency' => $currency,
                    'description' => "Shipment fee for #{$shipment->tracking_number}",
                    'status' => 'pending',
                    'metadata' => [
                        'shipment_number' => $shipment->tracking_number,
                        'free_quota_used' => false,
                    ],
                ]);

                // Update shipment with rate
                $shipment->update([
                    'rate_amount' => $amount,
                ]);

                Log::info('Charged merchant for shipment', [
                    'merchant_id' => $merchantId,
                    'shipment_id' => $shipmentId,
                    'amount' => $amount,
                ]);

                return $transaction;
            } else {
                // Record as free shipment
                MerchantTransaction::create([
                    'merchant_id' => $merchantId,
                    'shipment_id' => $shipmentId,
                    'transaction_type' => 'shipment_fee',
                    'amount' => 0,
                    'currency' => $this->config['currency'],
                    'description' => "Free shipment quota used for #{$shipment->tracking_number}",
                    'status' => 'completed',
                    'metadata' => [
                        'shipment_number' => $shipment->tracking_number,
                        'free_quota_used' => true,
                    ],
                ]);

                Log::info('Free shipment quota used', [
                    'merchant_id' => $merchantId,
                    'shipment_id' => $shipmentId,
                    'remaining_quota' => $freeQuota - 1,
                ]);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to charge for shipment', [
                'merchant_id' => $merchantId,
                'shipment_id' => $shipmentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get remaining free quota for merchant.
     */
    public function getRemainingFreeQuota($merchantId)
    {
        $freeQuota = $this->config['free_shipment_quota'];
        $usedQuota = MerchantTransaction::where('merchant_id', $merchantId)
            ->where('transaction_type', 'shipment_fee')
            ->where('amount', 0)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return max(0, $freeQuota - $usedQuota);
    }

    /**
     * Generate monthly billing.
     */
    public function generateMonthlyBilling($merchantId, $month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        // Check if billing already exists
        $existingBilling = MerchantBilling::where('merchant_id', $merchantId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existingBilling) {
            return $existingBilling;
        }

        // Calculate shipments
        $totalShipments = AramexShipment::where('merchant_id', $merchantId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();

        $freeShipmentsUsed = MerchantTransaction::where('merchant_id', $merchantId)
            ->where('transaction_type', 'shipment_fee')
            ->where('amount', 0)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();

        $paidShipments = $totalShipments - $freeShipmentsUsed;

        // Calculate amounts
        $monthlyFee = $this->config['monthly_subscription_fee'];
        $perShipmentFee = $this->config['per_shipment_fee'];
        $shipmentsTotal = $paidShipments * $perShipmentFee;
        $totalAmount = $monthlyFee + $shipmentsTotal;

        // Create billing record
        $billing = MerchantBilling::create([
            'merchant_id' => $merchantId,
            'billing_period' => sprintf('%04d-%02d', $year, $month),
            'month' => $month,
            'year' => $year,
            'monthly_subscription_fee' => $monthlyFee,
            'total_shipments' => $totalShipments,
            'free_shipments_used' => $freeShipmentsUsed,
            'paid_shipments' => $paidShipments,
            'per_shipment_fee' => $perShipmentFee,
            'total_amount' => $totalAmount,
            'currency' => $this->config['currency'],
            'status' => 'pending',
        ]);

        Log::info('Monthly billing generated', [
            'merchant_id' => $merchantId,
            'billing_id' => $billing->id,
            'total_amount' => $totalAmount,
        ]);

        return $billing;
    }

    /**
     * Mark billing as paid.
     */
    public function markBillingAsPaid($billingId, $paidAt = null)
    {
        $billing = MerchantBilling::findOrFail($billingId);
        
        $billing->update([
            'status' => 'paid',
            'paid_at' => $paidAt ?? now(),
        ]);

        // Update all related transactions
        MerchantTransaction::where('billing_id', $billingId)
            ->where('status', 'pending')
            ->update(['status' => 'completed']);

        return $billing;
    }
}

