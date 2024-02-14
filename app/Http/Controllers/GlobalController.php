<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TemporaryData;
use App\Http\Helpers\Response;
use App\Models\Admin\Currency;
use App\Http\Helpers\Api\Helpers;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\SendMoneyGateway;
use App\Models\Admin\TransactionSetting;
use Illuminate\Support\Facades\Validator;

class GlobalController extends Controller
{
    protected  $trx_id;

    public function __construct()
    {
        $this->trx_id = 'SM'.getTrxNum();
    }

    /**
     * Funtion for get state under a country
     * @param country_id
     * @return json $state list
     */
    public function getStates(Request $request) {
        $request->validate([
            'country_id' => 'required|integer',
        ]);
        $country_id = $request->country_id;
        // Get All States From Country
        $country_states = get_country_states($country_id);
        return response()->json($country_states,200);
    }


    public function getCities(Request $request) {
        $request->validate([
            'state_id' => 'required|integer',
        ]);

        $state_id = $request->state_id;
        $state_cities = get_state_cities($state_id);

        return response()->json($state_cities,200);
        // return $state_id;
    }


    public function getCountries(Request $request) {
        $countries = get_all_countries();

        return response()->json($countries,200);
    }


    public function getTimezones(Request $request) {
        $timeZones = get_all_timezones();

        return response()->json($timeZones,200);
    }
    public function userInfo(Request $request) {
        $validator = Validator::make($request->all(),[
            'text'      => "required|string",
        ]);
        if($validator->fails()) {
            return Response::error($validator->errors(),null,400);
        }
        $validated = $validator->validate();
        $field_name = "email";
        // if(check_email($validated['text'])) {
        //     $field_name = "email";
        // }

        try{
            $user = User::where('id','!=',auth()->user()->id)->where($field_name,$validated['text'])->first();
            // return Response::success($user,200);
            if($user != null) {
                if(@$user->address->country === null ||  @$user->address->country != get_default_currency_name()) {
                    $error = ['error' => ["User Country doesn't match with default currency country"]];
                    return Response::error($error, null, 500);
                }
            }
        }catch(Exception $e) {
            $error = ['error' => [$e->getMessage()]];
            return Response::error($error,null,500);
        }
        $success = ['success' => ['Successfully executed']];
        return Response::success($success,$user,200);
    }
    /**
     * Method for send money data information
     */
    public function sendMoney(){
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
        ];
        $message =  ['success'=>[__('Send Money Information')]];
        return Helpers::success($data,$message);
    }
    /**
     * Method for send money confirm
     * @param \Illuminate\Htpp\Request $request
     */
    public function confirmed(Request $request){
        
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
        
        $amount             = $request->amount;
        $currency           = $request->currency;
        $receiver_email     = $request->email;
        $sender_email       = $request->sender_email;

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
        }catch(Exception $e){
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $data       = [
            'temporary_data' => $temporary_data,
            'redirect_url'   => setRoute('api.send.money.redirect.url',$temporary_data->identifier)
        ];
        $message  = ['success' => ['Send Money Data stored successfully.']];
        return Helpers::success($data,$message,200);
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
        $stripe_url      = setRoute('api.send.money.stripe.payment.gateway');

        
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
                    // if( $basic_setting->email_notification == true){
                    //     $notifyDataSender = [
                    //         'trx_id'  => $trx_id,
                    //         'title'  => "Send Money to @" . @$data->data->receiver_email,
                    //         'request_amount'  => getAmount($data->data->amount,4).' '.get_default_currency_code(),
                    //         'payable'   =>  getAmount($data->data->payable,4).' ' .get_default_currency_code(),
                    //         'charges'   => getAmount($data->data->total_charge, 2).' ' .get_default_currency_code(),
                    //         'received_amount'  => getAmount( $data->data->will_get, 2).' ' .get_default_currency_code(),
                    //         'status'  => "Success",
                    //     ];
                    //     //sender notifications
                    //     // $user->notify(new SenderMail($user,(object)$notifyDataSender));
                    // }
                }
                $route  = route("send.money.index");
               
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
        $details =[
            'data' => $data->data,
            'recipient_amount' => $data->data->will_get
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => null,
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
    public function insertSenderCharges($data,$id) {
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
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
}
