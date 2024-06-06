<?php

use App\Imap\LazyInitializedConnection;
use Tests\TestBootstrap\Traits\CanTestSmtpServer;

uses(CanTestSmtpServer::class);

beforeEach(function () {
    $this->startTestSmtp();
});

afterEach(function () {
    $this->stopTestSmtp();
});

it('can connect to imap', function () {
    $connection = $this->establishImapTestConnection();

    expect($connection->ping())->toBeTrue();
})->covers(LazyInitializedConnection::class);
