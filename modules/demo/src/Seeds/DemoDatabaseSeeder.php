<?php

namespace Demo\Seeds;

use Illuminate\Database\Seeder;

class DemoDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            DemoGridDatabaseSeeder::class,
        ]);
    }
}
