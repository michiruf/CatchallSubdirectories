<?php

use App\Imap\LazyInitializedConnection;
use Tests\TestBootstrap\Traits\CanTestMailServer;

uses(CanTestMailServer::class);

beforeEach(function () {
    $this->startTestServer();
});

afterEach(function () {
    $this->stopTestServer();
});

it('can connect to imap', function () {
    $connection = $this->establishImapTestConnection();

    expect($connection->ping())->toBeTrue();
})->covers(LazyInitializedConnection::class);
