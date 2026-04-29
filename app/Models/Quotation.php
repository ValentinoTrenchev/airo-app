<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'ages',
        'currency_id',
        'start_date',
        'end_date',
        'total'
    ];
    protected $casts = [
        'total' => 'float',
    ];
}
