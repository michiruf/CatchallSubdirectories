<?php

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\TestBootstrap\TestDeployServer;

uses()->group('deploy', 'autopull');

beforeEach(function () {
    $this->deployServer = (new TestDeployServer('autopull', [
        'MYSQL_ROOT_PASSWORD' => 'test',
        'MYSQL_DATABASE' => 'test',
        'MYSQL_USER' => 'test',
        'MYSQL_PASSWORD' => 'test',
    ], 300));
});

it('can start the deploy server', function () {
    // First may clear the state, so we always start fresh, then start
    $this->deployServer
        ->clearPersistence()
        ->start()
        ->awaitStart();
    expect($this->deployServer->log())->toContain(TestDeployServer::$startupMessage);
});

it('can wait for the deploy server to complete deployment', function () {
    $this->deployServer->awaitMessage('=> deploy completed');
})->throwsNoExceptions();

it('has correct deployed file ownerships', function () {
    // TODO
});

it('can access the website on the running system', function () {
    expect(Http::get('http://localhost:8080'))
        ->status()
        ->toBeGreaterThanOrEqual(200)
        ->toBeLessThan(400);
});

it('can check health on the running system', function () {
    retry(
        60,
        function () {
            $healthResponse = Http::get('http://localhost:8080/health.json?fresh');
            expect($healthResponse)
                ->status()->toBe(200)
                ->and(collect($healthResponse->json()['checkResults'])
                    ->mapWithKeys(fn (array $data) => [$data['name'] => $data['status']]))
                ->get('Cache')->toBe('ok', 'Cache status failed')
                ->get('Database')->toBe('ok', 'Database status failed')
                ->get('DebugMode')->toBe('ok', 'DebugMode status failed')
                ->get('Environment')->toBe('ok', 'Environment status failed')
                ->get('OptimizedApp')->toBe('ok', 'OptimizedApp status failed')
                ->get('Horizon')->toBe('ok', 'Horizon status failed')
                ->get('Schedule')->toBe('ok', 'Schedule status failed')
                ->get('Queue')->toBe('ok', 'Queue status failed')
                ->get('Redis')->toBe('ok', 'Redis status failed');
        },
        1000,
        fn ($exception) => $exception instanceof ExpectationFailedException
    );
});

it('can access horizon on the running system', function () {
    $response = Http::get('http://localhost:8080/horizon/dashboard?ok');
    expect($response)
        ->status()
        ->toBeGreaterThanOrEqual(200)
        ->toBeLessThan(400)
        ->and(collect($response->cookies()->toArray()))
        ->where(fn (array $data) => $data['Name'] == 'viewHorizon')
        ->toHaveCount(1);
});

it('can stop the deploy server', function () {
    $this->deployServer
        ->stop()
        ->clearPersistence();
    expect($this->deployServer->log())->toBe('');
});
