@php
    $selectedMachine = old('machine_id', $memo?->machine_id ?? request('machine_id'));
@endphp

<section class="tmq-card">
    <h2>申し送り内容</h2>
    <label class="tmq-field">
        <span>件名 <em>*</em></span>
        <input type="text" name="title" value="{{ old('title', $memo?->title) }}" required>
    </label>
    <div class="tmq-grid tmq-grid--3">
        <label class="tmq-field">
            <span>関連設備</span>
            <select name="machine_id">
                <option value="">なし</option>
                @foreach ($machines as $machine)
                    <option value="{{ $machine->id }}" @selected((string) $selectedMachine === (string) $machine->id)>
                        {{ $machine->displayName() }}
                    </option>
                @endforeach
            </select>
        </label>
        <label class="tmq-field">
            <span>勤務帯 <em>*</em></span>
            <select name="shift" required>
                @foreach ($shifts as $shift)
                    <option value="{{ $shift->value }}" @selected(old('shift', $memo?->shift?->value ?? 'day') === $shift->value)>
                        {{ $shift->label() }}
                    </option>
                @endforeach
            </select>
        </label>
        <label class="tmq-field">
            <span>重要度 <em>*</em></span>
            <select name="priority" required>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->value }}" @selected(old('priority', $memo?->priority?->value ?? 'normal') === $priority->value)>
                        {{ $priority->label() }}
                    </option>
                @endforeach
            </select>
        </label>
    </div>
    <label class="tmq-field">
        <span>分類</span>
        <input type="text" name="category" value="{{ old('category', $memo?->category) }}" placeholder="運転 / 保全 / 安全 など">
    </label>
    <x-speech-textarea
        name="body"
        label="申し送り内容"
        :value="old('body', $memo?->body)"
        rows="8"
        placeholder="次の勤務へ伝えたい内容"
    />
    <label class="tmq-field">
        <span>写真を追加</span>
        <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
    </label>

    @if ($memo?->images?->isNotEmpty())
        <p class="tmq-hint">登録済みの写真</p>
        <div class="tmq-thumb-grid">
            @foreach ($memo->images as $image)
                <figure>
                    <img src="{{ route('memos.images.show', [$memo, $image]) }}" alt="{{ $image->original_name }}">
                    <figcaption>{{ $image->original_name }}</figcaption>
                </figure>
            @endforeach
        </div>
    @endif
</section>
