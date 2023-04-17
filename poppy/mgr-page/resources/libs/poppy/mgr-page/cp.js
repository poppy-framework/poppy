/**
 * 后台控制面板
 * @author     Mark <zhaody901@126.com>
 * @copyright  Copyright (c) 2018 Sour Lemon Team
 */
(function () {

    $(function () {

        var $body = $('body');

        // backend nav
        var $showCtr = $('#show-slide_out');
        var $hideCtr = $('#hide-slide_out');
        var $ele = $('#slide-out');
        $showCtr.on('click', function () {
            $ele.css({
                transform: 'translateX(0)',
                transition: 'transform 0.5s ease-in-out'
            }).animate();
            $body.addClass('fixed-sn-force');
            $hideCtr.show();
            $showCtr.hide();
        });
        $hideCtr.on('click', function () {
            $ele.css({
                transform: 'translateX(-105%)',
                transition: 'transform 0.5s ease-in-out'

            }).animate();
            $body.removeClass('fixed-sn-force');
            $hideCtr.hide();
            $showCtr.show();
        });


        var $showQuickButton = $('#show-quick-button');
        var $quickButton = $('#quick-button');
        $showQuickButton.on('click', function () {
            if ($quickButton.hasClass('open')) {
                $quickButton.removeClass('open')
            } else {
                $quickButton.addClass('open')
            }
            $quickButton.css({
                transform: 'translateX(0)',
                transition: 'transform 0.5s ease-in-out'
            }).animate();
        });

        if ($.support.pjax) {
            $(document).on('submit', 'form[data-pjax]', function (event) {
                var container = $(this).attr('pjax-ctr');
                var timeout = $(this).attr('pjax-timeout');
                if (!container) {
                    container = '#pjax-container'
                }
                $.pjax.submit(event, container, {
                    fragment: container,
                    timeout: timeout ? timeout: 3000
                });
                event.preventDefault();
            });
            $(document).on('click', 'a[data-pjax], [data-pjax] a:not(.J_ignore)', function (event) {
                let container = $(this).closest('[pjax-ctr]');
                let ctr = container.attr('pjax-ctr');
                if (typeof ctr === 'undefined') {
                    ctr = '#pjax-container'
                }

                if ($(ctr).length === 0) {
                    Util.splash({
                        status: 1,
                        message: '你的页面中没有 Pjax 容器' + ctr + ',请添加, 否则无法进行页面请求'
                    });
                    return false;
                }

                $.pjax.click(event, {
                    container: ctr,
                    fragment: ctr,
                    timeout: 3000
                })
            });
            $(document).on('pjax:send', function () {
                layer.load(3)
            });
            $(document).on('pjax:complete', function () {
                layer.closeAll();
                layui.form.render();
            });
            $(document).on('pjax:error', function (event, xhr) {
                if (xhr.statusText === 'timeout') {
                    setTimeout(function () {
                        layer.msg('请求超时');
                    }, 100)
                    event.preventDefault();
                    return false;
                }
                setTimeout(function () {
                    layer.msg(xhr.responseText);
                }, 100)
                event.preventDefault();
                return false;
            });
        }
    })
})();