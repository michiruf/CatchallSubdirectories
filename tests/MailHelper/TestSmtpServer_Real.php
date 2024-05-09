<?php

namespace Tests\MailHelper;

use Exception;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

// TODO Separate docker execution
class TestSmtpServer_Real
{
    public const string STARTUP_MESSAGE = 'asdasdadsasd';

    public const string USER_CREATION_MESSAGE = ' You need at least one mail account to start Dovecot';

    private string $processDescriptor;

    public function __construct(
        private readonly string $hostname = 'mail',
        private readonly string $mail = 'user@example.com',
        private readonly string $password = 'test',
        private readonly int $timeoutSeconds = 60
    ) {
        $this->processDescriptor = Str::of("--name $hostname")
            ->append(
                ' -p 40025:25',
                ' -p 40143:143',
                ' -p 40587:587',
                ' -p 40993:993',
            )
            ->append(
                ' -h mail',
                ' --domainname local'
            )
            ->append(
                ' -e ONE_DIR=1',
                ' -e DMS_DEBUG=1',
                ' -e SSL_TYPE=self-signed',
                ' -e ENABLE_SPAMASSASSIN=0',
                ' -e ENABLE_CLAMAV=0',
                ' -e ENABLE_FAIL2BAN=0',
                ' -e ENABLE_POSTGREY=0',
                ' -e SPOOF_PROTECTION=0',
            )
            ->append(
                ' --cap-add NET_ADMIN',
                ' --cap-add SYS_PTRACE',
            )
            ->append(' -t docker.io/mailserver/docker-mailserver:latest')
            ->toString();

        //        dd($this->processDescriptor);
    }

    public function start(): static
    {
        // Ensure that the container does not exist
        try {
            $this->remove();
        } catch (Exception) {
        }

        $this->run("docker run -d $this->processDescriptor");

        return $this;
    }

    public function stop(): static
    {
        $this->run("docker stop $this->hostname");

        return $this;
    }

    public function remove(): static
    {
        $this->run("docker rm -f $this->hostname");

        return $this;
    }

    public function log(): string
    {
        return $this->run("docker logs $this->hostname")->output();
    }

    public function awaitMessage(string $message)
    {
        retry(
            $this->timeoutSeconds,
            fn () => throw_unless(
                Str::contains($this->log(), $message),
                "Could not receive message '$message' in time from container."
            ),
            1000,
        );

        return $this;
    }

    public function awaitStart()
    {
        $this->awaitMessage(static::STARTUP_MESSAGE);
    }

    public function createAccount(): static
    {
        $this->awaitMessage(self::USER_CREATION_MESSAGE);

        //$this->run("docker exec $this->hostname setup email add $this->mail");
        $this->run(dd(
            //"docker exec $this->hostname /bin/bash -c ".
            "docker exec $this->hostname ".
            //"'echo \"$this->mail|$(doveadm pw -s SHA512-CRYPT -u \"$this->mail\" -p \"$this->password\")\" >> docker-data/dms/config/postfix-accounts.cf'"
            "'echo \"$this->mail|$(doveadm pw -s SHA512-CRYPT -u $this->mail -p $this->password)\" >> docker-data/dms/config/postfix-accounts.cf'"
        ));

        return $this;
    }

    public function createCatchAll(): static
    {
        return $this;
    }

    private function run(string $command): ProcessResult
    {
        $process = Process::command($command)
            ->timeout($this->timeoutSeconds)
            ->start()
            ->wait();

        if ($process->exitCode() !== 0) {
            Log::error($process->output());
            throw new Exception($process->errorOutput());
        }

        return $process;
    }
}
