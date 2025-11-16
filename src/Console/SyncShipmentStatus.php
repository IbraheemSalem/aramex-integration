<?php

namespace Ibraheem\AramexIntegration\Console;

use Illuminate\Console\Command;
use Ibraheem\AramexIntegration\Models\AramexShipment;
use Ibraheem\AramexIntegration\Services\AramexService;
use Illuminate\Support\Facades\Log;

class SyncShipmentStatus extends Command
{
    protected $signature = 'aramex:sync-status 
                            {--merchant-id= : Sync for specific merchant}
                            {--limit=100 : Number of shipments to sync}';

    protected $description = 'Sync shipment statuses from Aramex API';

    public function handle(AramexService $aramexService)
    {
        $this->info('Starting shipment status sync...');

        $query = AramexShipment::where('status', '!=', 'delivered')
            ->where('status', '!=', 'cancelled');

        if ($merchantId = $this->option('merchant-id')) {
            $query->where('merchant_id', $merchantId);
        }

        $shipments = $query->limit($this->option('limit'))->get();
        $synced = 0;
        $failed = 0;

        foreach ($shipments as $shipment) {
            try {
                $aramexService->trackShipment($shipment->merchant_id, $shipment->tracking_number);
                $synced++;
                $this->info("Synced: {$shipment->tracking_number}");
            } catch (\Exception $e) {
                $failed++;
                $this->error("Failed: {$shipment->tracking_number} - {$e->getMessage()}");
                Log::error('Sync shipment status failed', [
                    'shipment_id' => $shipment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sync completed. Synced: {$synced}, Failed: {$failed}");
    }
}

