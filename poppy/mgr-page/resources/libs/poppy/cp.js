/**
 * Fe控制
 * @author     Mark <zhaody901@126.com>
 * @copyright  Copyright (c) 2018 Sour Lemon Team
 */
(function () {

    $(function () {

        let $body = $('body');

        $body.on('mouseenter', '.J_tooltip', function () {
            let title = $(this).attr('title');
            layer.tips(title, this, {
                tips: 1
            });
        })
        $body.on('mouseleave', '.J_tooltip', function () {
            let index = layer.tips();
            layer.close(index)
        })

        /* 粘贴板
         * ---------------------------------------- */
        if (typeof ClipboardJS !== 'undefined') {
            let clipboard = new ClipboardJS('.J_copy', {
                text: function (trigger) {
                    return trigger.getAttribute('data-text');
                }
            });
            clipboard.on('success', function (e) {
                Util.splash({
                    status: 0,
                    message: '已复制'
                })
            });
        }


        // 对话框, 用于显示信息提示
        // 不能用于生成图片组件
        // @see http://stackoverflow.com/questions/12271105/swfupload-startupload-fails-if-not-called-within-the-file-dialog-complete-hand
        $body.on('click', '.J_dialog', function (e) {
            // confirm
            let tip = $(this).attr('data-tip');
            let element = $(this).attr('data-element');
            let title = $(this).attr('data-title') ? $(this).attr('data-title') : $(this).html();
            let width = parseInt($(this).attr('data-width')) ? parseInt($(this).attr('data-width')) : 700;
            let height = parseInt($(this).attr('data-height')) ? parseInt($(this).attr('data-height')) : '';
            let area = height ? [width + 'px', height + 'px'] : width + 'px';

            // 获取到元素的 html, 并且存入到当前元素
            if (element) {
                tip = $(element).html();
                $(this).attr('data-tip', tip);
            }

            const regex = /<script/gmi
            if (regex.test(tip)) {
                layer.msg('潜在危险 html, 联系管理员排查安全隐患');
                return;
            }


            // open with layer
            layer.open({
                // type   : 1,
                title: title,
                content: tip,
                area: area,
                btn: [],
                shadeClose: true
            });
            e.preventDefault();
        });

        // 弹出 iframe url
        $body.on('click', '.J_iframe', function (e) {
            let $this = $(this);
            // confirm
            let href = $(this).attr('href');
            if (!href) {
                href = $(this).attr('data-href');
            }
            let title = $(this).attr('data-title') ? $(this).attr('data-title') : '';
            if (!title) {
                title = $(this).attr('title') ? $(this).attr('title') : '';
            }
            if (!title) {
                title = $(this).attr('data-original-title') ? $(this).attr('data-original-title') : $(this).html();
            }

            let windowWidth = $(window).width();
            let windowHeight = $(window).height();

            let width = parseInt($(this).attr('data-width')) ? parseInt($(this).attr('data-width')) : '500';
            let height = parseInt($(this).attr('data-height')) ? parseInt($(this).attr('data-height')) : '500';
            if (width > windowWidth) {
                width = windowWidth * 0.9;
            }
            if (height > windowHeight) {
                height = windowHeight * 0.9;
            }
            let shade_close = $(this).attr('data-shade_close') !== 'false';
            let append = $this.attr('data-append');
            let data = Util.appendToObj(append);
            data._iframe = 'poppy';
            href = Util.objToUrl(data, href);
            layer.open({
                type: 2,
                content: href,
                area: [width + 'px', height + 'px'],
                title: title,
                shadeClose: shade_close
            });
            e.preventDefault();
            return false;
        });

        // 全选 start
        $body.on('click change', '.J_check_all', function () {
            if (this.checked) {
                $(".J_check_item").prop('checked', true)
            } else {
                $(".J_check_item").prop('checked', false)
            }
        });

        // 确定 请求后台操作, POST 方法
        $body.on('click', '.J_request', function (e) {
            let $btn = $(this);
            Util.buttonInteraction($btn, 5);
            layer.load(3, {
                shade: [0.1, '#000000']
            });
            Util.requestEvent($(this), function (data) {
                Util.splash(data);
                Util.buttonInteraction($btn, data);
                layer.closeAll();
            });
            e.preventDefault();
        });


        // 图片预览
        $body.on('click', '.J_image_preview', function (e) {
            //loading层
            let _src = $(this).attr('data-src');
            if (!_src) {
                _src = $(this).attr('src');
            }
            if (!_src) {
                Util.splash({
                    status: 1,
                    message: '没有图像文件!'
                })
                return;
            }
            if (e.ctrlKey) {
                window.open($(this).attr('src'), '_blank')
            } else {
                if (!_src) {
                    Util.splash({
                        status: 1,
                        message: '没有图像文件!'
                    });
                    return false;
                }
                let _parents = $(this).attr('data-parents');
                let urls = [];
                if (_parents) {
                    $(this).parents(_parents).find('.J_image_preview').each(function (key, value) {
                        let src = $(value).attr('data-src') ? $(value).attr('data-src') : $(value).attr('src');
                        urls.push({
                            alt: '',
                            pid: key,
                            src: src,
                            thumb: src
                        })
                    })
                } else {
                    urls.push({
                        "alt": "",
                        "pid": 2, //图片id
                        "src": _src, //原图地址
                        "thumb": _src //缩略图地址
                    })
                }

                let index = _.findIndex(urls, function (item) {
                    return item.src === _src;
                })

                layer.photos({
                    shade: [0.6, '#393d49'],
                    photos: {
                        "title": "预览", //相册标题
                        "id": 1, //相册id
                        "start": index, //初始显示的图片序号，默认0
                        "data": urls
                    },
                    anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
                });
            }
            e.stopPropagation();
        });

        // reload
        $body.on('click', '.J_reload', function () {
            window.location.reload();
        });

        // print
        $body.on('click', '.J_print', function () {
            window.print();
        });

        // load view
        $body.on('click', '.J_load_view', function (e) {
            let href = $(this).attr('href');
            let title = $(this).attr('title');
            top.layui.index.loadView({
                menuPath: href,
                menuName: title ? title : '无标题',
                menuId: href
            })
            e.preventDefault();
        });

        /**
         * 把当前表单的数据临时提交到指定的地址
         * .J_submit     用法
         * data-url     : 设置本表单请求的URL
         * data-ajax    : true|false  设置是否进行ajax 请求
         * data-confirm : 确认操作提交的提示信息
         * data-method  : 提交方式
         */
        $body.on('click', '.J_submit', function (e) {
            let request_url = $(this).attr('data-url');
            let $form = $(this).parents('form');
            if (!$form.length) {
                Util.splash({
                    status: 'error',
                    msg: '您不在表单范围内， 请添加到表单范围内'
                });
                return false;
            }

            let old_url = $form.attr('action');
            if (!request_url) {
                request_url = old_url;
            }
            // confirm
            let str_confirm = $(this).attr('data-confirm');
            if (str_confirm === 'true') {
                str_confirm = '您确定删除此条目 ?';
            }
            if (str_confirm && !confirm(str_confirm)) return false;

            let data_ajax = $(this).attr('data-ajax');
            let data_method = $(this).attr('data-method') ? $(this).attr('data-method') : 'post';

            $form.attr('action', request_url);
            $form.attr('method', data_method);

            // 显示 layer 层
            let index = layer.load(0, { shade: [0.1, '#000000'] });
            let conf;
            if ((data_ajax === 'false')) {
                conf = Util.validateConfig({}, false);
                $form.validate(conf);
                $form.submit();
            } else {
                $form.validate(Util.validateConfig({}, true))
                let $btn = $(this);
                Util.buttonInteraction($btn, 5);
                $form.ajaxSubmit({
                    success: function (data) {
                        layer.close(index);
                        Util.splash(data);
                        Util.buttonInteraction($btn, data)
                    }
                });
            }
            // 还原
            $form.attr('action', old_url);
            e.preventDefault();
        });

        /**
         * 表单的验证提交
         */
        $body.on('click', '.J_validate', function (element) {
            let $form = $(this).parents('form');
            if (!$form.length) {
                Util.splash({
                    status: 1,
                    message: '没有 form 表单'
                });
                return;
            }

            // confirm
            let data_ajax = $form.attr('data-ajax');
            let conf;
            if ((data_ajax === 'false')) {
                conf = Util.validateConfig({}, false);
                $form.validate(conf);
                // ajax 禁用掉默认
                $(element).on('click', function (e) {
                    e.preventDefault();
                })
            } else {
                conf = Util.validateConfig({}, true);
                $form.validate(conf);
            }
        });


        /**
         * 禁用按钮
         */
        $body.on('click', '.J_delay', function (e) {
            let $this = $(this);
            let tag = $this.prop("tagName").toLowerCase();
            if (tag === 'a' && !$this.data('delay')) {
                let _href = $(this).attr('href');
                $this.attr('href', 'javascript:void(0)').addClass('disabled').attr('data-delay', 'ing');
                setTimeout(function () {
                    $this.attr('href', _href).removeClass('disabled').removeAttr('data-delay');
                }, 3000);
                e.preventDefault();
            }
            if (tag === 'button' && !$this.data('delay')) {
                $this.addClass('disabled');
                if ($(this).parents('form') && $this.prop('type') === 'submit') {
                    $(this).parents('form').submit(function () {
                        $this.prop('disabled', true);
                    });
                }
                setTimeout(function () {
                    $this.removeClass('disabled');
                    $this.prop('disabled', false);
                }, 3000)
            }

        });

        /**
         * 返回传输的内容, 并且将内容显示在弹窗中
         */
        $(".J_info").each(function () {
            let $this = $(this);
            let data_url = $this.attr("data-url");
            let layer_id = "";
            let index = '';
            let common_opt = {
                type: 1,
                area: ['400px', 'auto'],
                tips: [2, '#ffffff'],
                closeBtn: 0,
                shade: 0,
                shift: 5
            };
            $this.on("mouseover", function () {
                $.ajax({
                    type: 'get',
                    url: data_url,
                    data: {
                        _token: Util.csrfToken()
                    },
                    success: function (data) {
                        let com_content = data.content; //html内容
                        let com_opt = $.extend({}, common_opt, {
                            content: com_content,
                            success: function (layer_obj) {
                                layer_id = layer_obj.selector;
                            }
                        });
                        index = layer.open(com_opt);
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        alert(XMLHttpRequest.status);
                        alert(XMLHttpRequest.readyState);
                        alert(textStatus);
                    }
                })
            }).on("mouseout", function () {
                let count = 0;
                $(layer_id).on('mouseover', function () {
                    count = 1;
                }).on('mouseout', function () {
                    count = 0;
                });
                $this.on('mouseover', function () {
                    count = 1;
                });
                $body.on('mouseover', function () {
                    if (count == 3) {
                        clearInterval(t);
                    }
                });
                let t = setInterval(function () {
                    if (count == 0) {
                        layer.close(index);
                        count = 3;
                    }
                }, 150);
            })
        });
    })


    $('body').on('keydown', function (event) {
        if (event.keyCode === 27) { // esc
            layer.closeAll();
        }
    });
})();
