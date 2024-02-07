<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\SendMoneyGateway;
use Illuminate\Http\Request;

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
        
        return view('admin.sections.send-money.edit',compact(
            'page_title',
            'data'
        ));
    }

}
