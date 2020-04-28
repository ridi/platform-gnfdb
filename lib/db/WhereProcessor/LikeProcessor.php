<?php
declare(strict_types=1);

namespace Gnf\db\WhereProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlLike;
use Gnf\db\Interpreter\Super\WhereProcessorInterface;

class LikeProcessor implements WhereProcessorInterface
{
    public function isCondition($key, $value): bool
    {
        return $value instanceof GnfSqlLike;
    }

    public function process($key, $value)
    {
        return EscapeHelper::escapeColumnName($key) . ' like "%' . EscapeHelper::escapeLiteral($value->dat) . '%"';
    }
}
