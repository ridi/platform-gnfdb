<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\WhereProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlNull;
use Gnf\db\Interpreter\Super\WhereProcessorInterface;

class NullProcessor implements WhereProcessorInterface
{
    public static function isCondition($value, $key): bool
    {
        return $value instanceof GnfSqlNull || is_null($value);
    }

    public static function process($interpreter_provider, $value, $key)
    {
        return EscapeHelper::escapeColumnName($key) . ' is NULL';
    }
}
