<?php

use App\Actions\ConnectImap;
use App\Actions\CreateOrGetImapDirectory;
use App\Actions\ReadImapDirectoryMails;

it('can create or get a imap directory', function () {
    $connection = app(ConnectImap::class, [
        'hostname' => 'localhost',
        'port' => 40993,
        'username' => 'debug@local',
        'password' => 'debug',
        'validateCert' => false,
    ])->execute();

    // Create the directory
    $directory = app(CreateOrGetImapDirectory::class, [
        'connection' => $connection,
        'directory' => 'foo'
    ])->execute();
    expect($directory)
        ->getName()->toBe('INBOX\\foo')
        ->count()->toBe(0)
        ->and($connection->hasMailbox('INBOX\\foo'))->toBeTrue();

    // Get the directory again
    $sameDirectory = app(CreateOrGetImapDirectory::class, [
        'connection' => $connection,
        'directory' => 'foo'
    ])->execute();
    expect($sameDirectory->getFullEncodedName())
        ->toBe($directory->getFullEncodedName());
})->covers(CreateOrGetImapDirectory::class);
