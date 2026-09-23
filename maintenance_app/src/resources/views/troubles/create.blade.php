@extends('layouts.app')

@section('title', 'トラブル新規登録')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>トラブル新規登録</h1>
            <p class="tmq-lead">テキストエリア横のマイクボタンで音声入力できます（Chrome / Edge 推奨）。</p>
        </div>
    </div>

    <form method="post" action="{{ route('troubles.store') }}" class="tmq-form" novalidate>
        @csrf

        <section class="tmq-card">
            <h2>分類</h2>
            <div class="tmq-grid tmq-grid--3">
                <label class="tmq-field">
                    <span>大分類（設備） <em>*</em></span>
                    <input list="major-categories" name="category_major" value="{{ old('category_major', '設備') }}" required>
                    <datalist id="major-categories">
                        @foreach ($majorCategories as $item)
                            <option value="{{ $item }}"></option>
                        @endforeach
                    </datalist>
                </label>
                <label class="tmq-field">
                    <span>中分類（エリア・機器）</span>
                    <input type="text" name="category_middle" value="{{ old('category_middle') }}" placeholder="例: 反応槽A / ポンプP-101">
                </label>
                <label class="tmq-field">
                    <span>小分類（計器・パーツ）</span>
                    <input type="text" name="category_minor" value="{{ old('category_minor') }}" placeholder="例: 圧力計 / シール">
                </label>
            </div>
            <label class="tmq-field">
                <span>関連設備</span>
                <select name="machine_id">
                    <option value="">なし</option>
                    @foreach ($machines as $machine)
                        <option value="{{ $machine->id }}" @selected((string) old('machine_id') === (string) $machine->id)>
                            {{ $machine->displayName() }}
                        </option>
                    @endforeach
                </select>
            </label>
        </section>

        <section class="tmq-card">
            <h2>トラブル内容</h2>
            <label class="tmq-field">
                <span>件名 <em>*</em></span>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="255">
            </label>

            <x-speech-textarea
                name="content"
                label="内容"
                :value="old('content')"
                rows="4"
                placeholder="発生状況・現象を記入"
            />

            <x-speech-textarea
                name="investigation"
                label="調査内容"
                :value="old('investigation')"
                rows="4"
                placeholder="現地確認・測定結果など"
            />

            <x-speech-textarea
                name="estimated_cause"
                label="推定原因"
                :value="old('estimated_cause')"
                rows="3"
                placeholder="現時点での推定原因"
            />
        </section>

        <section class="tmq-card">
            <h2>日程・必要資材</h2>
            <div class="tmq-grid tmq-grid--2">
                <label class="tmq-field">
                    <span>発生日</span>
                    <input type="date" name="occurred_on" value="{{ old('occurred_on', now()->toDateString()) }}">
                </label>
                <label class="tmq-field">
                    <span>補修依頼日</span>
                    <input type="date" name="repair_requested_on" value="{{ old('repair_requested_on') }}">
                </label>
            </div>

            <x-speech-textarea
                name="required_spare_parts"
                label="必要予備品"
                :value="old('required_spare_parts')"
                rows="3"
                placeholder="品名・数量など"
            />

            <x-speech-textarea
                name="required_drawings"
                label="必要図面"
                :value="old('required_drawings')"
                rows="3"
                placeholder="図面番号・名称など"
            />
        </section>

        <section class="tmq-card">
            <h2>作成情報</h2>
            <div class="tmq-grid tmq-grid--2">
                <label class="tmq-field">
                    <span>作成Gr <em>*</em></span>
                    <input list="groups" name="created_group" value="{{ old('created_group', auth()->user()->group_name) }}" required>
                    <datalist id="groups">
                        @foreach ($groups as $group)
                            <option value="{{ $group }}"></option>
                        @endforeach
                    </datalist>
                </label>
                <label class="tmq-field">
                    <span>報告者 <em>*</em></span>
                    <input type="text" name="reporter_name" value="{{ old('reporter_name', auth()->user()->name) }}" required>
                </label>
            </div>
            <p class="tmq-hint">登録後、作成Gr（承認対応）と保全Gr（補修準備）の TO DO が自動作成されます。</p>
        </section>

        <div class="tmq-actions">
            <a class="tmq-btn tmq-btn--ghost" href="{{ route('troubles.index') }}">キャンセル</a>
            <button type="submit" class="tmq-btn tmq-btn--primary">登録する</button>
        </div>
    </form>
@endsection
