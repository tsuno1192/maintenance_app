@props([
    'name',
    'label',
    'value' => '',
    'rows' => 4,
    'placeholder' => '',
    'required' => false,
])

<div class="tmq-field tmq-field--speech">
    <div class="tmq-field__label-row">
        <span>{{ $label }}@if($required) <em>*</em>@endif</span>
        <button
            type="button"
            class="tmq-speech-btn"
            data-speech-target="{{ $name }}"
            aria-label="{{ $label }}を音声入力"
            title="音声入力"
        >
            <svg class="tmq-speech-btn__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 1 0-6 0v5a3 3 0 0 0 3 3Z" stroke="currentColor" stroke-width="1.8"/>
                <path d="M19 11a7 7 0 0 1-14 0M12 18v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            <span class="tmq-speech-btn__text">音声入力</span>
        </button>
    </div>
    <textarea
        id="field-{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
    >{{ $value }}</textarea>
    <p class="tmq-speech-status" data-speech-status-for="{{ $name }}" hidden></p>
</div>
