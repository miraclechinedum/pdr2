<x-guest-layout>
    <div class="register-container">
        <div class="register-right">
            <div class="register-form">
                <div class="title-con">
                    <p style="margin-bottom: 15px">Forgot your password? No problem. Just let us know your email address
                        and we will email you a
                        password reset link that will allow you to choose a new one.</p>
                </div>

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                            :value="old('email')" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button>
                            {{ __('Email Password Reset Link') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
</x-guest-layout>