<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\TableProcessor;

use Gnf\db\EscapeHelper;
use Gnf\db\Helper\GnfSqlTable;
use Gnf\db\Interpreter\TableInterpreter;
use Gnf\db\Interpreter\Super\TableProcessorInterface;

class TableProcessor implements TableProcessorInterface
{
    public static function isCondition($value): bool
    {
        return $value instanceof GnfSqlTable;
    }

    /**
     * @param TableInterpreter $interpreter_provider
     * @param GnfSqlTable      $value
     *
     * @return string
     */
    public static function process($interpreter_provider, $value)
    {
        return EscapeHelper::escapeTableNameFromTableElement($value->dat);
    }
}
