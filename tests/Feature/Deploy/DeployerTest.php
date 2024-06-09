<?php

use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\TestBootstrap\TestDeployServer;

uses()->group('deploy', 'deployer');

function error(string $command, string|ProcessResult $output): string
{
    if ($output instanceof ProcessResult) {
        $output = $output->output();
    }

    return "Error running:\n$command\nWith output:\n$output}";
}

beforeEach(function () {
    $this->password = 'test';

    $this->deployServer = (new TestDeployServer('deployer', [
        'USE_PUBLIC_KEY' => 'false',
        'SSH_PASSWORD' => $this->password,
        'MYSQL_ROOT_PASSWORD' => 'test',
        'MYSQL_DATABASE' => 'test',
        'MYSQL_USER' => 'test',
        'MYSQL_PASSWORD' => 'test',
    ], 300));

    // We want to test with the latest git hash instead of the main branch
    // Unfortunately, this means that the current changes cannot be tested but must be committed and pushed first
    $this->gitHash = once(fn () => Process::path(base_path())
        ->command('git rev-parse HEAD')
        ->run()
        ->output());
    if (empty($this->gitHash) || ! is_string($this->gitHash)) {
        throw new RuntimeException('Cannot get git hash');
    }
});

it('has sshpass installed on the test system', function () {
    expect(Process::command('which sshpass')->run())
        ->exitCode()->toBe(0, 'sshpass must be installed on the test system');
});

it('can start the deploy server', function () {
    // First may clear the state, so we always start fresh, then start
    $this->deployServer
        ->clearPersistence()
        ->start()
        ->awaitStart();
    expect($this->deployServer->log())->toContain(TestDeployServer::$startupMessage);
});

// -----------------------------------------------------------------
// Perform the setup steps, that only need to be done once
// -----------------------------------------------------------------

it('can perform an initial deploy', function () {
    $initialDeployCommand = "sshpass -p $this->password vendor/bin/dep deploy test --revision=$this->gitHash";
    $initialDeploy = Process::path(base_path())
        ->timeout(300)
        ->run($initialDeployCommand);
    expect($initialDeploy->output())
        ->toContainWithMessage('successfully deployed', error($initialDeployCommand, $initialDeploy));
});

it('can generate an application key', function () {
    $setupAppKeyCommand = "sshpass -p $this->password vendor/bin/dep artisan:key:generate test";
    $setupAppKey = Process::path(base_path())
        ->run($setupAppKeyCommand);
    expect($setupAppKey)
        ->exitCode()->toBe(0, error($setupAppKeyCommand, $setupAppKey));
});

// -----------------------------------------------------------------
// Perform the deployment once more, when everything is set up
// -----------------------------------------------------------------

it('can deploy again when everything is set up', function () {
    $deployCommand = "sshpass -p $this->password vendor/bin/dep deploy test --revision=$this->gitHash";
    $deploy = Process::path(base_path())
        ->timeout(300)
        ->run($deployCommand);
    expect($deploy)
        ->exitCode()->toBe(0, error($deployCommand, $deploy))
        ->and($deploy->output())
        ->not->toContain('.env file is empty')
        ->toContain('successfully deployed');

    // Wait for horizon and workers to get started properly (needed for tests below)
    $this->deployServer->awaitMessage('Horizon started successfully');
    $this->deployServer->awaitMessage('Running scheduled tasks');
});

// -----------------------------------------------------------------
// Perform the tests against the running system
// -----------------------------------------------------------------

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
