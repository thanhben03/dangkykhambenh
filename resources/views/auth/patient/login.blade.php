<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('patient.login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Số CCCD')" />
            <x-text-input id="cccd" class="block mt-1 w-full" type="text" name="cccd" :value="old('cccd')" required
                autofocus autocomplete="cccd" />
            <x-input-error :messages="$errors->get('passsword')" class="mt-2" />
            @if ($errors->has('not-found'))
                <x-input-error :messages="$errors->get('not-found')" class="mt-2" />
            @endif
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Mật khẩu')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>


        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a onclick="alertForget()" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    href="#">
                    {{ __('Quên mật khẩu ?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Đăng Nhập') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

<script>
    function alertForget() {
        alert('Vui lòng liên hệ hotline: 123456 để được cấp lại mật khẩu !')
    }
</script>