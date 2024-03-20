<?php

namespace Database\Seeders\Update;

use App\Models\Admin\AppSettings;
use Illuminate\Database\Seeder;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $app_settings = array(
            'version' => '4.2.0',
            'agent_version' => '4.2.0',
            'merchant_version' => '4.2.0',
            'agent_splash_screen_image' => 'seeder/agent/splash_screen.webp',
            'merchant_splash_screen_image' => 'seeder/merchant/splash_screen.webp',
          );
        $appSettings = AppSettings::first();
        $appSettings->update($app_settings);
    }
}
