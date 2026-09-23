<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            スタッフ新規登録
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8"> 
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                
                <form method="POST" action="{{ route('admin.staff.register') }}">
                    @csrf

                    <!-- Staff ID (追加) -->
                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 mb-1">スタッフID</label>
                        <input type="text" name="staff_id" value="{{ old('staff_id') }}" required autofocus class="block w-3/4 border-gray-300 rounded-md shadow-sm">
                        @error('staff_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Name -->
                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 mb-1">名前</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="block w-3/4 border-gray-300 rounded-md shadow-sm">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 mb-1">メールアドレス</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="block w-3/4 border-gray-300 rounded-md shadow-sm">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 mb-1">パスワード</label>
                        <input type="password" name="password" required class="block w-3/4 border-gray-300 rounded-md shadow-sm">
                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label class="block font-medium text-sm text-gray-700 mb-1">パスワード（確認）</label>
                        <input type="password" name="password_confirmation" required class="block w-3/4 border-gray-300 rounded-md shadow-sm">
                    </div>

                    <!-- 登録ボタン -->
                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500 shadow">
                            登録する
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>