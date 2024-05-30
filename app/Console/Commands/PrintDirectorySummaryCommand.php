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
        collect($connection->getMailboxes())
            ->sortKeys()
            ->each(fn (MailboxInterface $directory) => $this->line("{$directory->getName()} -> {$directory->count()}"));

        return static::SUCCESS;
    }
}
