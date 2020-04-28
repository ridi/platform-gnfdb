<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

class ScalarProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return is_scalar($value);
    }

    public static function process($interpreter_provider, $value, $column)
    {
        return '"' . EscapeHelper::escapeLiteral($value) . '"';
    }
}
