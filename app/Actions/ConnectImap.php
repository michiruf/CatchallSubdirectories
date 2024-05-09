<?php

namespace App\Actions;

use Ddeboer\Imap\Connection;
use Ddeboer\Imap\Server;

class ConnectImap
{
    public function __construct(
        private readonly ?string $hostname = null,
        private readonly ?int $port = 993,
        private readonly ?string $username = null,
        private readonly ?string $password = null,
        private readonly ?bool $validateCert = null
    ) {
    }

    public function execute(): Connection
    {
        $server = new Server(
            $this->hostname ?? config('app.mail.hostname'),
            $this->port ?? config('app.mail.port'),
            ($this->validateCert ?? config('app.mail.validateCert', true))
                ? '/imap/ssl/validate-cert'
                : '/imap/ssl/novalidate-cert'
        );

        return $server->authenticate(
            $this->username ?? config('app.mail.username'),
            $this->password ?? config('app.mail.password')
        );
    }
}
