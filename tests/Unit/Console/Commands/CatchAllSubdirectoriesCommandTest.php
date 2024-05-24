<?php

use App\Console\Commands\CatchAllSubdirectoriesCommand;
use App\Jobs\CatchAllSubdirectories;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\Console\Command\Command;

beforeEach(function () {
    $this->startTestSmtp();
});

afterEach(function () {
    $this->stopTestSmtp();
});

it('can invoke command app:catch-all-subdirectories', function () {
    Bus::fake([
        CatchAllSubdirectories::class,
    ]);

    $this->artisan('app:catch-all-subdirectories')
        ->assertExitCode(Command::SUCCESS);

    Bus::assertDispatched(CatchAllSubdirectories::class);
})->covers(CatchAllSubdirectoriesCommand::class);
