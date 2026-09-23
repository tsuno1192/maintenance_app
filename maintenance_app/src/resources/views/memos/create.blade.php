@extends('layouts.app')

@section('title', '申し送りを書く')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>申し送りを書く</h1>
            <p class="tmq-lead">写真は JPEG / PNG / WebP（各 5MB まで、最大 8 枚）を添付できます。</p>
        </div>
    </div>

    <form method="post" action="{{ route('memos.store') }}" class="tmq-form" enctype="multipart/form-data">
        @csrf
        @include('memos._form', ['memo' => null])
        <div class="tmq-actions">
            <a class="tmq-btn tmq-btn--ghost" href="{{ route('memos.index') }}">キャンセル</a>
            <button type="submit" class="tmq-btn tmq-btn--primary">登録する</button>
        </div>
    </form>
@endsection
