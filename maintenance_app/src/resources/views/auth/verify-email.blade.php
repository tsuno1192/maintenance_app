<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        登録ありがとうございます。始める前に、メールに記載されたリンクをクリックしてメールアドレスを確認してください。メールが届いていない場合は再送できます。
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            新しい確認リンクを、登録時のメールアドレスへ送信しました。
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <div>
                <x-primary-button>
                    確認メールを再送
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                ログアウト
            </button>
        </form>
    </div>
</x-guest-layout>
