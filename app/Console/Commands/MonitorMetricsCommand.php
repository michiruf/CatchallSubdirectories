<?php

namespace App\Console\Commands;

use App\Actions\ConnectImap;
use Ddeboer\Imap\MailboxInterface;
use Illuminate\Console\Command;
use Sentry\Metrics\MetricsUnit;
use Symfony\Component\Console\Attribute\AsCommand;
use function Sentry\metrics;

#[AsCommand(name: 'app:print-directory-summary')]
class MonitorMetricsCommand extends Command
{
    protected $description = 'Print a summary of directories';

    protected $signature = 'app:print-directory-summary';

    public function handle(): int
    {
        $connection = app(ConnectImap::class)->execute();

        $directoryCount = collect($connection->getMailboxes())->count();

        metrics()->gauge(
            'imap_directories',
            $directoryCount,
        );

        // TODO How could we report the directories existent?
        //collect($connection->getMailboxes())
        //    ->sortKeys()
        //    ->each(fn (MailboxInterface $directory) => $this->line("{$directory->getName()} -> {$directory->count()}"));

        $connection->close();

        return static::SUCCESS;
    }
}
