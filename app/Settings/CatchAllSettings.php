<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CatchAllSettings extends Settings
{
    public bool $enabled;

    public ?string $hostname;

    public ?int $port;

    public ?string $username;

    public ?string $password;

    public ?bool $validate_cert;

    public ?string $inbox_name;

    public ?string $mail_domain;

    public bool $subscribe_new_folders;

    public static function group(): string
    {
        return 'catchall';
    }

    public function hostname(): string
    {
        return $this->hostname ?: config('catchall.hostname');
    }

    public function port(): int
    {
        return $this->port ?: config('catchall.port');
    }

    public function username(): string
    {
        return $this->username ?: config('catchall.username');
    }

    public function password(): ?string
    {
        return $this->password ?: config('catchall.password');
    }

    public function validateCert(): bool
    {
        return $this->validate_cert ?? config('catchall.validate_cert', true);
    }

    public function inboxName(): string
    {
        return $this->inbox_name ?: config('catchall.inbox_name', 'INBOX');
    }

    public function mailDomain(): string
    {
        return $this->mail_domain ?: config('catchall.mail_domain');
    }
}
