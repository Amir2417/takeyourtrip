<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Blog;
use App\Models\User;
use App\Models\Contact;
use App\Models\Newsletter;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use App\Models\TemporaryData;
use App\Http\Helpers\Response;
use App\Models\Admin\Currency;
use App\Models\Admin\Language;
use App\Models\Admin\SetupPage;
use App\Http\Helpers\Api\Helpers;
use App\Models\Admin\AppSettings;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Http\Helpers\PaymentGateway;
use Illuminate\Support\Facades\Auth;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\SendMoneyGateway;
use App\Http\Helpers\PaymentGatewayApi;
use App\Models\Admin\TransactionSetting;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers\SendMoneyGateway as SendMoneyGatewayHelper;

class SiteController extends Controller
{
    protected  $trx_id;

    public function __construct()
    {
        $this->trx_id = 'SM'.getTrxNum();
    }
    public function home(){

        $basic_settings = BasicSettings::first();
        $page_title = $basic_settings->site_title??"Home";
        $app_urls = AppSettings::first();
        return view('frontend.index',compact('page_title','app_urls'));
    }
    public function about(){
        $page_title = "About";
        return view('frontend.about',compact('page_title'));
    }
    public function faq(){
        $page_title = "Faq";
        return view('frontend.faq',compact('page_title'));
    }
    public function service(){
        $page_title = "Service";
        return view('frontend.service',compact('page_title'));
    }
    public function blog(){
        $page_title = "Blog";
        $categories = BlogCategory::active()->latest()->get();
        $blogs = Blog::active()->orderBy('id',"DESC")->paginate(8);
        $recentPost = Blog::active()->latest()->limit(3)->get();
        return view('frontend.blog',compact('page_title','blogs','recentPost','categories'));
    }
    public function blogDetails($id,$slug){
        $page_title = "Blog Details";
        $categories = BlogCategory::active()->latest()->get();
        $blog = Blog::where('id',$id)->where('slug',$slug)->first();
        $recentPost = Blog::active()->where('id',"!=",$id)->latest()->limit(3)->get();
        return view('frontend.blogDetails',compact('page_title','blog','recentPost','categories'));
    }
    public function blogByCategory($id,$slug){
        $categories = BlogCategory::active()->latest()->get();
        $category = BlogCategory::findOrfail($id);
        $page_title = __("category");
        $blogs = Blog::active()->where('category_id',$category->id)->latest()->paginate(8);
        $recentPost = Blog::active()->latest()->limit(3)->get();
        return view('frontend.blogByCategory',compact('page_title','blogs','category','categories','recentPost'));
    }
    public function agentInfo(){
        $page_title = "Agent";
        return redirect()->route('agent.login');
    }
    public function merchant(){
        $page_title = "Merchant";
        return view('frontend.merchant',compact('page_title'));
    }
    public function contact(){
        $page_title = "Contact Us";
        return view('frontend.contact',compact('page_title'));
    }
    public function contactStore(Request $request){

        $validator = Validator::make($request->all(),[
            'name'    => 'required|string',
            'email'   => 'required|email',
            'mobile'  => 'required',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validate();
        try {
            Contact::create($validated);
        } catch (\Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__('Your Message Submitted!')]]);

    }
    public function changeLanguage($lang = null)
    {
        $language = Language::where('code', $lang)->first();
        session()->put('local', $lang);
        return redirect()->back();
    }
    public function usefulPage($slug){
        $defualt = selectedLang();
        $page = SetupPage::where('slug', $slug)->where('status', 1)->first();
        if(empty($page)){
            abort(404);
        }
        $page_title = $page->title->language->$defualt->title;

        return view('frontend.policy_pages',compact('page_title','page','defualt'));
    }
    public function newsletterSubmit(Request $request){
        $validator = Validator::make($request->all(),[
            'fullname' => 'required|string|max:100',
            'email' => 'required|email|unique:newsletters',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $in['fullname'] = $request->fullname;
        $in['email'] = $request->email;
        try{
            Newsletter::create($in);
            return redirect()->back()->with(['success' => [__('Your newsletter information submission successfully')]]);
        }catch(Exception $e){
            return back()->with(['error' => [$e->getMessage()]]);
        }
    }
    public function pagaditoSuccess(){
        $request_data = request()->all();
        //if payment is successful
            $token = $request_data['param1'];
            $checkTempData = TemporaryData::where("type",PaymentGatewayConst::PAGADITO)->where("identifier",$token)->first();
            if($checkTempData->data->env_type == 'web'){
                if(!$checkTempData) return redirect()->route('user.add.money.index')->with(['error' => [__("Transaction Failed. Record didn\'t saved properly. Please try again")]]);
                $checkTempData = $checkTempData->toArray();
                try{
                    PaymentGateway::init($checkTempData)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive('pagadito');
                }catch(Exception $e) {
                    return back()->with(['error' => [$e->getMessage()]]);
                }
                return redirect()->route("user.add.money.index")->with(['success' => ['Successfully added money']]);

            }elseif($checkTempData->data->env_type == 'agent'){
                if(!$checkTempData) return redirect()->route('agent.add.money.index')->with(['error' => [__("Transaction Failed. Record didn\'t saved properly. Please try again")]]);
                $checkTempData = $checkTempData->toArray();
                try{
                    PaymentGateway::init($checkTempData)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive('pagadito');
                }catch(Exception $e) {
                    return back()->with(['error' => [$e->getMessage()]]);
                }
                return redirect()->route("agent.add.money.index")->with(['success' => ['Successfully added money']]);

            }elseif($checkTempData->data->env_type == 'api'){
                if(!$checkTempData) {
                    $message = ['error' => [__("Transaction Failed. Record didn\'t saved properly. Please try again")]];
                    return Helpers::error($message);
                }
                $checkTempData = $checkTempData->toArray();
                try{
                    PaymentGatewayApi::init($checkTempData)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive('pagadito');
                }catch(Exception $e) {
                    $message = ['error' => [$e->getMessage()]];
                    Helpers::error($message);
                }
                $message = ['success' => [__("Payment Successful, Please Go Back Your App")]];
                return Helpers::onlysuccess($message);
            }elseif($checkTempData->data->env_type == 'agent_api'){
                if(!$checkTempData) {
                    $message = ['error' => [__("Transaction Failed. Record didn\'t saved properly. Please try again")]];
                    return Helpers::error($message);
                }
                $checkTempData = $checkTempData->toArray();
                $creator_table = $checkTempData['data']->creator_table ?? null;
                $creator_id = $checkTempData['data']->creator_id ?? null;
                $creator_guard = $checkTempData['data']->creator_guard ?? null;
                $api_authenticated_guards = PaymentGatewayConst::apiAuthenticateGuard();
                if($creator_table != null && $creator_id != null && $creator_guard != null) {
                    if(!array_key_exists($creator_guard,$api_authenticated_guards)) throw new Exception(__("Request user doesn\'t save properly. Please try again"));
                    $creator = DB::table($creator_table)->where("id",$creator_id)->first();
                    if(!$creator) throw new Exception(__("Request user doesn\'t save properly. Please try again"));
                    $api_user_login_guard = $api_authenticated_guards[$creator_guard];
                    Auth::guard($api_user_login_guard)->loginUsingId($creator->id);
                }

                try{
                    PaymentGatewayApi::init($checkTempData)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive('pagadito');
                }catch(Exception $e) {
                    $message = ['error' => [$e->getMessage()]];
                    Helpers::error($message);
                }
                $message = ['success' => [__("Payment Successful, Please Go Back Your App")]];
                return Helpers::onlysuccess($message);
            }else{
                $message = ['error' => [__("Transaction failed")]];
                Helpers::error($message);
            }


        }






    /**
     * Method for view send money page
     * @return view
     */
    public function sendMoney(Request $request){
        
        $page_title         = "Send Money";
        $sendMoneyCharge    = TransactionSetting::where('slug','transfer')->where('status',1)->first();
        $google_pay_gateway = SendMoneyGateway::where('slug',global_const()::GOOGLE_PAY)->where('status',true)->first();
        $paypal_gateway     = SendMoneyGateway::where('slug',global_const()::PAYPAL)->where('status',true)->first();
        $apple_pay_gateway  = SendMoneyGateway::where('slug',global_const()::APPLE_PAY)->where('status',true)->first();
        $agent              = new Agent();
        $os                 = Str::lower($agent->platform());
        $email              = $request->email ?? '';
        
        return view('frontend.send-money',compact(
            'page_title',
            'sendMoneyCharge',
            'google_pay_gateway',
            'paypal_gateway',
            'apple_pay_gateway',
            'os',
            'email'
        ));
    }
    /**
     * Method for send money confirm 
     * @param \Illuminate\Http\Request $request
     */
    /**
     * Method for Send Money form submit using google pay
     */
    public function handlePaymentConfirmation(Request $request){
        $request->validate([
            'amount'           => 'required|numeric|gt:0',
            'receiverEmail'    => 'required|email',
            'paymentMethod'    => 'required',
            'senderEmail'      => 'nullable'
        ]);
        $basic_setting = BasicSettings::first();
        
        $amount             = floatval($request->amount);
        $currency           = $request->currency;
        $receiver_email     = $request->receiverEmail;
        $sender_email       = $request->senderEmail;
        
        $sendMoneyCharge    = TransactionSetting::where('slug','transfer')->where('status',1)->first();
        $payment_gateway    = SendMoneyGateway::where('id',$request->paymentMethod)->first();
       
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
        
        // $receiver_wallet    = UserWallet::where

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
     * Method for direct url for send money
     * @param $identifier
     * @param \Illuminate\Http\Request $request
     */
    public function redirectUrl($identifier){
        $data            = TemporaryData::where('identifier',$identifier)->first();
        if(!$data)  return back()->with(['error' => ['Sorry! Data not found.']]);
        $payment_gateway = SendMoneyGateway::where('id',$data->data->payment_gateway)->first();
        if($payment_gateway->slug == global_const()::GOOGLE_PAY){
            $stripe_url      = setRoute('send.money.stripe.payment.gateway');

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
        if(auth()->check()){
            $user  = auth()->user()->id;
        }else{
            $user  = null;
        }
        $details =[
            'data' => $data->data,
            'recipient_amount' => $data->data->will_get
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user,
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
    //sender transaction
    public function insertReceiver($trx_id,$data) {
        $trx_id = $trx_id;
        $receiver   = UserWallet::where('user_id',$data->data->receiver_wallet->user_id)->first();
        if(auth()->check()){
            $user  = auth()->user();
            $fullname  = $user->fullname;
        }else{
            $user  = null;
            $fullname  = null;
        }
        $details =[
            'data' => $data->data,
            'recipient_amount' => $data->data->will_get
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
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPETRANSFERMONEY," ")) . " From " .$fullname,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::RECEIVED,
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
       
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $data->data->percent_charge,
                'fixed_charge'      => $data->data->fixed_charge,
                'total_charge'      => $data->data->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();


        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    
}
