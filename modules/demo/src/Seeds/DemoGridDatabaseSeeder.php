<?php

declare(strict_types = 1);

namespace Demo\Seeds;

use Demo\Models\DemoGrid;
use Illuminate\Database\Seeder;

class DemoGridDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(DemoGrid::class, 50)->create();
    }
}
