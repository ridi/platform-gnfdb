<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\Helper\GnfSqlWhere;
use Gnf\db\InterpreterProvider;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

class WhereProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return $value instanceof GnfSqlWhere;
    }

    /**
     * @param InterpreterProvider $interpreter_provider
     * @param GnfSqlWhere         $value
     * @param string|null         $column
     *
     * @return string
     */
    public static function process($interpreter_provider, $value, $column)
    {
        if (count($value->dat) === 0) {
            throw new \InvalidArgumentException('zero size array can not serialize');
        }
        $where_interpreter = $interpreter_provider->getWhereInterpreter();
        if (array_keys($value->dat) > 0) {
            foreach ($value->dat as $k => $v) {
                $wheres[] = $where_interpreter->process($v, $k);
            }
        } else {
            foreach ($value->dat as $v) {
                $wheres[] = $where_interpreter->process($v);
            }
        }
        $wheres = array_filter($wheres, 'strlen');

        return implode(' and ', $wheres);
    }
}
