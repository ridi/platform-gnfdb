<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter\Redirector;

use Gnf\db\Superclass\RedirectInterface;

class TableRedirector implements RedirectInterface
{
    private $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
