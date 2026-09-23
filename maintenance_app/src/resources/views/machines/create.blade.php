@extends('layouts.app')

@section('title', '設備を登録')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>設備を登録</h1>
            <p class="tmq-lead">設備番号と名称は必須です。</p>
        </div>
    </div>

    <form method="post" action="{{ route('machines.store') }}" class="tmq-form">
        @csrf
        @include('machines._form', ['machine' => null])
        <div class="tmq-actions">
            <a class="tmq-btn tmq-btn--ghost" href="{{ route('machines.index') }}">キャンセル</a>
            <button type="submit" class="tmq-btn tmq-btn--primary">登録する</button>
        </div>
    </form>
@endsection
