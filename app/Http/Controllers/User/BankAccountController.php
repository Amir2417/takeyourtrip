<?php

namespace App\Http\Controllers\User;

use App\Constants\BankAccountConst;
use App\Http\Controllers\Controller;
use App\Models\Admin\Bank;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    /**
     * Method for view bank account page
     * @return view
     */
    public function index(){
        $page_title     = __("Bank Account");
        $bank_account   = BankAccount::auth()->where('status',BankAccountConst::APPROVED)->first();
        $banks          = Bank::where('status',true)->orderBy('id','desc')->get();

        return view('user.sections.bank-account.index',compact(
            'page_title',
            'bank_account',
            'banks'
        ));
    }
    
}
