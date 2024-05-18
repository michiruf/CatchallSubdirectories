<?php

/** @noinspection MultipleExpectChainableInspection */

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Tests\TestBootstrap\TestDeployServer;

function updateKnownHosts(string $host = 'localhost', int $port = 8022): void
{
    // Update known hosts
    // See https://unix.stackexchange.com/a/276007
    $removeKeys = Process::run("ssh-keygen -R [$host]:$port");
    expect($removeKeys)->exitCode()->toBe(0, $removeKeys->output());
    $insertKey = Process::run("ssh-keyscan -p $port $host >> ~/.ssh/known_hosts");
    expect($insertKey)->exitCode()->toBe(0, $insertKey->output());
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

it('can deploy the application', function () {
    // These setups must only be done once on the server
    $initialDeploy = Process::path(base_path())
        ->command("sshpass -p $this->password vendor/bin/dep deploy")
        ->timeout(300)
        ->run();
    expect($initialDeploy)
        ->exitCode()->toBe(0, $initialDeploy->output())
        ->output()->toContain('.env file is empty');
    $setupEnv = Process::run("sshpass -p $this->password ssh application@localhost -p 8022 'cp /app/current/.env.example /app/shared/.env'");
    expect($setupEnv)
        ->exitCode()->toBe(0, $setupEnv->output());
    $setupAppKey = Process::path(base_path())
        ->command("sshpass -p $this->password vendor/bin/dep artisan:key:generate")
        ->run();
    expect($setupAppKey)
        ->exitCode()->toBe(0, $setupAppKey->output());

    // Perform the deployment once more, when everything is set up
    $deploy = Process::path(base_path())
        ->command("sshpass -p $this->password vendor/bin/dep deploy")
        ->timeout(300)
        ->run();
    expect($deploy)
        ->exitCode()->toBe(0, $deploy->output())
        ->output()->not->toContain('.env file is empty')
        ->output()->toContain('successfully deployed');

    expect(Http::get('http://localhost:8080'))
        ->status()->toBe(200);

    // TODO Expect health endpoint when its ready
})->onlyOnLinux();
