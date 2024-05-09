<?php

namespace Tests;

use AllowDynamicProperties;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\MailHelper\TestSmtpServer;

abstract class TestCase extends BaseTestCase
{
    protected TestSmtpServer $server;

    protected function setUp(): void
    {
        parent::setUp();
        // We want to set the server up in the TestCase class, since beforeAll() of pest does not
        // have access to $this
        $this->startTestSmtp();
    }

    protected function tearDown(): void
    {
        $this->stopTestSmtp();
        parent::tearDown();
    }

    private function startTestSmtp(): void
    {
        $this->server = (new TestSmtpServer(timeoutSeconds: 120))
            ->start()
            ->awaitStart();

        expect($this->server->log())
            ->not->toBeEmpty();
    }

    private function stopTestSmtp(): void
    {
        $this->server->remove();
    }
}
