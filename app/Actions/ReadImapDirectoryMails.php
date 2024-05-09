<?php

namespace App\Actions;

use Ddeboer\Imap\Connection;
use Ddeboer\Imap\Message;
use Illuminate\Support\Collection;

class ReadImapDirectoryMails
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ?string $inboxName = null
    ) {
    }

    /**
     * @return Collection<int, Message>
     */
    public function execute(): Collection
    {
        $inboxName = $this->inboxName ?? config('app.mail.inboxName', 'INBOX');
        $mailbox = $this->connection->getMailbox($inboxName);
        return collect($mailbox->getMessages());
    }
}
