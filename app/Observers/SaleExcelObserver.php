<?php

namespace App\Observers;

use App\Models\Sale;
use App\Services\ExcelSyncService;
use Illuminate\Support\Facades\Log;

class SaleExcelObserver
{
    public function __construct(private readonly ExcelSyncService $excelSync) {}

    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        $this->sync($sale, 'created');
    }

    /**
     * Handle the Sale "updated" event.
     */
    public function updated(Sale $sale): void
    {
        $this->sync($sale, 'updated');
    }

    /**
     * Handle the Sale "deleted" event.
     */
    public function deleted(Sale $sale): void
    {
        try {
            $this->excelSync->removeSale($sale->id);
        } catch (\Throwable $e) {
            Log::error("ExcelSync: failed to remove Sale #{$sale->id} from Excel: ".$e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function sync(Sale $sale, string $event): void
    {
        try {
            $this->excelSync->syncSale($sale);
        } catch (\Throwable $e) {
            Log::error("ExcelSync: failed to sync Sale #{$sale->id} on {$event}: ".$e->getMessage());
        }
    }
}
