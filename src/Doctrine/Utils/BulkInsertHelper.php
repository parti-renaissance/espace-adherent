<?php

declare(strict_types=1);

namespace App\Doctrine\Utils;

use Doctrine\DBAL\Connection;

class BulkInsertHelper
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Bulk INSERT IGNORE on a given table.
     *
     * Note: $table is concatenated into SQL — internal use only, never combined
     * with user input. The regex validation below guards against typos or
     * future non-internal usage.
     *
     * @param array<int, array<string, mixed>> $rows all rows must share the same keys
     *
     * @return int number of affected rows
     */
    public function insertIgnore(string $table, array $rows): int
    {
        if (!$rows) {
            return 0;
        }

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            throw new \InvalidArgumentException(\sprintf('Invalid table name: "%s".', $table));
        }

        $cols = array_keys(reset($rows));
        $placeholders = [];
        $params = [];

        foreach ($rows as $r) {
            $placeholders[] = '('.implode(',', array_fill(0, \count($cols), '?')).')';
            foreach ($cols as $c) {
                $params[] = $r[$c] ?? null;
            }
        }

        $sql = \sprintf(
            'INSERT IGNORE INTO %s (%s) VALUES %s',
            $table,
            implode(',', $cols),
            implode(',', $placeholders),
        );

        return (int) $this->connection->executeStatement($sql, $params);
    }
}
