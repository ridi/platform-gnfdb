<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter;

use Gnf\db\Interpreter\ItemProcessor\AddProcessor;
use Gnf\db\Interpreter\ItemProcessor\ArrayProcessor;
use Gnf\db\Interpreter\ItemProcessor\BooleanProcessor;
use Gnf\db\Interpreter\ItemProcessor\ColumnProcessor;
use Gnf\db\Interpreter\ItemProcessor\LikeBeginProcessor;
use Gnf\db\Interpreter\ItemProcessor\LikeProcessor;
use Gnf\db\Interpreter\ItemProcessor\LimitProcessor;
use Gnf\db\Interpreter\ItemProcessor\NowProcessor;
use Gnf\db\Interpreter\ItemProcessor\ObjectProcessor;
use Gnf\db\Interpreter\ItemProcessor\PasswordProcessor;
use Gnf\db\Interpreter\ItemProcessor\RawProcessor;
use Gnf\db\Interpreter\ItemProcessor\ScalarProcessor;
use Gnf\db\Interpreter\ItemProcessor\StrcatProcessor;
use Gnf\db\Interpreter\ItemProcessor\TableProcessor;
use Gnf\db\Interpreter\ItemProcessor\WhereProcessor;
use Gnf\db\Interpreter\Redirector\ItemRedirector;
use Gnf\db\InterpreterProvider;
use Gnf\db\Superclass\InterpreterInterface;
use Gnf\db\Interpreter\Super\ItemProcessorInterface;
use Gnf\db\Superclass\RedirectInterface;

class ItemInterpreter implements InterpreterInterface
{
    /** @var InterpreterProvider */
    private $interpreter_provider;
    /** @var ItemProcessorInterface[] */
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
            BooleanProcessor::class,
            ScalarProcessor::class,
            ArrayProcessor::class,

            NowProcessor::class,
            PasswordProcessor::class,
            LikeProcessor::class,
            LikeBeginProcessor::class,
            RawProcessor::class,
            TableProcessor::class,
            ColumnProcessor::class,
            WhereProcessor::class,
            LimitProcessor::class,
            AddProcessor::class,
            StrcatProcessor::class,
            ObjectProcessor::class,
        ];
    }

    public function process($value, $column = null)
    {
        foreach ($this->processors as $processor) {
            if (!$processor::isCondition($value, $column)) {
                continue;
            }

            $processed_value = $processor::process($this->interpreter_provider, $value, $column);

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
        return new ItemRedirector();
    }
}
