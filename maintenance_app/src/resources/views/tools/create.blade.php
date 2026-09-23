@extends('layouts.app')

@section('title', '工具を登録')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>工具を登録</h1>
            <p class="tmq-lead">管理番号と名称は必須です。</p>
        </div>
    </div>

    <form method="post" action="{{ route('tools.store') }}" class="tmq-form">
        @csrf
        @include('tools._form', ['tool' => null])
        <div class="tmq-actions">
            <a class="tmq-btn tmq-btn--ghost" href="{{ route('tools.index') }}">キャンセル</a>
            <button type="submit" class="tmq-btn tmq-btn--primary">登録する</button>
        </div>
    </form>
@endsection
