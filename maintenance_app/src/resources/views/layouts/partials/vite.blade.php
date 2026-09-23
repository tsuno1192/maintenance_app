@php
    $viteReady = file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json'));
@endphp

@if ($viteReady)
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
@endif
