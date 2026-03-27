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
    dump('Mail storage:', trim(shell_exec('docker exec local find / -path /proc -prune -o -path /sys -prune -o -name "cur" -print -o -name "new" -print 2>/dev/null')));
    dump('Dovecot mail location:', trim(shell_exec('docker exec local doveconf mail_location 2>&1')));

    // Test the resolved connection directly
    $resolved = app(ConnectionInterface::class);
    dump('Resolved class:', get_class($resolved));
    dump('Ping:', $resolved->ping());
    $mailboxes = $resolved->getMailboxes();
    dump('Mailbox count:', count($mailboxes));
    dump('Mailbox names:', array_map(fn ($m) => $m->getName(), $mailboxes));
    dump('Mailbox keys:', array_keys($mailboxes));

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
