<?php

namespace App\Imap;

use App\Settings\CatchAllSettings;
use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\ImapResourceInterface;
use Ddeboer\Imap\MailboxInterface;
use Ddeboer\Imap\Server;

class LazyInitializedConnection implements ConnectionInterface
{
    private ?ConnectionInterface $connection = null;

    private bool $closed = false;

    public function __construct(
        private readonly CatchAllSettings $settings,
    ) {}

    protected function ensureConnected(): void
    {
        if (! $this->connection || ! $this->connection->ping()) {
            $this->connection = $this->establishConnection();
        }
    }

    protected function establishConnection(): ConnectionInterface
    {
        $server = new Server(
            $this->settings->hostname(),
            (string) $this->settings->port(),
            $this->settings->validateCert()
                ? '/imap/ssl/validate-cert'
                : '/imap/ssl/novalidate-cert'
        );

        return $server->authenticate(
            $this->settings->username(),
            $this->settings->password()
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

    public function subscribeMailbox(string $name): void
    {
        $this->ensureConnected();
        $this->connection->subscribeMailbox($name);
    }

    public function count(): int
    {
        $this->ensureConnected();

        return $this->connection->count();
    }
}
