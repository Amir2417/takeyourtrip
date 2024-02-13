<?php

namespace App\Http\Controllers\Api\User;

use Exception;
use App\Models\User;
use App\Models\UserQrCode;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TemporaryData;
use App\Models\Admin\Currency;
use App\Models\UserNotification;
use App\Http\Helpers\Api\Helpers;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Constants\NotificationConst;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\SendMoneyGateway;
use App\Models\Admin\AdminNotification;
use App\Models\Admin\TransactionSetting;
use Illuminate\Support\Facades\Validator;
use App\Notifications\User\SendMoney\SenderMail;
use App\Notifications\User\SendMoney\ReceiverMail;

class SendMoneyController extends Controller
{
    protected  $trx_id;
    public function __construct()
    {
        $this->trx_id = 'SM'.getTrxNum();
    }
    public function sendMoneyInfo(){
        $user = auth()->user();
        $sendMoneyCharge = TransactionSetting::where('slug','transfer')->where('status',1)->get()->map(function($data){
            return[
                'id' => $data->id,
                'slug' => $data->slug,
                'title' => $data->title,
                'fixed_charge' => getAmount($data->fixed_charge,2),
                'percent_charge' => getAmount($data->percent_charge,2),
                'min_limit' => getAmount($data->min_limit,2),
                'max_limit' => getAmount($data->max_limit,2),
                'monthly_limit' => getAmount($data->monthly_limit,2),
                'daily_limit' => getAmount($data->daily_limit,2),
            ];
        })->first();
        $transactions = Transaction::auth()->senMoney()->latest()->take(10)->get()->map(function($item){
            $statusInfo = [
                "success" =>      1,
                "pending" =>      2,
                "rejected" =>     3,
                ];
                if($item->attribute == payment_gateway_const()::SEND){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'transaction_heading' => "Send Money to @" . @$item->details->data->receiver_email,
                        'request_amount' => getAmount(@$item->request_amount,2) ,
                        'total_charge' => getAmount(@$item->charge->total_charge,2),
                        'payable' => getAmount(@$item->payable,2),
                        'recipient_received' => getAmount(@$item->details->recipient_amount,2),
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                        'status_info' =>(object)@$statusInfo ,
                    ];
                }elseif($item->attribute == payment_gateway_const()::RECEIVED){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'transaction_heading' => "Received Money from @" .@$item->details->sender->username." (".@$item->details->sender->email.")",
                        'recipient_received' => getAmount(@$item->request_amount,2).' '.get_default_currency_code(),
                        'current_balance' => getAmount(@$item->available_balance,2).' '.get_default_currency_code(),
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                        'status_info' =>(object)@$statusInfo ,
                    ];

                }

        });
        $send_money_gateway  = SendMoneyGateway::where('slug',global_const()::GOOGLE_PAY)->where('status',true)->first();
        
        $send_money_image_path            = [
            'base_url'         => url("/"),
            'path_location'    => files_asset_path_basename("send-money-gateway"),
            'default_image'    => files_asset_path_basename("default"),

        ];
        $data =[
            'base_curr'             => get_default_currency_code(),
            'base_curr_rate'        => get_default_currency_rate(),
            'sendMoneyCharge'       => (object)$sendMoneyCharge,
            'send_money_gateway'    => $send_money_gateway,
            'send_money_image_path' => $send_money_image_path,
            'transactions'          => $transactions,
        ];
        $message =  ['success'=>[__('Send Money Information')]];
        return Helpers::success($data,$message);
    }
    public function checkUser(Request $request){
        $validator = Validator::make(request()->all(), [
            'email'     => "required|email",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $column = "email";
        if(check_email($request->email)) $column = "email";
        $exist = User::where($column,$request->email)->first();
        if( !$exist){
            $error = ['error'=>[__('User not found')]];
            return Helpers::error($error);
        }
        $user = auth()->user();
        if(@$exist && $user->email == @$exist->email){
             $error = ['error'=>[__("Can't send money to your own")]];
            return Helpers::error($error);
        }
        $data =[
            'exist_user'   => $exist,
        ];
        $message =  ['success'=>[__('Valid user for transaction.')]];
        return Helpers::success($data,$message);
    }
    public function qrScan(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'qr_code'     => "required|string",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $qr_code = $request->qr_code;
        $qrCode = UserQrCode::where('qr_code',$qr_code)->first();
        if(!$qrCode){
            $error = ['error'=>[__('Not found')]];
            return Helpers::error($error);
        }
        $user = User::find($qrCode->user_id);
        if(!$user){
            $error = ['error'=>[__('User not found')]];
            return Helpers::error($error);
        }
        if( $user->email == auth()->user()->email){
            $error = ['error'=>[__("Can't send money to your own")]];
            return Helpers::error($error);
        }
        $data =[
            'user_email'   => $user->email,
            ];
        $message =  ['success'=>[__('QR Scan Result.')]];
        return Helpers::success($data,$message);
    }

    public function confirmedSendMoney(Request $request){
        $validator = Validator::make(request()->all(), [
            'amount'            => 'required|numeric|gt:0',
            'email'             => 'required|email',
            'payment_method'    => 'required',
            'sender_email'      => 'nullable'
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        if($basic_setting->kyc_verification){
            if( $user->kyc_verified == 0){
                $error = ['error'=>[__('Please submit kyc information!')]];
                return Helpers::error($error);
            }elseif($user->kyc_verified == 2){
                $error = ['error'=>[__('Please wait before admin approved your kyc information')]];
                return Helpers::error($error);
            }elseif($user->kyc_verified == 3){
                $error = ['error'=>[__('Admin rejected your kyc information, Please re-submit again')]];
                return Helpers::error($error);
            }
        }
        $amount             = $request->amount;
        $currency           = $request->currency;
        $receiver_email     = $request->email;
        $sender_email       = $request->sender_email;
        $user               = auth()->user();

        $sendMoneyCharge = TransactionSetting::where('slug','transfer')->where('status',1)->first();
        $payment_gateway    = SendMoneyGateway::where('id',$request->payment_method)->first();
        
        $this_month_start   = date('Y-m-01');
        $this_month_end     = date('Y-m-t');
        $this_month_send_money  = Transaction::where('type',PaymentGatewayConst::TYPETRANSFERMONEY)->whereDate('created_at',">=" , $this_month_start)
                            ->whereDate('created_at',"<=" , $this_month_end)
                            ->sum('request_amount');
        if($sendMoneyCharge->monthly_limit < $this_month_send_money){
            $error = ['error'=>[__('The receiver have exceeded the monthly amount. Please try smaller amount.')]];
            return Helpers::error($error);
        }
        $total_request_amount = $amount + $this_month_send_money;
        if($sendMoneyCharge->monthly_limit < $total_request_amount){
            $error = ['error'=>[__('The receiver have exceeded the monthly amount. Please try smaller amount.')]];
            return Helpers::error($error);
        }

        $baseCurrency = Currency::default();
        if(!$baseCurrency){
            $error = ['error'=>[__('Default currency not found')]];
            return Helpers::error($error);
        }
        $rate = $baseCurrency->rate;
        $email = $request->email;
        

        $minLimit =  $sendMoneyCharge->min_limit *  $rate;
        $maxLimit =  $sendMoneyCharge->max_limit *  $rate;
        if($amount < $minLimit || $amount > $maxLimit) {
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }
        //charge calculations
        $fixedCharge        = $sendMoneyCharge->fixed_charge *  $rate;
        $percent_charge     = ($request->amount / 100) * $sendMoneyCharge->percent_charge;
        $total_charge       = $fixedCharge + $percent_charge;
        $payable            = $total_charge + $amount;
        

        $validated['identifier']     = Str::uuid();
        $data     = [
            'type'                   => global_const()::SENDMONEY,
            'identifier'             => $validated['identifier'],
            'data'                   => [
                'payment_gateway'    => $payment_gateway->id,
                'amount'             => floatval($amount),
                'total_charge'       => $total_charge,
                'percent_charge'     => $percent_charge,
                'fixed_charge'       => $fixedCharge,
                'payable'            => $payable,
                'currency'           => $currency,
                'sender_email'       => $sender_email,
                'receiver_email'     => $receiver_email,
                'will_get'           => floatval($amount),
            ],  
        ];
        try{
            $temporary_data = TemporaryData::create($data);
            $this->redirectUrl($temporary_data->identifier);
            
        }catch(Exception $e){
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message  = ['success' => ['Send Money Data stored successfully.']];
        return Helpers::onlysuccess($message);
    }
    /**
     * Method for redirect url
     * @param $identifier
     * @param \Illuminate\Http\Request $request
     */
    public function redirectUrl($identifier){
        $data            = TemporaryData::where('identifier',$identifier)->first();
        if(!$data){
            $error       = ['error' => [__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $payment_gateway = SendMoneyGateway::where('id',$data->data->payment_gateway)->first();
        
        return view('payment-gateway.google-pay',compact(
            'data',
            'payment_gateway'
        ));
    }

     //sender transaction
     public function insertSender($trx_id,$user,$userWallet,$amount,$recipient,$payable,$receiver) {
        $trx_id = $trx_id;
        $authWallet = $userWallet;
        $afterCharge = ($authWallet->balance - $payable);
        $details =[
            'recipient_amount' => $recipient,
            'receiver' => $receiver,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::TYPETRANSFERMONEY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPETRANSFERMONEY," ")) . " To " .$receiver->fullname,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function updateSenderWalletBalance($authWalle,$afterCharge) {
        $authWalle->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($fixedCharge,$percent_charge, $total_charge, $amount,$user,$id,$receiver) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $percent_charge,
                'fixed_charge'      =>$fixedCharge,
                'total_charge'      =>$total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => __("Send Money"),
                'message'       => __('Transfer Money to')." ".$receiver->fullname.' ' .$amount.' '.get_default_currency_code()." ".__('Successful'),
                'image'         => files_asset_path('profile-default'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::TRANSFER_MONEY,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);

             //admin create notifications
             $notification_content['title'] = __('Transfer Money Send To').' ('.$receiver->username.')';
             AdminNotification::create([
                 'type'      => NotificationConst::TRANSFER_MONEY,
                 'admin_id'  => 1,
                 'message'   => $notification_content,
             ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    //Receiver Transaction
    public function insertReceiver($trx_id,$user,$userWallet,$amount,$recipient,$payable,$receiver,$receiverWallet) {
        $trx_id = $trx_id;
        $receiverWallet = $receiverWallet;
        $recipient_amount = ($receiverWallet->balance + $recipient);
        $details =[
            'sender_amount' => $amount,
            'sender' => $user,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $receiver->id,
                'user_wallet_id'                => $receiverWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::TYPETRANSFERMONEY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $recipient_amount,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPETRANSFERMONEY," ")) . " From " .$user->fullname,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::RECEIVED,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateReceiverWalletBalance($receiverWallet,$recipient_amount);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function updateReceiverWalletBalance($receiverWallet,$recipient_amount) {
        $receiverWallet->update([
            'balance'   => $recipient_amount,
        ]);
    }
    public function insertReceiverCharges($fixedCharge,$percent_charge, $total_charge, $amount,$user,$id,$receiver) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $percent_charge,
                'fixed_charge'      =>$fixedCharge,
                'total_charge'      =>$total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => __("Send Money"),
                'message'       => __('Transfer Money from')." ".$user->fullname.' ' .$amount.' '.get_default_currency_code()." ".__('Successful'),
                'image'         => files_asset_path('profile-default'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::TRANSFER_MONEY,
                'user_id'  => $receiver->id,
                'message'   => $notification_content,
            ]);

             //admin notification
             $notification_content['title'] = __('Transfer Money Received From').' ('.$user->username.')';
            AdminNotification::create([
                'type'      => NotificationConst::TRANSFER_MONEY,
                'admin_id'  => 1,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }

}
