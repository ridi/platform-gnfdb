<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\Super;

interface WhereProcessorInterface
{
    public static function isCondition($value, $key): bool;

    public static function process($interpreter_provider, $value, $key);
}