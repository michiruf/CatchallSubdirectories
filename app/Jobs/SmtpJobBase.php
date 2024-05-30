<?php

namespace App\Jobs;

use App\Actions\ConnectImap;
use Ddeboer\Imap\ConnectionInterface;

abstract class SmtpJobBase
{
    protected ?ConnectionInterface $smtpConnection;

    protected bool $connectionEstablished = false;

    public function __construct(
        ?ConnectionInterface $connection = null,
    ) {
        $this->smtpConnection = $connection;
    }

    protected function mayEstablishConnection(): static
    {
        if (! $this->smtpConnection) {
            $this->connectionEstablished = true;
            $this->smtpConnection = app(ConnectImap::class)->execute();
        }

        return $this;
    }

    protected function mayCloseConnection(): static
    {
        if ($this->connectionEstablished) {
            $this->smtpConnection->close();
        }

        return $this;
    }
}
