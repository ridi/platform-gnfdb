<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\WhereProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\InterpreterProvider;
use Gnf\db\Interpreter\Super\WhereProcessorInterface;

class RawProcessor implements WhereProcessorInterface
{
    public static function isCondition($value, $key): bool
    {
        return true;
    }

    /**
     * @param InterpreterProvider $interpreter_provider
     * @param mixed               $value
     * @param string              $key
     *
     * @return string
     */
    public static function process($interpreter_provider, $value, $key)
    {
        $item_interpreter = $interpreter_provider->getItemInterpreter();

        return EscapeHelper::escapeColumnName($key) . ' = ' . $item_interpreter->process($value, $key);
    }
}
