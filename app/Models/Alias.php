<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alias extends Model
{
    protected $fillable = [
        'source_prefix',
        'destination_prefix',
    ];
}
