<?php

namespace Ashraf\LaravelAiOrbit\Services\Concerns;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

trait UsesAiConnection
{
    /** @var array<string, bool> */
    protected static array $missingTableLogged = [];

    /**
     * Get the database connection configured for AI conversations.
     */
    protected function connection(): ConnectionInterface
    {
        return DB::connection(config('ai.conversations.connection'));
    }

    /**
     * Check if a table exists.
     */
    protected function hasTable(string $table): bool
    {
        $exists = Schema::hasTable($table);

        if (! $exists) {
            $this->logMissingTable($table);
        }

        return $exists;
    }

    /**
     * Check if a column exists in a table.
     */
    protected function hasColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }

    /**
     * Log a warning once per request when a required table is missing.
     */
    private function logMissingTable(string $table): void
    {
        $key = $table.'-'.spl_object_id($this);

        if (isset(static::$missingTableLogged[$key])) {
            return;
        }

        static::$missingTableLogged[$key] = true;

        Log::warning("Laravel AI Orbit: table [{$table}] not found. Run [php artisan migrate] to create the Laravel AI SDK tables.");
    }
}
