<li class="layui-nav-item search-main" lay-unselect id="search-main">
    <a href="#" v-on:click="switchQuick">
        <i class="layui-icon layui-icon-search"></i>
    </a>
    <div id="search-ctr" class="main-ctr">
        <input type="text" id="search-input">
        <div class="main-list">
            @if (isset($_menus))
                @foreach($_menus as $k_menu => $v_menu)
                    @foreach($v_menu['groups'] as $k_group => $v_group)
                        @foreach($v_group['children'] as $v_link)
                            @if (isset($v_link['url']) && $v_link['url'])
                                <div class="search" v-on:click="switchQuick">
                                    <span class="hide">{!! Poppy\MgrPage\Classes\SearchCache::py($v_link['title']) !!}</span>
                                    {!! mgr_menu_title($v_link) !!}
                                </div>
                            @endif
                            @if($v_link['children']??[])
                                @foreach ($v_link['children'] as $c_link)
                                    <div class="search" v-on:click="switchQuick">
                                        <span class="hide">{!! Poppy\MgrPage\Classes\SearchCache::py($c_link['title']) !!}</span>
                                        {!! mgr_menu_title($c_link) !!}
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    @endforeach
                @endforeach
            @endif
        </div>
    </div>
    <div class="main-bg" id="bg-ctr" v-on:click="switchQuick"></div>
</li>

<script>
$(function () {
    Util.holmes({
        input: '#search-input',
        find: '#search-main .search',
        placeholder: '<h5> No Search Result!</h5>'
    });
});
new Vue({
    el: '#search-main',
    data: {
        show: 'none'
    },
    methods: {
        switchQuick: function () {
            let $searchCtr = $('#search-ctr');
            let display = $searchCtr.css('display');
            if (display === 'none') {
                $searchCtr.css('display', 'block');
                $('#bg-ctr').css('display', 'block');
            } else {
                $searchCtr.css('display', 'none');
                $('#bg-ctr').css('display', 'none');
            }
            $('#search-input').focus();
        }
    }
});
</script>