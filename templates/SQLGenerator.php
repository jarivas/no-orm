<?php

namespace Entity;

class SQLGenerator
{
    const PROJECTION_FORMAT = '%s.%s as %s';
    const PROJECTION_FORMAT_EXTENDED = '%s.%s as %s_%s';

    const ON_CALL_ALLOWED_METHODS = ['set', 'get'];

    public static function generateProjection(string $tableName, array $columnsInfo, array $projection = [], bool $useExtended = true): array
    {
        $result = [];

        if (count($projection)) {

            foreach ($projection as $col) {
                $alias = $columnsInfo[$col];
                $result[] = sprintf(self::PROJECTION_FORMAT, $tableName, $col, $alias);
            }

            return $result;
        }

        foreach ($columnsInfo as $col => $alias) {
            if ($useExtended)
                $result[] = sprintf(self::PROJECTION_FORMAT_EXTENDED, $tableName, $col, strtoupper($tableName), $alias);
            else
                $result[] = sprintf(self::PROJECTION_FORMAT, $tableName, $col, $alias);
        }

        return $result;
    }

    protected static function generateJoins(array $joins): string
    {
        $sql = '';

        if (count($joins)) {
            foreach ($joins as $join)
                $sql .= sprintf(' %s JOIN %s ON %s = %s', $join['type'], $join['tableName'], $join['onCol1'], $join['onCol2']);
        }

        return $sql;
    }

    protected static function generateConditions(string $type, array $conditions): string
    {
        $sql = '';

        if (count($conditions)) {
            $sql .= " $type 1";

            foreach ($conditions as $condition)
                $sql .= ' ' . $condition['operator'] . ' ' . $condition['condition'];
        }

        return $sql;
    }

    public static function generateSelect(string $tableName, array $projection, array $joins = [], array $where = [], string $group = '',
                                          array $having = [], string $order = '', int $limit = 1000, int $offset = 0): string
    {
        $sql = 'SELECT ';

        foreach ($projection as $col)
            $sql .= $col . ', ';

        $sql = substr_replace($sql, " FROM {$tableName}", strlen($sql) - 3);

        $sql .= self::generateJoins($joins);

        $sql .= self::generateConditions('WHERE', $where);

        if (strlen($group))
            $sql .= ' GROUP BY ' . $group;

        $sql .= self::generateConditions('HAVING', $having);

        if (strlen($order))
            $sql .= ' ORDER BY ' . $order;

        $sql .= sprintf(' LIMIT %d OFFSET %d', $limit, $offset);

        return $sql;
    }

    public static function generateInsert(string $tableName, array $columns, array $values, string $onDuplicateKeyUpdate = ''): string
    {
        $sql = sprintf('INSERT INTO %s (', $tableName);

        foreach ($columns as $column)
            $sql .= $column . ', ';

        $sql = substr_replace($sql, ") VALUES (", strlen($sql) - 3);

        foreach ($values as $value)
            $sql .= sprintf('"%s", ', $value);

        $sql = substr_replace($sql, ")", strlen($sql) - 3);

        if (strlen($onDuplicateKeyUpdate))
            $sql .= 'ON DUPLICATE KEY UPDATE ' . $onDuplicateKeyUpdate;

        return $sql;
    }

    public static function generateUpdate(string $tableName, array $assignments, array $where = [], string $order = '', int $limit = 0)
    {
        $sql = 'UPDATE ' . $tableName . ' SET ';

        foreach ($assignments as $assignment)
            $sql .= $assignment . ', ';

        $sql = substr($sql, 0, strlen($sql) - 3) . self::generateConditions('WHERE', $where);

        if (strlen($order))
            $sql .= ' ORDER BY ' . $order;

        $sql .= sprintf(' LIMIT %d', $limit);

        return $sql;
    }

    public static function generateDelete(string $tableName, array $where = [], string $order = '', int $limit = 0): string
    {
        $sql = 'DELETE FROM ' . $tableName . self::generateConditions('WHERE', $where);

        if (strlen($order))
            $sql .= ' ORDER BY ' . $order;

        $sql .= sprintf(' LIMIT %d', $limit);

        return $sql;
    }
}
