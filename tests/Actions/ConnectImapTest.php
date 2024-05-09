<?php

use App\Actions\ConnectImap;

it('can connect to imap', function () {
    $connection = app(ConnectImap::class, [
        'hostname' => 'localhost',
        'port' => 40993,
        'username' => 'debug@local',
        'password' => 'debug',
        'validateCert' => false,
    ])->execute();

    expect($connection->ping())->toBeTrue();
})->covers(ConnectImap::class);
