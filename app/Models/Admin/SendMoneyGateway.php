<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendMoneyGateway extends Model
{
    use HasFactory;

    protected $guarded  = ['id'];

    protected $casts    = [
        'id'            => 'integer',
        'slug'          => 'string',
        'name'          => 'string',
        'credentials'   => 'object',
        'status'        => 'integer'
    ];

}
