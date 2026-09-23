<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">アレルギー・配膳ガード</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>@endif
            @if(session('allergy_alert'))
            <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" id="allergy-popup">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border-4 border-red-500">
                    <h3 class="text-xl font-bold text-red-700 mb-2">⚠ アレルギー注意</h3>
                    <p class="text-sm text-gray-800 mb-2">{{ session('allergy_child') }} さん</p>
                    <p class="text-sm font-semibold text-red-800">{{ implode(' / ', session('allergy_matched', [])) }}</p>
                    <button onclick="document.getElementById('allergy-popup').remove()" class="mt-4 w-full bg-red-600 text-white py-2 rounded-md">確認した</button>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('allergies.check') }}" class="bg-amber-50 border border-amber-200 p-6 rounded-lg grid md:grid-cols-3 gap-3">
                @csrf
                <select name="child_id" required class="border-gray-300 rounded-md">
                    <option value="">配膳対象の児童</option>
                    @foreach($children as $child)<option value="{{ $child->id }}">{{ $child->name }}</option>@endforeach
                </select>
                <select name="meal_type" class="border-gray-300 rounded-md">
                    <option value="lunch">給食</option>
                    <option value="snack">おやつ</option>
                </select>
                <input name="menu_allergens" required placeholder="献立アレルゲン（カンマ区切り: 卵,乳）" class="border-gray-300 rounded-md md:col-span-2">
                <button class="bg-amber-600 text-white rounded-md text-sm font-semibold">配膳前チェック</button>
            </form>

           <div class="bg-white rounded shadow-sm divide-y">
                @foreach($children as $child)
                <!-- p-4の中身を flex で横並びにする -->
                <div class="p-4 text-sm flex items-center gap-4">
                    <!-- 名前の幅を固定、またはそのまま並べる -->
                    <div class="font-semibold w-32 shrink-0">{{ $child->name }}</div>
                    
                    <!-- アレルギー情報 -->
                    <div class="text-gray-600">
                        @forelse($child->allergyRecords as $allergy)
                        <span class="inline-block mr-2">{{ $allergy->allergen }}（{{ $allergy->severity }}）</span>
                        @empty
                        <span class="text-gray-400">登録なし</span>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
            <div>{{ $children->links() }}</div>
        </div>
    </div>
</x-app-layout>