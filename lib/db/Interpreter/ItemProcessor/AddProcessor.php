<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlAdd;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

//only for update
class AddProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return $value instanceof GnfSqlAdd && is_string($column);
    }

    public static function process($interpreter_provider, $value, $column)
    {
        if ($value->dat > 0) {
            return EscapeHelper::escapeColumnName($column) . ' + ' . ($value->dat);
        }
        if ($value->dat < 0) {
            return EscapeHelper::escapeColumnName($column) . ' ' . ($value->dat);
        }

        return EscapeHelper::escapeColumnName($column);
    }
}
