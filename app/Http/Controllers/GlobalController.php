<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Http\Helpers\Api\Helpers;
use App\Models\Admin\SendMoneyGateway;
use App\Models\Admin\TransactionSetting;
use Illuminate\Support\Facades\Validator;

class GlobalController extends Controller
{

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
}
