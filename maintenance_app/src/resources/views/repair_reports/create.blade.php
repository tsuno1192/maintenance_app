@extends('layouts.app')

@section('title', '完了報告の登録')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>完了報告の登録</h1>
            <p class="tmq-lead">現場補修完了後の報告を入力します。保存すると完了報告として登録され、承認フローが開始されます。</p>
        </div>
    </div>

    <section class="tmq-card">
        <h2>関連トラブル（参照）</h2>
        <dl class="tmq-dl">
            <dt>トラブル</dt>
            <dd><a href="{{ route('troubles.show', $trouble) }}">#{{ $trouble->id }} {{ $trouble->title }}</a></dd>
            <dt>分類</dt>
            <dd>
                {{ $trouble->category_major }}
                @if ($trouble->category_middle) / {{ $trouble->category_middle }} @endif
                @if ($trouble->category_minor) / {{ $trouble->category_minor }} @endif
            </dd>
            <dt>推定原因（依頼時）</dt>
            <dd>{{ $trouble->estimated_cause ?: '—' }}</dd>
            <dt>必要予備品（依頼時）</dt>
            <dd>{{ $trouble->required_spare_parts ?: '—' }}</dd>
        </dl>
    </section>

    <form method="post" action="{{ route('repair-reports.store', $trouble) }}" class="tmq-form">
        @csrf

        <section class="tmq-card">
            <h2>分類</h2>
            <div class="tmq-grid tmq-grid--3">
                <label class="tmq-field">
                    <span>大分類 <em>*</em></span>
                    <input type="text" name="category_major" value="{{ old('category_major', $defaults['category_major']) }}" required>
                </label>
                <label class="tmq-field">
                    <span>中分類</span>
                    <input type="text" name="category_middle" value="{{ old('category_middle', $defaults['category_middle']) }}">
                </label>
                <label class="tmq-field">
                    <span>小分類</span>
                    <input type="text" name="category_minor" value="{{ old('category_minor', $defaults['category_minor']) }}">
                </label>
            </div>
        </section>

        <section class="tmq-card">
            <h2>発生事象</h2>
            <label class="tmq-field">
                <span>発生事象件名 <em>*</em></span>
                <input type="text" name="title" value="{{ old('title', $defaults['title']) }}" required maxlength="255">
            </label>

            <x-speech-textarea
                name="content"
                label="内容"
                :value="old('content', $defaults['content'])"
                rows="4"
                placeholder="発生事象の概要"
            />
            <x-speech-textarea
                name="investigation_result"
                label="調査結果"
                :value="old('investigation_result', $defaults['investigation_result'])"
                rows="4"
                placeholder="現地調査・分解確認などの結果"
            />
            <x-speech-textarea
                name="cause"
                label="発生原因"
                :value="old('cause', $defaults['cause'])"
                rows="3"
                placeholder="確定した発生原因"
            />
        </section>

        <section class="tmq-card">
            <h2>補修・動作確認</h2>
            <label class="tmq-field">
                <span>補修日 <em>*</em></span>
                <input type="date" name="repaired_on" value="{{ old('repaired_on', $defaults['repaired_on']) }}" required>
            </label>
            <x-speech-textarea
                name="used_spare_parts"
                label="使用予備品"
                :value="old('used_spare_parts', $defaults['used_spare_parts'])"
                rows="3"
                placeholder="使用した部品・数量"
            />
            <x-speech-textarea
                name="used_drawings"
                label="使用図面"
                :value="old('used_drawings')"
                rows="3"
                placeholder="参照した図面番号"
            />
            <x-speech-textarea
                name="trial_run_result"
                label="試運転結果"
                :value="old('trial_run_result')"
                rows="3"
                placeholder="試運転の合否・所見"
                :required="true"
            />
            <x-speech-textarea
                name="operation_records"
                label="各種動作記録"
                :value="old('operation_records')"
                rows="4"
                placeholder="振動・温度・電流など動作記録"
            />
        </section>

        <div class="tmq-actions">
            <a class="tmq-btn tmq-btn--ghost" href="{{ route('troubles.show', $trouble) }}">キャンセル</a>
            <button type="submit" class="tmq-btn tmq-btn--primary">完了報告を保存</button>
        </div>
    </form>
@endsection
