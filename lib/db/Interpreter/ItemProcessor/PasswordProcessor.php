<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\Helper\GnfSqlPassword;
use Gnf\db\InterpreterProvider;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

class PasswordProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return $value instanceof GnfSqlPassword;
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
        return 'password(' . $interpreter_provider->getItemInterpreter()->process($value->dat, null) . ')';
    }
}
