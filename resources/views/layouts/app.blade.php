<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <title>{{ @Config::get('name', 'Perfect Order'); }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--
            Start: Favicon Generator Settings
            Reference: https://realfavicongenerator.net/favicon_result?file_id=p1hk8amvff1cpt16ur1i4r87k10796
        -->
        <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
        <link rel="manifest" href="/favicon/site.webmanifest">
        <link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#7a7a7a">
        <meta name="msapplication-TileColor" content="#00aba9">
        <meta name="theme-color" content="#ffffff">
        <!-- End: Favicon Generator Settings  -->

        @vite('resources/css/app.css')

    </head>
    <body>
        @yield('content')
    </body>
</html>
