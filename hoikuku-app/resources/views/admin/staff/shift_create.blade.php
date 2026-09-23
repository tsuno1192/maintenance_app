<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>シフト希望入力</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .alert-success { color: green; margin-bottom: 15px; }
        .alert-error { color: red; margin-bottom: 15px; }
        table { border-collapse: collapse; width: 100%; max-width: 600px; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        button { padding: 10px 20px; background-color: #007bff; color: #fff; border: none; cursor: pointer; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>

    <h1>シフト希望入力</h1>
    <p>対象期間: {{ $shiftPeriod->name }} （{{ $shiftPeriod->start_date }} 〜 {{ $shiftPeriod->end_date }}）</p>

    {{-- 成功・エラーメッセージの表示 --}}
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    <form action="{{ route('staff.shifts.store', $shiftPeriod->id) }}" method="POST">
        @csrf

        <table>
            <thead>
                <tr>
                    <th>日付</th>
                    <th>シフト希望</th>
                </tr>
            </thead>
            <tbody>
                @foreach($periodDates as $date)
                    @php
                        $dateString = $date->format('Y-m-d');
                        // 既に保存されている希望があれば初期値として取得
                        $selectedPatternId = $existingSubmissions[$dateString] ?? '';
                    @endphp
                    <tr>
                        <td>
                            {{ $date->format('Y/m/d') }} 
                            ({{ ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] }})
                        </td>
                        <td>
                            <select name="submissions[{{ $dateString }]]">
                                <option value="">選択してください（出勤日・休みなど）</option>
                                @foreach($shiftPatterns as $pattern)
                                    <option value="{{ $pattern->id }}" {{ $selectedPatternId == $pattern->id ? 'selected' : '' }}>
                                        {{ $pattern->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit">シフト希望を保存する</button>
    </form>

</body>
</html>