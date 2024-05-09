<?php

namespace Tests\MailHelper;

use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class TestMails
{
    /**
     * @see /phpunit.xml for configuration
     */
    public static function sendTestMails(string $hostname, string $user, string $password): void
    {
        $mailer = Mail::mailer('smtp-test');

        $mailer->raw('Hello World!', fn (Message $msg) => $msg
            ->from('foo@foo.local')
            ->to('debug@local')
            ->subject('Test Email'));

        $mailer->raw('Hello World!', fn (Message $msg) => $msg
            ->from('bar@bar.local')
            ->to('another@local')
            ->subject('Test Email'));
    }
}
