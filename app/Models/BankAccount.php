<?php

namespace App\Models;

use App\Models\Admin\Bank;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $guarded  = ['id'];

    protected $casts    = [
        'id'            => 'integer',
        'user_id'       => 'integer',
        'bank_id'       => 'integer',
        'credentials'   => 'object',
        'reject_reason' => 'string',
        'status'        => 'integer',
        'created_at'    => 'date:Y-m-d',
        'updated_at'    => 'date:Y-m-d',
    ];

    public function scopeAuth($query){
        $query->where('user_id',auth()->user()->id);
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function bank(){
        return $this->belongsTo(Bank::class,'bank_id');
    }
}
