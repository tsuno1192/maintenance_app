<x-app-layout title="設備詳細">
    <x-slot:header>{{ $machine->name }}</x-slot:header>
    <x-slot:headerActions>
        <a href="{{ route('machines.edit', $machine) }}" class="inline-flex min-h-12 items-center rounded-xl border border-slate-300 px-5 text-lg font-semibold">編集</a>
        <a href="{{ route('memos.create', ['machine_id' => $machine->id]) }}" class="inline-flex min-h-12 items-center rounded-xl bg-orange-600 px-5 text-lg font-bold text-white">申し送り</a>
    </x-slot:headerActions>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 lg:col-span-1">
            <x-status-badge :label="$machine->status->label()" :color="$machine->status->color()" />
            <dl class="mt-5 space-y-4 text-lg">
                <div>
                    <dt class="text-slate-500">QR識別子</dt>
                    <dd class="font-mono font-semibold">{{ $machine->qr_identifier }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">設置場所</dt>
                    <dd class="font-semibold">{{ $machine->location ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">マニュアル</dt>
                    <dd>
                        @if ($machine->manual_url)
                            <a href="{{ $machine->manual_url }}" class="text-orange-600 underline" target="_blank" rel="noopener">開く</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 lg:col-span-2">
            <h2 class="mb-4 text-2xl font-bold">最近の申し送り</h2>
            <div class="divide-y divide-slate-200">
                @forelse ($machine->memos as $memo)
                    <a href="{{ route('memos.show', $memo) }}" class="block py-4">
                        <div class="flex items-center justify-between gap-3">
                            <x-status-badge :label="$memo->status->label()" :color="$memo->status->color()" />
                            <span class="text-base text-slate-500">{{ $memo->created_at?->format('Y-m-d H:i') }}</span>
                        </div>
                        <p class="mt-2 text-lg">{{ $memo->message }}</p>
                    </a>
                @empty
                    <p class="py-8 text-center text-lg text-slate-500">申し送りはまだありません。</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
