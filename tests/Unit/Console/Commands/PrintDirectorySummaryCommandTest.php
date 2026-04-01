<?php

use App\Console\Commands\PrintDirectorySummaryCommand;
use Symfony\Component\Console\Command\Command;
use Tests\TestBootstrap\Traits\CanTestMailServer;

uses(CanTestMailServer::class);

beforeEach(function () {
    $this->startTestServer();
});

afterEach(function () {
    $this->stopTestServer();
});

it('can invoke command app:print-directory-summary', function () {
    $this->server->createTestMails();
    $connection = $this->establishImapTestConnection(true);

    $this->artisan('app:print-directory-summary')
        ->expectsOutput('Drafts -> 0')
        ->expectsOutput('INBOX -> 2')
        ->expectsOutput('Sent -> 0')
        ->expectsOutput('Trash -> 0')
        ->assertExitCode(Command::SUCCESS);

    $connection->close();
})->covers(PrintDirectorySummaryCommand::class);
