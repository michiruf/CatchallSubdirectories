<?php

use App\Actions\DeleteImapDirectory;
use Ddeboer\Imap\MessageInterface;
use Tests\TestBootstrap\Traits\CanTestMailServer;

uses(CanTestMailServer::class);

beforeEach(function () {
    $this->startTestServer();
});

afterEach(function () {
    $this->stopTestServer();
});

it('can delete directories', function () {
    $connection = $this->establishImapTestConnection();
    $directoryName = 'INBOX.foo';

    $connection->createMailbox($directoryName);

    app(DeleteImapDirectory::class, [
        'connection' => $connection,
        'directory' => $directoryName,
    ])->execute();

    expect($connection->hasMailbox($directoryName))->toBeFalse();

    $ping = $connection->ping();
    expect($ping)->toBeTrue();
});

it('cannot delete non-empty directories', function () {
    $connection = $this->establishImapTestConnection();
    $this->server->createTestMails();
    $directoryName = 'INBOX.foo';

    // Create directory and move mails to there
    $directory = $connection->createMailbox($directoryName);
    collect($connection->getMailbox('INBOX')->getMessages())
        ->each(function (MessageInterface $message) use ($directory) {
            $message->move($directory);
        });

    app(DeleteImapDirectory::class, [
        'connection' => $connection,
        'directory' => $directoryName,
    ])->execute();
})->throws(RuntimeException::class, 'Cannot delete non empty directory \'INBOX.foo\'.');

it('can delete empty directories by force', function () {
    $connection = $this->establishImapTestConnection();
    $this->server->createTestMails();
    $directoryName = 'INBOX.foo';

    // Create directory and move mails to there
    $directory = $connection->createMailbox($directoryName);
    collect($connection->getMailbox('INBOX')->getMessages())
        ->each(function (MessageInterface $message) use ($directory) {
            $message->move($directory);
        });

    app(DeleteImapDirectory::class, [
        'connection' => $connection,
        'directory' => $directoryName,
        'forceDelete' => true,
    ])->execute();

    expect($connection->hasMailbox($directoryName))->toBeFalse();

    $ping = $connection->ping();
    expect($ping)->toBeTrue();
});
