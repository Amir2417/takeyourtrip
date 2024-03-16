<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Models\Admin\SendMoneyGateway;
use Illuminate\Support\Facades\Validator;

class SendMoneyGatewayController extends Controller
{
    /**
     * Method for view send money gateway index
     * @return view
     */
    public function index(){
        $page_title     = "Send Money Gateway";
        $send_money     = SendMoneyGateway::orderBy('id','desc')->get();

        return view('admin.sections.send-money.index',compact(
            'page_title',
            'send_money'
        ));
    }
    /**
     * Method for view edit send money page
     * @return view $slug
     * @param \Illuminate\Http\Request $request
     */
    public function edit($slug){
        $page_title     = "Send Money Gateway Edit";
        $data           = SendMoneyGateway::where('slug',$slug)->first();
        if(!$data) return back()->with(['error' => ['Sorry! Data not found.']]);

        return view('admin.sections.send-money.edit',compact(
            'page_title',
            'data'
        ));
    }
    /**
     * Method for update send money gateway information
     * @param $slug
     * @param \Illuminate\Http\Request $request
     */
    public function update(Request $request,$slug){
        $data                       = SendMoneyGateway::where('slug',$slug)->first();
        if(!$data) return back()->with(['error' => ['Sorry! Data not found.']]);

        $validator                  = Validator::make($request->all(),[
            'slug'                  => 'required|in:google-pay,paypal',
            'name'                  => 'required_if:slug,google-pay',
            'env'                   => 'required_if:slug,google-pay,paypal',
            'gateway'               => 'required_if:slug,google-pay',
            'stripe_version'        => 'required_if:slug,google-pay',
            'stripe_publishable_key'=> 'required_if:slug,google-pay',
            'stripe_secret_key'     => 'required_if:slug,google-pay',
            'merchant_id'           => 'required_if:slug,google-pay',
            'merchant_name'         => 'required_if:slug,google-pay',
            'image'                 => "nullable|mimes:png,jpg,jpeg,webp",
        ]);
        if($validator->fails()) return back()->withErrors($validator)->withInput($request->all());
        $update_data = array_filter($request->except('_token','image','slug','name','fileholder-image','_method','env'));
        $data->name        = $request->name;
        
        
        if($request->slug == global_const()::GOOGLE_PAY){
            $image = $data->image;
            if($request->hasFile('image')) {
                $image = get_files_from_fileholder($request,'image');
                $upload_image = upload_files_from_path_dynamic($image,'send-money-gateway',$data->image);
                $image = $upload_image;
            }
            $data->credentials = $update_data;
            $data->update([
                'credentials' => $update_data,
                'image'         => $image,
                'env'         => $request->env
            ]);
        }elseif($request->slug == global_const()::PAYPAL){

            $credentials_validation_rules = [];
            $credentials = $data->credentials;
            
            foreach($credentials as $values) {
                $values = (array) $values;
                
                $credentials_validation_rules[$values['name']] = "nullable|string";
            }
    
            $credentials_input_fields = array_keys($credentials_validation_rules);
            $validated_credentials = Validator::make($request->only($credentials_input_fields),$credentials_validation_rules)->validate();
    
            $credentials_array = json_decode(json_encode($credentials),true);
            foreach($credentials_array as $key => $item) {
                foreach($validated_credentials as $input_name => $value) {
                    if($input_name == $item['name']) {
                        $item['value'] = $value;
                    }
                    $credentials_array[$key] = $item;
                }
            }
            $image = $data->image;
            if($request->hasFile('image')) {
                $image = get_files_from_fileholder($request,'image');
                $upload_image = upload_files_from_path_dynamic($image,'send-money-gateway',$data->image);
                $image = $upload_image;
            }
            $data->update([
                'credentials'   => $credentials_array,
                'image'         => $image,
                'env'         => $request->env
            ]);
        }
        
        return redirect()->route('admin.send.money.gateway.index')->with(['success' => ['Send Money Gateway Updated Successfully.']]);
    }
    /**
     * Method for status update for Outside wallet
     * @param string
     * @param \Illuminate\Http\Request $request
     */
    public function statusUpdate(Request $request) {
        $validator = Validator::make($request->all(),[
            'data_target'       => 'required|numeric|exists:send_money_gateways,id',
            'status'            => 'required|boolean',
        ]);

        if($validator->fails()) {
            $errors = ['error' => $validator->errors() ];
            return Response::error($errors);
        }

        $validated = $validator->validate();


        $send_money = SendMoneyGateway::find($validated['data_target']);

        try{
            $send_money->update([
                'status'        => ($validated['status']) ? false : true,
            ]);
        }catch(Exception $e) {
            $errors = ['error' => ['Something went wrong! Please try again.'] ];
            return Response::error($errors,null,500);
        }

        $success = ['success' => ['Send Money Gateway status updated successfully!']];
        return Response::success($success);
    }

}
