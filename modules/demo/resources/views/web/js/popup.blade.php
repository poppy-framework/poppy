@extends('py-mgr-page::backend.tpl.dialog')
@section('backend-main')
    {!! mgr_actions(function (Poppy\MgrPage\Classes\Operations $operations){
        $operations->request('_top_reload', route_url('demo:web.js.popup', null, ['type'=>'_top_reload']));
        $operations->request('_parent_reload', route_url('demo:web.js.popup', null, ['type'=>'_parent_reload']));
        $operations->request('_reload', route_url('demo:web.js.popup', null, ['type'=>'_reload']));

        $operations->request('_top_location', route_url('demo:web.js.popup', null, ['type'=>'_top_location']))->plain('black');
        $operations->request('_parent_location', route_url('demo:web.js.popup', null, ['type'=>'_parent_location']))->plain('black');
        $operations->request('_location', route_url('demo:web.js.popup', null, ['type'=>'_location']))->plain('black');
        $operations->iframe('弹窗', route('demo:web.js.popup'));
    }) !!}
@endsection