@php($tool = $tool ?? null)

<div>
    <label class="mb-2 block text-lg font-semibold" for="name">工具名</label>
    <input id="name" name="name" value="{{ old('name', $tool?->name) }}" required class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 text-lg">
    @error('name')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="mb-2 block text-lg font-semibold" for="serial_number">シリアル番号</label>
    <input id="serial_number" name="serial_number" value="{{ old('serial_number', $tool?->serial_number) }}" required placeholder="TL-TW50-001" class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 font-mono text-lg">
    @error('serial_number')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="mb-2 block text-lg font-semibold" for="current_location">現在位置</label>
    <input id="current_location" name="current_location" value="{{ old('current_location', $tool?->current_location) }}" class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 text-lg">
    @error('current_location')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="mb-2 block text-lg font-semibold" for="status">ステータス</label>
    <select id="status" name="status" required class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 text-lg">
        @foreach (\App\Enums\ToolStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(old('status', $tool?->status?->value) === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
    @error('status')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
</div>
