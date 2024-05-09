<?php

namespace Tests\MailHelper;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class TestMailCreator
{
    public static function create(string $hostname, string $user, string $password): void
    {
        // Mail::mailer()->raw()

        // TODO
        // https://atymic.dev/tips/laravel-send-test-email/
        Mail::raw('Hello World!', fn (Message $msg) => $msg
            ->from('foo@foo.local')
            ->to('test@local')
            ->subject('Test Email'));

        Mail::raw('Hello World!', fn (Message $msg) => $msg
            ->from('bar@bar.local')
            ->to('another@local')
            ->subject('Test Email'));
    }
}
