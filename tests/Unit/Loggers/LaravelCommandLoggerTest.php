<?php

use App\Loggers\LaravelCommandLogger;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

it('can log inside commands', function () {
    Artisan::command('test:command', function () {
        $previousLogger = Log::getFacadeRoot();
        Log::info('Not displayed 1');

        Log::swap(new LaravelCommandLogger($this));
        Log::info('Test');
        Log::error('Error');

        Log::swap($previousLogger);
        Log::info('Not displayed 2');
    });

    $this->artisan('test:command')
        ->doesntExpectOutput('Not displayed 1')
        ->expectsOutput('Test')
        ->expectsOutput('Error')
        ->doesntExpectOutput('Not displayed 2');
});
