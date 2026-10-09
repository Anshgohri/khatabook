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

            $this->info("Generated dump. Uploading to S3 path: {$path}");

            // Note: Make sure AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, and AWS_BUCKET are set in .env
            $uploaded = Storage::disk('s3')->put($path, $sqlDump);

            if ($uploaded) {
                $this->info('Database backup successfully uploaded to S3!');
                return Command::SUCCESS;
            } else {
                $this->error('Failed to upload the backup to S3.');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('An error occurred during the backup process: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
