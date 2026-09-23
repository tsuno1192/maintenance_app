<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            プロフィール情報
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            氏名・メールアドレス・所属グループを更新できます。
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="氏名" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="メールアドレス" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="group_name" value="所属グループ" />
            <x-text-input id="group_name" name="group_name" type="text" class="mt-1 block w-full" :value="old('group_name', $user->group_name)" autocomplete="organization" />
            <x-input-error class="mt-2" :messages="$errors->get('group_name')" />
            <p class="mt-2 text-sm text-gray-600">ロール: {{ $user->role?->label() ?? '—' }}（管理者のみ変更可）</p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>保存する</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">保存しました。</p>
            @endif
        </div>
    </form>
</section>
