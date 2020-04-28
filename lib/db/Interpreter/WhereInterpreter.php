<?php
declare(strict_types=1);

namespace Gnf\db\Interpreter;

use Gnf\db\Interpreter\Redirector\WhereRedirector;
use Gnf\db\Interpreter\Super\WhereProcessorInterface;
use Gnf\db\Interpreter\WhereProcessor\AndOrProcessor;
use Gnf\db\Interpreter\WhereProcessor\ArrayProcessor;
use Gnf\db\Interpreter\WhereProcessor\BetweenProcessor;
use Gnf\db\Interpreter\WhereProcessor\GreaterEqualProcessor;
use Gnf\db\Interpreter\WhereProcessor\GreaterProcessor;
use Gnf\db\Interpreter\WhereProcessor\IntKeyProcessor;
use Gnf\db\Interpreter\WhereProcessor\LesserEqualProcessor;
use Gnf\db\Interpreter\WhereProcessor\LesserProcessor;
use Gnf\db\Interpreter\WhereProcessor\LikeBeginProcessor;
use Gnf\db\Interpreter\WhereProcessor\LikeProcessor;
use Gnf\db\Interpreter\WhereProcessor\NotNullProcessor;
use Gnf\db\Interpreter\WhereProcessor\NotProcessor;
use Gnf\db\Interpreter\WhereProcessor\NullProcessor;
use Gnf\db\Interpreter\WhereProcessor\OrProcessor;
use Gnf\db\Interpreter\WhereProcessor\RangeProcessor;
use Gnf\db\Interpreter\WhereProcessor\RawProcessor;
use Gnf\db\InterpreterProvider;
use Gnf\db\Superclass\InterpreterInterface;
use Gnf\db\Superclass\RedirectInterface;

class WhereInterpreter implements InterpreterInterface
{
    /** @var InterpreterProvider */
    private $interpreter_provider;
    /** @var WhereProcessorInterface[] */
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
            NullProcessor::class,
            NotNullProcessor::class,
            NotProcessor::class,
            LikeProcessor::class,
            LikeBeginProcessor::class,
            GreaterProcessor::class,
            LesserProcessor::class,
            GreaterEqualProcessor::class,
            LesserEqualProcessor::class,
            BetweenProcessor::class,
            RangeProcessor::class,
            AndOrProcessor::class,
            IntKeyProcessor::class,
            ArrayProcessor::class,
            RawProcessor::class,
        ];
    }


    public function process($value, $key = null)
    {
        foreach ($this->processors as $processor) {
            if (!$processor::isCondition($value, $key)) {
                continue;
            }

            $processed_value = $processor::process($this->interpreter_provider, $value, $key);

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
        return new WhereRedirector();
    }
}
