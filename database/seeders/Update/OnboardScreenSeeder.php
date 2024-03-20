<?php

namespace Database\Seeders\Update;

use App\Constants\GlobalConst;
use App\Models\Admin\AppOnboardScreens;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OnboardScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $app_onboard_screens = array(
            array('type'=>GlobalConst::AGENT,'title' => 'Get Profit in Every Transaction','sub_title' => 'Start your agency business with QRPay and get the best profit on every transaction','image' => 'seeder/agent/onboard.png','status' => '1','last_edit_by' => '1','created_at' => now(),'updated_at' => now()),

            array('type'=>GlobalConst::MERCHANT,'title' => 'Fast Payment Receiver','sub_title' => 'Easy way to collect payment. fast, secure and reliable platform.','image' => 'seeder/merchant/onboard.png','status' => '1','last_edit_by' => '1','created_at' => now(),'updated_at' => now())

          );
        AppOnboardScreens::insert($app_onboard_screens);
    }
}
