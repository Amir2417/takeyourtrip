<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Admin\Bank;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Constants\BankAccountConst;
use App\Http\Controllers\Controller;

class BankAccountController extends Controller
{
    /**
     * Method for bank details information
     */
    public function index(){
        $bank_list      = Bank::where('status',true)->orderBy('id','desc')->get()->map(function($data){
            return [
                'id'            => $data->id,
                'slug'          => $data->slug,
                'bank_name'     => $data->bank_name,
                'desc'          => $data->desc,
                'input_fields'  => $data->input_fields
            ];
        });
        $bank_account_approved      = BankAccount::auth()->where('status',BankAccountConst::APPROVED)->first();
        $bank_account_pending       = BankAccount::auth()->where('status',BankAccountConst::PENDING)->first();
        $bank_account_reject        = BankAccount::auth()->where('status',BankAccountConst::REJECTED)->first();
        
        return Response::success([__('Bank List Fetch Successfully.')],[
            'bank_list'                 => $bank_list,
            'bank_account_approved'     => $bank_account_approved,
            'bank_account_pending'      => $bank_account_pending,
            'bank_account_reject'       => $bank_account_reject
        ],200);
    }
}
