<?php

use App\Actions\ReadImapDirectoryMails;
use Tests\TestBootstrap\Traits\CanTestSmtpServer;

uses(CanTestSmtpServer::class);

beforeEach(function () {
    $this->startTestSmtp();
});

afterEach(function () {
    $this->stopTestSmtp();
});

it('can read imap directory mails', function () {
    $this->server->createTestMails();
    $connection = $this->establishImapTestConnection();

    $mails = app(ReadImapDirectoryMails::class, [
        'connection' => $connection,
    ])->execute();

    expect($mails)
        ->not->toBeEmpty('There are no test mails set up in the smtp server');

    $ping = $connection->ping();
    expect($ping)->toBeTrue();
})->covers(ReadImapDirectoryMails::class);
