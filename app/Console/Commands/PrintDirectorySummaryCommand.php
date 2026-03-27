<?php

namespace App\Console\Commands;

use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\MailboxInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:print-directory-summary')]
class PrintDirectorySummaryCommand extends Command
{
    protected $description = 'Print a summary of directories';

    protected $signature = 'app:print-directory-summary';

    public function handle(ConnectionInterface $connection): int
    {
        // TODO: Remove debug
        dump('Command handle - connection class:', get_class($connection));
        $mailboxes = $connection->getMailboxes();
        dump('Command handle - mailbox count:', count($mailboxes));
        dump('Command handle - mailbox keys:', array_keys($mailboxes));

        collect($mailboxes)
            ->sortKeys()
            ->each(fn (MailboxInterface $directory) => $this->line("{$directory->getName()} -> {$directory->count()}"));

        return static::SUCCESS;
    }
}
