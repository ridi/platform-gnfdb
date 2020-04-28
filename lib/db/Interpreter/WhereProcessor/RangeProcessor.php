<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\WhereProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlRange;
use Gnf\db\InterpreterProvider;
use Gnf\db\Interpreter\Super\WhereProcessorInterface;

class RangeProcessor implements WhereProcessorInterface
{
    public static function isCondition($value, $key): bool
    {
        return $value instanceof GnfSqlRange;
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
        $start_value = $item_interpreter->process($value->dat, $key);
        $end_value = $item_interpreter->process($value->dat2, $key);

        $escaped_key = EscapeHelper::escapeColumnName($key);

        return '(' . $start_value . ' <= ' . $escaped_key . ' and ' . $escaped_key . ' < ' . $end_value . ')';
    }
}
