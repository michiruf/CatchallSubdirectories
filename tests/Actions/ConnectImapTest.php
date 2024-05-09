<?php

use App\Actions\ConnectImap;

it('can connect to imap', function () {
    $connection = establishImapTestConnection();
    expect($connection->ping())->toBeTrue();
})->covers(ConnectImap::class);
