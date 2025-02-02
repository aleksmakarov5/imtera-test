<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class vertex extends Model
{
    protected $fillable = [

        'y',
        'z',
        'npp',
        'shear_id'
    ];
}
