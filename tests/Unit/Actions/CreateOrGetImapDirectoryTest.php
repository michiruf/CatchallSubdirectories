<?php

use App\Actions\CreateOrGetImapDirectory;

beforeEach(function () {
    $this->startTestSmtp();
});

afterEach(function () {
    $this->stopTestSmtp();
});

it('can create or get an imap directory', function () {
    $connection = establishImapTestConnection();

    // Create the directory
    $directory = app(CreateOrGetImapDirectory::class, [
        'connection' => $connection,
        'directory' => 'foo',
    ])->execute();
    expect($directory)
        ->getName()->toBe('INBOX\\foo')
        ->count()->toBe(0)
        ->and($connection->hasMailbox('INBOX\\foo'))->toBeTrue();

    // Get the directory again
    $sameDirectory = app(CreateOrGetImapDirectory::class, [
        'connection' => $connection,
        'directory' => 'foo',
    ])->execute();
    expect($sameDirectory->getFullEncodedName())
        ->toBe($directory->getFullEncodedName());

    $connection->close();
})->covers(CreateOrGetImapDirectory::class);
