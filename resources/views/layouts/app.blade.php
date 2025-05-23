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

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"></div>

        {{-- Sidebar (off-canvas on mobile, static on lg+) --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 w-64 text-gray-200 z-30
             transform transition-transform duration-200 ease-in-out
             lg:relative lg:translate-x-0 lg:transform-none lg:transition-none sidebar ">
            <div class="p-6 text-white text-lg font-bold">
                <a href="/" class="logo">
                    <img src="{{ asset('images/police_logo.png') }}" alt="Logo">
                </a>
            </div>

            <nav x-data class="space-y-1 px-4">
                <a href="{{ route('dashboard') }}" class="block py-2 px-3 rounded transition
             {{ request()->routeIs('dashboard')
                 ? 'bg-gray-500 text-white'
                 : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}">
                    Dashboard
                </a>

                @php
                $usersOpen = request()->routeIs('users.*') ? 'true' : 'false';
                @endphp

                <div x-data="{ open: {{ $usersOpen }} }" class="space-y-1">
                    <button @click="open = !open" class="w-full flex items-center justify-between py-2 px-3 rounded transition focus:outline-none
                         {{ request()->routeIs('users.*')
                             ? 'bg-gray-700 text-white'
                             : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}">
                        <span>Users</span>
                        <svg :class="{ 'rotate-180': open }" class="h-4 w-4 transform transition-transform"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse x-cloak class="space-y-1 pl-6">
                        <a href="{{ route('users.create')}}" class="block py-2 px-3 rounded transition
                 {{ request()->routeIs('users.create')
                     ? 'bg-gray-700 text-white'
                     : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}">
                            Add User
                        </a>
                        <a href="{{ route('users.index')}}" class="block py-2 px-3 rounded transition
                 {{ request()->routeIs('users.index')
                     ? 'bg-gray-700 text-white'
                     : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}">
                            All Users
                        </a>
                    </div>
                </div>

                {{-- ↓ START Products menu ↓ --}}
                @php
                // Determine if we're on a products.* route
                $productsOpen = request()->routeIs('products.*') ? 'true' : 'false';
                @endphp

                <div x-data="{ open: {{ $productsOpen }} }" class="space-y-1">
                    <button @click="open = !open" class="w-full flex items-center justify-between py-2 px-3 rounded transition focus:outline-none
            {{ request()->routeIs('products.*')
                ? 'bg-gray-700 text-white'
                : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}
          ">
                        <span>Products</span>
                        <svg :class="{ 'rotate-180': open }" class="h-4 w-4 transform transition-transform"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse x-cloak class="space-y-1 pl-6">
                        {{-- Add Product --}}
                        <a href="{{ route('products.create')}}" class="block py-2 px-3 rounded transition
               {{ request()->routeIs('products.create')
                   ? 'bg-gray-700 text-white'
                   : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}
             ">
                            Add Product
                        </a>

                        {{-- All Products --}}
                        <a href="{{ route('products.index')}}" class="block py-2 px-3 rounded transition
               {{ request()->routeIs('products.index')
                   ? 'bg-gray-700 text-white'
                   : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}
             ">
                            All Products
                        </a>

                        {{-- Add Product Category --}}
                        <a href="{{ route('categories.index')}}" class="block py-2 px-3 rounded transition
               {{ request()->routeIs('categories.index')
                   ? 'bg-gray-700 text-white'
                   : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}
             ">
                            Add Product Category
                        </a>

                        <a href="{{ route('products.my') }}" class="block py-2 px-3 rounded transition
       {{ request()->routeIs('products.my')
         ? 'bg-gray-700 text-white'
         : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}">
                            My Products
                        </a>
                    </div>
                </div>
                {{-- ↑ END Products menu ↑ --}}

                {{-- ↓ START Business menu ↓ --}}
                @php
                // expand if we’re on any of the businesses/branches routes
                $businessOpen = request()->routeIs('businesses.*') || request()->routeIs('branches.*')
                ? 'true'
                : 'false';
                @endphp

                <div x-data="{ open: {{ $businessOpen }} }" class="space-y-1">
                    <button @click="open = !open" class="w-full flex items-center justify-between py-2 px-3 rounded transition focus:outline-none
      {{ request()->routeIs('businesses.*') || request()->routeIs('branches.*')
          ? 'bg-gray-700 text-white'
          : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}
    ">
                        <span>Business</span>
                        <svg :class="{ 'rotate-180': open }" class="h-4 w-4 transform transition-transform"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse x-cloak class="space-y-1 pl-6">
                        {{-- Add Business --}}
                        <a href="{{ route('businesses.create') }}" class="block py-2 px-3 rounded transition
        {{ request()->routeIs('businesses.create')
            ? 'bg-gray-700 text-white'
            : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}
      ">
                            Add Business
                        </a>

                        {{-- All Businesses --}}
                        <a href="{{ route('businesses.index') }}" class="block py-2 px-3 rounded transition
        {{ request()->routeIs('businesses.index')
            ? 'bg-gray-700 text-white'
            : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}
      ">
                            All Businesses
                        </a>

                        {{-- Business Branches --}}
                        <a href="{{ route('branches.index') }}" class="block py-2 px-3 rounded transition
        {{ request()->routeIs('branches.*')
            ? 'bg-gray-700 text-white'
            : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}
      ">
                            Business Branches
                        </a>
                    </div>
                </div>
                {{-- ↑ END Business menu ↑ --}}

                {{-- receiptsOpen detect --}}
                @php $receiptsOpen = request()->routeIs('receipts.*') ? 'true' : 'false'; @endphp

                <div x-data="{ open: {{ $receiptsOpen }} }" class="space-y-1">
                    <button @click="open = !open" class="w-full flex items-center justify-between py-2 px-3 rounded transition focus:outline-none
            {{ request()->routeIs('receipts.*')
                ? 'bg-gray-700 text-white'
                : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}">
                        <span>Receipts</span>
                        <svg :class="{ 'rotate-180': open }" class="h-4 w-4 transform transition-transform"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse x-cloak class="space-y-1 pl-6">
                        <a href="{{ route('receipts.index') }}" class="block py-2 px-3 rounded transition
                {{ request()->routeIs('receipts.index')
                    ? 'bg-gray-700 text-white'
                    : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}">
                            All Receipts
                        </a>
                        <a href="{{ route('receipts.create') }}" class="block py-2 px-3 rounded transition
                {{ request()->routeIs('receipts.create')
                    ? 'bg-gray-700 text-white'
                    : 'text-gray-200 hover:bg-gray-500 hover:text-white' }}">
                            Generate Receipt
                        </a>
                    </div>
                </div>


            </nav>
        </aside>

        {{-- Main content area --}}
        <div class="flex-1 flex flex-col">

            {{-- Topbar --}}
            <div class="header-con">
                <div class="flex items-center justify-between px-6 py-4 top-header">

                    {{-- Mobile hamburger --}}
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-600 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    {{-- Title --}}
                    <div class="hidden lg:block text-xl font-semibold">Police Digital Receipt</div>

                    {{-- User dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none">
                            <div class="w-10 h-10 bg-gray-300 rounded-full flex-shrink-0"></div>
                            <div class="text-left">
                                <div class="text-800 font-medium">{{ Auth::user()->name }}</div>
                                <div class="text-gray-400 text-sm">
                                    {{ Auth::user()->roles->pluck('name')->first() ?? '—' }}
                                </div>
                            </div>
                            <svg :class="{ 'rotate-180': open }"
                                class="h-5 w-5 text-gray-200 transform transition-transform"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" x-cloak @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 w-48 bg-white border rounded-md shadow-lg z-50 py-1">
                            <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11.049 2.927c.3-1.1 1.603-1.1 1.902 0a1.724 1.724 0 001.33 1.33c1.1.3 1.1 1.603 0 1.902a1.724 1.724 0 00-1.33 1.33c-.3 1.1-1.603 1.1-1.902 0a1.724 1.724 0 00-1.33-1.33c-1.1-.3-1.1-1.603 0-1.902a1.724 1.724 0 001.33-1.33z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="ml-2">Settings</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1m0-9V5" />
                                    </svg>
                                    <span class="ml-2">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center px-6 py-4 bottom-header">
                    <p class="text-xl font-bold">Central Registry</p>
                </div>
            </div>

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