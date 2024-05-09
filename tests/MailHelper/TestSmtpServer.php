<?php

namespace Tests\MailHelper;

use Exception;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class TestSmtpServer
{
    public const string STARTUP_MESSAGE = 'SSL parameters regeneration completed';

    private string $processDescriptor;

    public function __construct(
        private readonly string $containerName = 'local',
        private readonly int $timeoutSeconds = 60
    ) {
        // TODO Use this one -> no this is smtp (is there also imap?)
        // https://github.com/rnwood/smtp4dev/wiki/Configuration
        // docker run --rm -it -p 5000:80 -p 2525:25 rnwood/smtp4dev

        $this->processDescriptor = Str::of("--name $containerName")
            ->append(
                ' -p 40025:25',
                ' -p 40143:143',
                ' -p 40993:993',
            )
            ->append(
                ' -e MAILNAME=local',
                // Specifying these values is not random, but must be set for a catchall! (except MAILNAME)
                ' -e MAIL_ADDRESS=debug@local',
                ' -e MAIL_PASS=debug'
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
        TestMailCreator::create($this->containerName, 'debug@local', 'debug');

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
