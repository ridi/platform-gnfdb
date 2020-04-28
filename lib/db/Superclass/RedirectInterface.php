<?php
declare(strict_types=1);

namespace Gnf\db\Superclass;

interface RedirectInterface
{
    public function __construct($value = null);

    public function getValue();
}