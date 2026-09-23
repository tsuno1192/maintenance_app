{{-- 視覚的ステータスバッジ（手袋操作でも判別しやすい大きめ表示） --}}
@php
    $classes = match ($color) {
        'green' => 'bg-emerald-500/15 text-emerald-700 ring-emerald-600/30',
        'red' => 'bg-rose-500/15 text-rose-700 ring-rose-600/30',
        'amber' => 'bg-amber-500/15 text-amber-800 ring-amber-600/30',
        'blue' => 'bg-sky-500/15 text-sky-800 ring-sky-600/30',
        default => 'bg-slate-500/15 text-slate-700 ring-slate-600/30',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-lg px-3 py-1.5 text-base font-semibold ring-1 ring-inset {$classes}"]) }}>
    {{ $label }}
</span>
