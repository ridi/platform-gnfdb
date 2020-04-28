<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\TableProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Interpreter\Super\TableProcessorInterface;

class RawProcessor implements TableProcessorInterface
{
    public function isCondition($value): bool
    {
        return true;
    }

    public function process($interpreter_provider, $value)
    {
        return EscapeHelper::escapeTableNameFromTableElement($value);
    }
}
