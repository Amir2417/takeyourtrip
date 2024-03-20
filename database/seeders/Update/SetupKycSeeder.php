<?php

namespace Database\Seeders\Update;

use Illuminate\Database\Seeder;
use App\Models\Admin\SetupKyc;


class SetupKycSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $setup_kycs = array(
            array(
                'slug' => 'agent','user_type' => 'AGENT','fields' => '[{"type":"file","label":"Id Back Part","name":"id_back_part","required":true,"validation":{"max":"10","mimes":["jpg","png","svg","webp"],"min":0,"options":[],"required":true}},{"type":"file","label":"Id Front Part","name":"id_front_part","required":true,"validation":{"max":"10","mimes":["jpg","png","svg","webp"],"min":0,"options":[],"required":true}}]','status' => '1','last_edit_by' => '1','created_at' => now(),'updated_at' => now())
          );
         SetupKyc::insert($setup_kycs);
    }
}
