<?php
declare(strict_types=1);

namespace Gnf\db\WhereProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlNot;
use Gnf\db\Helper\GnfSqlNull;
use Gnf\db\Interpreter\Super\WhereProcessorInterface;

class NotNullProcessor implements WhereProcessorInterface
{
    public function isCondition($key, $value): bool
    {
        return $value instanceof GnfSqlNot && ($value->dat instanceof GnfSqlNull || is_null($value->dat));
    }

    public function process($key, $value)
    {
        return EscapeHelper::escapeColumnName($key) . ' is not NULL';
    }
}
