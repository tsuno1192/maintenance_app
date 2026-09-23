<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ご相談受信ボックス（保育士・管理者用）
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-4">※保護者からのメッセージは、AIにより建設的でスムーズなやり取りができるトーンに自動調整されています。</p>

                <div class="space-y-4">
                    @foreach($tickets as $ticket)
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="font-bold text-lg text-gray-800">{{ $ticket->title }}</h3>
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">ステータス: {{ $ticket->status }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mb-2">送信者: {{ $ticket->user->name ?? '匿名' }} / {{ $ticket->created_at }}</p>

                            <div class="bg-white p-3 rounded border border-gray-200 text-gray-700">
                                @foreach($ticket->messages as $message)
                                    <p>{{ $message->body }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">{{ $tickets->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
