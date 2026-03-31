<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CatchAllSettings extends Settings
{
    public bool $enabled;

    public static function group(): string
    {
        return 'catchall';
    }
}
