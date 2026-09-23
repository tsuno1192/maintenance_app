<x-app-layout title="工具編集">
    <x-slot:header>工具を編集</x-slot:header>

    <form method="POST" action="{{ route('tools.update', $tool) }}" class="mx-auto max-w-2xl space-y-5 rounded-3xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')
        @include('tools._form', ['tool' => $tool])
        <button type="submit" class="inline-flex min-h-14 w-full items-center justify-center rounded-2xl bg-orange-600 text-xl font-bold text-white hover:bg-orange-500">
            更新する
        </button>
    </form>
</x-app-layout>
