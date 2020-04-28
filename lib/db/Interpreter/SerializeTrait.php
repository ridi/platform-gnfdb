<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter;

use Gnf\db\InterpreterProvider;

trait SerializeTrait
{
    /**
     * @param InterpreterProvider $interpreter_provider
     * @param mixed               $value
     *
     * @return string
     */
    public static function serializeWhere($interpreter_provider, $value)
    {
        $where_interpreter = $interpreter_provider->getWhereInterpreter();

        if (array_keys($value) > 0) {
            foreach ($value as $k => $v) {
                $wheres[] = $where_interpreter->process($v, $k);
            }
        } else {
            foreach ($value as $v) {
                $wheres[] = $where_interpreter->process($v);
            }
        }
        $wheres = array_filter($wheres, 'strlen');

        return implode(' and ', $wheres);
    }
}