<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\SendMoneyGateway;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SendMoneyGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $send_money_gateway = array(
            array('admin_id' => '1','slug' => 'google-pay','name' => 'Google Pay','image' => 'seeder/google-pay.png','credentials' => '{"gateway":"stripe","stripe_version":"2018-10-31","stripe_publishable_key":"pk_test_51NECrlJXLo7QTdMco2E4YxHSeoBnDvKmmi0CZl3hxjGgH1JwgcLVUF3ZR0yFraoRgT7hf0LtOReFADhShAZqTNuB003PnBSlGP","merchant_id":"BCR2DN4TXWR5LIAH","merchant_name":"AppDevs","mode":"TEST"}','status' => true,'created_at' => '2024-02-07 15:05:05','updated_at' => '2024-02-07 15:05:05'),
            array('admin_id' => '1','slug' => 'paypal','name' => 'Paypal','image' => 'seeder/paypal.webp','credentials' => '{"secret_id":"EOmsQW73ja4jFXUIpkeTuKj5qLcqiXRCPZMPx-2UNzNy729C5lzN8cYIdZRfHIx7xPVh9cyaeByefXJL","client_id":"AZhkSGtOqSoGsixors18c5UDkmHD53TzNogt2ksVfxqDeu1RzqdjMClVv8VGarayaAH1exMK0JHMjE8v"}','status' => true,'created_at' => '2024-02-07 15:05:05','updated_at' => '2024-02-07 15:05:05')
        );
        SendMoneyGateway::insert($send_money_gateway);
    }
}
