<?php

namespace Tests\TestBootstrap;

use Exception;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;

/**
 * @see https://github.com/antespi/docker-imap-devel
 */
class TestMailServer extends TestServer
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

    public function createTestMails(string $hostname = 'localhost', int $port = 40025): static
    {
        $transport = new EsmtpTransport($hostname, $port);
        $transport->setAutoTls(false);
        $mailer = new Mailer($transport);

        $mailer->send(
            (new Email)
                ->from('foo@foo.local')
                ->to('debug@local')
                ->subject('Test Email')
                ->text('Hello World!')
        );

        $mailer->send(
            (new Email)
                ->from('bar@bar.local')
                ->to('another@local')
                ->subject('Test Email')
                ->text('Hello World!')
        );

        $this->awaitMessage('saved mail to INBOX');

        return $this;
    }
}
