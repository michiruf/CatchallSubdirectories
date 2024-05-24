<?php

use App\Actions\ConnectImap;

beforeEach(function () {
    $this->startTestSmtp();
});

afterEach(function () {
    $this->stopTestSmtp();
});

it('can connect to imap', function () {
    $connection = establishImapTestConnection();

    expect($connection->ping())->toBeTrue();

    $connection->close();
})->covers(ConnectImap::class);
