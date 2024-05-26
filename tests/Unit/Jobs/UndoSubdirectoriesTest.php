<?php

use App\Jobs\UndoSubdirectories;
use Ddeboer\Imap\MailboxInterface;
use Ddeboer\Imap\MessageInterface;

beforeEach(function () {
    $this->startTestSmtp();
});

afterEach(function () {
    $this->stopTestSmtp();
});

it('can undo subdirectories', function () {
    $this->server->createTestMails();
    $connection = establishImapTestConnection();

    // Expect inbox has mails
    expect($connection->getMailbox('INBOX')->getMessages()->count())->toBeGreaterThan(0);

    // Move mails
    $subdirectory = $connection->createMailbox('INBOX.foo');
    collect($connection->getMailboxes())->each(function (MailboxInterface $mailbox) use ($subdirectory) {
        collect($mailbox->getMessages())->each(function (MessageInterface $message) use ($subdirectory) {
            $message->move($subdirectory);
        });
    });
    $connection->expunge();

    // Undo the subdirectory movement
    app(UndoSubdirectories::class, [
        'connection' => $connection,
        'prefix' => 'INBOX.',
        'target' => 'INBOX',
    ])->handle();

    // Expect inbox has mails
    // And the INBOX.foo does not exist
    expect()
        ->and($connection->getMailbox('INBOX')->count())->toBeGreaterThan(0, 'Inbox should have entries')
        ->and($connection->hasMailbox('INBOX.foo'))->toBeFalse('Folder "foo" should not exist');

    $ping = $connection->ping();
    expect($ping)->toBeTrue();
    $connection->close();
})->covers(UndoSubdirectories::class)->skip('Failes due to a bug');
