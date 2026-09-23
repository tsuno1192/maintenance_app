<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>保全依頼表 #{{ $trouble->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: '{{ $fontFamily }}', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            margin: 0;
            padding: 18px 22px;
        }
        .sheet-title {
            text-align: center;
            font-size: 20px;
            letter-spacing: 0.2em;
            margin: 0 0 6px;
            padding-bottom: 8px;
            border-bottom: 2px solid #222;
        }
        .meta {
            width: 100%;
            margin-bottom: 12px;
        }
        .meta td {
            padding: 2px 0;
            vertical-align: top;
        }
        .meta .label { width: 18%; color: #444; }
        .meta .value { width: 32%; }
        table.form {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.form th,
        table.form td {
            border: 1px solid #333;
            padding: 7px 8px;
            vertical-align: top;
            word-wrap: break-word;
        }
        table.form th {
            width: 18%;
            background: #f0f0f0;
            text-align: left;
            font-weight: normal;
        }
        table.form td { width: 32%; }
        table.form td.full { width: 82%; }
        .block {
            min-height: 48px;
            white-space: pre-wrap;
            line-height: 1.45;
        }
        .block-lg { min-height: 72px; }
        .section-label {
            margin: 14px 0 6px;
            font-size: 12px;
            border-left: 4px solid #0f6a5a;
            padding-left: 8px;
        }
        .approvals {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .approvals th,
        .approvals td {
            border: 1px solid #333;
            padding: 8px 6px;
            text-align: center;
            font-size: 10px;
        }
        .approvals th { background: #f0f0f0; }
        .approvals .stamp {
            height: 54px;
            vertical-align: middle;
        }
        .footer {
            margin-top: 14px;
            font-size: 9px;
            color: #555;
            text-align: right;
        }
    </style>
</head>
<body>
    <h1 class="sheet-title">保　全　依　頼　表</h1>

    <table class="meta">
        <tr>
            <td class="label">帳票番号</td>
            <td class="value">TMQ-{{ str_pad((string) $trouble->id, 6, '0', STR_PAD_LEFT) }}</td>
            <td class="label">出力日時</td>
            <td class="value">{{ now()->format('Y-m-d H:i') }}</td>
        </tr>
        <tr>
            <td class="label">ワークフロー</td>
            <td class="value">{{ $trouble->status->label() }}</td>
            <td class="label">作成Gr</td>
            <td class="value">{{ $trouble->created_group ?: '—' }}</td>
        </tr>
    </table>

    <div class="section-label">依頼内容</div>
    <table class="form">
        <tr>
            <th>大分類</th>
            <td>{{ $trouble->category_major }}</td>
            <th>中分類</th>
            <td>{{ $trouble->category_middle ?: '—' }}</td>
        </tr>
        <tr>
            <th>小分類</th>
            <td>{{ $trouble->category_minor ?: '—' }}</td>
            <th>報告者</th>
            <td>{{ $trouble->reporter_name ?: '—' }}</td>
        </tr>
        <tr>
            <th>発生日</th>
            <td>{{ optional($trouble->occurred_on)->format('Y-m-d') ?? '—' }}</td>
            <th>補修依頼日</th>
            <td>{{ optional($trouble->repair_requested_on)->format('Y-m-d') ?? '—' }}</td>
        </tr>
        <tr>
            <th>件名</th>
            <td class="full" colspan="3">{{ $trouble->title }}</td>
        </tr>
        <tr>
            <th>内容</th>
            <td class="full" colspan="3"><div class="block block-lg">{{ $trouble->content ?: '' }}</div></td>
        </tr>
        <tr>
            <th>調査内容</th>
            <td class="full" colspan="3"><div class="block block-lg">{{ $trouble->investigation ?: '' }}</div></td>
        </tr>
        <tr>
            <th>推定原因</th>
            <td class="full" colspan="3"><div class="block">{{ $trouble->estimated_cause ?: '' }}</div></td>
        </tr>
        <tr>
            <th>必要予備品</th>
            <td class="full" colspan="3"><div class="block">{{ $trouble->required_spare_parts ?: '' }}</div></td>
        </tr>
        <tr>
            <th>必要図面</th>
            <td class="full" colspan="3"><div class="block">{{ $trouble->required_drawings ?: '' }}</div></td>
        </tr>
    </table>

    <div class="section-label">承認欄</div>
    <table class="approvals">
        <tr>
            @foreach (\App\Enums\TroubleStatus::workflowSteps() as $step)
                @if ($step !== \App\Enums\TroubleStatus::Completed)
                    <th>{{ $step->label() }}</th>
                @endif
            @endforeach
        </tr>
        <tr>
            @foreach (\App\Enums\TroubleStatus::workflowSteps() as $step)
                @if ($step !== \App\Enums\TroubleStatus::Completed)
                    @php
                        $flag = $step->approvalFlagColumn();
                        $at = $step->approvalAtColumn();
                        $approved = $flag && $trouble->{$flag};
                    @endphp
                    <td class="stamp">
                        @if ($approved)
                            承認済<br>
                            {{ $at && $trouble->{$at} ? $trouble->{$at}->format('Y/m/d') : '' }}
                        @else
                            &nbsp;
                        @endif
                    </td>
                @endif
            @endforeach
        </tr>
    </table>

    <div class="footer">TMQ 現場トラブル管理 / 保全依頼表</div>
</body>
</html>
