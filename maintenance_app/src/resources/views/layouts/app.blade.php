<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', $title ?? 'ダッシュボード') — {{ config('app.name', 'TMQ') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @include('layouts.partials.vite')
        <link rel="stylesheet" href="{{ asset('css/tmq.css') }}">
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot ?? '' }}

                @hasSection('content')
                    <div class="tmq-main">
                        @if (session('success'))
                            <div class="tmq-alert tmq-alert--success" role="status">{{ session('success') }}</div>
                        @endif

                        @if (session('status'))
                            <div class="tmq-alert tmq-alert--success" role="status">{{ session('status') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="tmq-alert tmq-alert--error" role="alert">
                                <p>入力内容を確認してください。</p>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                @endif
            </main>
        </div>

        <script src="{{ asset('js/speech-input.js') }}" defer></script>
        @stack('scripts')
    </body>
</html>
