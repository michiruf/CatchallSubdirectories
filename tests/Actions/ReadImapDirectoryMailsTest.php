<?php

use App\Actions\ReadImapDirectoryMails;

beforeEach(function () {
    $this->startTestSmtp();
});

afterEach(function () {
    $this->stopTestSmtp();
});

it('can read imap directory mails', function () {
    $this->server->createTestMails();
    $connection = establishImapTestConnection();

    $mails = app(ReadImapDirectoryMails::class, [
        'connection' => $connection,
    ])->execute();

    expect($mails)
        ->not->toBeEmpty('There are no test mails set up in the smtp server');
})->covers(ReadImapDirectoryMails::class);
