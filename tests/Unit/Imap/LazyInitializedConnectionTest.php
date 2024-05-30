<?php

use App\Imap\LazyInitializedConnection;

beforeEach(function () {
    $this->startTestSmtp();
});

afterEach(function () {
    $this->stopTestSmtp();
});

it('can connect to imap', function () {
    $connection = establishImapTestConnection();

    expect($connection->ping())->toBeTrue();
})->covers(LazyInitializedConnection::class);
