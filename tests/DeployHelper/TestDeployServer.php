<?php

namespace Tests\DeployHelper;

use Exception;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class TestDeployServer
{
    public const string STARTUP_MESSAGE = 'syslogd entered RUNNING';

    private array $processEnv;

    public function __construct(
        private readonly string $containerName = 'deploy',
        private readonly int $timeoutSeconds = 60,
        readonly string $sshPassword = 'test',
    ) {
        $this->processEnv = [
            'USE_PUBLIC_KEY' => 'false',
            'SSH_PASSWORD' => $sshPassword,
        ];
    }

    public function start(): static
    {
        // Ensure that the container does not exist
        try {
            $this->remove();
        } catch (Exception) {
        }

        $this->run('docker compose up -d');

        return $this;
    }

    public function stop(): static
    {
        $this->run('docker compose stop');

        return $this;
    }

    public function remove(): static
    {
        $this->run('docker compose down');

        return $this;
    }

    public function clearPersistence()
    {
        $this->run('docker volume prune -af');
    }

    public function log(): string
    {
        return $this->run("docker compose logs $this->containerName")->output();
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

    private function run(string $command): ProcessResult
    {
        $process = Process::path(base_path('_deploy'))
            ->command($command)
            ->env($this->processEnv)
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
