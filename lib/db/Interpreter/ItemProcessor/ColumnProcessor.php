<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlColumn;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

class ColumnProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return $value instanceof GnfSqlColumn;
    }

    public static function process($interpreter_provider, $value, $column)
    {
        return EscapeHelper::escapeColumnName($value->dat);
    }
}
