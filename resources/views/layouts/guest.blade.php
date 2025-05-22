<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="register-container">

    <div class="register-left">
        <div class="nav">
            <a href="/login" class="logo"><img src={{ asset("images/police_logo.png")}} alt="Logo"></a>
        </div>
        <h1>Nigeria Police Force</h1>
        <h4>Police Digital Receipt (PDR)</h4>
    </div>

    <div class="register-right">
        {{ $slot }}
    </div>
</body>

</html>