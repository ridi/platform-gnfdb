<?php
declare(strict_types=1);

namespace Gnf\db;

class EscapeHelper
{
    public static function escapeColumnName($k)
    {
        if (is_int($k)) {
            throw new \InvalidArgumentException('cannot implict int key as column : ' . $k);
        }
        $k = str_replace(['`', '.'], ['', '`.`'], $k);

        return '`' . $k . '`';
    }

    public static function escapeTableNameFromFullColumnElement($fullsized_column)
    {
        $dot_count = substr_count($fullsized_column, '.');
        if ($dot_count !== 1 && $dot_count !== 2) {
            throw new \Exception('invalid column name (' . $fullsized_column . ') to extract table name');
        }
        $fullsized_column_items = explode('.', $fullsized_column);
        array_pop($fullsized_column_items);
        $fullsized_column_items = array_map(
            function ($item) {
                return self::escapeFullColumnElement($item);
            },
            $fullsized_column_items
        );

        return implode('.', $fullsized_column_items);
    }

    public static function escapeFullColumnElement($table_column_element)
    {
        $table_column_element = preg_replace("/\..+/", "", $table_column_element);
        $table_column_element = str_replace('`', '', $table_column_element);

        return '`' . $table_column_element . '`';
    }

    public static function escapeTableNameFromTableElement($tablename)
    {
        return self::escapeFullColumnElement($tablename);
    }

    /*
     * addslashes is not safe in multibyte
     * str_replace is safe in multibyte but only utf-8
     */
    public static function escapeLiteral($value)
    {
        if (!is_string($value)) {
            $value = (string)$value;
        }

        return str_replace(
            ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
            ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
            $value
        );
    }
}
