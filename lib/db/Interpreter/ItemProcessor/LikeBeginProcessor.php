<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlLikeBegin;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

class LikeBeginProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return $value instanceof GnfSqlLikeBegin;
    }

    public static function process($interpreter_provider, $value, $column)
    {
        return '"' . EscapeHelper::escapeLiteral($value->dat) . '%"';
    }
}
