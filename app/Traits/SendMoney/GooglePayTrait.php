<?php
namespace App\Traits\SendMoney;

trait GooglePayTrait{
    
    public function googlePayInit($output){
        
        return view('payment-gateway.google-pay',compact('output'));
    }
}