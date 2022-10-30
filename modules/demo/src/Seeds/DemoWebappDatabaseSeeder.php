<?php

namespace Demo\Seeds;

use Demo\Models\DemoWebapp;
use Illuminate\Database\Seeder;

class DemoWebappDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(DemoWebapp::class, 500)->create();
    }
}
