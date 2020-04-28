<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\WhereProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\InterpreterProvider;
use Gnf\db\Interpreter\Super\WhereProcessorInterface;

class ArrayProcessor implements WhereProcessorInterface
{
    public static function isCondition($value, $key): bool
    {
        return is_array($value);
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
        //divide
        $scalars = [];
        $objects = [];
        if (count($value) == 0) {
            throw new \InvalidArgumentException('zero size array, key : ' . $key);
        }

        foreach ($value as $operand) {
            if (is_scalar($operand)) {
                $scalars[] = $operand;
            } else {
                $objects[] = $operand;
            }
        }

        //process
        $objects_query = '';
        if (count($objects) > 0) {
            $where_interpreter = $interpreter_provider->getWhereInterpreter();
            foreach ($objects as $k => $object) {
                $objects[$k] = $where_interpreter->process($object, $key);
            }
            $objects_query = '( ' . implode(' or ', array_filter($objects, 'strlen')) . ' )';
        }

        $scalars_query = '';
        if (count($scalars) > 0) {
            $item_interpreter = $interpreter_provider->getItemInterpreter();
            $scalars_query = EscapeHelper::escapeColumnName($key) . ' in ' . $item_interpreter->process($scalars, $key);
        }

        //merge
        if ($objects_query !== '' && $scalars_query !== '') {
            return '( ' . $objects_query . ' or ' . $scalars_query . ' )';
        }

        return $objects_query . $scalars_query;
    }
}
