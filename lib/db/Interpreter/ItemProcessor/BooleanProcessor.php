<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\Interpreter\Super\ItemProcessorInterface;

class BooleanProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return is_scalar($value) && is_bool($value);
    }

    public static function process($interpreter_provider, $value, $column)
    {
        return $value ? 'true' : 'false';
    }
}
