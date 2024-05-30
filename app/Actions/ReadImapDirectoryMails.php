<?php

namespace App\Actions;

use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\MessageInterface;
use Illuminate\Support\Collection;

use function collect;

class ReadImapDirectoryMails
{
    public function __construct(
        private readonly ConnectionInterface $connection,
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

        /** @phpstan-ignore-next-line */
        return collect($mailbox->getMessages());
    }
}
