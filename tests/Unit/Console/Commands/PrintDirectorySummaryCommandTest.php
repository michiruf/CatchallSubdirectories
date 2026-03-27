<?php

use App\Console\Commands\PrintDirectorySummaryCommand;
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

    // Debug: diagnose CI failure without interfering with lazy connection
    dump('Container logs:', $this->server->log());
    dump('Docker version:', trim(shell_exec('docker version --format "{{.Server.Version}}"')));
    dump('PHP imap extension:', phpversion('imap'));
    dump('Maildir contents:', trim(shell_exec('docker exec local find /home/vmail -type f 2>&1')));
    dump('Dovecot mailbox config:', trim(shell_exec('docker exec local cat /etc/dovecot/conf.d/15-mailboxes.conf 2>&1')));

    Artisan::call('app:print-directory-summary');
    dump('Command output:', Artisan::output());

    $this->artisan('app:print-directory-summary')
        ->expectsOutput('Drafts -> 0')
        ->expectsOutput('INBOX -> 2')
        ->expectsOutput('Sent -> 0')
        ->expectsOutput('Trash -> 0')
        ->assertExitCode(Command::SUCCESS);

    $connection->close();
})->covers(PrintDirectorySummaryCommand::class);
