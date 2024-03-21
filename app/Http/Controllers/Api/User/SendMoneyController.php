<?php

namespace App\Http\Controllers\Api\User;


use Exception;
use App\Models\User;
use App\Models\UserQrCode;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Models\TemporaryData;
use App\Http\Helpers\Response;
use App\Models\Admin\Currency;
use App\Models\UserNotification;
use App\Http\Helpers\Api\Helpers;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Constants\NotificationConst;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\SendMoneyGateway;
use App\Models\Admin\AdminNotification;
use App\Models\Admin\TransactionSetting;
use Illuminate\Support\Facades\Validator;
use App\Notifications\User\SendMoney\SenderMail;
use App\Notifications\User\SendMoney\ReceiverMail;
use App\Http\Helpers\SendMoneyGateway as SendMoneyGatewayHelper;

class SendMoneyController extends Controller
{
    protected  $trx_id;
    public function __construct()
    {
        $this->trx_id = 'SM'.getTrxNum();
    }
    public function sendMoneyInfo(Request $request){
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
        $send_money_gateway  = SendMoneyGateway::where('status',true)->whereNot('slug','apple-pay')->get();
        
        $apple_pay_gateway   = SendMoneyGateway::where('status',true)->whereNot('slug','google-pay')->get();
        
        $send_money_image_path            = [
            'base_url'         => url("/"),
            'path_location'    => files_asset_path_basename("send-money-gateway"),
            'default_image'    => files_asset_path_basename("default"),

        ];
        
        $os                         = $request->device;
        if($os == 'android'){
            $data =[
                'base_curr'             => get_default_currency_code(),
                'base_curr_rate'        => get_default_currency_rate(),
                'os'                    => $os,
                'sendMoneyCharge'       => (object)$sendMoneyCharge,
                'send_money_gateway'    => $send_money_gateway,
                'send_money_image_path' => $send_money_image_path,
            ];
            $message =  ['success'=>[__('Send Money Information')]];
            return Helpers::success($data,$message);
        }else if($os == 'ios'){
            $data = [
                'base_curr'             => get_default_currency_code(),
                'base_curr_rate'        => get_default_currency_rate(),
                'os'                    => $os,
                'sendMoneyCharge'       => (object)$sendMoneyCharge,
                'send_money_gateway'    => $apple_pay_gateway,
                'send_money_image_path' => $send_money_image_path,
            ];
            $message =  ['success'=>[__('Send Money Information')]];
            return Helpers::success($data,$message);
        }
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
            'sender_email'      => 'nullable',
            'currency'          => 'required'
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
        
        $receiver_info      = User::where('email',$receiver_email)->first();
        if(isset($receiver_info)){
            if(auth()->check()){
                if($receiver_info->email == $sender_email || $receiver_info->email == auth()->user()->email){
                    return Response::error([__("Can't send money to your own")],[],404);
                }
            }else{
                if($receiver_info->email == $sender_email){
                    return Response::error([__("Can't send money to your own")],[],404);
                }
            }
        }else{
            return Response::error([__("Receiver not found.")],[],404);
        }


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
        if(auth()->check()){
            $authenticated  = true;
            $user           = auth()->user()->id;
        }else{
            $authenticated  = false;
            $user           = null;
        }

        $receiver_wallet             = UserWallet::where('user_id',$receiver_info->id)->first();

        if(!$receiver_wallet) return back()->with(['error' => ['Receiver wallet address not found.']]);

        $validated['identifier']     = Str::uuid();
        $data     = [
            'type'                   => global_const()::SENDMONEY,
            'identifier'             => $validated['identifier'],
            'data'                   => [
                'login_user'         => $user,
                'payment_gateway'    => $payment_gateway->id,
                'amount'             => floatval($amount),
                'total_charge'       => $total_charge,
                'percent_charge'     => $percent_charge,
                'fixed_charge'       => $fixedCharge,
                'payable'            => $payable,
                'currency'           => $currency,
                'sender_email'       => $sender_email,
                'receiver_email'     => $receiver_email,
                'receiver_wallet'    => [
                    'id'             => $receiver_wallet->id,
                    'user_id'        => $receiver_wallet->user_id,
                    'balance'        => $receiver_wallet->balance,
                ],
                'will_get'           => floatval($amount),
                'authenticated'      => $authenticated
            ],  
        ];
        try{
            
            $temporary_data = TemporaryData::create($data);  
        }catch(Exception $e){
            
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $payment_gateway = SendMoneyGateway::where('id',$temporary_data->data->payment_gateway)->first();
        
        if($payment_gateway->slug == global_const()::GOOGLE_PAY){
            $data       = [
                'temporary_data' => $temporary_data,
                'redirect_url'   => setRoute('api.user.send.money.redirect.url',$temporary_data->identifier)
            ];
            $message  = ['success' => ['Send Money Data stored successfully.']];
            return Helpers::success($data,$message,200);
        }else{
            
            $request_data = [
                'identifier'    => $temporary_data->identifier,
                'gateway'       => $payment_gateway->slug,
            ];
            try{

                $instance  = SendMoneyGatewayHelper::init($request_data)->gateway()->api()->get();
                
            }catch(Exception $e){
               
                return Response::error([__('Something went wrong! Please try again.')],[],404);
            }
        
            $trx = $instance['response']['id']??$instance['response']['trx']??$instance['response']['reference_id']??$instance['response']['order_id']??$instance['response'];
            $temData = TemporaryData::where('identifier',$trx)->first();
           
            if(!$temData){
                $error = ['error'=>["Invalid Request"]];
                return Helpers::error($error);
            }
            $payment_informations =[
                'trx' =>  $temData->identifier,
                'gateway_name' =>  $payment_gateway->name,
                'request_amount' => getAmount($temData->data->amount->requested_amount,4),
                'total_charge' => getAmount($temData->data->amount->total_charge,2).' '.$temData->data->amount->sender_cur_code,
                'will_get' => getAmount($temData->data->amount->will_get,2).' '.$temData->data->amount->default_currency,
                'payable_amount' =>  getAmount($temData->data->amount->total_amount,2).' '.$temData->data->amount->sender_cur_code,
            ];
            $data =[
                'gategay_type' => $payment_gateway->type,
                'gateway_name' => $payment_gateway->name,
                'slug' => $payment_gateway->slug,
                'identify' => $temData->type,
                'payment_informations' => $payment_informations,
                'url' => @$temData->data->response->links,
                'method' => "get",
            ];
            $message =  ['success'=>[__('Send Money Inserted Successfully')]];
            return Helpers::success($data, $message);
        }
        
    }
    /**
     * Method for redirect url
     * @param $identifier
     * @param \Illuminate\Http\Request $request
     */
    public function redirectUrl($identifier){
        $data            = TemporaryData::where('identifier',$identifier)->first();
        $login_user      = User::where('id',$data->data->login_user)->first();
        $user = Auth::loginUsingId($login_user->id);
       
        if(!$data){
            $error       = ['error' => [__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $payment_gateway = SendMoneyGateway::where('id',$data->data->payment_gateway)->first();

        $stripe_url      = setRoute('api.user.send.money.stripe.payment.gateway');

        return view('payment-gateway.google-pay',compact(
            'data',
            'payment_gateway',
            'stripe_url'
        ));
        
        
    }
    /**
     * Method for stripe payment gateway 
     * @param $identifier
     */
    public function stripePaymentGateway(Request $request){
        $basic_setting      = BasicSettings::first();
        $validator          = Validator::make($request->all(),[
            'identifier'    => 'required|string',
            'paymentToken'   => 'required'
        ]);

        if($validator->fails()) {
            return Response::error($validator->errors()->all());
        }

        $validated          = $validator->validated();
        $data               = TemporaryData::where('identifier',$validated['identifier'])->first();
        $payment_gateway    = SendMoneyGateway::where('id',$data->data->payment_gateway)->first();
        $payment_token      = $request->paymentToken;
        $user               = auth()->user();
       
        $stripe             = new \Stripe\StripeClient($payment_gateway->credentials->stripe_secret_key);
       
        if($payment_gateway->env == global_const()::TEST){
            $response           =  $stripe->charges->create([
                'amount'        => $data->data->payable * 100,
                'currency'      => 'usd',
                'source'        => 'tok_visa',
            ]);
        }else{
            $response           =  $stripe->charges->create([
                'amount'        => $data->data->payable * 100,
                'currency'      => $data->data->currency,
                'source'        => $payment_token,
            ]);
        }
       
        if($response->status == 'succeeded'){
           
            try{
                $trx_id = $this->trx_id;
                $sender = $this->insertSender($trx_id,$data);
                $this->insertReceiver( $trx_id,$data);
                if($sender){
                    
                    $this->insertSenderCharges($data,$sender);
                    
                }
                $route  = route("user.send.money.index");
               
                return Response::success(['Send Money Successful'],['data' => $route],200);
            }catch(Exception $e) {
                return Response::error(__("Something went wrong! Please try again."),404);
                
            }
        }else{
            return Response::error(__("Something went wrong! Please try again."),404);
        }
    }
    //sender transaction
    public function insertSender($trx_id,$data) {
        $trx_id = $trx_id;
        $user   = auth()->user();
        $details =[
            'data' => $data->data,
            'recipient_amount' => $data->data->will_get
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => null,
                'payment_gateway_currency_id'   => null,
                'send_money_gateway_id'         => $data->data->payment_gateway,
                'type'                          => PaymentGatewayConst::TYPETRANSFERMONEY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $data->data->amount,
                'payable'                       => $data->data->payable,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPETRANSFERMONEY," ")) . " To " .$data->data->receiver_email,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateWalletBalance($data);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    //sender transaction
    public function insertReceiver($trx_id,$data) {
        $trx_id = $trx_id;
        $receiver   = UserWallet::where('user_id',$data->data->receiver_wallet->user_id)->first();
        $balance = floatval($receiver->balance) + floatval($data->data->amount);
        $user   = auth()->user();
        $details =[
            'data' => $data->data,
            'recipient_amount' => $data->data->will_get,
            'current_balance'  => $balance,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $receiver->user_id,
                'user_wallet_id'                => $receiver->id,
                'payment_gateway_currency_id'   => null,
                'send_money_gateway_id'         => $data->data->payment_gateway,
                'type'                          => PaymentGatewayConst::TYPETRANSFERMONEY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $data->data->amount,
                'payable'                       => $data->data->payable,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPETRANSFERMONEY," ")) . " From " .$user->fullname,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::RECEIVED,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    //updateWalletBalance
    function updateWalletBalance($data){
        $receiver_wallet = UserWallet::where('user_id',$data->data->receiver_wallet->user_id)->first();
        if(!$receiver_wallet) return back()->with(['error' => ['Wallet not found.']]);
        
        $balance = floatval($receiver_wallet->balance) + floatval($data->data->amount);
        $receiver_wallet->update([
            'balance'   => $balance,
        ]);
    } 

    public function insertSenderCharges($data,$id) {
        $user = auth()->user();
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $data->data->percent_charge,
                'fixed_charge'      =>$data->data->fixed_charge,
                'total_charge'      =>$data->data->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //store notification
            $notification_content = [
                'title'         => __("Send Money"),
                'message'       => __('Transfer Money to')." ".$data->data->receiver_email.' ' .$data->data->amount.' '.get_default_currency_code()." ".__('Successful'),
                'image'         =>  get_image($user->image,'user-profile'),
            ];
            UserNotification::create([
                'type'      => NotificationConst::TRANSFER_MONEY,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);

            
            DB::commit();

        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }

    public function success(Request $request, $gateway)
    {
        $requestData = $request->all();
        $token = $requestData['token'] ?? "";
        $checkTempData = TemporaryData::where("type", $gateway)->where("identifier", $token)->first();
        if (!$checkTempData){
            $message = ['error' => [__('Transaction failed. Record didn\'t saved properly. Please try again')]];
            return Helpers::error($message);
        }

        $checkTempData = $checkTempData->toArray();
        try {
            $data = SendMoneyGatewayHelper::init($checkTempData)->responseReceive();
        } catch (Exception $e) {
           
            $message = ['error' => [__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }
        
        $message = ['success' => [__("Payment Successful, Please Go Back Your App")]];
        return Helpers::onlysuccess($message);
    }
    public function cancel(Request $request, $gateway)
    {
        $message = ['error' => [__("Something went wrong! Please try again.")]];
        return Helpers::error($message);
    }

}
