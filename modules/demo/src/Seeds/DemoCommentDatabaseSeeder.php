<?php

namespace Demo\Seeds;

use Demo\Models\DemoComment;
use Illuminate\Database\Seeder;

class DemoCommentDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(DemoComment::class, 500)->create();
    }
}
