<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">午睡チェック（SIDS見守り）</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6"
            @if(auth()->user()->isStaff())
            x-data="napAlertMonitor({
            feedUrl: @js(route('naps.alerts.feed')),
            csrf: @js(csrf_token()),
            initial: @js($openAlerts->map(fn ($a) => [
            'id' => $a->id,
            'child_name' => $a->child->name ?? '',
            'message' => $a->message,
            'ack_url' => route('naps.alerts.ack', $a),
            ])->values()),
            echoEnabled: @js(in_array(config('broadcasting.default'), ['reverb', 'pusher'], true)),
            })"
            x-init="boot()"
            @endif
            >
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>@endif

            @if(auth()->user()->isStaff())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 space-y-2" x-show="alerts.length > 0" x-cloak>
                <div class="flex items-center justify-between gap-3">
                    <h3 class="font-bold text-red-800">未確認アラート（リアルタイム）</h3>
                    <span class="text-xs text-red-700" x-text="transportLabel"></span>
                </div>
                <template x-for="alert in alerts" :key="alert.id">
                    <div class="flex justify-between gap-3 text-sm border-b border-red-100 py-2">
                        <span>
                            <span x-text="alert.child_name"></span>:
                            <span x-text="alert.message"></span>
                        </span>
                        <button type="button" class="text-red-700 underline" @click="acknowledge(alert)">確認</button>
                    </div>
                </template>
            </div>

            <div
                x-show="toast"
                x-transition
                class="fixed bottom-4 right-4 z-50 max-w-sm rounded-lg bg-red-700 text-white px-4 py-3 shadow-lg"
                x-text="toast"></div>

            <form method="POST" action="{{ route('naps.store') }}" class="bg-white shadow-sm sm:rounded-lg p-6 grid md:grid-cols-4 gap-3">
                @csrf
                <select name="child_id" required class="border-gray-300 rounded-md">
                    <option value="">児童</option>
                    @foreach($children as $child)<option value="{{ $child->id }}">{{ $child->name }}</option>@endforeach
                </select>
                <select name="posture" class="border-gray-300 rounded-md">
                    <option value="back">仰向け</option>
                    <option value="side">横向き</option>
                    <option value="stomach">うつ伏せ</option>
                    <option value="unknown">不明</option>
                </select>
                <select name="breathing_status" class="border-gray-300 rounded-md">
                    <option value="normal">呼吸正常</option>
                    <option value="irregular">不規則</option>
                    <option value="none">検知なし</option>
                </select>
                <button class="bg-slate-900 text-white rounded-md text-sm font-semibold">記録</button>
            </form>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="p-3">時刻</th>
                            <th class="p-3">児童</th>
                            <th class="p-3">体位</th>
                            <th class="p-3">呼吸</th>
                            <th class="p-3">警戒</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checks as $check)
                        <tr class="border-t">
                            <td class="p-3">{{ $check->checked_at?->format('H:i') }}</td>
                            <td class="p-3">{{ $check->child->name ?? '-' }}</td>

                            <!-- 体位の日本語化 -->
                            <td class="p-3">
                                @switch($check->posture)
                                @case('back') 仰向け @break
                                @case('side') 横向き @break
                                @case('stomach') うつ伏せ @break
                                @default 不明
                                @endswitch
                            </td>

                            <!-- 呼吸の日本語化 -->
                            <td class="p-3">
                                @switch($check->breathing_status)
                                @case('normal') 呼吸正常 @break
                                @case('irregular') 不規則 @break
                                @case('none') 検知なし @break
                                @default {{ $check->breathing_status }}
                                @endswitch
                            </td>

                            <!-- 警戒レベルの日本語化 -->
                            <td class="p-3 font-semibold {{ $check->alert_level !== 'none' ? 'text-red-700' : 'text-gray-500' }}">
                                @switch($check->alert_level)
                                @case('none') なし @break
                                @case('caution') 注意 @break
                                @case('warning') 警戒 @break
                                @default {{ $check->alert_level }}
                                @endswitch
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div>{{ $checks->links() }}</div>
        </div>
    </div>

    @if(auth()->user()->isStaff())
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('napAlertMonitor', (config) => ({
                alerts: config.initial || [],
                feedUrl: config.feedUrl,
                csrf: config.csrf,
                echoEnabled: config.echoEnabled,
                toast: '',
                pollTimer: null,
                transportLabel: '接続準備中…',
                boot() {
                    if (this.echoEnabled && window.Echo) {
                        this.transportLabel = 'WebSocket (Echo)';
                        window.Echo.private('staff.nap-alerts')
                            .listen('.nap.alert.created', (payload) => this.pushAlert(payload));
                        return;
                    }
                    this.transportLabel = 'ポーリング（WebSocket未設定時）';
                    this.poll();
                    this.pollTimer = setInterval(() => this.poll(), 4000);
                },
                async poll() {
                    try {
                        const res = await fetch(this.feedUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                        });
                        if (!res.ok) return;
                        const json = await res.json();
                        this.alerts = json.data || [];
                    } catch (e) {}
                },
                pushAlert(payload) {
                    if (!payload?.id) return;
                    if (this.alerts.some((a) => a.id === payload.id)) return;
                    this.alerts.unshift({
                        id: payload.id,
                        child_name: payload.child_name || '',
                        message: payload.message,
                        ack_url: `/naps/alerts/${payload.id}`,
                    });
                    this.toast = `午睡アラート: ${payload.child_name || ''} ${payload.message || ''}`;
                    setTimeout(() => this.toast = '', 6000);
                },
                async acknowledge(alert) {
                    const form = new FormData();
                    form.append('_token', this.csrf);
                    form.append('_method', 'PATCH');
                    await fetch(alert.ack_url, {
                        method: 'POST',
                        body: form,
                        credentials: 'same-origin'
                    });
                    this.alerts = this.alerts.filter((a) => a.id !== alert.id);
                },
            }));
        });
    </script>
    @endif
</x-app-layout>