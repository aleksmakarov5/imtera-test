<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class transaction extends Model
{

    protected $fillable = [
        'Date',
        'Description',
        'Kontragent',
        'Type',
        'Summ',
        'Sch',
        'NazPay',
        'budget_item_id',
        'deal_id',
        'status_id',
    ];
}