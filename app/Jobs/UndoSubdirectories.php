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
use Illuminate\Support\Str;

class UndoSubdirectories extends SmtpJobBase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private MailboxInterface $targetMailbox;

    /**
     * @var Collection<int, MailboxInterface>
     */
    private Collection $relevantMailboxes;

    public function __construct(
        ?ConnectionInterface $connection = null,
        private readonly string $prefix = 'INBOX.',
        private readonly string $target = 'INBOX'
    ) {
        parent::__construct($connection);
    }

    public function handle(): static
    {
        return $this
            ->mayEstablishConnection()
            ->findTargetMailbox()
            ->getRelevantMailboxes()
            ->moveMessages()
            ->deleteDirectories()
            ->mayCloseConnection();
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
        // Mark messages for deletion first
        $this->relevantMailboxes->each(function (MailboxInterface $directory) {
            collect($directory->getMessages())
                ->each(fn (MessageInterface $message) => $message->move($this->targetMailbox));
        });

        // Finish the transaction by calling expunge
        // https://www.php.net/manual/de/function.imap-expunge.php
        $this->smtpConnection->expunge();

        return $this;
    }

    private function deleteDirectories(): static
    {
        // Mark relevant mailboxes for deletion
        $this->relevantMailboxes->each(function (MailboxInterface $directory) {
            app(DeleteImapDirectory::class, [
                'connection' => $this->smtpConnection,
                'directory' => $directory,
                'noExpunge' => true,
            ])->execute();
        });

        // Finish the transaction by calling expunge
        // https://www.php.net/manual/de/function.imap-expunge.php
        $this->smtpConnection->expunge();

        return $this;
    }
}
