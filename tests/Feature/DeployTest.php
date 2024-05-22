<?php

/** @noinspection MultipleExpectChainableInspection */

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Tests\TestBootstrap\TestDeployServer;

function sshCommand(string $password, string $command): string
{
    return "sshpass -p $password ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null application@localhost -p 8022 '$command'";
}

function updateDotEnv(string $password, array $env): void
{
    foreach ($env as $envName => $envValue) {
        $command = sshCommand($password, "sed -i \"s|$envName=.*|$envName=$envValue|g\" /app/shared/.env");
        $updateEnv = Process::run($command);
        expect($updateEnv)->exitCode()->toBe(0, "Error running:\n$command\nWith output:\n{$updateEnv->output()}");
    }
}

beforeEach(function () {
    $this->password = 'test';

    // Assert that sshpass is installed
    expect(Process::command('which sshpass')->run())
        ->exitCode()->toBe(0, 'sshpass must be installed on the test system');

    // First may clear the state, so we always start fresh, then start
    // Service name from _deploy/docker-compose.yml
    $this->deployServer = (new TestDeployServer('app', sshPassword: $this->password))
        ->clearPersistence()
        ->start()
        ->awaitStart();
    expect($this->deployServer->log())->toContain(TestDeployServer::$startupMessage);
});

afterEach(function () {
//    $this->deployServer
//        ->stop()
//        ->clearPersistence();
//    expect($this->deployServer->log())->toBe('');
});

it('can deploy the application to the provided docker container in directory _deploy', function () {
    // -----------------------------------------------------------------
    // Perform the setup steps, that only need to be done once
    // -----------------------------------------------------------------
    $initialDeploy = Process::path(base_path())
        ->command("sshpass -p $this->password vendor/bin/dep deploy test")
        ->timeout(300)
        ->run();
    expect($initialDeploy)
        ->exitCode()->toBe(1, $initialDeploy->output())
        ->and($initialDeploy->output())
        ->toContain('.env file is empty')
        ->toContain('successfully deployed')
        ->toContain('Connection refused');

    $createDotEnvCommand = sshCommand($this->password, 'cp /app/current/.env.example /app/shared/.env');
    $createDotEnv = Process::run($createDotEnvCommand);
    expect($createDotEnv)
        ->exitCode()->toBe(0, "Error running:\n$createDotEnvCommand\nWith output:\n{$createDotEnv->output()}");

    // TODO Think about propagating docker env to user application inside the container
    updateDotEnv($this->password, [
        'REDIS_HOST' => 'redis',
    ]);

    $setupAppKey = Process::path(base_path())
        ->command("sshpass -p $this->password vendor/bin/dep artisan:key:generate test")
        ->run();
    expect($setupAppKey)
        ->exitCode()->toBe(0, $setupAppKey->output());

    // -----------------------------------------------------------------
    // Perform the deployment once more, when everything is set up
    // -----------------------------------------------------------------
    $deploy = Process::path(base_path())
        ->command("sshpass -p $this->password vendor/bin/dep deploy test")
        ->timeout(300)
        ->run();
    expect($deploy)
        ->exitCode()->toBe(0, $deploy->output())
        ->output()->not->toContain('.env file is empty')
        ->output()->toContain('successfully deployed');

    // Wait for horizon to get started properly (needed for tests below)
    $this->deployServer->awaitMessage('Horizon started successfully');

    // -----------------------------------------------------------------
    // Start the tests
    // -----------------------------------------------------------------
    expect(Http::get('http://localhost:8080'))
        ->status()->toBe(200);

    $horizonStatus = Process::path(base_path())
        ->command("sshpass -p $this->password vendor/bin/dep artisan:horizon:status test")
        ->run();
    expect($horizonStatus)
        ->exitCode()->toBe(0, $horizonStatus->output())
        ->output()->toContain('Horizon is running');

    // TODO Expect health endpoint when its ready
    // TODO Expect job dispatching successful (php artisan app:catch-all-subdirectories)
})->skipOnWindows();
