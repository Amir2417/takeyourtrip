<?php
namespace App\Http\Helpers;

use Exception;
use Illuminate\Support\Str;
use App\Models\TemporaryData;
use App\Traits\SendMoney\Paypal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Constants\PaymentGatewayConst;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Admin\SendMoneyGateway as AdminSendMoneyGateway;

class SendMoneyGateway {
    use Paypal;
    protected $request_data;
    protected $output;
    protected $temporary_data_identifier = "identifier";
    protected $send_money_gateway = "gateway";
    protected $predefined_user_wallet;
    protected $predefined_guard;
    protected $predefined_user;


    public function __construct(array $request_data)
    {
        $this->request_data = $request_data;
    }
    public static function init(array $data) {
        
        return new SendMoneyGateway($data);
    }
    public function gateway() {
        $request_data = $this->request_data;
        if(empty($request_data)) throw new Exception(__("Gateway Information is not available."));
        $validated = $this->validator($request_data)->validate();
        $gateway_currency = AdminSendMoneyGateway::where("slug",$validated[$this->send_money_gateway])->first();
        $data = TemporaryData::where("identifier",$validated[$this->temporary_data_identifier])->first();
        

        if(!$gateway_currency) {
            throw ValidationException::withMessages([
                $this->send_money_gateway = __("Gateway not available"),
            ]);
        }
        
        $this->output['gateway']    = $gateway_currency;
        $this->output['amount']     = $this->chargeCalculate($data);
        $this->output['form_data']  = $this->request_data;
        $this->output['distribute'] = $this->gatewayDistribute($gateway_currency->gateway);
        
        return $this;
    }

    //validator function
    public function validator($data) {
        return Validator::make($data,[
            $this->temporary_data_identifier  => "required",
            $this->send_money_gateway         => "required",
        ]);
    }

    //charge calculation 
    public function chargeCalculate($data) {
        
        $data = [
            'requested_amount'          => $data->data->amount,
            'sender_cur_code'           => $data->data->currency,
            'fixed_charge'              => $data->data->fixed_charge,
            'percent_charge'            => $data->data->percent_charge,
            'total_charge'              => $data->data->total_charge,
            'total_amount'              => $data->data->payable,
            'will_get'                  => $data->data->will_get,
            'default_currency'          => get_default_currency_code(),
        ];

        return (object) $data;
    }

    // gateway distribute
    public function gatewayDistribute($gateway = null) {

        if(!$gateway) $gateway = $this->output['gateway'];
        $alias = Str::lower($gateway->slug);
        
        $method = PaymentGatewayConst::send_money_register($alias);
        
        if(method_exists($this,$method)) {
            return $method;
        }
        return throw new Exception("Gateway(".$gateway->name.") Trait or Method (".$method."()) does not exists");
    }

    //render function
    public function render() {
        $output = $this->output;

        if(!is_array($output)) throw new Exception(__("Render Failed! Please call with valid gateway/credentials"));

        $common_keys = ['gateway','amount','distribute'];
        foreach($output as $key => $item) {
            if(!array_key_exists($key,$common_keys)) {
                $this->gateway();
                break;
            }
        }

        $distributeMethod = $this->output['distribute'];
        return $this->$distributeMethod($output) ?? throw new Exception(__("Something went wrong! Please try again."));
    }

    //response receive
    public function responseReceive($type = null) {
        $tempData = $this->request_data;
        
        if(empty($tempData) || empty($tempData['type'])) throw new Exception(__('Transaction Failed. Record didn\'t saved properly. Please try again'));

        $method_name = $tempData['type']."SendMoneySuccess";
        $send_money_data = TemporaryData::where('identifier',$tempData['data']->user_record)->first();
        if($send_money_data->data->authenticated == true){
            if($this->requestIsApiUser()) {
                $creator_table = $tempData['data']->creator_table ?? null;
                $creator_id = $tempData['data']->creator_id ?? null;
                $creator_guard = $tempData['data']->creator_guard ?? null;
                $api_authenticated_guards = PaymentGatewayConst::apiAuthenticateGuard();
                if(!array_key_exists($creator_guard,$api_authenticated_guards)) throw new Exception(__("Request user doesn\'t save properly. Please try again"));
                if($creator_table == null || $creator_id == null || $creator_guard == null) throw new Exception(__("Request user doesn\'t save properly. Please try again"));
                $creator = DB::table($creator_table)->where("id",$creator_id)->first();
                if(!$creator) throw new Exception(__("Request user doesn\'t save properly. Please try again"));
                $api_user_login_guard = $api_authenticated_guards[$creator_guard];
                $this->output['api_login_guard'] = $api_user_login_guard;
                Auth::guard($api_user_login_guard)->loginUsingId($creator->id);
            }
        }
        $gateway_id = $tempData['data']->gateway ?? "";
        $gateway_currency = AdminSendMoneyGateway::find($gateway_id);
        
        if(!$gateway_currency) throw new Exception(__('Transaction Failed. Gateway not available'));
        $requested_amount = $tempData['data']->amount->requested_amount ?? 0;
        $validator_data = [
            $this->temporary_data_identifier  => $tempData['data']->user_record,
            $this->send_money_gateway         => $gateway_currency->slug
        ];
        
        $this->request_data = $validator_data;
        $this->gateway();
        $this->output['tempData'] = $tempData;
        if(method_exists(Paypal::class,$method_name)) {
            
            return $this->$method_name($this->output);
        }
       
        throw new Exception("Response method ".$method_name."() does not exists.");
    }

    public function requestIsApiUser() {
        $request_source = request()->get('r-source');
        if($request_source != null && $request_source == PaymentGatewayConst::APP) return true;
        return false;
    }

    public function api() {
        $output = $this->output;
        $output['distribute']   = $this->gatewayDistribute() . "Api";
        $method = $output['distribute'];
        $response = $this->$method($output);
        $output['response'] = $response;
        if( $output['distribute'] == "pagaditoInitApi"){
            $parts = parse_url( $output['response']);
                parse_str($parts['query'], $query);
                // Extract the token value
                if (isset($query['token'])) {
                    $tokenValue = $query['token'];
                } else {
                    $tokenValue = '';
                }
            $output['response'] =  $tokenValue;
        }


        $this->output = $output;
        return $this;
    }
    public function get() {
        return $this->output;
    }
}