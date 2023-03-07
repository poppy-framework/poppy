<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="keywords" content="@yield('keywords')">
    <meta name="description" content="@yield('description')">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta name="csrf-token" content="{!! csrf_token() !!}">
    @yield('head-meta')
    @yield('head-css')
    @yield('head-script')
    @yield('head-content')
</head>
<body class="@yield('body-class')" style="@yield('body-style')">

@yield('body-main')

</body>

@yield('footer-script')

</html>
