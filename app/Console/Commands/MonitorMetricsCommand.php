<?php

namespace App\Console\Commands;

use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\MailboxInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

use function Sentry\traceMetrics;

#[AsCommand(name: 'app:monitor-metrics')]
class MonitorMetricsCommand extends Command
{
    protected $description = 'Monitor metrics';

    protected $signature = 'app:monitor-metrics';

    public function handle(ConnectionInterface $connection): int
    {
        $directoryCount = collect($connection->getMailboxes())
            ->filter(fn (MailboxInterface $directory) => str($directory->getName())->startsWith('INBOX'))
            ->count();

        traceMetrics()->gauge(
            'imap_directories',
            $directoryCount,
        );

        // TODO How could we report the existing directories?
        // collect($connection->getMailboxes())
        //    ->sortKeys()
        //    ->each(fn (MailboxInterface $directory) => $this->line("{$directory->getName()} -> {$directory->count()}"));

        return static::SUCCESS;
    }
}
