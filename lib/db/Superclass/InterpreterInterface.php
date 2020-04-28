<?php
declare(strict_types=1);

namespace Gnf\db\Superclass;

interface InterpreterInterface
{
    public function getDefaultProcessors(): array;

    public function getRedirector(): RedirectInterface;
}