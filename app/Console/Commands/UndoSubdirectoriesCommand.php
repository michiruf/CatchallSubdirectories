<?php

namespace App\Console\Commands;

use App\Jobs\UndoSubdirectories;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use function Laravel\Prompts\text;

#[AsCommand(name: 'app:undo-subdirectories')]
class UndoSubdirectoriesCommand extends Command
{
    protected $description = 'Undo moving mails into a subdirectory';

    protected $signature = 'app:undo-subdirectories {prefix?} {target?} {--detach}';

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

        $args = [
            'prefix' => $prefix,
            'target' => $target,
        ];

        $this->option('detach')
            ? UndoSubdirectories::dispatch(...$args)
            : UndoSubdirectories::dispatchSync(...$args);

        $this->runCommand('app:print-directory-summary', [], $this->output);

        return static::SUCCESS;
    }
}
