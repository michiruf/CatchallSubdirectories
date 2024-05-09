<?php

use App\Actions\CreateOrGetImapDirectory;

it('can create or get a imap directory', function () {
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
})->covers(CreateOrGetImapDirectory::class);
