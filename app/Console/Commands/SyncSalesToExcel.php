<?php

namespace App\Console\Commands;

use App\Services\ExcelSyncService;
use Illuminate\Console\Command;

class SyncSalesToExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:sync-excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the customer sales Excel file from all records in the database.';

    public function __construct(private readonly ExcelSyncService $excelSync)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Rebuilding customer sales Excel file…');

        try {
            $this->excelSync->rebuildFromDatabase();
            $disk = config('filesystems.default', 'local');
            $this->info("✅ Excel file rebuilt successfully on [{$disk}] disk at: exports/customer_sales_records.xlsx");
        } catch (\Throwable $e) {
            $this->error('❌ Failed: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
