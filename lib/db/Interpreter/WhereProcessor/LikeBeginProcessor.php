<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\WhereProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlLikeBegin;
use Gnf\db\Interpreter\Super\WhereProcessorInterface;

class LikeBeginProcessor implements WhereProcessorInterface
{
    public static function isCondition($value, $key): bool
    {
        return $value instanceof GnfSqlLikeBegin;
    }

    public static function process($interpreter_provider, $value, $key)
    {
        return EscapeHelper::escapeColumnName($key) . ' like "' . EscapeHelper::escapeLiteral($value->dat) . '%"';
    }
}
