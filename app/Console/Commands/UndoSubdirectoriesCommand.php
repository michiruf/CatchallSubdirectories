<?php

namespace App\Console\Commands;

use App\Jobs\UndoSubdirectories;
use App\Loggers\LaravelCommandLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Attribute\AsCommand;
use function Laravel\Prompts\text;

#[AsCommand(name: 'app:undo-subdirectories')]
class UndoSubdirectoriesCommand extends Command
{
    protected $description = 'Undo moving mails into a subdirectory';

    protected $signature = 'app:undo-subdirectories {prefix?} {target?} {--detach} {--forcedelete}';

    public function handle(): int
    {
        $prefix = str($this->argument('prefix') ?? text(
            label: 'What is the email directory prefix for all mails you want to move?',
            placeholder: 'INBOX.foo.',
            required: true,
        ))->trim()->toString();

        $target = str($this->argument('target') ?? text(
            label: 'What is the email target directory you want the mails to be moved to?',
            placeholder: 'INBOX',
            required: true,
        ))->trim()->toString();

        if (! $this->confirm('Are you really sure you want move all mails with this prefix to the target directory?')) {
            return 130;
        }

        if ($this->option('forcedelete') && ! $this->confirm('You have force delete turned on. Really sure?')) {
            return 130;
        }

        $args = [
            'prefix' => $prefix,
            'target' => $target,
            'forceDelete' => $this->option('forcedelete'),
        ];

        if ($this->option('detach')) {
            UndoSubdirectories::dispatch(...$args);

            return static::SUCCESS;
        }

        $previousLogger = Log::getFacadeRoot();
        Log::swap(new LaravelCommandLogger($this));

        UndoSubdirectories::dispatchSync(...$args);

        // If in test environment on the ci server, this still gets called,
        // so we introduce this 'if' to deny that
        if (! App::environment('testing')) {
            $this->newLine(2);
            $this->line('New directory summary');
            $this->call('app:print-directory-summary');
        }

        Log::swap($previousLogger);

        return static::SUCCESS;
    }
}
