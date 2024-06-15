<?php

use Illuminate\Support\Facades\Process;
use Tests\TestBootstrap\Traits\CanTestDeployServer;

uses(CanTestDeployServer::class);
uses()->group('deploy', 'deployer');

beforeEach(function () {
    $this->password = 'test';

    $this->createDeployServer('deployer', [
        'USE_PUBLIC_KEY' => 'false',
        'SSH_PASSWORD' => $this->password,
        'MYSQL_ROOT_PASSWORD' => 'test',
        'MYSQL_DATABASE' => 'test',
        'MYSQL_USER' => 'test',
        'MYSQL_PASSWORD' => 'test',
    ]);

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
    $this->startDeployServer();
});

// -----------------------------------------------------------------
// Perform the setup steps, that only need to be done once
// -----------------------------------------------------------------

it('can perform an initial deploy', function () {
    $initialDeploy = Process::path(base_path())
        ->timeout(300)
        ->run("sshpass -p $this->password vendor/bin/dep deploy test --revision=$this->gitHash");
    expect($initialDeploy->output())
        ->toContainWithMessage('successfully deployed', $this->formatError($initialDeploy));
});

it('can generate an application key', function () {
    $setupAppKey = Process::path(base_path())
        ->run("sshpass -p $this->password vendor/bin/dep artisan:key:generate test");
    expect($setupAppKey)
        ->exitCode()->toBe(0, $this->formatError($setupAppKey));
});

// -----------------------------------------------------------------
// Perform the deployment once more, when everything is set up
// -----------------------------------------------------------------

it('can deploy again when everything is set up', function () {
    $deploy = Process::path(base_path())
        ->timeout(300)
        ->run("sshpass -p $this->password vendor/bin/dep deploy test --revision=$this->gitHash");
    expect($deploy)
        ->exitCode()->toBe(0, $this->formatError($deploy))
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

it('has correct file ownerships', function () {
    $this->fileOwnerShipTest();
});

it('can access the website on the running system', function () {
    $this->websiteAccessTest();
});

it('can check health on the running system', function () {
    $this->healthChecksTest();
});

it('can access horizon on the running system', function () {
    $this->horizonAccessTest();
});

it('can access pulse on the running system', function () {
    $this->pulseAccessTest();
});

it('can stop the deploy server', function () {
    $this->stopDeployServer();
});
