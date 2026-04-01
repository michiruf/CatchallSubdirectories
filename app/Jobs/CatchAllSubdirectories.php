<?php

namespace App\Jobs;

use App\Actions\CreateOrGetImapDirectory;
use App\Actions\ReadImapDirectoryMails;
use App\Models\Alias;
use App\Settings\CatchAllSettings;
use Ddeboer\Imap\ConnectionInterface;
use Ddeboer\Imap\Message\EmailAddress;
use Ddeboer\Imap\MessageInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CatchAllSubdirectories implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ConnectionInterface $imapConnection;

    private CatchAllSettings $settings;

    /** @var Collection<int, MessageInterface> */
    private Collection $mails;

    public function handle(ConnectionInterface $connection, CatchAllSettings $settings): static
    {
        $this->imapConnection = $connection;
        $this->settings = $settings;

        return $this
            ->fetchMails()
            ->createSubdirectoriesAndMoveMails()
            ->updateLastRun();
    }

    private function fetchMails(): static
    {
        $this->mails = app(ReadImapDirectoryMails::class, [
            'connection' => $this->imapConnection,
        ])->execute();

        return $this;
    }

    private function createSubdirectoriesAndMoveMails(): static
    {
        $this->mails->each(function (MessageInterface $mail) {
            /** @var ?EmailAddress $relevantReceiver */
            $relevantReceiver = collect($mail->getTo())
                ->first(fn (EmailAddress $address) => Str::lower($address->getHostname()) === $this->settings->mailDomain());

            if ($relevantReceiver === null) {
                return;
            }

            $prefix = Str::of($relevantReceiver->getAddress())
                ->before('@')
                ->lower()
                ->toString();

            $alias = Alias::where('source_prefix', $prefix)->first();

            $directoryName = Str::title($alias?->destination_prefix ?: $prefix);

            $directory = app(CreateOrGetImapDirectory::class, [
                'connection' => $this->imapConnection,
                'directory' => $directoryName,
            ])->execute();

            Log::info("Moving mail '{$mail->getSubject()}' sent to {$relevantReceiver->getAddress()} to directory $directoryName");
            $mail->move($directory);
        });

        // Finish the transaction by calling expunge
        // https://www.php.net/manual/de/function.imap-expunge.php
        $this->imapConnection->expunge();

        return $this;
    }

    private function updateLastRun(): static
    {
        $this->settings->last_run_at = Carbon::now()->toImmutable();
        $this->settings->save();

        return $this;
    }
}
