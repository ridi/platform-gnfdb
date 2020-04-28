<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\WhereProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlLesser;
use Gnf\db\InterpreterProvider;
use Gnf\db\Interpreter\Super\WhereProcessorInterface;

class LesserProcessor implements WhereProcessorInterface
{
    public static function isCondition($value, $key): bool
    {
        return $value instanceof GnfSqlLesser;
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
        $value = $interpreter_provider->getItemInterpreter()->process($value->dat, $key);

        return EscapeHelper::escapeColumnName($key) . ' < ' . $value;
    }
}
