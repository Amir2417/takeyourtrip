<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Models\Admin\BasicSettings;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use App\Notifications\Admin\WalletToBank\CompleteEmailNotification;
use App\Notifications\Admin\WalletToBank\RejectEmailNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class BankTransactionController extends Controller
{
    /**
     * Method for bank transaction all logs
     */
    public function index(){
        $page_title     = "All Bank Transactions";
        $transactions   = Transaction::walletToBank()->orderBy('id','desc')->paginate(10);
        
        return view('admin.sections.bank-transaction.index',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for bank transaction pending logs
     */
    public function pending(){
        $page_title     = "Panding Bank Transactions";
        $transactions   = Transaction::walletToBank()->where('status',PaymentGatewayConst::STATUSPENDING)->orderBy('id','desc')->paginate(10);
        
        return view('admin.sections.bank-transaction.pending',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for bank transaction pending logs
     */
    public function complete(){
        $page_title     = "Complete Bank Transactions";
        $transactions   = Transaction::walletToBank()->where('status',PaymentGatewayConst::STATUSSUCCESS)->orderBy('id','desc')->paginate(10);
        
        return view('admin.sections.bank-transaction.complete',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for bank transaction pending logs
     */
    public function reject(){
        $page_title     = "Panding Bank Transactions";
        $transactions   = Transaction::walletToBank()->where('status',PaymentGatewayConst::STATUSREJECTED)->orderBy('id','desc')->paginate(10);
        
        return view('admin.sections.bank-transaction.reject',compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Method for bank transaction details page
     * @param $trx_id
     */
    public function details($trx_id){
        $page_title = "Bank Transaction Details";
        $data   = Transaction::walletToBank()->where('trx_id',$trx_id)->first();
        if(!$data) return back()->with(['error' => ['Sorry data not found!']]);

        return view('admin.sections.bank-transaction.details',compact(
            'page_title',
            'data'
        ));
    }
    /**
     * Method for approve transaction
     * @param $id
     * @param \Illuminate\Http\Request $request
     */
    public function statusUpdate(Request $request,$trx_id){
        $basic_settings     = BasicSettings::first();
        $validator = Validator::make($request->all(),[
            'status'            => 'required|integer',
        ]);

        if($validator->fails()) {
            $errors = ['error' => $validator->errors() ];
            return Response::error($errors);
        }
        $validated      = $validator->validate();
        $data      = Transaction::walletToBank()->where('trx_id',$trx_id)->first();
        
        
        try{
            $data->update([
                'status'    => $validated['status'],
            ]);
            $form_data = [
                'data'        => $data,
                'status'      => 'Complete',
            ];
            if($basic_settings->email_notification == true){
                Notification::route("mail",$data->details->data->user_info->email)->notify(new CompleteEmailNotification($form_data));
            }
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Bank transaction status updated successfully.']]);
    }
    /**
     * Method for approve transaction
     * @param $id
     * @param \Illuminate\Http\Request $request
     */
    public function rejectStatus(Request $request,$trx_id){
        $basic_settings     = BasicSettings::first();
        $validator = Validator::make($request->all(),[
            'status'            => 'required|integer',
            'reject_reason'     => 'required|string'
        ]);

        if($validator->fails()) {
            $errors = ['error' => $validator->errors() ];
            return Response::error($errors);
        }
        $validated      = $validator->validate();
        $data      = Transaction::walletToBank()->where('trx_id',$trx_id)->first();
        
        
        try{
            
            $data->update([
                'status'    => $validated['status'],
                'reject_reason' => $validated['reject_reason']
            ]);

            $user_wallet = UserWallet::where('id',$data->details->data->user_wallet)->first();
            $balance = floatval($user_wallet->balance) + floatval($data->details->data->total_payable);
            $user_wallet->update([
                'balance'   => $balance
            ]);
            $form_data = [
                'data'        => $data,
                'status'      => 'Reject',
                'reject_reason'=> $validated['reject_reason']
            ]; 
            if($basic_settings->email_notification == true){
                Notification::route("mail",$data->details->data->user_info->email)->notify(new RejectEmailNotification($form_data));
            }
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Bank transaction status updated successfully.']]);
    }
}
