@extends('layouts.app')

@section('title', '設備を編集')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>設備を編集</h1>
            <p class="tmq-lead">{{ $machine->displayName() }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('machines.update', $machine) }}" class="tmq-form">
        @csrf
        @method('put')
        @include('machines._form', ['machine' => $machine])
        <div class="tmq-actions">
            <a class="tmq-btn tmq-btn--ghost" href="{{ route('machines.show', $machine) }}">キャンセル</a>
            <button type="submit" class="tmq-btn tmq-btn--primary">更新する</button>
        </div>
    </form>
@endsection
