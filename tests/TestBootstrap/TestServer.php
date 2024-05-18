<?php

namespace Tests\TestBootstrap;

use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

abstract class TestServer
{
    public static ?string $startupMessage;

    public string $path;

    public array $processEnv;

    public function __construct(
        protected readonly int $timeoutSeconds = 60
    ) {
    }

    abstract public function start(): static;

    abstract public function stop(): static;

    abstract public function clearPersistence(): static;

    abstract public function log(): string;

    public function awaitMessage(string $message): static
    {
        retry(
            $this->timeoutSeconds,
            fn () => throw_unless(
                Str::contains($this->log(), $message),
                "Could not receive message '$message' in time from container."
            ),
            1000,
        );

        return $this;
    }

    public function awaitStart(): static
    {
        if (!static::$startupMessage) {
            throw new RuntimeException('$startupMessage not set up');
        }

        $this->awaitMessage(static::$startupMessage);

        return $this;
    }

    public function run(string $command): ProcessResult
    {
        $process = Process::command($command)
            ->path($this->path ?? base_path())
            ->env($this->processEnv ?? [])
            ->timeout($this->timeoutSeconds)
            ->run();

        if ($process->exitCode() !== 0) {
            Log::error($process->output());
            throw new RuntimeException($process->errorOutput());
        }

        return $process;
    }
}
