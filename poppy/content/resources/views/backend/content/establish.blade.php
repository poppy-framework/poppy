@extends('py-mgr-page::backend.tpl.default')
@section('backend-main')
    <div class="layui-card-header">
        文章{!! Route::input('id') ? '编辑' : '创建' !!}
    </div>
    <div class="layui-card-body">
        {!! Form::model($item ?? null, ['route' => [$_route, Route::input('id')], 'class' => 'layui-form']) !!}
        {!! Form::hidden('type', $type) !!}
        <div class="layui-form-item">
            {!! Form::label('title', '标题', ['class' => 'layui-form-label']) !!}
            <div class="layui-input-block">
                {!! app('poppy.mgr-page.form')->text('title', null, [
                    'class' => 'layui-input',
                    'id' => 'title'
                ]) !!}
            </div>
        </div>
        <div class="layui-form-item">
            {!! Form::label('title', '优化名称', ['class' => 'layui-form-label']) !!}
            <div class="layui-input-block">
                {!! app('poppy.mgr-page.form')->text('slug', null, [
                    'placeholder' => '使用字母, 数字, 中杠线来编写',
                    'class' => 'layui-input',
                    'id' => 'slug',
                ]) !!}
                <div class="layui-word-aux">slug 长度需要大于等于10</div>
            </div>
        </div>
        @if ($type)
            <div class="layui-form-item">
                {!! Form::label('title', '分类', ['class' => 'layui-form-label']) !!}
                <div class="layui-input-block">
                    {!!
                        app('poppy.mgr-page.form')
                        ->select('cat_id', Poppy\Category\Models\SysCategory::tree($type))
                    !!}
                </div>
            </div>
        @endif
        <div class="layui-form-item">
            {!! Form::label('title', '缩略图', ['class' => 'layui-form-label']) !!}
            <div class="layui-input-block">
                {!! app('poppy.mgr-page.form')->thumb('thumb') !!}
            </div>
        </div>
        <div class="layui-form-item">
            {!! Form::label('title', '内容', ['class' => 'layui-form-label']) !!}
            <div class="layui-input-block">
                {!! app('poppy.mgr-page.form')->editor('content') !!}
            </div>
        </div>
        <div class="layui-form-item">
            {!! Form::label('keyword', '关键词', ['class' => 'layui-form-label']) !!}
            <div class="layui-input-block">
                {!! app('poppy.mgr-page.form')->text('keyword', null, [
                     'class' => 'layui-input',
                    'id' => 'keyword'
                ]) !!}
            </div>
        </div>
        <div class="layui-form-item">
            {!! Form::label('description', '描述', ['class' => 'layui-form-label']) !!}
            <div class="layui-input-block">
                {!! app('poppy.mgr-page.form')->textarea('description', null, [
                    'class' => 'layui-textarea',
                    'rows' => 2,
                    'id' => 'description'
                ]) !!}
            </div>
        </div>
        <div class="layui-form-item">
            {!! Form::label('title', '作者', ['class' => 'layui-form-label']) !!}
            <div class="layui-input-block">
                {!! app('poppy.mgr-page.form')->text('author', null, [
                    'class' => 'layui-input'
                ]) !!}
            </div>
        </div>

        <div class="layui-form-item">
            {!! Form::label('title', '创作时间', ['class' => 'layui-form-label']) !!}
            <div class="layui-input-block">
                {!! app('poppy.mgr-page.form')->datetimePicker('create_at', $item['create_at'] ?? null) !!}
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                {!! Form::button('提交', ['class'=>'layui-btn J_submit', 'type' => 'submit']) !!}
            </div>
        </div>
        {!! Form::close() !!}
        <script>
        layui.use(['form', 'layer'], function () {
            $('#title').on('blur', function () {
                if ($('#slug').val()) {
                    return;
                }
                Util.makeRequest('{!! route('py-content:backend.content.pinyin') !!}', {
                    title: $('#title').val()
                }, function (data) {
                    $('#slug').val(_.get(data, 'data.pinyin'));
                })
            })
        })
        </script>
    </div>
@endsection
