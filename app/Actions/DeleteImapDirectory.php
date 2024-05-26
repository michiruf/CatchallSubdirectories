<?php

namespace App\Actions;

use Ddeboer\Imap\Connection;
use Ddeboer\Imap\MailboxInterface;
use RuntimeException;

class DeleteImapDirectory
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string|MailboxInterface $directory,
        private readonly bool $noExpunge = false,
    ) {
    }

    public function execute(): void
    {
        $mailbox = is_string($this->directory)
            ? $this->connection->getMailbox($this->directory)
            : $this->directory;

        if ($mailbox->count() !== 0) {
            throw new RuntimeException('Cannot delete non empty directories.');
        }

        $this->connection->deleteMailbox($mailbox);

        // TODO
        throw_if(
            ! $this->connection->ping(),
            'There is currently a bug, that dovecot responds with '.
            '"Selected mailbox was deleted, have to disconnect." that is either '.
            'issued in the test environments dovecot server, or in the missing ability '.
            'of the current library to reconnect a connection. '.
            'The other actions that might be performed before, still were executed properly.'
        );

        if (! $this->noExpunge) {
            // Finish the transaction by calling expunge
            // https://www.php.net/manual/de/function.imap-expunge.php
            $this->connection->expunge();
        }
    }
}
