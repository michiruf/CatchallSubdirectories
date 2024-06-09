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

    public function __construct(
        public int $timeoutSeconds = 60,
        public array $processEnv = [],
    ) {
    }

    abstract public function start(): static;

    abstract public function stop(): static;

    abstract public function clearPersistence(): static;

    abstract public function log(): string;

    public function awaitMessage(string $message, bool $regex = false): static
    {
        retry(
            $this->timeoutSeconds,
            fn () => throw_unless(
                $regex
                    ? Str::match($message, $this->log())
                    : Str::contains($this->log(), $message),
                $regex
                    ? "Could not match regex '$message' in time from container."
                    : "Could not receive message '$message' in time from container."
            ),
            1000,
        );

        return $this;
    }

    public function awaitStart(): static
    {
        if (! static::$startupMessage) {
            throw new RuntimeException('$startupMessage not set up');
        }

        $this->awaitMessage(static::$startupMessage);

        return $this;
    }

    public function run(string $command, bool $throwOnError = true): ProcessResult
    {
        $process = Process::path($this->path ?? base_path())
            ->env($this->processEnv ?? [])
            ->timeout($this->timeoutSeconds)
            ->run($command);

        if ($throwOnError && $process->exitCode() !== 0) {
            $errorOutput = $process->errorOutput();
            if (Str::length($errorOutput) == 0) {
                $errorOutput = $process->output();
            }
            Log::error($errorOutput);
            throw new RuntimeException($errorOutput);
        }

        return $process;
    }
}
