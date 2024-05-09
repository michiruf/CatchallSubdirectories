<?php

namespace App\Jobs;

use App\Actions\ConnectImap;
use App\Actions\CreateOrGetImapDirectory;
use App\Actions\ReadImapDirectoryMails;
use Ddeboer\Imap\Connection;
use Ddeboer\Imap\Message\EmailAddress;
use Ddeboer\Imap\MessageInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CatchAllSubdirectories implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ?Connection $smtpConnection;

    /** @var Collection<int, MessageInterface> */
    private Collection $mails;

    public function __construct(
        ?Connection $connection = null,
        private readonly ?string $mailDomain = null
    ) {
        $this->smtpConnection = $connection;
    }

    public function execute(): static
    {
        return $this
            ->mayEstablishConnection()
            ->fetchMails()
            ->createSubdirectoriesAndMoveMails();
    }

    private function mayEstablishConnection(): static
    {
        if (! $this->smtpConnection) {
            $this->smtpConnection = app(ConnectImap::class)->execute();
        }

        return $this;
    }

    private function fetchMails(): static
    {
        $this->mails = app(ReadImapDirectoryMails::class, [
            'connection' => $this->smtpConnection,
        ])->execute();

        return $this;
    }

    private function createSubdirectoriesAndMoveMails(): static
    {
        $mailDomain = $this->mailDomain ?? config('catchall.mail_domain');

        $this->mails->each(function (MessageInterface $mail) use ($mailDomain) {
            /** @var EmailAddress $relevantReceiver */
            $relevantReceiver = collect($mail->getTo())
                ->firstOrFail(fn (EmailAddress $address) => $address->getHostname() === $mailDomain);

            $directoryName = Str::before($relevantReceiver->getAddress(), '@');

            $directory = app(CreateOrGetImapDirectory::class, [
                'connection' => $this->smtpConnection,
                'directory' => $directoryName,
            ])->execute();

            Log::info("Moving mail '{$mail->getSubject()}' sent to {$relevantReceiver->getAddress()} to directory $directoryName");
            $mail->move($directory);
        });

        // Finish the transaction by calling expunge
        // https://www.php.net/manual/de/function.imap-expunge.php
        $this->smtpConnection->expunge();

        return $this;
    }
}
