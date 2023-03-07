let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Web Url : https://laravel-mix.com
 |--------------------------------------------------------------------------
 */
mix
    .browserSync({
        // 这里替换地址
        proxy: 'http://poppy-v4.duoli.com/',
        files: [
            "public/assets/**/*.js",
            "public/assets/**/*.css",
            "modules/**/resources/views/**/*.blade.php",
            "poppy/**/resources/views/**/*.blade.php",
        ]
    })
    .options({
        processCssUrls: false
    })
    .disableNotifications()
    /* 开发使用[便于文件加载]
     * ---------------------------------------- */
    // develop
    .less(
        'poppy/mgr-page/resources/style/mgr-page.less',
        'assets/_temp/css/_mgr-page.css'
    )
    .sass(
        'poppy/mgr-page/resources/style/bootstrap.scss',
        'assets/_temp/css/_bootstrap.css'
    )
    .styles([
        'public/assets/_temp/css/_mgr-page.css',
        'public/assets/_temp/css/_bootstrap.css',
    ], 'public/assets/libs/boot/style.css')
    .scripts([
            'poppy/mgr-page/resources/libs/jquery/2.2.4/jquery.min.js',
            'poppy/mgr-page/resources/libs/jquery/form/jquery.form.js',
            'poppy/mgr-page/resources/libs/jquery/pjax/jquery.pjax.js',
            'poppy/mgr-page/resources/libs/jquery/poshytip/jquery.poshytip.js',
            'poppy/mgr-page/resources/libs/jquery/validation/jquery.validation.js',
            'poppy/mgr-page/resources/libs/jquery/drag-arrange/drag-arrange.js',
            'poppy/mgr-page/resources/libs/tom-select/tom-select.complete.min.js',
            'poppy/mgr-page/resources/libs/clipboard/clipboard.min.js',
            'poppy/mgr-page/resources/libs/sortable/sortable.js',
            'poppy/mgr-page/resources/libs/lodash/lodash.min.js',
            'poppy/mgr-page/resources/libs/easytimer/easytimer.min.js',
            // bootstrap
            'poppy/mgr-page/resources/libs/bootstrap@5.2/bootstrap.bundle.min.js',

            // mgr-page
            'poppy/mgr-page/resources/libs/poppy/util.js',
            'poppy/mgr-page/resources/libs/poppy/cp.js',
            'poppy/mgr-page/resources/libs/poppy/mgr-page/cp.js'
        ],
        'public/assets/libs/boot/app.min.js'
    )
    .copyDirectory('poppy/mgr-page/resources/font/', 'public/assets/font/')
    .copyDirectory('poppy/mgr-page/resources/images/', 'public/assets/images/')
    .copyDirectory('poppy/mgr-page/resources/libs/jquery/', 'public/assets/libs/jquery/')
    .copyDirectory('poppy/mgr-page/resources/libs/easy-web/', 'public/assets/libs/easy-web')
    .copyDirectory('poppy/mgr-page/resources/libs/layui/', 'public/assets/libs/layui')
    .copyDirectory('poppy/mgr-page/resources/libs/vue/', 'public/assets/libs/vue');