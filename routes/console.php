<?php

use App\Console\Commands\CatchAllSubdirectoriesCommand;
use App\Console\Commands\MonitorMetricsCommand;
use App\Settings\CatchAllSettings;
use Illuminate\Support\Facades\Schedule;
use Spatie\Health\Commands\DispatchQueueCheckJobsCommand;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;
use Spatie\Health\Models\HealthCheckResultHistoryItem;

// App
Schedule::command(CatchAllSubdirectoriesCommand::class)
    ->withoutOverlapping()
    ->everyFiveMinutes()
    ->when(fn () => app(CatchAllSettings::class)->enabled)
    ->sentryMonitor();
Schedule::command(MonitorMetricsCommand::class)
    ->withoutOverlapping()
    ->everyFiveMinutes()
    ->sentryMonitor();

// Horizon
Schedule::command('horizon:snapshot')->everyFiveMinutes();

// Health
Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
Schedule::command(DispatchQueueCheckJobsCommand::class)->everyMinute();
Schedule::command(RunHealthChecksCommand::class)->everyMinute();

// Cleanup / pruning
Schedule::command('model:prune', ['--model' => [HealthCheckResultHistoryItem::class]])->daily();
Schedule::command('pulse:clear', ['--force' => true])->weekly();
Schedule::command('telescope:prune')->daily();
