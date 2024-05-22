<?php

/** @noinspection MultipleExpectChainableInspection */

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Tests\TestBootstrap\TestDeployServer;

function updateKnownHosts(string $host = 'localhost', int $port = 8022): void
{
    // Expect that ssh-keygen is installed
    expect(Process::command('which ssh-keygen')->run())
        ->exitCode()->toBe(0, 'ssh-keygen must be available on the test system');

    // Expect that the ssh directory exists, which is needed by ssh-keygen
    expect(File::isDirectory('/etc/ssh/'))->toBeTrue();

    // Update known hosts
    // See https://unix.stackexchange.com/a/276007
    $removeKeys = Process::run("ssh-keygen -R [$host]:$port");
    Log::info($removeKeys->output());
    $insertKey = Process::run("ssh-keyscan -p $port $host >> ~/.ssh/known_hosts");
    expect($insertKey)->exitCode()->toBe(0, $insertKey->output());
}

function updateDotEnv(array $env, string $user, string $password, string $host = 'localhost', int $port = 8022): void
{
    foreach ($env as $envName => $envValue) {
        $command = "sshpass -p $password ssh $user@$host -p $port 'sed -i \"s|$envName=.*|$envName=$envValue|g\" /app/shared/.env'";
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

    updateKnownHosts();
});

afterEach(function () {
    $this->deployServer
        ->stop()
        ->clearPersistence();
    expect($this->deployServer->log())->toBe('');
});

it('can deploy the application to the provided docker container in directory _deploy', function () {
    // -----------------------------------------------------------------
    // Perform the setup steps, that only need to be done once
    // -----------------------------------------------------------------
    $initialDeploy = Process::path(base_path())
        ->command("sshpass -p $this->password vendor/bin/dep deploy")
        ->timeout(300)
        ->run();
    expect($initialDeploy)
        ->exitCode()->toBe(1, $initialDeploy->output())
        ->and($initialDeploy->output())
        ->toContain('.env file is empty')
        ->toContain('successfully deployed')
        ->toContain('Connection refused');

    $createDotEnv = Process::run("sshpass -p $this->password ssh application@localhost -p 8022 'cp /app/current/.env.example /app/shared/.env'");
    expect($createDotEnv)
        ->exitCode()->toBe(0, $createDotEnv->output());

    // TODO Think about propagating docker env to user application inside the container
    updateDotEnv([
        'REDIS_HOST' => 'redis',
    ], 'application', $this->password);

    $setupAppKey = Process::path(base_path())
        ->command("sshpass -p $this->password vendor/bin/dep artisan:key:generate")
        ->run();
    expect($setupAppKey)
        ->exitCode()->toBe(0, $setupAppKey->output());

    // -----------------------------------------------------------------
    // Perform the deployment once more, when everything is set up
    // -----------------------------------------------------------------
    $deploy = Process::path(base_path())
        ->command("sshpass -p $this->password vendor/bin/dep deploy")
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
        ->command("sshpass -p $this->password vendor/bin/dep artisan:horizon:status")
        ->run();
    expect($horizonStatus)
        ->exitCode()->toBe(0, $horizonStatus->output())
        ->output()->toContain('Horizon is running');

    // TODO Expect health endpoint when its ready
    // TODO Expect job dispatching successful (php artisan app:catch-all-subdirectories)
})->skipOnWindows();
