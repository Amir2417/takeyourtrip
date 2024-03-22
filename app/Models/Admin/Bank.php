<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts        = [
        'id'                => 'integer',
        'slug'              => 'string',
        'bank_name'         => 'string',
        'currency_name'     => 'string',
        'currency_code'     => 'string',
        'currency_symbol'   => 'string',
        'min_limit'         => 'string',
        'max_limit'         => 'string',
        'percent_charge'    => 'string',
        'fixed_charge'      => 'string',
        'rate'              => 'string',
        'desc'              => 'string',
        'input_fields'      => 'object',
        'status'            => 'integer',
        'created_at'        => 'date:Y-m-d',
        'updated_at'        => 'date:Y-m-d',
    ];
}
