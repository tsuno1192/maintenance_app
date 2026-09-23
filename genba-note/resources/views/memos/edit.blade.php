<x-app-layout title="申し送り編集">
    <x-slot:header>申し送りを編集</x-slot:header>

    <form method="POST" action="{{ route('memos.update', $memo) }}" class="mx-auto max-w-2xl space-y-5 rounded-3xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')

        <div>
            <label class="mb-2 block text-lg font-semibold" for="machine_id">対象設備</label>
            <select id="machine_id" name="machine_id" required class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 text-lg">
                @foreach ($machines as $machine)
                    <option value="{{ $machine->id }}" @selected(old('machine_id', $memo->machine_id) === $machine->id)>{{ $machine->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-lg font-semibold" for="message">内容</label>
            <textarea id="message" name="message" rows="6" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-lg">{{ old('message', $memo->message) }}</textarea>
        </div>

        <div>
            <label class="mb-2 block text-lg font-semibold" for="status">ステータス</label>
            <select id="status" name="status" required class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 text-lg">
                @foreach (\App\Enums\MemoStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $memo->status->value) === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="inline-flex min-h-14 w-full items-center justify-center rounded-2xl bg-orange-600 text-xl font-bold text-white hover:bg-orange-500">
            更新する
        </button>
    </form>
</x-app-layout>
