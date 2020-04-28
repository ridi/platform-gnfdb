<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\Helper\GnfSqlRaw;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

class RawProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return $value instanceof GnfSqlRaw;
    }

    public static function process($interpreter_provider, $value, $column)
    {
        return $value->dat;
    }
}
