<section class="tmq-card">
    <h2>基本情報</h2>
    <div class="tmq-grid tmq-grid--2">
        <label class="tmq-field">
            <span>管理番号 <em>*</em></span>
            <input type="text" name="code" value="{{ old('code', $tool?->code) }}" required>
        </label>
        <label class="tmq-field">
            <span>工具名 <em>*</em></span>
            <input type="text" name="name" value="{{ old('name', $tool?->name) }}" required>
        </label>
        <label class="tmq-field">
            <span>分類</span>
            <input type="text" name="category" value="{{ old('category', $tool?->category) }}" placeholder="手工具 / 測定器 など">
        </label>
        <label class="tmq-field">
            <span>保管場所</span>
            <input type="text" name="location" value="{{ old('location', $tool?->location) }}">
        </label>
        <label class="tmq-field">
            <span>状態 <em>*</em></span>
            <select name="status" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $tool?->status?->value ?? 'available') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </label>
        <label class="tmq-field">
            <span>在庫数 <em>*</em></span>
            <input type="number" name="quantity" min="0" value="{{ old('quantity', $tool?->quantity ?? 1) }}" required>
        </label>
        <label class="tmq-field">
            <span>メーカー</span>
            <input type="text" name="manufacturer" value="{{ old('manufacturer', $tool?->manufacturer) }}">
        </label>
        <label class="tmq-field">
            <span>購入日</span>
            <input type="date" name="purchased_on" value="{{ old('purchased_on', optional($tool?->purchased_on)->format('Y-m-d')) }}">
        </label>
    </div>
    <label class="tmq-field">
        <span>備考</span>
        <textarea name="notes" rows="4">{{ old('notes', $tool?->notes) }}</textarea>
    </label>
</section>
