<?php

use Demo\Models\DemoGrid;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Carbon;
use Poppy\Area\Models\SysArea;
use Faker\Generator as Faker;
use Poppy\System\Models\PamAccount;

// php artisan poppy:seed module.demo --class='\Demo\Database\Seeds\DemoGripDatabaseSeeder'

/** @var Factory $factory */
$factory->define(DemoGrid::class, function (Faker $faker) {
    return [
        /* 日期
         * ---------------------------------------- */
        'birth_date' => Carbon::now()->addDays(random_int(-1000, 1000))->toDate(),
        'post_at'    => Carbon::now()->addDays(random_int(-30, 30))->toDateTimeString(),

        /* 内容
         * ---------------------------------------- */
        'title'      => $faker->sentence(20),
        'content'    => $faker->sentence(1000),

        /* Cast
        * ---------------------------------------- */
        'setting'    => json_encode([
            'is_open' => $faker->randomElement(['Y', 'N']),
            'blog'    => $faker->randomElement(['weibo', 'twitter', 'fb', 'github']),
        ], JSON_THROW_ON_ERROR),


        /* 用户
         * ---------------------------------------- */
        'email'      => $faker->email,
        'truename'   => $faker->lastName . $faker->userName,
        'age'        => $faker->randomElement(range(30, 80)),
        'score'      => $faker->randomElement(range(1, 100)),

        /* 自定义 排序 | 样式
         * ---------------------------------------- */
        'sort'       => $faker->randomElement(range(1, 1000)),

        /* 状态
         * ---------------------------------------- */
        'status'     => $faker->randomElement(range(1, 5)),
        'progress'   => random_int(1, 100),

        'is_enable' => $faker->randomElement(range(1, 0)),

        /* File | Image | Link | Images
        * ---------------------------------------- */
        'file'      => $faker->randomElement([
            'https://test-oss.iliexiang.com/_res/pdf/2022-damo.pdf',
            'https://test-oss.iliexiang.com/_res/rpm/percona-xtrabackup-24-2.4.21-1.el7.x86_64.rpm',
            'https://test-oss.iliexiang.com/_res/video/h-918k.mp4',
        ]),
        'files'     => implode(',', [
            'https://test-oss.iliexiang.com/_res/pdf/2022-damo.pdf',
            'https://test-oss.iliexiang.com/_res/rpm/percona-xtrabackup-24-2.4.21-1.el7.x86_64.rpm',
        ]),
        'pdf'       => $faker->randomElement([
            'https://test-oss.iliexiang.com/_res/pdf/2022-damo.pdf',
        ]),
        'video'     => $faker->randomElement([
            'https://test-oss.iliexiang.com/_res/video/h-918k.mp4',
            'https://test-oss.iliexiang.com/_res/video/v-8m.mp4',
            'https://test-oss.iliexiang.com/_res/video/h-15m-actor.mp4',
        ]),
        'audio'     => $faker->randomElement([
            'https://test-oss.iliexiang.com/_res/audio/actor.mp3',
        ]),

        'image'      => $faker->randomElement([
            $faker->imageUrl(),
            'https://test-oss.iliexiang.com/_res/avatar/07.jpg',
            'https://test-oss.iliexiang.com/_res/avatar/10.jpg',
        ]),
        'images'     => implode(',', [
            $faker->imageUrl(),
            'https://test-oss.iliexiang.com/_res/avatar/07.jpg',
            'https://test-oss.iliexiang.com/_res/avatar/10.jpg',
        ])
        ,
        'link'       => $faker->url,


        /* 连表
         * ---------------------------------------- */
        'account_id' => PamAccount::inRandomOrder()->value('id'),
        'area_id'    => SysArea::inRandomOrder()->value('id'),
    ];
});
