@extends('layouts.app')

@section('title', '工具を編集')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>工具を編集</h1>
            <p class="tmq-lead">{{ $tool->displayName() }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('tools.update', $tool) }}" class="tmq-form">
        @csrf
        @method('put')
        @include('tools._form', ['tool' => $tool])
        <div class="tmq-actions">
            <a class="tmq-btn tmq-btn--ghost" href="{{ route('tools.show', $tool) }}">キャンセル</a>
            <button type="submit" class="tmq-btn tmq-btn--primary">更新する</button>
        </div>
    </form>
@endsection
