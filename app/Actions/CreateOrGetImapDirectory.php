<?php

namespace App\Actions;

use App\Settings\CatchAllSettings;
use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\MailboxInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateOrGetImapDirectory
{
    public function __construct(
        private readonly CatchAllSettings $settings,
        private readonly ConnectionInterface $connection,
        private readonly string $directory,
    ) {}

    public function execute(): MailboxInterface
    {
        $inboxName = $this->settings->inboxName();
        $directoryIdentifier = Str::of($inboxName)->append('.')->append($this->directory)->toString();

        if (! $this->connection->hasMailbox($directoryIdentifier)) {
            $directory = $this->connection->createMailbox($directoryIdentifier);

            if ($this->settings->subscribe_new_folders) {
                Log::info("Subscribing to new folder {$directory->getName()}");
                imap_subscribe($this->connection->getResource()->getStream(), $directory->getFullEncodedName());
            }

            return $directory;
        }

        return $this->connection->getMailbox($directoryIdentifier);
    }
}
