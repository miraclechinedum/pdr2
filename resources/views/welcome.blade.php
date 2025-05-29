<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bai+Jamjuree:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;1,200;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="">
    <header class="not-has-[nav]:hidden">
        <div class="top-header">
            <a href="/" class="logo">
                <img src={{ asset("images/police_logo.png")}} alt="">
            </a>
            @if (Route::has('login'))
            <nav class="flex items-center justify-end gap-4">
                @auth
                <a href="{{ url('/dashboard') }}" class="">
                    Dashboard
                </a>
                @else
                <a href="{{ route('login') }}" class="">
                    Log in
                </a>

                @if (Route::has('register'))
                <a href="{{ route('register') }}"
                    class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                    Register
                </a>
                @endif
                @endauth
            </nav>
            @endif
        </div>

        <div class="bottom-header">
            <h3>Central Registry</h3>
        </div>
    </header>

    <div class="hero-banner">
        <div class="hero-content">
            <div class="title">
                <h1>Police Digital Receipt (PDR)</h1>
            </div>
            <div class="description">
                <p>Save time and money by instantly verifying mobile phones and other used tech before you buy or sell.
                </p>
            </div>
            <div class="search-con">
                <form action="{{ route('lookup.result','__serial__') }}" method="get"
                    onsubmit="this.action=this.action.replace('__serial__', encodeURIComponent(this.serial.value))">
                    <input name="serial" placeholder="Type product serial number" type="text" class="form-control"
                        required>
                    <button type="submit" class="search-btn btn btn-primary">
                        Lookup
                    </button>
                </form>
            </div>
        </div>
    </div>

    <section class="why-immobilise">
        <div class="container">
            <h2>Why use PDR</h2>
            <div class="features">
                <div class="feature">
                    <div class="icon" style="background: black">
                        <img src={{ asset("images/images/public.svg")}} alt="">
                    </div>
                    <h3>FREE for Public Use</h3>
                    <p>Create a secure inventory of all your treasured possessions in one place.</p>
                </div>
                <div class="feature">
                    <div class="icon" style="background: black">
                        <img src={{ asset("images/images/search.svg")}} alt="">
                    </div>
                    <h3>Searchable by Police</h3>
                    <p>Loss and theft alerts are immediately visible on police systems.</p>
                </div>
                <div class="feature">
                    <div class="icon" style="background: black">
                        <img src={{ asset("images/images/recover.svg")}} alt="">
                    </div>
                    <h3>Recover your Valuables</h3>
                    <p>If your registered items are recovered by police they are able to return them.</p>
                </div>
                <div class="feature">
                    <div class="icon" style="background: black">
                        <img src={{ asset("images/images/owner.svg")}} alt="">
                    </div>
                    <h3>Ownership Certificates</h3>
                    <p>Download property details to help make insurance claims and police reports easier.</p>
                </div>
            </div>
        </div>
    </section>

    @if (Route::has('login'))
    <div class="h-14.5 hidden lg:block"></div>
    @endif
</body>

</html>