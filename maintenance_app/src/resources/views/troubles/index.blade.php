@extends('layouts.app')

@section('title', 'トラブル一覧')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>トラブル一覧</h1>
            <p class="tmq-lead">現場で発生したトラブルの登録・進捗を管理します。</p>
        </div>
        <a class="tmq-btn tmq-btn--primary" href="{{ route('troubles.create') }}">新規登録</a>
    </div>

    <div class="tmq-table-wrap">
        <table class="tmq-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>件名</th>
                    <th>設備</th>
                    <th>分類</th>
                    <th>作成Gr</th>
                    <th>状態</th>
                    <th>発生日</th>
                    <th>TO DO</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($troubles as $trouble)
                    <tr>
                        <td>{{ $trouble->id }}</td>
                        <td><a href="{{ route('troubles.show', $trouble) }}">{{ $trouble->title }}</a></td>
                        <td>
                            @if ($trouble->machine)
                                <a href="{{ route('machines.show', $trouble->machine) }}">{{ $trouble->machine->code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $trouble->category_major }}@if($trouble->category_middle) / {{ $trouble->category_middle }}@endif</td>
                        <td>{{ $trouble->created_group }}</td>
                        <td><span class="tmq-badge">{{ $trouble->status->label() }}</span></td>
                        <td>{{ optional($trouble->occurred_on)->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $trouble->todos_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="tmq-empty">登録されたトラブルはありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $troubles->links() }}
@endsection
