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
            array('admin_id' => '1','slug' => 'google-pay','name' => 'Google Pay','image' => 'seeder/google-pay.png','credentials' => '{"gateway":"stripe","stripe_version":"2018-10-31","stripe_publishable_key":"pk_test_51NECrlJXLo7QTdMco2E4YxHSeoBnDvKmmi0CZl3hxjGgH1JwgcLVUF3ZR0yFraoRgT7hf0LtOReFADhShAZqTNuB003PnBSlGP","merchant_id":"BCR2DN4TXWR5LIAH","merchant_name":"AppDevs"}','status' => true,'created_at' => '2024-02-07 15:05:05','updated_at' => '2024-02-07 15:05:05')
        );
        SendMoneyGateway::insert($send_money_gateway);
    }
}
