<?php

namespace App\Http\Controllers\Api\User;

use DB;
use Illuminate\Http\Request;
use App\Http\Helpers\Api\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB as FacadesDB;

class ReceiveMoneyController extends Controller
{
    public function index() {
        $user = auth()->user();
        $user->createQr();
        $userQrCode = $user->qrCode()->first();
        $uniqueCode = $userQrCode->qr_code??'';
        $web_link   = route('send.money.index');
        $data = [
            'uniqueCode' => @$uniqueCode,
            'web_link' => $web_link,
        ];
        $qrCode = generateQr($web_link);
        $result = [
            'uniqueCode' => @$uniqueCode,
            'web_link'   => $web_link,
            'qrCode'     => $qrCode
        ];
        

        $message = ['success' => [__('Receive Money')]];
        return Helpers::success($result, $message);

    }
}
