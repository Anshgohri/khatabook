<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupController extends Controller
{
    public function __construct(private readonly DatabaseBackupService $backup) {}

    /**
     * Stream a full database backup SQL file as a download.
     * Only System Admins can trigger this.
     */
    public function download(Request $request): StreamedResponse
    {
        // Only System Admins should be able to download the raw database
        if (! $request->user()?->isSystemAdmin()) {
            abort(403, 'Only System Administrators can download database backups.');
        }

        $filename = $this->backup->filename();

        return response()->streamDownload(function () {
            echo $this->backup->generateSqlDump();
        }, $filename, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
