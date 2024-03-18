<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Models\TemporaryData;
use App\Http\Helpers\Response;
use App\Models\Admin\Currency;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Constants\NotificationConst;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\SendMoneyGateway;
use App\Models\Admin\AdminNotification;
use Illuminate\Support\Facades\Session;
use App\Models\Admin\TransactionSetting;
use App\Traits\SendMoney\GooglePayTrait;
use Illuminate\Support\Facades\Validator;
use App\Notifications\User\SendMoney\SenderMail;
use App\Notifications\User\SendMoney\ReceiverMail;
use App\Events\User\NotificationEvent as UserNotificationEvent;
use App\Http\Helpers\SendMoneyGateway as SendMoneyGatewayHelper;
use App\Models\Admin\PaymentGateway;
use PDO;

class SendMoneyController extends Controller
{
    use GooglePayTrait;
    protected  $trx_id;

    public function __construct()
    {
        $this->trx_id = 'SM'.getTrxNum();
    }
    public function index() {
        $page_title         = __("Send Money");
        $sendMoneyCharge    = TransactionSetting::where('slug','transfer')->where('status',1)->first();
        $transactions       = Transaction::auth()->senMoney()->latest()->take(10)->get();
        $google_pay_gateway = SendMoneyGateway::where('slug',global_const()::GOOGLE_PAY)->where('status',true)->first();
        $paypal_gateway     = SendMoneyGateway::where('slug',global_const()::PAYPAL)->where('status',true)->first();
        $agent              = new Agent();
        $os                 = Str::lower($agent->platform());

        return view('user.sections.send-money.index',compact(
            "page_title",
            'sendMoneyCharge',
            'transactions',
            'google_pay_gateway',
            'paypal_gateway',
            'os',
        ));
    }
    public function checkUser(Request $request){
        $email = $request->email;
        $exist['data'] = User::where('email',$email)->first();

        $user = auth()->user();
        if(@$exist['data'] && $user->email == @$exist['data']->email){
            return response()->json(['own'=>__("Can't send money to your own")]);
        }
        return response($exist);
    }
    public function confirmed(Request $request){
        
        $request->validate([
            'amount'            => 'required|numeric|gt:0',
            'email'             => 'required|email',
            'payment_method'    => 'required',
            'sender_email'      => 'nullable'
        ]);
        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        if($basic_setting->kyc_verification){
            if( $user->kyc_verified == 0){
                return redirect()->route('user.profile.index')->with(['error' => [__('Please submit kyc information!')]]);
            }elseif($user->kyc_verified == 2){
                return redirect()->route('user.profile.index')->with(['error' => [__('Please wait before admin approved your kyc information')]]);
            }elseif($user->kyc_verified == 3){
                return redirect()->route('user.profile.index')->with(['error' => [__('Admin rejected your kyc information, Please re-submit again')]]);
            }
        }
        $amount             = floatval($request->amount);
        $currency           = $request->currency;
        $receiver_email     = $request->email;
        $sender_email       = $request->sender_email;
        $user               = auth()->user();
        $sendMoneyCharge    = TransactionSetting::where('slug','transfer')->where('status',1)->first();
        $payment_gateway    = SendMoneyGateway::where('id',$request->payment_method)->first();
       
        $this_month_start   = date('Y-m-01');
        $this_month_end     = date('Y-m-t');
        $this_month_send_money  = Transaction::where('type',PaymentGatewayConst::TYPETRANSFERMONEY)->whereDate('created_at',">=" , $this_month_start)
                            ->whereDate('created_at',"<=" , $this_month_end)
                            ->sum('request_amount');
        if($sendMoneyCharge->monthly_limit < $this_month_send_money){
            return back()->with(['error' => [__('The receiver have exceeded the monthly amount. Please try smaller amount.')]]);
        }
        $total_request_amount = $amount + $this_month_send_money;
        if($sendMoneyCharge->monthly_limit < $total_request_amount){
            return back()->with(['error' => [__('The receiver have exceeded the monthly amount. Please try smaller amount.')]]);
        }

        $baseCurrency = Currency::default();
        $rate = $baseCurrency->rate;
        if(!$baseCurrency){
            return back()->with(['error' => [__('Default currency not found')]]);
        }
        

        $minLimit =  $sendMoneyCharge->min_limit *  $rate;
        $maxLimit =  $sendMoneyCharge->max_limit *  $rate;
        if($amount < $minLimit || $amount > $maxLimit) {
            return back()->with(['error' => [__("Please follow the transaction limit")]]);
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
                'login_user'         => auth()->user()->id,
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
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return redirect()->route('user.send.money.redirect.url',$temporary_data->identifier);
        
    }
    /**
     * Method for Send Money form submit using google pay
     */
    public function handlePaymentConfirmation(Request $request){
        $request->validate([
            'amount'            => 'required|numeric|gt:0',
            'receiverEmail'             => 'required|email',
            'paymentMethod'    => 'required',
            'senderEmail'      => 'nullable'
        ]);
        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        if($basic_setting->kyc_verification){
            if( $user->kyc_verified == 0){
                return Response::error([__('Please submit kyc information!')],[],404);
            }elseif($user->kyc_verified == 2){
                return Response::error([__('Please wait before admin approved your kyc information')],[],404);
            }elseif($user->kyc_verified == 3){
                return Response::error([__('Admin rejected your kyc information, Please re-submit again')],[],404);
            }
        }
        $amount             = floatval($request->amount);
        $currency           = $request->currency;
        $receiver_email     = $request->receiverEmail;
        $sender_email       = $request->senderEmail;
        $user               = auth()->user();
        $sendMoneyCharge    = TransactionSetting::where('slug','transfer')->where('status',1)->first();
        $payment_gateway    = SendMoneyGateway::where('id',$request->paymentMethod)->first();
       
        $this_month_start   = date('Y-m-01');
        $this_month_end     = date('Y-m-t');
        $this_month_send_money  = Transaction::where('type',PaymentGatewayConst::TYPETRANSFERMONEY)->whereDate('created_at',">=" , $this_month_start)
                            ->whereDate('created_at',"<=" , $this_month_end)
                            ->sum('request_amount');
        if($sendMoneyCharge->monthly_limit < $this_month_send_money){
            return Response::error([__('The receiver have exceeded the monthly amount. Please try smaller amount.')],[],404);
        }
        $total_request_amount = $amount + $this_month_send_money;
        if($sendMoneyCharge->monthly_limit < $total_request_amount){
            return Response::error([__('The receiver have exceeded the monthly amount. Please try smaller amount.')],[],404);
        }

        $baseCurrency = Currency::default();
        $rate = $baseCurrency->rate;
        if(!$baseCurrency){
            return Response::error([__('Default currency not found')],[],404);
        }
        

        $minLimit =  $sendMoneyCharge->min_limit *  $rate;
        $maxLimit =  $sendMoneyCharge->max_limit *  $rate;
        if($amount < $minLimit || $amount > $maxLimit) {
            return Response::error([__('Please follow the transaction limit')],[],404);
        }
        //charge calculations
        $fixedCharge        = $sendMoneyCharge->fixed_charge *  $rate;
        $percent_charge     = ($request->amount / 100) * $sendMoneyCharge->percent_charge;
        $total_charge       = $fixedCharge + $percent_charge;
        $payable            = $total_charge + $amount;
        if(auth()->check()){
            $authenticated  = true;
        }else{
            $authenticated  = false;
        }

        $validated['identifier']     = Str::uuid();
        $data     = [
            'type'                   => global_const()::SENDMONEY,
            'identifier'             => $validated['identifier'],
            'data'                   => [
                'login_user'         => auth()->user()->id,
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
                'authenticated'      => $authenticated,
            ],  
        ];
        try{
            $temporary_data = TemporaryData::create($data);
        }catch(Exception $e){
            return Response::error([__('Something went wrong! Please try again.')],[],404);
        }
        $payment_gateway = SendMoneyGateway::where('id',$temporary_data->data->payment_gateway)->first();
        return Response::success([__('Data stored')],[
            'data' => $temporary_data,
            'payment_gateway' => $payment_gateway
        ],200);
    }
    
    /**
     * Method for view redirect url
     * @param $identifier
     * @param \Illuminate\Http\Request $request
     */
    public function redirectUrl($identifier){
        $data            = TemporaryData::where('identifier',$identifier)->first();
        if(!$data)  return back()->with(['error' => ['Sorry! Data not found.']]);
        $payment_gateway = SendMoneyGateway::where('id',$data->data->payment_gateway)->first();
        if($payment_gateway->slug == global_const()::GOOGLE_PAY){
            $stripe_url      = setRoute('user.send.money.stripe.payment.gateway');

            return view('payment-gateway.google-pay',compact(
                'data',
                'payment_gateway',
                'stripe_url'
            ));
        }elseif($payment_gateway->slug == global_const()::PAYPAL){
            $request_data = [
                'identifier'    => $data->identifier,
                'gateway'       => $payment_gateway->slug,
            ];
            try{

                $instance  = SendMoneyGatewayHelper::init($request_data)->gateway()->render();
                
            }catch(Exception $e){
                return Response::error([__('Something went wrong! Please try again.')],[],404);
            }
            return $instance;
        }
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
       
        $response           =  $stripe->charges->create([
            'amount'        => $data->data->payable * 100,
            'currency'      => 'usd',
            'source'        => 'tok_visa',
        ]);
       
        if($response->status == 'succeeded'){
           
            try{
                $trx_id = $this->trx_id;
                $sender = $this->insertSender($trx_id,$data);

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
    //success
    public function success(Request $request, $gateway){
        $requestData = $request->all();
       
        $token = $requestData['token'] ?? "";
        $checkTempData = TemporaryData::where("identifier",$token)->first();
        if(!$checkTempData) return redirect()->route('user.send.money.index')->with(['error' => [__('Transaction failed. Record didn\'t saved properly. Please try again')]]);
        $checkTempData = $checkTempData->toArray();
        
        try{
            
            $data = SendMoneyGatewayHelper::init($checkTempData)->responseReceive();
            
        }catch(Exception $e) {
            return back()->with(['error' => [__('Something went wrong! Please try again.')]]);
        }
        $data = $data->details->data;
        if($data->authenticated == false){
            return redirect()->route("send.money.index")->with(['success' => [__("Successfully Send Money")]]);
        }else{
            return redirect()->route("user.send.money.index")->with(['success' => [__("Successfully Send Money")]]);
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

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    /**
     * Method for insert sender charges
     */
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
    
    
}
