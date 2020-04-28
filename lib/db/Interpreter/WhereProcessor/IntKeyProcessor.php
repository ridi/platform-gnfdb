<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\WhereProcessor;

use Gnf\db\Interpreter\Super\WhereProcessorInterface;

class IntKeyProcessor implements WhereProcessorInterface
{
    public static function isCondition($value, $key): bool
    {
        return is_int($key);
    }

    public static function process($interpreter_provider, $value, $key)
    {
        throw new \InvalidArgumentException('cannot implict int key as column : ' . $key);
    }
}
