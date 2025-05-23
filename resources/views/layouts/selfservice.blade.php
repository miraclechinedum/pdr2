<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'pdr2') }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-...HASH..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Fonts & Tailwind/Vite -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="flex bg-gray-100 min-h-screen">

        {{-- Main content area --}}
        <div class="flex-1 flex flex-col">

            {{-- Topbar --}}
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

            {{-- Main Content --}}
            <main class="flex-1 overflow-y-auto p-6 pt-12">
                @yield('content')
            </main>
        </div>
    </div>


    <!-- jQuery (if you don’t already have it) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    @stack('scripts')


</body>

</html>