<?php

use App\Actions\ReadImapDirectoryMails;
use Tests\TestBootstrap\Traits\CanTestMailServer;

uses(CanTestMailServer::class);

beforeEach(function () {
    $this->startTestServer();
});

afterEach(function () {
    $this->stopTestServer();
});

it('can read imap directory mails', function () {
    $this->server->createTestMails();
    $connection = $this->establishImapTestConnection();

    $mails = app(ReadImapDirectoryMails::class, [
        'connection' => $connection,
    ])->execute();

    expect($mails)
        ->not->toBeEmpty('There are no test mails set up in the mail server');

    $ping = $connection->ping();
    expect($ping)->toBeTrue();
})->covers(ReadImapDirectoryMails::class);
