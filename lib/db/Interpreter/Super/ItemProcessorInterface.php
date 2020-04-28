<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\Super;

interface ItemProcessorInterface
{
    public static function isCondition($value, $column): bool;

    public static function process($interpreter_provider, $value, $column);
}
