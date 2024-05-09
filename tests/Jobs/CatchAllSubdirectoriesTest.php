<?php

use App\Actions\ConnectImap;
use App\Jobs\CatchAllSubdirectories;

it('can create catch all mail subdirectories', function () {
    $this->server->createTestMails();
    $connection = establishImapTestConnection();

    app(CatchAllSubdirectories::class, [
        'connection' => $connection,
        'mailDomain' => 'local',
    ])->execute();

    // Expectations here depend on data in `TestMails::sendTestMails`
    $debugCount = $connection->getMailbox('INBOX\\debug')->count();
    $anotherCount = $connection->getMailbox('INBOX\\another')->count();
    $inboxCount = $connection->getMailbox('INBOX')->count() - $debugCount - $anotherCount;
    expect()
        ->and($inboxCount)->toBe(0, 'Inbox should be empty')
        ->and($debugCount)->toBeGreaterThan(0, 'Folder "debug" should have entries')
        ->and($anotherCount)->toBeGreaterThan(0, 'Folder "another" should have entries');
})->covers(ConnectImap::class);
