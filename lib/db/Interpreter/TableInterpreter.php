<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter;

use Gnf\db\Interpreter\Redirector\TableRedirector;
use Gnf\db\Interpreter\Super\TableProcessorInterface;
use Gnf\db\Interpreter\TableProcessor\JoinProcessor;
use Gnf\db\Interpreter\TableProcessor\RawProcessor;
use Gnf\db\Interpreter\TableProcessor\TableProcessor;
use Gnf\db\InterpreterProvider;
use Gnf\db\Superclass\InterpreterInterface;
use Gnf\db\Superclass\RedirectInterface;

class TableInterpreter implements InterpreterInterface
{
    /** @var InterpreterProvider */
    private $interpreter_provider;
    /** @var TableProcessorInterface[] */
    private $processors = [];

    public function __construct(InterpreterProvider $interpreter_provider, array $processors = [])
    {
        if (empty($processors)) {
            $processors = $this->getDefaultProcessors();
        }

        $this->interpreter_provider = $interpreter_provider;
        $this->processors = $processors;
    }

    public function getDefaultProcessors(): array
    {
        return [
            JoinProcessor::class,
            TableProcessor::class,
            RawProcessor::class,
        ];
    }


    public function process($value)
    {
        foreach ($this->processors as $processor) {
            if (!$processor::isCondition($value)) {
                continue;
            }

            $processed_value = $processor::process($this->interpreter_provider, $value);

            if ($processed_value instanceof RedirectInterface) {
                $interpreter = $this->interpreter_provider->proxyRedirector($processed_value);
                $processed_value = $interpreter->process($processed_value->getValue());
            }

            return $processed_value;
        }

        throw new \InvalidArgumentException('invalid escape item');
    }

    public function getRedirector(): RedirectInterface
    {
        return new TableRedirector();
    }
}
