<?php
declare(strict_types=1);

namespace Gnf\db;

use Gnf\db\Interpreter\ItemInterpreter;
use Gnf\db\Interpreter\TableInterpreter;
use Gnf\db\Interpreter\WhereInterpreter;
use Gnf\db\Superclass\InterpreterInterface;
use Gnf\db\Superclass\RedirectInterface;

class InterpreterProvider
{
    /** @var InterpreterInterface */
    private $table_interpreter;
    /** @var InterpreterInterface */
    private $item_interpreter;
    /** @var InterpreterInterface */
    private $where_interpreter;

    /** @var RedirectInterface */
    private $table_redirector;
    /** @var RedirectInterface */
    private $item_redirector;
    /** @var RedirectInterface */
    private $where_redirector;

    public function __construct(
        ?TableInterpreter $table_interpreter = null,
        ?ItemInterpreter $item_interpreter = null,
        ?WhereInterpreter $where_interpreter = null
    ) {
        if ($table_interpreter === null) {
            $table_interpreter = new TableInterpreter($this);
        }
        if ($item_interpreter === null) {
            $item_interpreter = new ItemInterpreter($this);
        }
        if ($where_interpreter === null) {
            $where_interpreter = new WhereInterpreter($this);
        }

        $this->table_interpreter = $table_interpreter;
        $this->item_interpreter = $item_interpreter;
        $this->where_interpreter = $where_interpreter;

        $this->table_redirector = $table_interpreter->getRedirector();
        $this->item_redirector = $item_interpreter->getRedirector();
        $this->where_redirector = $where_interpreter->getRedirector();
    }

    public function getTableInterpreter(): InterpreterInterface
    {
        return $this->table_interpreter;
    }

    public function getItemInterpreter(): InterpreterInterface
    {
        return $this->item_interpreter;
    }

    public function getWhereInterpreter(): InterpreterInterface
    {
        return $this->where_interpreter;
    }

    public function proxyRedirector(RedirectInterface $redirector)
    {
        if ($redirector instanceof $this->table_redirector) {
            return $this->table_interpreter;
        }
        if ($redirector instanceof $this->item_redirector) {
            return $this->item_interpreter;
        }
        if ($redirector instanceof $this->where_redirector) {
            return $this->where_interpreter;
        }

        throw new \RuntimeException('Invalid redirect');
    }
}
