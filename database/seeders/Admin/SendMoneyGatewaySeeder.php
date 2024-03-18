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
            array('admin_id' => '1','slug' => 'google-pay','name' => 'Google Pay','image' => 'seeder/google-pay.png','credentials' => '{"gateway":"stripe","stripe_version":"2018-10-31","stripe_publishable_key":"pk_test_51NECrlJXLo7QTdMco2E4YxHSeoBnDvKmmi0CZl3hxjGgH1JwgcLVUF3ZR0yFraoRgT7hf0LtOReFADhShAZqTNuB003PnBSlGP","stripe_secret_key":"sk_test_51Ohnd9BiPRG4Rx6BCNFAnK5JaXlXtIFyQlI1QVzNoqwOxuSHwJTcoENDUcD8dVw4R3w4eDRV1Q13Mn7orbJZBu2S00BPQ0pCpD","merchant_id":"5341013666","merchant_name":"DCode"}','env'=>'TEST','status' => true,'created_at' => '2024-02-07 15:05:05','updated_at' => '2024-02-07 15:05:05'),
            array('admin_id' => '1','slug' => 'paypal','name' => 'Paypal','image' => 'seeder/paypal.webp','credentials' => '[{"label":"Secret Id","placeholder":"Enter Secret Id","name":"secret-id","value":"EOmsQW73ja4jFXUIpkeTuKj5qLcqiXRCPZMPx-2UNzNy729C5lzN8cYIdZRfHIx7xPVh9cyaeByefXJL"},{"label":"Client Id","placeholder":"Enter Client Id","name":"client-id","value":"AZhkSGtOqSoGsixors18c5UDkmHD53TzNogt2ksVfxqDeu1RzqdjMClVv8VGarayaAH1exMK0JHMjE8v"}]','env'=>'SANDBOX','status' => true,'created_at' => '2024-02-07 15:05:05','updated_at' => '2024-02-07 15:05:05'),
            array('admin_id' => '1','slug' => 'apple-pay','name' => 'Apple Pay','image' => 'seeder/apple-pay.webp','credentials' => '[{"label":"Secret Id","placeholder":"Enter Secret Id","name":"secret-id","value":"EOmsQW73ja4jFXUIpkeTuKj5qLcqiXRCPZMPx-2UNzNy729C5lzN8cYIdZRfHIx7xPVh9cyaeByefXJL"},{"label":"Client Id","placeholder":"Enter Client Id","name":"client-id","value":"AZhkSGtOqSoGsixors18c5UDkmHD53TzNogt2ksVfxqDeu1RzqdjMClVv8VGarayaAH1exMK0JHMjE8v"}]','env'=>'SANDBOX','status' => true,'created_at' => '2024-02-07 15:05:05','updated_at' => '2024-02-07 15:05:05'),
        );
        SendMoneyGateway::insert($send_money_gateway);
    }
}
