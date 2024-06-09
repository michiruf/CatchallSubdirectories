<?php

namespace Tests\TestBootstrap;

use Illuminate\Process\ProcessResult;

class TestDeployServer extends TestServer
{
    public static ?string $startupMessage = 'syslogd entered RUNNING';

    public function __construct(
        public string $variant,
        array $processEnv,
        int $timeoutSeconds = 60,
    ) {
        parent::__construct($timeoutSeconds, $processEnv);
    }

    public function start(): static
    {
        $this->run($this->composeString('up -d --build'));

        return $this;
    }

    public function stop(): static
    {
        $this->run($this->composeString('stop'));

        return $this;
    }

    public function clearPersistence(): static
    {
        $this->run($this->composeString('down -v'));

        return $this;
    }

    public function log(): string
    {
        return $this->run($this->composeString('logs app'))->output();
    }

    public function exec(string $command): ProcessResult
    {
        return $this->run($this->composeString("exec app $command"), false);
    }

    public function composeString($command): string
    {
        return "docker compose -f _docker/$this->variant/docker-compose.yml $command";
    }
}
