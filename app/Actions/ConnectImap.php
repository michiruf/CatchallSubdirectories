<?php

namespace App\Actions;

use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\Server;

class ConnectImap
{
    public function __construct(
        private readonly ?string $hostname = null,
        private readonly ?int $port = null,
        private readonly ?string $username = null,
        private readonly ?string $password = null,
        private readonly ?bool $validateCert = null
    ) {
    }

    public function execute(): ConnectionInterface
    {
        $server = new Server(
            $this->hostname ?? config('catchall.hostname'),
            $this->port ?? config('catchall.port'),
            ($this->validateCert ?? config('catchall.validate_cert', true))
                ? '/imap/ssl/validate-cert'
                : '/imap/ssl/novalidate-cert'
        );

        return $server->authenticate(
            $this->username ?? config('catchall.username'),
            $this->password ?? config('catchall.password')
        );
    }
}
