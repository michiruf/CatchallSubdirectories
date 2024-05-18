<?php

use Illuminate\Support\Facades\Process;
use Tests\DeployHelper\TestDeployServer;

it('can deploy the application', function () {
    $deployServer = new TestDeployServer('app'); // name from _deploy/docker-compose.yml
    $deployServer->start();
    $deployServer->awaitStart();
    expect($deployServer->log())->toContain(TestDeployServer::STARTUP_MESSAGE);

    expect(Process::command('which sshpass')->run())
        ->exitCode()->toBe(0, 'sshpass must be installed on the test system');

    $deployer = Process::path(base_path())
        ->command("sshpass -p test vendor/bin/dep deploy")
        ->timeout(300)
        ->start()
        ->wait();
    expect($deployer)
        ->exitCode()->toBe(0, $deployer->output())
        ->output()->toContain('successfully deployed');

    $deployServerResponse = $this->get('http://localhost:8022');
    $deployServerResponse->assertStatus(200);

    $deployServer->remove();
    expect($deployServer->log())->toBe('');
})->onlyOnLinux();
