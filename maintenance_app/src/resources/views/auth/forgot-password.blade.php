<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        登録メールアドレス宛に、パスワード再設定用のリンクを送信します。
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="メールアドレス" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                リンクを送信
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
