<?php

use App\Actions\ConnectImap;
use App\Console\Commands\PrintDirectorySummaryCommand;
use Symfony\Component\Console\Command\Command;

beforeEach(function () {
    $this->startTestSmtp();
});

afterEach(function () {
    $this->stopTestSmtp();
});

it('can invoke command app:print-directory-summary', function () {
    $this->server->createTestMails();
    $connection = establishImapTestConnection();

    $connectImapMock = mock(ConnectImap::class)
        ->shouldReceive('execute')
        ->andReturn($connection)
        ->getMock();
    app()->bind(ConnectImap::class, fn () => $connectImapMock);

    $this->artisan('app:print-directory-summary')
        ->expectsOutput('Drafts -> 0')
        ->expectsOutput('INBOX -> 2')
        ->expectsOutput('Sent -> 0')
        ->expectsOutput('Trash -> 0')
        ->assertExitCode(Command::SUCCESS);
})->covers(PrintDirectorySummaryCommand::class);
