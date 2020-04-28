<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\ItemProcessor;

use Gnf\db\Helper\GnfSqlTable;
use Gnf\db\InterpreterProvider;
use Gnf\db\Interpreter\Redirector\TableRedirector;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;

class TableProcessor implements ItemProcessorInterface
{
    public static function isCondition($value, $column): bool
    {
        return $value instanceof GnfSqlTable;
    }

    /**
     * @param InterpreterProvider $interpreter_provider
     * @param GnfSqlTable         $value
     * @param string|null         $column
     *
     * @return TableRedirector
     */
    public static function process($interpreter_provider, $value, $column)
    {
        return new TableRedirector($value);
    }
}
