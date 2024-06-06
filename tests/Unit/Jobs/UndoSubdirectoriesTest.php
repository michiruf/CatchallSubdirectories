<?php

use App\Jobs\UndoSubdirectories;
use Ddeboer\Imap\MailboxInterface;
use Ddeboer\Imap\MessageInterface;
use Tests\TestBootstrap\Traits\CanTestSmtpServer;

uses(CanTestSmtpServer::class);

beforeEach(function () {
    $this->startTestSmtp();
});

afterEach(function () {
    $this->stopTestSmtp();
});

it('can undo subdirectories', function () {
    $this->server->createTestMails();
    $connection = $this->establishImapTestConnection(true);

    // Expect inbox has mails exist
    expect($connection->getMailbox('INBOX')->getMessages()->count())->toBeGreaterThan(0);

    // Move mails and expect new directory has mails
    $subdirectory = $connection->createMailbox('INBOX.foo');
    collect($connection->getMailboxes())->each(function (MailboxInterface $mailbox) use ($subdirectory) {
        collect($mailbox->getMessages())->each(function (MessageInterface $message) use ($subdirectory) {
            $message->move($subdirectory);
        });
    });
    $connection->expunge();
    expect($connection->getMailbox('INBOX.foo')->count())->toBeGreaterThan(0, 'Folder "foo" should have entries');

    // Undo the subdirectory movement
    UndoSubdirectories::dispatch(
        prefix: 'INBOX.',
        target: 'INBOX',
    );

    // Expect inbox has mails
    // And the INBOX.foo does not exist
    expect()
        ->and($connection->getMailbox('INBOX')->count())->toBeGreaterThan(0, 'Inbox should have entries')
        ->and($connection->hasMailbox('INBOX.foo'))->toBeFalse('Folder "foo" should not exist');

    $ping = $connection->ping();
    expect($ping)->toBeTrue();
    $connection->close();
})->covers(UndoSubdirectories::class);
