<section class="tmq-card">
    <h2>基本情報</h2>
    <div class="tmq-grid tmq-grid--2">
        <label class="tmq-field">
            <span>設備番号 <em>*</em></span>
            <input type="text" name="code" value="{{ old('code', $machine?->code) }}" required>
        </label>
        <label class="tmq-field">
            <span>設備名 <em>*</em></span>
            <input type="text" name="name" value="{{ old('name', $machine?->name) }}" required>
        </label>
        <label class="tmq-field">
            <span>エリア</span>
            <input type="text" name="area" value="{{ old('area', $machine?->area) }}" placeholder="例: 第1プラント">
        </label>
        <label class="tmq-field">
            <span>分類</span>
            <input type="text" name="category" value="{{ old('category', $machine?->category) }}" placeholder="例: 回転機">
        </label>
        <label class="tmq-field">
            <span>メーカー</span>
            <input type="text" name="manufacturer" value="{{ old('manufacturer', $machine?->manufacturer) }}">
        </label>
        <label class="tmq-field">
            <span>型式</span>
            <input type="text" name="model" value="{{ old('model', $machine?->model) }}">
        </label>
        <label class="tmq-field">
            <span>設置日</span>
            <input type="date" name="installed_on" value="{{ old('installed_on', optional($machine?->installed_on)->format('Y-m-d')) }}">
        </label>
        <label class="tmq-field">
            <span>稼働状態 <em>*</em></span>
            <select name="status" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $machine?->status?->value ?? 'running') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </label>
    </div>
    <label class="tmq-field">
        <span>備考</span>
        <textarea name="notes" rows="4">{{ old('notes', $machine?->notes) }}</textarea>
    </label>
</section>
