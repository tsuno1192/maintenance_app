<x-app-layout title="工具一覧">
    <x-slot:header>工具一覧</x-slot:header>
    <x-slot:headerActions>
        <a href="{{ route('tools.create') }}" class="inline-flex min-h-12 items-center rounded-xl bg-orange-600 px-5 text-lg font-bold text-white hover:bg-orange-500">
            工具を追加
        </a>
    </x-slot:headerActions>

    <form method="GET" class="mb-6 grid gap-3 rounded-3xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_auto_auto]">
        <input
            type="search"
            name="q"
            value="{{ request('q') }}"
            placeholder="工具名・シリアル・場所で検索"
            class="min-h-14 rounded-2xl border border-slate-300 px-4 text-lg"
        >
        <select name="status" class="min-h-14 rounded-2xl border border-slate-300 px-4 text-lg">
            <option value="">すべてのステータス</option>
            @foreach (\App\Enums\ToolStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="inline-flex min-h-14 items-center justify-center rounded-2xl bg-slate-900 px-6 text-lg font-bold text-white">
            絞り込み
        </button>
    </form>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($tools as $tool)
            <a href="{{ route('tools.show', $tool) }}" class="block rounded-3xl border border-slate-200 bg-white p-5 transition hover:border-orange-400">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xl font-bold">{{ $tool->name }}</div>
                        <div class="mt-1 font-mono text-base text-slate-500">{{ $tool->serial_number }}</div>
                    </div>
                    <x-status-badge :label="$tool->status->label()" :color="$tool->status->color()" />
                </div>
                <div class="mt-4 text-base text-slate-600">
                    {{ $tool->current_location ?? '場所未設定' }}
                </div>
            </a>
        @empty
            <p class="col-span-full rounded-3xl border border-dashed border-slate-300 p-10 text-center text-lg text-slate-500">
                工具がまだ登録されていません。
            </p>
        @endforelse
    </div>

    <div class="mt-6">{{ $tools->links() }}</div>
</x-app-layout>
