<?php

namespace App\Observers;

use App\Services\CacheService;
use App\Services\ExcelSyncService;
use App\Models\Sale;
use Illuminate\Support\Facades\Log;

class SaleExcelObserver
{
    public function __construct(private readonly ExcelSyncService $excelSync) {}

    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        $this->bustCaches($sale);
        $this->sync($sale, 'created');
    }

    /**
     * Handle the Sale "updated" event.
     */
    public function updated(Sale $sale): void
    {
        $this->bustCaches($sale);
        $this->sync($sale, 'updated');
    }

    /**
     * Handle the Sale "deleted" event.
     */
    public function deleted(Sale $sale): void
    {
        $this->bustCaches($sale);

        try {
            $this->excelSync->removeSale($sale->id);
        } catch (\Throwable $e) {
            Log::error("ExcelSync: failed to remove Sale #{$sale->id} from Excel: ".$e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function bustCaches(Sale $sale): void
    {
        // Bust the dashboard/reports cache for the user who created this sale
        CacheService::invalidateUser($sale->user_id);

        // Also bust for customer if different
        if ($sale->customer_id && $sale->customer_id !== $sale->user_id) {
            CacheService::invalidateUser($sale->customer_id);
        }
    }

    private function sync(Sale $sale, string $event): void
    {
        try {
            $this->excelSync->syncSale($sale);
        } catch (\Throwable $e) {
            Log::error("ExcelSync: failed to sync Sale #{$sale->id} on {$event}: ".$e->getMessage());
        }
    }
}
