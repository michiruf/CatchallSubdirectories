<?php

namespace Tests\TestBootstrap;

class TestDeployServer extends TestServer
{
    public static ?string $startupMessage = 'syslogd entered RUNNING';

    public function __construct(
        private readonly string $containerName = 'deploy',
        int $timeoutSeconds = 60,
        string $sshPassword = 'test',
        string $pathFromBase = '_deploy'
    ) {
        parent::__construct($timeoutSeconds);
        $this->processEnv = [
            'USE_PUBLIC_KEY' => 'false',
            'SSH_PASSWORD' => $sshPassword,
        ];
        $this->path = base_path($pathFromBase);
    }

    public function start(): static
    {
        $this->run('docker compose up -d');

        return $this;
    }

    public function stop(): static
    {
        $this->run('docker compose stop');

        return $this;
    }

    public function clearPersistence(): static
    {
        $this->run('docker compose down -v');

        return $this;
    }

    public function log(): string
    {
        return $this->run("docker compose logs $this->containerName")->output();
    }
}
