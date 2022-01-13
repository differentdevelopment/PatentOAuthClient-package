<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ config('backpack.base.html_direction') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow">
    <title>Patent Porta</title>
    <link rel="stylesheet" type="text/css" href="/packages/backpack/base/css/bundle.css">
    <link rel="stylesheet" type="text/css" href="/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;400;600&display=swap" rel="stylesheet">
    <script>
        window._locale = '{{ app()->getLocale() }}';
        window._translations = {!! cache('translations') !!};
    </script>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
    </style>
</head>
<body>
    <a class="btn btn-primary btn-lg" href="{{ route("patent-oauth-client.redirect") }}">
        <b>
            @lang("backpack::base.login")
        </b>
    </a>    
</body>
</html>
