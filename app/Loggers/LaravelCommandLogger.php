<?php

namespace App\Loggers;

use Exception;
use Illuminate\Console\Command;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Stringable;

/**
 * Logs messages to the given commands output using colors, verbosity, etc
 *
 * @see https://github.com/artkonekt/extended-logger/blob/master/src/Loggers/LaravelCommandLogger.php
 */
class LaravelCommandLogger implements LoggerInterface
{
    use LoggerTrait;

    private array $formatLevelMap = [
        LogLevel::EMERGENCY => 'alert',
        LogLevel::ALERT => 'alert',
        LogLevel::CRITICAL => 'error',
        LogLevel::ERROR => 'error',
        LogLevel::WARNING => 'warn',
        LogLevel::NOTICE => 'line',
        LogLevel::INFO => 'info',
        LogLevel::DEBUG => 'comment',
    ];

    private Command $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $exception = $context['exception'] ?? null;
        if ($exception instanceof Exception) {
            $message .= ' | '.get_class($exception).': '.$exception->getMessage();
        }

        $writeInStyle = $this->formatLevelMap[$level] ?? 'line';
        $this->command->$writeInStyle($message);
    }
}
