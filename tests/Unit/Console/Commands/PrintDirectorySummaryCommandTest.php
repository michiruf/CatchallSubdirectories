<?php

use App\Console\Commands\PrintDirectorySummaryCommand;
use Ddeboer\Imap\ConnectionInterface;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command;
use Tests\TestBootstrap\Traits\CanTestSmtpServer;

uses(CanTestSmtpServer::class);

beforeEach(function () {
    $this->startTestSmtp();
});

afterEach(function () {
    $this->stopTestSmtp();
});

it('can invoke command app:print-directory-summary', function () {
    $this->server->createTestMails();
    $connection = $this->establishImapTestConnection(true);

    // Debug: diagnose CI failure
    $exitCode = Artisan::call('app:print-directory-summary');
    dump('Exit code:', $exitCode);
    dump('Command output:', Artisan::output());

    $this->artisan('app:print-directory-summary')
        ->expectsOutput('Drafts -> 0')
        ->expectsOutput('INBOX -> 2')
        ->expectsOutput('Sent -> 0')
        ->expectsOutput('Trash -> 0')
        ->assertExitCode(Command::SUCCESS);

    $connection->close();
})->covers(PrintDirectorySummaryCommand::class);
