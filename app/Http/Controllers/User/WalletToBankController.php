<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class WalletToBankController extends Controller
{
    /**
     * Method for wallet to bank transfer page
     * @return view
     */
    public function index(){
        $page_title   = "Wallet To Bank Transfer";
        $bank         = BankAccount::auth()->with(['bank'])->where('status',bank_account_const()::APPROVED)->first();
        
        return view('user.sections.wallet-to-bank.index',compact(
            'page_title',
            'bank',
        ));
    }
}
