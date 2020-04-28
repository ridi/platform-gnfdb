<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\WhereProcessor;

use Gnf\db\Helper\GnfSqlAnd;
use Gnf\db\Helper\GnfSqlNot;
use Gnf\db\Helper\GnfSqlOr;
use Gnf\db\Interpreter\SerializeTrait;
use Gnf\db\InterpreterProvider;
use Gnf\db\Interpreter\Super\WhereProcessorInterface;

class AndOrProcessor implements WhereProcessorInterface
{
    use SerializeTrait;

    public static function isCondition($value, $key): bool
    {
        return $value instanceof GnfSqlAnd || $value instanceof GnfSqlOr;
    }

    /**
     * @param InterpreterProvider $interpreter_provider
     * @param string              $key
     * @param mixed               $value
     *
     * @return string
     */
    public static function process($interpreter_provider, $value, $key)
    {
        $ret = [];
        foreach ($value->dat as $dat) {
            if (is_array($dat)) {
                $ret[] = '( ' . self::serializeWhere($interpreter_provider, $dat) . ' )';
            } elseif ($dat instanceof GnfSqlNot && is_array($dat->dat)) {
                $ret[] = '( ! ( ' . self::serializeWhere($interpreter_provider, $dat->dat) . ' ) )';
            } else {
                throw new \InvalidArgumentException('process sqlAnd needs where(key, value pair)');
            }
        }
        if (count($ret)) {
            if ($value instanceof GnfSqlAnd) {
                return '( ' . implode(' and ', $ret) . ' )';
            }

            return '( ' . implode(' or ', $ret) . ' )';
        }

        return '';
    }
}
