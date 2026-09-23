<x-app-layout title="申し送り一覧">
    <x-slot:header>申し送り一覧</x-slot:header>
    <x-slot:headerActions>
        <a href="{{ route('memos.create') }}" class="inline-flex min-h-12 items-center rounded-xl bg-orange-600 px-5 text-lg font-bold text-white hover:bg-orange-500">
            新規投稿
        </a>
    </x-slot:headerActions>

    <form method="GET" class="mb-6 grid gap-3 rounded-3xl border border-slate-200 bg-white p-4 sm:grid-cols-3">
        <select name="machine_id" class="min-h-14 rounded-2xl border border-slate-300 px-4 text-lg">
            <option value="">すべての設備</option>
            @foreach ($machines as $machine)
                <option value="{{ $machine->id }}" @selected(request('machine_id') === $machine->id)>{{ $machine->name }}</option>
            @endforeach
        </select>
        <select name="status" class="min-h-14 rounded-2xl border border-slate-300 px-4 text-lg">
            <option value="">すべてのステータス</option>
            @foreach (\App\Enums\MemoStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="inline-flex min-h-14 items-center justify-center rounded-2xl bg-slate-900 px-6 text-lg font-bold text-white">
            絞り込み
        </button>
    </form>

    <div class="space-y-4">
        @forelse ($memos as $memo)
            <a href="{{ route('memos.show', $memo) }}" class="block rounded-3xl border border-slate-200 bg-white p-5 transition hover:border-orange-400">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="text-xl font-bold">{{ $memo->machine?->name }}</div>
                    <x-status-badge :label="$memo->status->label()" :color="$memo->status->color()" />
                </div>
                <p class="mt-3 line-clamp-3 text-lg text-slate-700">{{ $memo->message }}</p>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @foreach (($memo->tags ?? []) as $tag)
                        <span class="rounded-lg bg-slate-200 px-3 py-1 text-base">#{{ $tag }}</span>
                    @endforeach
                    <span class="ml-auto text-base text-slate-500">{{ $memo->user?->name }} · {{ $memo->created_at?->format('m/d H:i') }}</span>
                </div>
            </a>
        @empty
            <p class="rounded-3xl border border-dashed border-slate-300 p-10 text-center text-lg text-slate-500">
                申し送りはまだありません。
            </p>
        @endforelse
    </div>

    <div class="mt-6">{{ $memos->links() }}</div>
</x-app-layout>
