<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlStrcat;
use Gnf\db\InterpreterProvider;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

//only for update
class StrcatProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return $value instanceof GnfSqlStrcat && is_string($column);
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
        $value = $interpreter_provider->getItemInterpreter()->process($value->dat, null);

        return sprintf('concat(ifnull(%s, ""), %s)', EscapeHelper::escapeColumnName($column), $value);
    }
}
