<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\Super;

interface TableProcessorInterface
{
    public static function isCondition($value): bool;

    public static function process($interpreter_provider, $value);
}