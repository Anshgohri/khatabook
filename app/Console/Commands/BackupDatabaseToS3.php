<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\Storage;

class BackupDatabaseToS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:s3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database and upload it to AWS S3 storage';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService)
    {
        $this->info('Starting database backup to S3...');

        try {
            $filename = $backupService->filename();
            $sqlDump = $backupService->generateSqlDump();
            
            $path = 'backup/' . $filename;

            $this->info("Generated dump. Uploading to path: {$path}");

            $isS3Configured = config('filesystems.disks.s3.driver') === 's3' && ! empty(config('filesystems.disks.s3.bucket'));
            $disk = $isS3Configured ? 's3' : 'local';

            $uploaded = Storage::disk($disk)->put($path, $sqlDump);

            if ($uploaded) {
                $target = $isS3Configured ? 'S3' : 'local disk';
                $this->info("Database backup successfully uploaded to {$target}!");
                return Command::SUCCESS;
            } else {
                $this->error('Failed to upload the backup.');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('An error occurred during the backup process: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
