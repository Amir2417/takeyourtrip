<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;

class SendMoneyLogController extends Controller
{
    /**
     * Method for send money log
     */
    public function index(){
        $page_title     = "Send Money Logs";
        $transactions   = Transaction::senMoney()->with(['send_money_gateway'])->where('attribute',PaymentGatewayConst::SEND)->paginate(10);

        return view('admin.sections.send-money-log.index',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for send money log details
     * @param $trx_id
     */
    public function details($trx_id){
        $data   = Transaction::where('trx_id',$trx_id)->first();
        if(!$data) return back()->with(['error' => ['Sorry data not found']]);
        $page_title     = "Send Money Details";

        return view('admin.sections.send-money-log.details',compact(
            'page_title',
            'data'
        ));
    }
}
