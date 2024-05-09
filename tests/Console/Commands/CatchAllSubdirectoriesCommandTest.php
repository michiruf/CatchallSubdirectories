<?php

use App\Jobs\CatchAllSubdirectories;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\Console\Command\Command;

it('can invoke command app:catch-all-subdirectories', function () {
    Bus::fake([
        CatchAllSubdirectories::class,
    ]);

    $this->artisan('app:catch-all-subdirectories')
        ->assertExitCode(Command::SUCCESS);

    Bus::assertDispatched(CatchAllSubdirectories::class);
});
