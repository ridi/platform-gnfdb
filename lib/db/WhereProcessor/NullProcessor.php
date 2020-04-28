<?php
declare(strict_types=1);

namespace Gnf\db\WhereProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlNull;
use Gnf\db\Interpreter\Super\WhereProcessorInterface;

class NullProcessor implements WhereProcessorInterface
{
    public function isCondition($key, $value): bool
    {
        return $value instanceof GnfSqlNull || is_null($value);
    }

    public function process($key, $value)
    {
        return sprintf('%s is NULL', EscapeHelper::escapeColumnName($key));
    }
}
