<?php

namespace App\Imap;

use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\ImapResourceInterface;
use Ddeboer\Imap\MailboxInterface;
use Ddeboer\Imap\Server;

class LazyInitializedConnection implements ConnectionInterface
{
    private ?ConnectionInterface $connection = null;

    private bool $closed = false;

    public function __construct(
        private readonly ?string $hostname = null,
        private readonly ?int $port = null,
        private readonly ?string $username = null,
        private readonly ?string $password = null,
        private readonly ?bool $validateCert = null
    ) {
    }

    protected function ensureConnected(): void
    {
        if (! $this->connection || ! $this->connection->ping()) {
            $this->connection = $this->establishConnection();
        }
    }

    protected function establishConnection(): ConnectionInterface
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

    public function __destruct()
    {
        if (! $this->closed) {
            $this->close();
        }
    }

    public function getResource(): ImapResourceInterface
    {
        $this->ensureConnected();

        return $this->connection->getResource();
    }

    public function expunge(): bool
    {
        $this->ensureConnected();

        return $this->connection->expunge();
    }

    public function close(int $flag = 0): bool
    {
        $this->closed = true;

        // No need to ensure connected here, since we want to disconnect anyway
        return $this->connection?->close($flag) ?? false;
    }

    public function ping(): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->ensureConnected();

        return $this->connection?->ping() ?? false;
    }

    public function getQuota(string $root = 'INBOX'): array
    {
        $this->ensureConnected();

        return $this->connection->getQuota();
    }

    public function getMailboxes(): array
    {
        $this->ensureConnected();

        return $this->connection->getMailboxes();
    }

    public function hasMailbox(string $name): bool
    {
        $this->ensureConnected();

        return $this->connection->hasMailbox($name);
    }

    public function getMailbox(string $name): MailboxInterface
    {
        $this->ensureConnected();

        return $this->connection->getMailbox($name);
    }

    public function createMailbox(string $name): MailboxInterface
    {
        $this->ensureConnected();

        return $this->connection->createMailbox($name);
    }

    public function deleteMailbox(MailboxInterface $mailbox): void
    {
        $this->ensureConnected();
        $this->connection->deleteMailbox($mailbox);
    }

    public function count(): int
    {
        $this->ensureConnected();

        return $this->connection->count();
    }
}
