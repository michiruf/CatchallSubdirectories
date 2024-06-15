<?php

namespace Tests\TestBootstrap\Traits;

use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\TestBootstrap\TestDeployServer;

trait CanTestDeployServer
{
    protected TestDeployServer $deployServer;

    public function createDeployServer(string $variant, array $env): void
    {
        $this->deployServer = (new TestDeployServer($variant, $env, 300));
    }

    public function startDeployServer(): void
    {
        // First may clear the state, so we always start fresh, then start
        $this->deployServer
            ->clearPersistence()
            ->start()
            ->awaitStart();
        expect($this->deployServer->log())->toContain(TestDeployServer::$startupMessage);
    }

    public function stopDeployServer(): void
    {
        $this->deployServer
            ->stop()
            ->clearPersistence();
        expect($this->deployServer->log())->toBe('');
    }

    public function fileOwnerShipTest(): void
    {
        // Normal-Command: find "$APPLICATION_PATH" -exec ls -ld {} + | awk '{print $3}' | sort | uniq -c
        // SH-Command: sh -c 'find "$APPLICATION_PATH" -exec ls -ld {} + | awk '\''{print $3}'\'' | sort | uniq -c'
        // Note that '\'' is an escaped ' in shell, but we then need to escape for php too:
        $cmd = $this->deployServer->exec('sh -c \'find "$APPLICATION_PATH" -exec ls -ld {} + | awk \'\\\'\'{print $3}\'\\\'\' | sort | uniq -c\'');
        expect($cmd)
            ->exitCode()->toBe(0, $this->formatError($cmd))
            ->output()->not->toContainWithMessage('root', 'Some files are owned by root');
    }

    public function websiteAccessTest(): void
    {
        expect(Http::get('http://localhost:8080'))
            ->status()
            ->toBeGreaterThanOrEqual(200)
            ->toBeLessThan(400);
    }

    public function healthChecksTest(): void
    {
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
    }

    public function horizonAccessTest(): void
    {
        $response = Http::get('http://localhost:8080/horizon/dashboard?ok');
        expect($response)
            ->status()
            ->toBeGreaterThanOrEqual(200)
            ->toBeLessThan(400)
            ->and(collect($response->cookies()->toArray()))
            ->where(fn (array $data) => $data['Name'] == 'viewHorizon')
            ->toHaveCount(1);
    }

    public function pulseAccessTest(): void
    {
        $response = Http::get('http://localhost:8080/pulse?ok');
        expect($response)
            ->status()
            ->toBeGreaterThanOrEqual(200)
            ->toBeLessThan(400)
            ->and(collect($response->cookies()->toArray()))
            ->where(fn (array $data) => $data['Name'] == 'viewPulse')
            ->toHaveCount(1);
    }

    public function formatError(ProcessResult $process): string
    {
        return "Error running:\n{$process->command()}\nWith output:\n{$process->output()}";
    }
}
