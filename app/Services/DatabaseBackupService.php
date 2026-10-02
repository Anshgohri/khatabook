<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseBackupService
{
    /**
     * Generate a full SQL dump of the database as a string.
     * Supports SQLite and PostgreSQL (works without pg_dump binary).
     */
    public function generateSqlDump(): string
    {
        $driver = config('database.default');

        return match ($driver) {
            'sqlite' => $this->dumpSqlite(),
            'pgsql', 'postgres' => $this->dumpPgsql(),
            'mysql', 'mariadb' => $this->dumpMysql(),
            default => throw new \RuntimeException("Database backup is not supported for driver: {$driver}"),
        };
    }

    /**
     * Get the suggested download filename.
     */
    public function filename(): string
    {
        $appName = strtolower(str_replace(' ', '_', config('app.name', 'khatabook')));
        $driver = config('database.default');

        return "{$appName}_backup_{$driver}_".now()->format('Y-m-d_His').'.sql';
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * SQLite: read the file directly and wrap in a SQL transaction.
     * We still do a SQL dump (not binary) so it's portable.
     */
    private function dumpSqlite(): string
    {
        $output = $this->header('sqlite');

        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");

        foreach ($tables as $table) {
            $tableName = $table->name;
            $output .= $this->dumpTable($tableName, 'sqlite');
        }

        return $output.$this->footer();
    }

    /**
     * PostgreSQL: dump all user tables via PDO (no pg_dump required).
     */
    private function dumpPgsql(): string
    {
        $output = $this->header('pgsql');

        $tables = DB::select("
            SELECT tablename FROM pg_tables
            WHERE schemaname = 'public'
            ORDER BY tablename
        ");

        foreach ($tables as $table) {
            $tableName = $table->tablename;
            $output .= $this->dumpTable($tableName, 'pgsql');
        }

        return $output.$this->footer();
    }

    /**
     * MySQL / MariaDB: dump all tables via PDO.
     */
    private function dumpMysql(): string
    {
        $output = $this->header('mysql');

        $database = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_'.$database;

        foreach ($tables as $table) {
            $tableName = $table->$key;
            $output .= $this->dumpTable($tableName, 'mysql');
        }

        return $output.$this->footer();
    }

    /**
     * Dump a single table: CREATE TABLE skeleton + INSERT rows.
     */
    private function dumpTable(string $table, string $driver): string
    {
        $rows = DB::table($table)->get();

        if ($rows->isEmpty()) {
            return "\n-- Table: {$table} (empty)\n";
        }

        $output = "\n-- ─────────────────────────────────────────────────────────────────────────\n";
        $output .= "-- Table: {$table}\n";
        $output .= "-- ─────────────────────────────────────────────────────────────────────────\n\n";

        // Chunk large tables to avoid memory exhaustion
        $chunkSize = 500;
        $allRows = $rows->toArray();
        $chunks = array_chunk($allRows, $chunkSize);

        foreach ($chunks as $chunk) {
            $columns = array_keys((array) $chunk[0]);
            $quotedCols = implode(', ', array_map(fn($c) => '"'.$c.'"', $columns));

            foreach ($chunk as $row) {
                $values = array_map(function ($value) {
                    if ($value === null) {
                        return 'NULL';
                    }
                    // Escape single quotes
                    return "'".str_replace("'", "''", (string) $value)."'";
                }, (array) $row);

                $output .= "INSERT INTO \"{$table}\" ({$quotedCols}) VALUES (".implode(', ', $values).");\n";
            }
        }

        return $output;
    }

    private function header(string $driver): string
    {
        $app = config('app.name', 'Khatabook');
        $now = now()->toDateTimeString();
        $db = config("database.connections.{$driver}.database", 'database');

        return <<<SQL
        -- ═══════════════════════════════════════════════════════════════════════════════
        -- {$app} — Full Database Backup
        -- Generated at : {$now}
        -- Driver       : {$driver}
        -- Database     : {$db}
        -- ⚠️  CAUTION: This file contains ALL your business data. Keep it secure.
        -- ═══════════════════════════════════════════════════════════════════════════════

        BEGIN;

        SQL;
    }

    private function footer(): string
    {
        return "\nCOMMIT;\n\n-- Backup complete.\n";
    }
}
