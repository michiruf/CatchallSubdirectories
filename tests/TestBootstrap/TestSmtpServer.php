<?php

namespace Tests\TestBootstrap;

use Exception;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

/**
 * @see https://github.com/antespi/docker-imap-devel
 */
class TestSmtpServer
{
    public const string STARTUP_MESSAGE = 'SSL parameters regeneration completed';

    private string $processDescriptor;

    public function __construct(
        private readonly string $containerName = 'local',
        private readonly int $timeoutSeconds = 60
    ) {
        $this->processDescriptor = Str::of("--name $containerName")
            ->append(
                ' -p 40025:25',
                ' -p 40143:143',
                ' -p 40993:993',
            )
            ->append(
                ' -e MAILNAME=local',
                // We must not specify a normal mail user, since the catch-all is configured automatically
                //' -e MAIL_ADDRESS=debug@local',
                //' -e MAIL_PASS=debug'
            )
            ->append(' -t antespi/docker-imap-devel:latest')
            ->toString();
    }

    public function start(): static
    {
        // Ensure that the container does not exist
        try {
            $this->remove();
        } catch (Exception) {
        }

        $this->run("docker run -d $this->processDescriptor");

        return $this;
    }

    public function stop(): static
    {
        $this->run("docker stop $this->containerName");

        return $this;
    }

    public function remove(): static
    {
        $this->run("docker rm -f $this->containerName");

        return $this;
    }

    public function log(): string
    {
        return $this->run("docker logs $this->containerName")->output();
    }

    public function awaitMessage(string $message)
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

    public function awaitStart(): self
    {
        $this->awaitMessage(static::STARTUP_MESSAGE);

        return $this;
    }

    public function createTestMails(): static
    {
        TestMails::sendTestMails($this->containerName, 'debug@local', 'debug');

        return $this;
    }

    private function run(string $command): ProcessResult
    {
        $process = Process::command($command)
            ->timeout($this->timeoutSeconds)
            ->start()
            ->wait();

        if ($process->exitCode() !== 0) {
            Log::error($process->output());
            throw new Exception($process->errorOutput());
        }

        return $process;
    }
}
