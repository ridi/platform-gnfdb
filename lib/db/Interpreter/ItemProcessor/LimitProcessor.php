<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\Helper\GnfSqlLimit;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

class LimitProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return $value instanceof GnfSqlLimit;
    }

    public static function process($interpreter_provider, $value, $column)
    {
        return 'limit ' . $value->from . ', ' . $value->count;
    }
}
