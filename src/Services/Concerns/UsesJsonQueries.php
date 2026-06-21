<?php

namespace Ashrafic\AiOrbit\Services\Concerns;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\Grammar;

trait UsesJsonQueries
{
    /**
     * Get a compiled SQL expression for a JSON path (for use in raw aggregates).
     *
     * Calls the query grammar's wrapJsonSelector() to produce per-driver SQL:
     *   MySQL:     json_unquote(json_extract(`col`, '$."key"'))
     *   PostgreSQL: "col"->>'key'
     *   SQLite:    json_extract("col", '$."key"')
     *   SQL Server: json_value("col", '$."key"')
     */
    protected function jsonExpr(string $column, string $path): string
    {
        $expr = \Closure::bind(
            fn (string $selector): string => $this->wrapJsonSelector($selector),
            $this->grammar(),
            Grammar::class,
        )("{$column}->{$path}");

        if ($this->driverName() === 'pgsql') {
            $pos = strpos($expr, '->');
            if ($pos !== false) {
                $expr = substr($expr, 0, $pos).'::jsonb'.substr($expr, $pos);
            }
        }

        return $expr;
    }

    /**
     * Like jsonExpr() but with explicit numeric casts for SUM/AVG on
     * PostgreSQL and SQL Server.
     */
    protected function jsonExprNumeric(string $column, string $path): string
    {
        $expr = $this->jsonExpr($column, $path);

        return match ($this->driverName()) {
            'pgsql' => "({$expr})::numeric",
            'sqlsrv' => "CAST({$expr} AS FLOAT)",
            default => $expr,
        };
    }

    /**
     * Build a portable COALESCE(SUM(json_numeric), 0) expression.
     *
     * @return Expression
     */
    protected function jsonSum(string $column, string $path, string $alias)
    {
        return $this->connection()->raw(
            'COALESCE(SUM('.$this->jsonExprNumeric($column, $path).'), 0) as '.$alias
        );
    }

    /**
     * Build a portable AVG(json_numeric) expression.
     *
     * @return Expression
     */
    protected function jsonAvg(string $column, string $path, string $alias)
    {
        return $this->connection()->raw(
            'AVG('.$this->jsonExprNumeric($column, $path).') as '.$alias
        );
    }

    /**
     * Get the query grammar for the configured connection.
     */
    private function grammar(): Grammar
    {
        $connection = $this->connection();

        /** @var Connection $connection */
        return $connection->getQueryGrammar();
    }

    /**
     * Get the driver name for the configured connection.
     */
    private function driverName(): string
    {
        $connection = $this->connection();

        /** @var Connection $connection */
        return $connection->getDriverName();
    }
}
