{{-- どの画面からでも申し送り投稿へ遷移できる FAB --}}
<a
    href="{{ route('memos.create') }}"
    class="fixed bottom-6 right-6 z-40 inline-flex min-h-16 min-w-16 items-center justify-center gap-2 rounded-2xl bg-orange-600 px-5 py-4 text-lg font-bold text-white shadow-lg shadow-orange-900/30 transition hover:bg-orange-500 focus:outline-none focus-visible:ring-4 focus-visible:ring-orange-300"
    aria-label="申し送りを投稿"
>
    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
    </svg>
    <span class="hidden sm:inline">申し送り</span>
</a>
