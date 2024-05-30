<?php

namespace App\Actions;

use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\MailboxInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateOrGetImapDirectory
{
    public function __construct(
        private readonly ConnectionInterface $connection,
        private readonly string $directory,
        private readonly ?string $inboxName = null,
        private readonly bool $subscribe = false
    ) {
    }

    public function execute(): MailboxInterface
    {
        $inboxName = $this->inboxName ?? config('catchall.inbox_name', 'INBOX');
        $directoryIdentifier = Str::of($inboxName)->append('.')->append($this->directory)->toString();

        if (! $this->connection->hasMailbox($directoryIdentifier)) {
            $directory = $this->connection->createMailbox($directoryIdentifier);

            if ($this->subscribe) {
                Log::info("Subscribing to new folder {$directory->getName()}");
                imap_subscribe($this->connection->getResource()->getStream(), $directory->getFullEncodedName());
            }

            return $directory;
        }

        return $this->connection->getMailbox($directoryIdentifier);
    }
}
