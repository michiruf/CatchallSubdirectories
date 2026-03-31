<?php

namespace App\Actions;

use App\Settings\CatchAllSettings;
use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\MessageInterface;
use Illuminate\Support\Collection;

use function collect;

readonly class ReadImapDirectoryMails
{
    public function __construct(
        private CatchAllSettings $settings,
        private ConnectionInterface $connection,
    ) {}

    /**
     * @return Collection<int, MessageInterface>
     */
    public function execute(): Collection
    {
        return collect($this->connection->getMailbox($this->settings->inboxName()));
    }
}
