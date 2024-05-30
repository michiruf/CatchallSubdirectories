<?php

use App\Console\Commands\UndoSubdirectoriesCommand;
use App\Jobs\UndoSubdirectories;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\Console\Command\Command;

it('can invoke command app:undo-subdirectories', function () {
    Bus::fake([
        UndoSubdirectories::class,
    ]);

    $this->artisan('app:undo-subdirectories')
        ->expectsQuestion('What is the email directory prefix for all mails you want to move?', 'INBOX.')
        ->expectsQuestion('What is the email target directory you want the mails to be moved to?', 'INBOX')
        ->expectsQuestion('Are you really sure you want move all mails with this prefix to the target directory?', true)
        ->assertExitCode(Command::SUCCESS);

    Bus::assertDispatched(UndoSubdirectories::class);
})->covers(UndoSubdirectoriesCommand::class);
