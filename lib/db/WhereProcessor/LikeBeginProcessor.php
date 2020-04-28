<?php
declare(strict_types=1);

namespace Gnf\db\WhereProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlLikeBegin;
use Gnf\db\Interpreter\Super\WhereProcessorInterface;

class LikeBeginProcessor implements WhereProcessorInterface
{
    public function isCondition($key, $value): bool
    {
        return $value instanceof GnfSqlLikeBegin;
    }

    public function process($key, $value)
    {
        return EscapeHelper::escapeColumnName($key) . ' like "' . EscapeHelper::escapeLiteral($value->dat) . '%"';
    }
}
