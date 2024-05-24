<?php

use App\Console\Commands\CatchAllSubdirectoriesCommand;
use Illuminate\Support\Facades\Schedule;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

Schedule::command(CatchAllSubdirectoriesCommand::class)->withoutOverlapping()->everyFiveMinutes();

Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
Schedule::command(RunHealthChecksCommand::class)->everyMinute();
