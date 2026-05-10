<?php

namespace Ashraf\LaravelAiOrbit\Services\Concerns;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait UsesAiConnection
{
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
        return Schema::hasTable($table);
    }

    /**
     * Check if a column exists in a table.
     */
    protected function hasColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }
}
