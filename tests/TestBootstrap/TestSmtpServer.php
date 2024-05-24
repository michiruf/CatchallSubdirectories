<?php

namespace Tests\TestBootstrap;

use Exception;
use Illuminate\Support\Str;

/**
 * @see https://github.com/antespi/docker-imap-devel
 */
class TestSmtpServer extends TestServer
{
    public static ?string $startupMessage = 'SSL parameters regeneration completed';

    private string $processDescriptor;

    public function __construct(
        private readonly string $containerName = 'local',
        int $timeoutSeconds = 60
    ) {
        parent::__construct($timeoutSeconds);
        $this->processDescriptor = Str::of("--name $containerName")
            ->append(
                ' -p 40025:25',
                ' -p 40143:143',
                ' -p 40993:993',
            )
            // We must not specify a normal mail user, since the catch-all is configured automatically
            // ' -e MAIL_ADDRESS=debug@local',
            // ' -e MAIL_PASS=debug'
            ->append(' -e MAILNAME=local')
            ->append(' -t antespi/docker-imap-devel:latest')
            ->toString();
    }

    public function start(): static
    {
        // Ensure that the container does not exist
        try {
            $this->clearPersistence();
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

    public function clearPersistence(): static
    {
        $this->run("docker rm -f $this->containerName");

        return $this;
    }

    public function log(): string
    {
        return $this->run("docker logs $this->containerName")->output();
    }

    public function createTestMails(): static
    {
        TestMails::sendTestMails($this->containerName, 'debug@local', 'debug');

        return $this;
    }
}
