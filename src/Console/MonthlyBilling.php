<?php

namespace Ibraheem\AramexIntegration\Console;

use Illuminate\Console\Command;
use Ibraheem\AramexIntegration\Models\MerchantAramexAccount;
use Ibraheem\AramexIntegration\Services\BillingService;
use Illuminate\Support\Facades\Log;

class MonthlyBilling extends Command
{
    protected $signature = 'aramex:monthly-billing 
                            {--merchant-id= : Generate billing for specific merchant}
                            {--month= : Month (1-12)}
                            {--year= : Year (YYYY)}';

    protected $description = 'Generate monthly billing for all merchants';

    public function handle(BillingService $billingService)
    {
        $this->info('Starting monthly billing generation...');

        $month = $this->option('month') ?? now()->month;
        $year = $this->option('year') ?? now()->year;

        $query = MerchantAramexAccount::where('is_active', true);

        if ($merchantId = $this->option('merchant-id')) {
            $query->where('merchant_id', $merchantId);
        }

        $accounts = $query->get();
        $generated = 0;
        $failed = 0;

        foreach ($accounts as $account) {
            try {
                $billing = $billingService->generateMonthlyBilling($account->merchant_id, $month, $year);
                $generated++;
                $this->info("Generated billing for merchant: {$account->merchant_id} - Amount: {$billing->total_amount}");
            } catch (\Exception $e) {
                $failed++;
                $this->error("Failed for merchant: {$account->merchant_id} - {$e->getMessage()}");
                Log::error('Monthly billing generation failed', [
                    'merchant_id' => $account->merchant_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Billing generation completed. Generated: {$generated}, Failed: {$failed}");
    }
}

