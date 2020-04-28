<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\InterpreterProvider;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

class ObjectProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return is_object($value) && property_exists($value, 'dat');
    }

    /**
     * @param InterpreterProvider $interpreter_provider
     * @param mixed               $value
     * @param string|null         $column
     *
     * @return mixed
     */
    public static function process($interpreter_provider, $value, $column)
    {
        return $interpreter_provider->getItemInterpreter()->process($value->dat, $column);
    }
}
