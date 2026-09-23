@extends('layouts.app')

@section('title', '申し送りを編集')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>申し送りを編集</h1>
            <p class="tmq-lead">{{ $memo->title }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('memos.update', $memo) }}" class="tmq-form" enctype="multipart/form-data">
        @csrf
        @method('put')
        @include('memos._form', ['memo' => $memo])
        <div class="tmq-actions">
            <a class="tmq-btn tmq-btn--ghost" href="{{ route('memos.show', $memo) }}">キャンセル</a>
            <button type="submit" class="tmq-btn tmq-btn--primary">更新する</button>
        </div>
    </form>
@endsection
