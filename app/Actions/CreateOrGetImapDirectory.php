<?php

namespace App\Actions;

use Ddeboer\Imap\Connection;
use Ddeboer\Imap\MailboxInterface;
use Illuminate\Support\Str;

class CreateOrGetImapDirectory
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $directory,
        private readonly ?string $inboxName = null
    ) {
    }

    public function execute(): MailboxInterface
    {
        $inboxName = $this->inboxName ?? config('app.mail.inboxName', 'INBOX');
        $directoryIdentifier = Str::of($inboxName)->append('\\')->append($this->directory)->toString();

        if (!$this->connection->hasMailbox($directoryIdentifier)) {
            $this->connection->createMailbox($directoryIdentifier);
        }

        return $this->connection->getMailbox($directoryIdentifier);
    }
}
