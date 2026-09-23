<x-app-layout title="工具詳細">
    <x-slot:header>{{ $tool->name }}</x-slot:header>
    <x-slot:headerActions>
        <a href="{{ route('tools.edit', $tool) }}" class="inline-flex min-h-12 items-center rounded-xl border border-slate-300 px-5 text-lg font-semibold">編集</a>
    </x-slot:headerActions>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-3xl border border-slate-200 bg-white p-6">
            <x-status-badge :label="$tool->status->label()" :color="$tool->status->color()" />
            <dl class="mt-5 space-y-4 text-lg">
                <div>
                    <dt class="text-slate-500">シリアル番号</dt>
                    <dd class="font-mono font-semibold">{{ $tool->serial_number }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">現在位置</dt>
                    <dd class="font-semibold">{{ $tool->current_location ?? '—' }}</dd>
                </div>
            </dl>

            <form method="POST" action="{{ route('tool-logs.store') }}" class="mt-8 space-y-4 border-t border-slate-200 pt-6">
                @csrf
                <input type="hidden" name="tool_id" value="{{ $tool->id }}">
                <div>
                    <label class="mb-2 block text-lg font-semibold" for="action">貸出 / 返却</label>
                    <select id="action" name="action" required class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 text-lg">
                        @foreach (\App\Enums\ToolLogAction::cases() as $action)
                            <option value="{{ $action->value }}">{{ $action->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-lg font-semibold" for="current_location">移動先（任意）</label>
                    <input id="current_location" name="current_location" class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 text-lg">
                </div>
                <div>
                    <label class="mb-2 block text-lg font-semibold" for="notes">備考</label>
                    <textarea id="notes" name="notes" rows="3" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-lg"></textarea>
                </div>
                <button type="submit" class="inline-flex min-h-14 w-full items-center justify-center rounded-2xl bg-orange-600 text-xl font-bold text-white">記録する</button>
            </form>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 lg:col-span-2">
            <h2 class="mb-4 text-2xl font-bold">履歴</h2>
            <div class="divide-y divide-slate-200">
                @forelse ($tool->logs as $log)
                    <div class="py-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xl font-semibold">{{ $log->action->label() }}</span>
                            <span class="text-base text-slate-500">{{ $log->created_at?->format('Y-m-d H:i') }}</span>
                        </div>
                        <div class="mt-1 text-lg text-slate-600">{{ $log->user?->name }}</div>
                        @if ($log->notes)
                            <p class="mt-2 text-base">{{ $log->notes }}</p>
                        @endif
                    </div>
                @empty
                    <p class="py-8 text-center text-lg text-slate-500">履歴はまだありません。</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
