@extends('py-mgr-page::develop.tpl.default')
@section('develop-main')
    @include('py-mgr-page::develop.tpl._header')
    {!! phpinfo() !!}
@endsection