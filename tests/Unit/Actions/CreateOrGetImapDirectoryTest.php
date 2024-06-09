<?php

use App\Actions\CreateOrGetImapDirectory;
use Tests\TestBootstrap\Traits\CanTestSmtpServer;

uses(CanTestSmtpServer::class);

beforeEach(function () {
    $this->startTestSmtp();
});

afterEach(function () {
    $this->stopTestSmtp();
});

it('can create or get an imap directory', function () {
    $connection = $this->establishImapTestConnection();

    // Create the directory
    $directory = app(CreateOrGetImapDirectory::class, [
        'connection' => $connection,
        'directory' => 'foo',
    ])->execute();
    expect($directory)
        ->getName()->toBe('INBOX.foo')
        ->count()->toBe(0)
        ->and($connection->hasMailbox('INBOX.foo'))->toBeTrue();

    // Get the directory again
    $sameDirectory = app(CreateOrGetImapDirectory::class, [
        'connection' => $connection,
        'directory' => 'foo',
    ])->execute();
    expect($sameDirectory->getFullEncodedName())
        ->toBe($directory->getFullEncodedName());

    $ping = $connection->ping();
    expect($ping)->toBeTrue();
})->covers(CreateOrGetImapDirectory::class);

it('can create and subscribe to an imap directory', function () {
    $connection = $this->establishImapTestConnection();

    $directory = app(CreateOrGetImapDirectory::class, [
        'connection' => $connection,
        'directory' => 'bar',
        'subscribe' => true,
    ])->execute();
    expect($directory)
        ->getName()->toBe('INBOX.bar')
        ->count()->toBe(0)
        ->and($connection->hasMailbox('INBOX.bar'))->toBeTrue();

    // NOTE There should be a check, that verifies that is it really subscribed,
    // but that might be not achievable with the current library

    $ping = $connection->ping();
    expect($ping)->toBeTrue();
})->covers(CreateOrGetImapDirectory::class);
