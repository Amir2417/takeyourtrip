<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReceiveMoneyController extends Controller
{
    public function index() {
        $page_title = __("Receive Money");
        $user = auth()->user();
      
        $user->createQr();
        $userQrCode = $user->qrCode()->first();
        $uniqueCode = $userQrCode->qr_code??'';
        $web_link   = route('send.money.index');
        $data       = [
            'web_link' => $web_link,
            'email' => $uniqueCode,
        ];
        $qrCode = generateQr(json_encode($data));
       
        return view('user.sections.receive-money.index',compact("page_title","uniqueCode","qrCode",'user'));
    }

}
