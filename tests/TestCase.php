<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\TestBootstrap\TestSmtpServer;

abstract class TestCase extends BaseTestCase
{
    protected TestSmtpServer $server;

    protected function startTestSmtp(): void
    {
        $this->server = (new TestSmtpServer(timeoutSeconds: 120))
            ->start()
            ->awaitStart();

        // NOTE There should be no expect here, since we do want to see risky tests (with 0 assertions)
        expect($this->server->log())
            ->not->toBeEmpty();
    }

    protected function stopTestSmtp(): void
    {
        $this->server
            ->stop()
            ->clearPersistence();
    }
}
