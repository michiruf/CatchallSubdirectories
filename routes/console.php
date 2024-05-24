<?php

use App\Console\Commands\CatchAllSubdirectoriesCommand;
use Illuminate\Support\Facades\Schedule;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

// App
Schedule::command(CatchAllSubdirectoriesCommand::class)->withoutOverlapping()->everyFiveMinutes();

// Horizon
Schedule::command('horizon:snapshot')->everyFiveMinutes();

// Health
Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
Schedule::command(RunHealthChecksCommand::class)->everyMinute();
