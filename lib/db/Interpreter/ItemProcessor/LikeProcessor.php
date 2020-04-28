<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlLike;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

class LikeProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return $value instanceof GnfSqlLike;
    }

    public static function process($interpreter_provider, $value, $column)
    {
        return '"%' . EscapeHelper::escapeLiteral($value->dat) . '%"';
    }
}
