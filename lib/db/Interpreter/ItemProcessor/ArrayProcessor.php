<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\InterpreterProvider;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

class ArrayProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return is_array($value);
    }

    /**
     * @param InterpreterProvider $interpreter_provider
     * @param mixed               $value
     * @param string|null         $column
     *
     * @return string
     */
    public static function process($interpreter_provider, $value, $column)
    {
        if (count($value) === 0) {
            throw new \InvalidArgumentException('zero size array, key : ' . (string)$column);
        }

        $processed = [];
        foreach ($value as $item) {
            $processed[] = $interpreter_provider->getItemInterpreter()->process($item, null);
        }

        return '(' . implode(', ', $processed) . ')';
    }
}
