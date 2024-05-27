<?php

namespace App\Console\Commands;

use App\Actions\ConnectImap;
use Ddeboer\Imap\MailboxInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:print-directory-summary')]
class PrintDirectorySummaryCommand extends Command
{
    protected $description = 'Print a summary of directories';

    protected $signature = 'app:print-directory-summary';

    public function handle(): int
    {
        $connection = app(ConnectImap::class)->execute();

        collect($connection->getMailboxes())
            ->each(fn (MailboxInterface $directory) => $this->line("{$directory->getName()} -> {$directory->count()}"));

        $connection->close();

        return static::SUCCESS;
    }
}
