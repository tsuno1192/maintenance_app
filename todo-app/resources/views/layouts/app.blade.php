<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Action List')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
    <header class="border-b border-slate-200 bg-white">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs tracking-wide text-teal-700 font-semibold uppercase">Action List</p>
                <h1 class="text-xl font-semibold">@yield('heading', 'タスク')</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('tasks.index') }}" class="text-sm text-slate-600 hover:text-slate-900">一覧へ</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-slate-600 hover:text-slate-900">ログアウト</button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8 space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-teal-900 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
