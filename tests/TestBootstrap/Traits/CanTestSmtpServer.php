<?php

namespace Tests\TestBootstrap\Traits;

use Ddeboer\Imap\ConnectionInterface;
use Tests\TestBootstrap\TestSmtpServer;

trait CanTestSmtpServer
{
    protected TestSmtpServer $server;

    public function startTestSmtp(): void
    {
        $this->server = (new TestSmtpServer(timeoutSeconds: 120))
            ->start()
            ->awaitStart();

        // NOTE There should be no expect here, since we do want to see risky tests (with 0 assertions)
        expect($this->server->log())
            ->not->toBeEmpty();
    }

    public function stopTestSmtp(): void
    {
        $this->server
            ->stop()
            ->clearPersistence();
    }

    public function establishImapTestConnection(bool $bindAgain = false): ConnectionInterface
    {
        $connection = app(ConnectionInterface::class, [
            'hostname' => 'localhost',
            'port' => 40993,
            'username' => 'debug@local',
            'password' => 'debug',
            'validateCert' => false,
        ]);

        // May bind again to not depend on the parameters
        // Ensure that the connection gets closed manually in that case, since __destruct will not get called
        if ($bindAgain) {
            app()->bind(ConnectionInterface::class, fn () => $connection);
        }

        return $connection;
    }
}
