<?php

namespace App\Http\Controllers\Api\User;

use Exception;
use App\Models\Admin\Bank;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Http\Helpers\Api\Helpers;
use App\Constants\BankAccountConst;
use App\Http\Controllers\Controller;
use App\Traits\ControlDynamicInputFields;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends Controller
{
    use ControlDynamicInputFields;
    /**
     * Method for bank list information
     */
    public function index(){
        $bank_list      = Bank::where('status',true)->orderBy('id','desc')->get()->map(function($data){
            return [
                'id'            => $data->id,
                'slug'          => $data->slug,
                'bank_name'     => $data->bank_name,
                'image'         => $data->image,
                'desc'          => $data->desc,
                'input_fields'  => $data->input_fields
            ];
        });
        $bank_account           = BankAccount::auth()->latest()->first();
        
        $bank_account_image_path            = [
            'base_url'         => url("/"),
            'path_location'    => files_asset_path_basename("kyc-files"),
            'default_image'    => files_asset_path_basename("default"),

        ];
        $bank_image_path            = [
            'base_url'         => url("/"),
            'path_location'    => files_asset_path_basename("bank"),
            'default_image'    => files_asset_path_basename("default"),

        ];
        $status = $bank_account->status ?? 0;
        $data    = [
            'bank_list'               => $bank_list,
            'status'                  => $status,
            'bank_image_path'         => $bank_image_path,
            'bank_account'            => $bank_account,
            'bank_account_image_path' => $bank_account_image_path,
        ];

        $message =  ['success'=>[__('Bank List Fetch Successfully.')]];
        return Helpers::success($data,$message);
    }
    /**
     * Method for bank account store
     */
    public function store(Request $request){
       
        $validator              = Validator::make($request->all(),[
            'bank_id'           => 'required',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }

        $bank_fields                    = Bank::where('id',$request->bank_id)->first()->input_fields ?? [];
        $validation_rules               = $this->generateValidationRules($bank_fields);
        $validated                      = Validator::make($request->all(),$validation_rules);
        if ($validated->fails()) {
            $message =  ['error' => $validated->errors()->all()];
            return Helpers::error($message);
        }
        $validated = $validated->validate();
        $get_values                     = $this->placeValueWithFields($bank_fields,$validated);
        $validated['user_id']           = auth()->user()->id;
        $validated['bank_id']           = $request->bank_id;
        $validated['credentials']       = $get_values;
        $user_account                   = BankAccount::where('user_id',auth()->user()->id)->where('status',BankAccountConst::PENDING)->count();
        if($user_account >= 1){
            $error  = ['error' => [__("Already you sent request for Bank Account.")]];
            return Helpers::error($error);
        }
        try{
            $bank_accout = BankAccount::create($validated);
        }catch(Exception $e){
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message =  ['success'=>[__('Bank Account created successfully')]];
        return Helpers::onlysuccess($message,$bank_accout);
    }
    /**
     * Method for delete bank account 
     * @param $id
     * @param \Illuminate\Http\Request $request 
     */
    public function delete(Request $request){
        $bank_account   = BankAccount::where('id',$request->id)->first();
        if(!$bank_account){
            $error  = ['error' => [__("Data not found.")]];
            return Helpers::error($error);
        }
        try{
            $bank_account->delete();
        }catch(Exception $e){
            $error  = ['error' => [__('Something went wrong. Please try again.')]];
        }
        $message  = ['success' => [__("Bank account deleted successfully.")]];
        return Helpers::onlysuccess($message);
    }
}
