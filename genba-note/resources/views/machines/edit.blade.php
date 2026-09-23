<x-app-layout title="設備編集">
    <x-slot:header>設備を編集</x-slot:header>

    <form method="POST" action="{{ route('machines.update', $machine) }}" class="mx-auto max-w-2xl space-y-5 rounded-3xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')
        @include('machines._form', ['machine' => $machine])
        <button type="submit" class="inline-flex min-h-14 w-full items-center justify-center rounded-2xl bg-orange-600 text-xl font-bold text-white hover:bg-orange-500">
            更新する
        </button>
    </form>
</x-app-layout>
