<?php

use App\Actions\ConnectImap;
use App\Actions\ReadSmtpDirectoryMails;

it('can read smtp directory mails', function () {
    $connection = app(ConnectImap::class, [
        'hostname' => 'localhost',
        'port' => 40993,
        'username' => 'debug@local',
        'password' => 'debug',
        'validateCert' => false,
    ])->execute();

    $mails = app(ReadSmtpDirectoryMails::class, [
        'connection' => $connection
    ])->execute();

    expect($mails)
        ->not->toBeEmpty('There are not test mails set up in the smtp server')
        //
    ;
})->covers(ReadSmtpDirectoryMails::class);
