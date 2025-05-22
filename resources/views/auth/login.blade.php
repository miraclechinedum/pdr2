<x-guest-layout>
    <div class="register-container">

        <div class="register-right">
            <div class="register-form">
                <div class="title-con">
                    <h3>Sign In</h3>
                    {{-- <p>or <a href="/register" style="color: rgb(65, 91, 230);">create an account</a></p> --}}
                </div>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input placeholder="Email" type="email" name="email" :value="old('email')" required autofocus
                        autocomplete="username">
                    <div class="password-wrapper">
                        <input placeholder="Password" type="password" name="password" required
                            autocomplete="current-password">
                        <span class="password-toggle">
                            <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 576 512"
                                height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M572.52 241.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400a144 144 0 1 1 144-144 143.93 143.93 0 0 1-144 144zm0-240a95.31 95.31 0 0 0-25.31 3.79 47.85 47.85 0 0 1-66.9 66.9A95.78 95.78 0 1 0 288 160z">
                                </path>
                            </svg>
                        </span>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    <div class="remember">
                        <input type="checkbox" name="remember">
                        <span style="color: rgb(111, 112, 115);">Remember</span>
                    </div>
                    <button type="submit" class="sign-in-btn">Sign In</button>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="color: rgb(65, 91, 230);">Forgotten your
                        password</a>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>