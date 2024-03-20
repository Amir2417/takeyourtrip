<?php

namespace Database\Seeders\Update;

use Illuminate\Database\Seeder;
use App\Models\Admin\PaymentGateway;
use App\Models\Admin\PaymentGatewayCurrency;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //update razorpay payment gateway
        $razor_pay_gateway = PaymentGateway::where('alias','razorpay')->first();
        $razor_pay_gateway->credentials =[
            ["label" => "Key ID", "placeholder" => "Enter Key ID", "name" => "key-id", "value" => "rzp_test_voV4gKUbSxoQez"],
            ["label" => "Secret Key", "placeholder" => "Enter Secret Key", "name" => "secret-key", "value" => "cJltc1jy6evA4Vvh9lTO7SWr"]
        ];
        $razor_pay_gateway->supported_currencies = ["USD","EUR","GBP","SGD","AED","AUD","CAD","CNY","SEK","NZD","MXN","BDT","EGP","HKD","INR","LBP","LKR","MAD","MYR","NGN","NPR","PHP","PKR","QAR","SAR","UZS","GHS"];
        $razor_pay_gateway->save();

         //perfect_money
         $perfect_money = PaymentGateway::latest()->first();
         if(!PaymentGateway::where('alias','perfect-money')->exists()){
            $payment_gateways_id = $perfect_money->id+2;
            $payment_gateways_code = PaymentGateway::max('code')+5;

            $payment_gateways = array(
                array('id' => $payment_gateways_id,'slug' => 'add-money','code' => $payment_gateways_code,'type' => 'AUTOMATIC','name' => 'Perfect Money','title' => 'Perfect Money Gateway','alias' => 'perfect-money','image' => 'seeder/pmoney.webp','credentials' => '[{"label":"Alternate Passphrase","placeholder":"Enter Alternate Passphrase","name":"alternate-passphrase","value":"t0d2nbK2ZA92fRTnIFsMTWsHT"},{"label":"EUR Account","placeholder":"Enter EUR Account","name":"eur-account","value":"E39620511"},{"label":"USD Account","placeholder":"Enter USD Account","name":"usd-account","value":"U39903302"}]','supported_currencies' => '["USD","EUR"]','crypto' => '0','desc' => NULL,'input_fields' => NULL,'env' => 'SANDBOX','status' => '1','last_edit_by' => '1','created_at' => now(),'updated_at' => now())
            );
            PaymentGateway::insert($payment_gateways);
            $payment_gateway_currencies = array(
                array('payment_gateway_id' => $payment_gateways_id,'name' => 'Perfect Money EUR','alias' => 'perfect-money-eur-automatic','currency_code' => 'EUR','currency_symbol' => '€','image' => 'seeder/pmoney.webp','min_limit' => '1.00000000','max_limit' => '1000.00000000','percent_charge' => '1.00000000','fixed_charge' => '1.00000000','rate' => '0.92000000','created_at' => now(),'updated_at' => now()),
                array('payment_gateway_id' => $payment_gateways_id,'name' => 'Perfect Money USD','alias' => 'perfect-money-usd-automatic','currency_code' => 'USD','currency_symbol' => '$','image' => 'seeder/pmoney.webp','min_limit' => '1.00000000','max_limit' => '1000.00000000','percent_charge' => '1.00000000','fixed_charge' => '1.00000000','rate' => '1.00000000','created_at' => now(),'updated_at' => now())
            );
            PaymentGatewayCurrency::insert($payment_gateway_currencies);
        }


    }
}
