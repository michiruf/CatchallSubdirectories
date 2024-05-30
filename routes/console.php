<?php

use App\Console\Commands\CatchAllSubdirectoriesCommand;
use App\Console\Commands\MonitorMetricsCommand;
use Illuminate\Support\Facades\Schedule;
use Spatie\Health\Commands\DispatchQueueCheckJobsCommand;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

// App
Schedule::command(CatchAllSubdirectoriesCommand::class)
    ->withoutOverlapping()
    ->everyFiveMinutes()
    ->sentryMonitor();
Schedule::command(MonitorMetricsCommand::class)
    ->withoutOverlapping()
    ->everyFiveMinutes()
    ->sentryMonitor();

// Horizon
Schedule::command('horizon:snapshot')->everyFiveMinutes()->sentryMonitor();

// Health
Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyMinute()->sentryMonitor();
Schedule::command(DispatchQueueCheckJobsCommand::class)->everyMinute()->sentryMonitor();
Schedule::command(RunHealthChecksCommand::class)->everyMinute()->sentryMonitor();
