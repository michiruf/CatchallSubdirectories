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
        $this->line('DEBUG: handle called, connection: '.get_class($connection));
        $mailboxes = $connection->getMailboxes();
        $this->line('DEBUG: mailbox count: '.count($mailboxes));
        $this->line('DEBUG: keys: '.implode(', ', array_keys($mailboxes)));

        collect($mailboxes)
            ->sortKeys()
            ->each(fn (MailboxInterface $directory) => $this->line("{$directory->getName()} -> {$directory->count()}"));

        return static::SUCCESS;
    }
}
