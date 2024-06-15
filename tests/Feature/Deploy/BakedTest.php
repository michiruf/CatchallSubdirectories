<?php

use Tests\TestBootstrap\Traits\CanTestDeployServer;

uses(CanTestDeployServer::class);
uses()->group('deploy', 'baked');

beforeEach(function () {
    $this->createDeployServer('baked', [
        'MYSQL_ROOT_PASSWORD' => 'test',
        'MYSQL_DATABASE' => 'test',
        'MYSQL_USER' => 'test',
        'MYSQL_PASSWORD' => 'test',
    ]);
});

it('can start the deploy server', function () {
    $this->startDeployServer();
});

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
