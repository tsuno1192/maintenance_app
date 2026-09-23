<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('インクルーシブ・発達支援 ダッシュボード') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- 成功メッセージ --}}
            @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- 左側：児童一覧リスト --}}
                <div class="md:col-span-1 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">児童一覧</h3>
                        {{-- 新規登録ボタンを追加 --}}
                        <a href="{{ route('children.create') }}" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-700 transition">
                            + 児童を登録
                        </a>
                    </div>

                    <ul class="space-y-2">
                        @forelse($children as $child)
                        <li>
                            <a href="#child-{{ $child->id }}" class="block p-3 rounded-lg hover:bg-indigo-50 border border-gray-200 transition">
                                <p class="font-bold text-gray-800">{{ $child->name }}</p>
                                <p class="text-xs text-gray-500">診断名: {{ $child->diagnosis ?? '未設定' }}</p>
                            </a>
                        </li>
                        @empty
                        <p class="text-sm text-gray-500">表示できる児童がいません。</p>
                        @endforelse
                    </ul>
                    @if (method_exists($children, 'links'))
                    <div class="mt-4">{{ $children->links() }}</div>
                    @endif
                </div>

                {{-- 右側：詳細・入力エリア --}}
                <div class="md:col-span-2 space-y-6">
                    @foreach($children as $child)
                    <div id="child-{{ $child->id }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">

                        {{-- 児童基本情報 & 発達特性（確認画面） --}}
                        <div class="bg-white p-6 rounded shadow-sm">
                            <!-- 児童名・生年月日・診断名 -->
                            <div class="text-xl font-bold">
                                {{ $child->name }} <span class="text-sm font-normal text-gray-600">({{ $child->birth_date }}生まれ)</span>
                            </div>
                            <div class="text-sm text-purple-600 mt-1">診断名: {{ $child->diagnosis ?: 'なし' }}</div>

                            <!-- 上段：感覚過敏の傾向 ＆ パニック時の対応手順 -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <div class="text-sm font-semibold text-gray-700 flex items-center gap-1">
                                        <span>⚡ 感覚過敏の傾向</span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ $child->sensory_tendencies ?: '特になし' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-sm font-semibold text-gray-700 flex items-center gap-1">
                                        <span>🚨 パニック時の対応手順</span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ $child->panic_response_steps ?: '特になし' }}
                                    </div>
                                </div>
                            </div>

                            <!-- 下段：アレルギー ＆ 普段の生活における注意事項（上と同じ横長スタイル） -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 pt-6 border-t border-gray-100">
                                <div>
                                    <div class="text-sm font-semibold text-red-600 flex items-center gap-1">
                                        <span>⚠️ アレルギー</span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ is_string($child->allergies) ? $child->allergies : ($child->allergies ?: '特になし') }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-sm font-semibold text-gray-700 flex items-center gap-1">
                                        <span>💡 普段の生活における注意事項</span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ $child->daily_precautions ?: '特になし' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 個別支援計画（IEP）の簡易表示 --}}
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-800 mb-2">🎯 現在の個別支援計画 (IEP)</h4>
                            @forelse($child->supportPlans as $plan)
                            <div class="bg-indigo-50/50 border border-indigo-100 p-3 rounded text-sm mb-2">
                                <p class="font-bold text-indigo-900">目標: {{ $plan->support_goal }}</p>
                                <p class="text-gray-600 mt-1">手立て: {{ $plan->specific_approaches }}</p>
                                <p class="text-xs text-gray-400 mt-1">期間: {{ $plan->start_date }} 〜 {{ $plan->end_date }}</p>
                            </div>
                            @empty
                            <p class="text-xs text-gray-500">有効な個別支援計画が登録されていません。</p>
                            @endforelse
                        </div>

                        <hr class="my-4 border-gray-200">

                        {{-- 日々の引き継ぎ・申し送り（支援ログ）の入力フォーム --}}
                        @if(auth()->user()->isStaff())
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-3">📝 日々の申し送り・支援ログを記録</h4>
                            <form action="{{ route('support.logs.store', $child->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">対象日</label>
                                    <input type="date" name="target_date" value="{{ date('Y-m-d') }}" class="w-full text-sm border-gray-300 rounded-md shadow-sm" required>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">今日の様子</label>
                                    <textarea name="daily_status" rows="2" class="w-full text-sm border-gray-300 rounded-md shadow-sm" placeholder="本人の様子や活動の参加状況など..." required></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">💬 保護者向け共有事項</label>
                                        <textarea name="parent_sharing" rows="2" class="w-full text-sm border-gray-300 rounded-md shadow-sm" placeholder="連絡帳に記載する内容など..."></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1 text-purple-700">🔒 専門職向け申し送り (スタッフ間)</label>
                                        <textarea name="staff_handover" rows="2" class="w-full text-sm border-gray-300 rounded-md shadow-sm" placeholder="引き継ぎ事項や専門的アプローチの共有..."></textarea>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-md shadow transition">
                                        ログを保存する
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                    </div>
                    @endforeach
                </div>

            </div>

        </div>
    </div>
</x-app-layout>