<?php

use App\Jobs\CatchAllSubdirectories;

beforeEach(function () {
    $this->startTestSmtp();
});

afterEach(function () {
    $this->stopTestSmtp();
});

it('can create catch all mail subdirectories', function () {
    $this->server->createTestMails();
    $connection = establishImapTestConnection();

    // Expect inbox has mails
    expect($connection->getMailbox('INBOX')->getMessages()->count())->toBeGreaterThan(0);

    // Perform the subdirectory movement
    app(CatchAllSubdirectories::class, [
        'connection' => $connection,
        'mailDomain' => 'local',
    ])->handle();

    // Expect inbox has no mails
    // And the specific folders do have mails
    // Expectations here depend on data in `TestMails::sendTestMails`
    expect()
        ->and($connection->getMailbox('INBOX')->count())->toBe(0, 'Inbox should be empty after sorting into subdirectory')
        ->and($connection->getMailbox('INBOX.Debug')->count())->toBeGreaterThan(0, 'Folder "debug" should have entries')
        ->and($connection->getMailbox('INBOX.Another')->count())->toBeGreaterThan(0, 'Folder "another" should have entries');

    $ping = $connection->ping();
    expect($ping)->toBeTrue();
    $connection->close();
})->covers(CatchAllSubdirectories::class);
