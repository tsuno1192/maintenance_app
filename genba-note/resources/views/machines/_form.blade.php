@php($machine = $machine ?? null)

<div>
    <label class="mb-2 block text-lg font-semibold" for="name">設備名</label>
    <input id="name" name="name" value="{{ old('name', $machine?->name) }}" required class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 text-lg">
    @error('name')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="mb-2 block text-lg font-semibold" for="qr_identifier">QR識別子</label>
    <input id="qr_identifier" name="qr_identifier" value="{{ old('qr_identifier', $machine?->qr_identifier) }}" required placeholder="MCH-0001" class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 font-mono text-lg">
    @error('qr_identifier')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="mb-2 block text-lg font-semibold" for="location">設置場所</label>
    <input id="location" name="location" value="{{ old('location', $machine?->location) }}" class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 text-lg">
    @error('location')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="mb-2 block text-lg font-semibold" for="manual_url">マニュアルURL</label>
    <input id="manual_url" type="url" name="manual_url" value="{{ old('manual_url', $machine?->manual_url) }}" class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 text-lg">
    @error('manual_url')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="mb-2 block text-lg font-semibold" for="status">稼働ステータス</label>
    <select id="status" name="status" required class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 text-lg">
        @foreach (\App\Enums\MachineStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(old('status', $machine?->status?->value) === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
    @error('status')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
</div>
