<?php

namespace App\Actions;

use App\Settings\CatchAllSettings;
use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\MessageInterface;
use Illuminate\Support\Collection;

use function collect;

class ReadImapDirectoryMails
{
    public function __construct(
        private readonly CatchAllSettings $settings,
        private readonly ConnectionInterface $connection,
    ) {}

    /**
     * @return Collection<int, MessageInterface>
     */
    public function execute(): Collection
    {
        return collect($this->connection->getMailbox($this->settings->inboxName()));
    }
}
