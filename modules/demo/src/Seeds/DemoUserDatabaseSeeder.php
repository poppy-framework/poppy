<?php

namespace Demo\Seeds;

use Demo\Models\DemoUser;
use Illuminate\Database\Seeder;

class DemoUserDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(DemoUser::class, 50)->create();
    }
}
