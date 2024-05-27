<?php

namespace App\Actions;

use Ddeboer\Imap\Connection;
use Ddeboer\Imap\MailboxInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
            throw new RuntimeException("Cannot delete non empty directory '{$mailbox->getName()}'.");
        }

        Log::info("Deleting folder {$mailbox->getName()}");

        // Reopen the resource manually, specifying that it should not be selected with OP_HALFOPEN
        // Since this resource is then already opened, the library will not open it again, and we can
        // delete the resource properly on the dovecot test server
        // After that, we select a parent resource again to avoid php error reporting
        imap_reopen($this->connection->getResource()->getStream(), $mailbox->getFullEncodedName(), OP_HALFOPEN);
        $this->connection->deleteMailbox($mailbox);
        $newSelectedMailbox = Str::of($mailbox->getFullEncodedName())->beforeLast('}')->append('}')->toString();
        imap_reopen($this->connection->getResource()->getStream(), $newSelectedMailbox);

        // TODO Open an issue
        throw_if(
            ! $this->connection->ping(),
            'There is currently a bug, that dovecot responds with '.
            '"Selected mailbox was deleted, have to disconnect." that is either '.
            'issued in the test environments 8 year old dovecot server, or in '.
            'the missing ability of the current library to reconnect a connection '.
            'or not open a resource when deleting. '.
            'The other actions that might got performed before, still were executed '.
            'properly.'
        );

        if (! $this->noExpunge) {
            // Finish the transaction by calling expunge
            // https://www.php.net/manual/de/function.imap-expunge.php
            $this->connection->expunge();
        }
    }
}
