<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4 text-primary" :status="session('status')" />

    <form method="POST" class="text-primary" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="text-primary">


            <x-input-label  class="text-primary" for="email" :value="__('Email')"/>
            <x-text-input id="email" class="block mt-1 w-full text-primary" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="flex items-center justify-center mt-2 font-bold text-lg" />

        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-center mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-background border-gray-300 dark:border-background text-indigo-600 shadow-sm focus:ring-background dark:focus:ring-backgound dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm">{{ __('Mantener Sesión Iniciada') }}</span>
            </label>
        </div>

        <br>
        <div class="flex items-center justify-center mt-4">

        <!-- Recuperacion de Clave (?)
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        -->

            <div class="mb-3">
                {{-- {!! NoCaptcha::display() !!} --}}
                {{-- @error('g-recaptcha-response')
                    <span class="text-danger">{{ $message }}</span>
                @enderror --}}
            </div>
        </div>
        <br>
        <div class="flex items-center justify-center mt-4 text-background">
            <x-primary-button class="ms-3 text-background">
                {{ __('Iniciar Sesión') }}
            </x-primary-button>
        </div>
    </form>
    {{-- {!! NoCaptcha::renderJs() !!} --}}
</x-guest-layout>
