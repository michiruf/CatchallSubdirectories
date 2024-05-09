<?php

use App\Actions\ConnectImap;
use App\Actions\ReadImapDirectoryMails;

it('can read imap directory mails', function () {
    $this->server->createTestMails();

    $connection = app(ConnectImap::class, [
        'hostname' => 'localhost',
        'port' => 40993,
        'username' => 'debug@local',
        'password' => 'debug',
        'validateCert' => false,
    ])->execute();


    $mails = app(ReadImapDirectoryMails::class, [
        'connection' => $connection
    ])->execute();

    expect($mails)
        ->not->toBeEmpty('There are no test mails set up in the smtp server');
})->covers(ReadImapDirectoryMails::class);
