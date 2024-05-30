<?php

namespace App\Console\Commands;

use App\Jobs\CatchAllSubdirectories;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:catch-all-subdirectories')]
class CatchAllSubdirectoriesCommand extends Command
{
    protected $description = 'Move mails into a subdirectory depending on the catchall prefix used';

    protected $signature = 'app:catch-all-subdirectories {--detach}';

    public function handle(): int
    {
        $this->option('detach')
            ? CatchAllSubdirectories::dispatch()
            : CatchAllSubdirectories::dispatchSync();

        return static::SUCCESS;
    }
}
