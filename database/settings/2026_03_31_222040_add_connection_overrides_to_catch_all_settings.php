<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('catchall.hostname');
        $this->migrator->add('catchall.port');
        $this->migrator->add('catchall.username');
        $this->migrator->add('catchall.password');
        $this->migrator->add('catchall.validate_cert');
        $this->migrator->add('catchall.inbox_name');
        $this->migrator->add('catchall.mail_domain');
        $this->migrator->add('catchall.subscribe_new_folders', true);
    }
};
