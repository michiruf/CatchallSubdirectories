<?php

namespace App\Jobs;

use App\Actions\CreateOrGetImapDirectory;
use App\Actions\ReadImapDirectoryMails;
use Ddeboer\Imap\ConnectionInterface;
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

class CatchAllSubdirectories extends SmtpJobBase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Collection<int, MessageInterface> */
    private Collection $mails;

    public function __construct(
        ?ConnectionInterface $connection = null,
        private readonly ?string $mailDomain = null
    ) {
        parent::__construct($connection);
    }

    public function handle(): static
    {
        return $this
            ->mayEstablishConnection()
            ->fetchMails()
            ->createSubdirectoriesAndMoveMails()
            ->mayCloseConnection();
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
            /** @var ?EmailAddress $relevantReceiver */
            $relevantReceiver = collect($mail->getTo())
                ->first(fn (EmailAddress $address) => Str::lower($address->getHostname()) === $mailDomain);

            if ($relevantReceiver === null) {
                return;
            }

            $directoryName = Str::of($relevantReceiver->getAddress())
                ->before('@')
                ->title()
                ->toString();

            $directory = app(CreateOrGetImapDirectory::class, [
                'connection' => $this->smtpConnection,
                'directory' => $directoryName,
                'subscribe' => true,
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
