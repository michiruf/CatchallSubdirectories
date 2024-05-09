<?php

namespace App\Actions;

use Ddeboer\Imap\Connection;
use Ddeboer\Imap\MessageInterface;
use Illuminate\Support\Collection;

class ReadImapDirectoryMails
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ?string $inboxName = null
    ) {
    }

    /**
     * @return Collection<int, MessageInterface>
     */
    public function execute(): Collection
    {
        $inboxName = $this->inboxName ?? config('catchall.inbox_name', 'INBOX');
        $mailbox = $this->connection->getMailbox($inboxName);
        return collect($mailbox->getMessages());
    }
}
