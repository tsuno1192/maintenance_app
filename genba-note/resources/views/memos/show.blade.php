<x-app-layout title="申し送り詳細">
    <x-slot:header>申し送り詳細</x-slot:header>
    <x-slot:headerActions>
        <a href="{{ route('memos.edit', $memo) }}" class="inline-flex min-h-12 items-center rounded-xl border border-slate-300 px-5 text-lg font-semibold">編集</a>
    </x-slot:headerActions>

    <article class="rounded-3xl border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-2xl font-bold">{{ $memo->machine?->name }}</div>
                <div class="mt-1 text-base text-slate-500">{{ $memo->user?->name }} · {{ $memo->created_at?->format('Y-m-d H:i') }}</div>
            </div>
            <x-status-badge :label="$memo->status->label()" :color="$memo->status->color()" />
        </div>

        <p class="mt-6 whitespace-pre-wrap text-xl leading-relaxed">{{ $memo->message }}</p>

        @if (! empty($memo->tags))
            <div class="mt-5 flex flex-wrap gap-2">
                @foreach ($memo->tags as $tag)
                    <span class="rounded-lg bg-slate-200 px-3 py-1 text-base">#{{ $tag }}</span>
                @endforeach
            </div>
        @endif

        @if ($memo->images->isNotEmpty())
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                @foreach ($memo->images as $image)
                    <figure class="overflow-hidden rounded-2xl border border-slate-200">
                        <img src="{{ $image->url() }}" alt="{{ $image->original_name }}" class="h-56 w-full object-cover">
                        <figcaption class="truncate px-3 py-2 text-sm text-slate-500">{{ $image->original_name }}</figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
    </article>
</x-app-layout>
