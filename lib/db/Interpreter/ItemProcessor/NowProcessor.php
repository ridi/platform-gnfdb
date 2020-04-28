<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\Helper\GnfSqlNow;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

class NowProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return $value instanceof GnfSqlNow;
    }

    public static function process($interpreter_provider, $value, $column)
    {
        return 'now()';
    }
}
