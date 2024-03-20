<?php

namespace Database\Seeders\Update;

use App\Constants\ModuleSetting;
use App\Models\Admin\ModuleSetting as AdminModuleSetting;
use Illuminate\Database\Seeder;

class ModuleSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(!AdminModuleSetting::where('slug','agent-remittance-money')->exists()){
            //make module for user
            $data = [
                ModuleSetting::AGENTMONEYOUT            => 'Money Out',
            ];
            $create = [];
            foreach($data as $slug => $item) {
                $create[] = [
                    'admin_id'          => 1,
                    'slug'              => $slug,
                    'user_type'         => "USER",
                    'status'            => true,
                    'created_at'        => now(),
                ];
            }
            AdminModuleSetting::insert($create);

            //make module for agent
            $data = [
                ModuleSetting::AGENT_RECEIVE_MONEY                  => 'Agent Receive Money',
                ModuleSetting::AGENT_ADD_MONEY                      => 'Agent Add Money',
                ModuleSetting::AGENT_WITHDRAW_MONEY                 => 'Agent Withdraw  Money',
                ModuleSetting::AGENT_TRANSFER_MONEY                 => 'Agent Transfer Money',
                ModuleSetting::AGENT_MONEY_IN                       => 'Agent Money In',
                ModuleSetting::AGENT_BILL_PAY                       => 'Agent Bill Pay',
                ModuleSetting::AGENT_MOBILE_TOPUP                   => 'Agent Mobile Topup',
                ModuleSetting::AGENT_REMITTANCE_MONEY               => 'Agent Remittance Money',

            ];
            $create = [];
            foreach($data as $slug => $item) {
                $create[] = [
                    'admin_id'          => 1,
                    'slug'              => $slug,
                    'user_type'         => "AGENT",
                    'status'            => true,
                    'created_at'        => now(),
                ];
            }
            AdminModuleSetting::insert($create);
        }
    }
}
