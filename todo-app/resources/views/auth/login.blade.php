<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Action List ログイン</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-xl shadow-sm p-6">
        <h1 class="text-xl font-semibold text-slate-900">Action List</h1>
        <p class="mt-2 text-sm text-slate-600">内部利用向けです。共有アクセストークンを入力してください。</p>

        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="access_token" class="block text-sm font-medium text-slate-700">アクセストークン</label>
                <input id="access_token" type="password" name="access_token" required
                       class="mt-1 w-full rounded-lg border-slate-300 shadow-sm">
                @error('access_token')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full rounded-lg bg-teal-700 text-white py-2.5 text-sm font-medium hover:bg-teal-800">
                ログイン
            </button>
        </form>
    </div>
</body>
</html>
