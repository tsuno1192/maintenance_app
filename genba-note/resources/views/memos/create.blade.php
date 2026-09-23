<x-app-layout title="申し送り投稿">
    <x-slot:header>申し送りを投稿</x-slot:header>

    <form
        method="POST"
        action="{{ route('memos.store') }}"
        enctype="multipart/form-data"
        class="mx-auto max-w-2xl space-y-5 rounded-3xl border border-slate-200 bg-white p-6"
        x-data="{ previews: [] }"
    >
        @csrf

        <div>
            <label class="mb-2 block text-lg font-semibold" for="machine_id">対象設備</label>
            <select id="machine_id" name="machine_id" required class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 text-lg">
                <option value="">選択してください</option>
                @foreach ($machines as $machine)
                    <option value="{{ $machine->id }}" @selected(old('machine_id', $selectedMachineId) === $machine->id)>
                        {{ $machine->name }}（{{ $machine->qr_identifier }}）
                    </option>
                @endforeach
            </select>
            @error('machine_id')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-2 block text-lg font-semibold" for="message">内容</label>
            <textarea id="message" name="message" rows="6" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-lg">{{ old('message') }}</textarea>
            @error('message')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-2 block text-lg font-semibold" for="tags">タグ（カンマ区切り・任意）</label>
            <input id="tags_input" type="text" value="{{ old('tags') ? implode(',', old('tags')) : '' }}" placeholder="異音,油漏れ" class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 text-lg" oninput="const box=document.getElementById('tags_hidden'); box.innerHTML=''; this.value.split(',').map(t=>t.trim()).filter(Boolean).forEach(t=>{const i=document.createElement('input'); i.type='hidden'; i.name='tags[]'; i.value=t; box.appendChild(i);})">
            <div id="tags_hidden">
                @foreach ((array) old('tags', []) as $tag)
                    <input type="hidden" name="tags[]" value="{{ $tag }}">
                @endforeach
            </div>
            @error('tags')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
            @error('tags.*')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-2 block text-lg font-semibold" for="images">添付画像（最大5枚）</label>
            <input
                id="images"
                type="file"
                name="images[]"
                accept="image/jpeg,image/png,image/webp"
                multiple
                class="min-h-14 w-full rounded-2xl border border-slate-300 px-4 py-3 text-lg"
            >
            @error('images')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
            @error('images.*')<p class="mt-2 text-rose-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="inline-flex min-h-14 w-full items-center justify-center rounded-2xl bg-orange-600 text-xl font-bold text-white hover:bg-orange-500">
            投稿する
        </button>
    </form>
</x-app-layout>
