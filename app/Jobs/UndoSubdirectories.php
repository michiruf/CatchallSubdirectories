<?php

namespace App\Jobs;

use App\Actions\DeleteImapDirectory;
use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\MailboxInterface;
use Ddeboer\Imap\MessageInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UndoSubdirectories implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ConnectionInterface $smtpConnection;

    private MailboxInterface $targetMailbox;

    /**
     * @var Collection<int, MailboxInterface>
     */
    private Collection $relevantMailboxes;

    public function __construct(
        private readonly string $prefix = 'INBOX.',
        private readonly string $target = 'INBOX',
        private readonly bool $forceDelete = false,
    ) {
    }

    public function handle(ConnectionInterface $connection): static
    {
        $this->smtpConnection = $connection;

        return $this
            ->findTargetMailbox()
            ->getRelevantMailboxes()
            ->moveMessages()
            ->deleteDirectories();
    }

    private function findTargetMailbox(): static
    {
        $this->targetMailbox = $this->smtpConnection->getMailbox($this->target);

        return $this;
    }

    private function getRelevantMailboxes(): static
    {
        $this->relevantMailboxes = collect($this->smtpConnection->getMailboxes())
            ->filter(fn (MailboxInterface $directory) => Str::startsWith($directory->getName(), $this->prefix));

        return $this;
    }

    private function moveMessages(): static
    {
        Log::info("Starting to move messages to target {$this->targetMailbox->getName()}");

        // Mark messages for deletion first
        $this->relevantMailboxes->each(function (MailboxInterface $directory) {
            Log::info("Moving {$directory->count()} messages from directory {$directory->getName()}");
            collect($directory->getMessages())
                ->each(fn (MessageInterface $message) => $message->move($this->targetMailbox));
        });

        // Finish the transaction by calling expunge
        // https://www.php.net/manual/de/function.imap-expunge.php
        $this->smtpConnection->expunge();
        Log::info('Expunged transaction');

        return $this;
    }

    private function deleteDirectories(): static
    {
        $this->forceDelete
            ? Log::warning('Starting to remove empty folders with FORCE')
            : Log::info('Starting to remove empty folders');

        // Mark relevant mailboxes for deletion
        $this->relevantMailboxes->each(function (MailboxInterface $directory) {
            Log::info("Deleting folder {$directory->getName()}");
            app(DeleteImapDirectory::class, [
                'connection' => $this->smtpConnection,
                'directory' => $directory,
                'noExpunge' => true,
                'forceDelete' => $this->forceDelete,
            ])->execute();
        });

        // Finish the transaction by calling expunge
        // https://www.php.net/manual/de/function.imap-expunge.php
        $this->smtpConnection->expunge();
        Log::info('Expunged transaction');

        return $this;
    }
}
