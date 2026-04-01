<?php

namespace Tests\TestBootstrap\Traits;

use App\Settings\CatchAllSettings;
use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\Server;
use Tests\TestBootstrap\TestSmtpServer;

trait CanTestSmtpServer
{
    protected TestSmtpServer $server;

    public function startTestSmtp(string $mailDomain = 'local'): void
    {
        $this->server = (new TestSmtpServer(timeoutSeconds: 120))
            ->start()
            ->awaitStart();

        // NOTE There should be no expect here, since we do want to see risky tests (with 0 assertions)
        expect($this->server->log())
            ->not->toBeEmpty();

        $settings = app(CatchAllSettings::class);
        $settings->mail_domain = $mailDomain;
        $settings->save();
    }

    public function stopTestSmtp(): void
    {
        $this->server
            ->stop()
            ->clearPersistence();
    }

    public function establishImapTestConnection(bool $bindAgain = false): ConnectionInterface
    {
        $server = new Server('localhost', 40993, '/imap/ssl/novalidate-cert');
        $connection = $server->authenticate('debug@local', 'debug');

        // May bind again to not depend on the parameters
        // Ensure that the connection gets closed manually in that case, since __destruct will not get called
        if ($bindAgain) {
            app()->bind(ConnectionInterface::class, fn () => $connection);
        }

        return $connection;
    }
}
