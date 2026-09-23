@extends('layouts.app')

@section('title', 'TO DOリスト')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>TO DOリスト</h1>
            <p class="tmq-lead">グループ別にタスクを確認できます。トラブル登録時に自動追加されます。</p>
        </div>
    </div>

    <form method="get" action="{{ route('todos.index') }}" class="tmq-filter">
        <label class="tmq-field">
            <span>グループ</span>
            <select name="group" onchange="this.form.submit()">
                <option value="">すべて</option>
                @foreach ($groups as $g)
                    <option value="{{ $g }}" @selected($group === $g)>{{ $g }}</option>
                @endforeach
            </select>
        </label>
    </form>

    <div class="tmq-table-wrap">
        <table class="tmq-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>グループ</th>
                    <th>タイトル</th>
                    <th>期限</th>
                    <th>関連トラブル</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($todos as $todo)
                    <tr @class(['is-done' => $todo->is_completed])>
                        <td>{{ $todo->is_completed ? '完了' : '未完了' }}</td>
                        <td>{{ $todo->group_name ?: '—' }}</td>
                        <td>{{ $todo->title }}</td>
                        <td>{{ optional($todo->due_on)->format('Y-m-d') ?? '—' }}</td>
                        <td>
                            @if ($todo->trouble)
                                <a href="{{ route('troubles.show', $todo->trouble) }}">#{{ $todo->trouble->id }} {{ $todo->trouble->title }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <form method="post" action="{{ route('todos.toggle', $todo) }}">
                                @csrf
                                <button type="submit" class="tmq-btn tmq-btn--ghost" style="padding:0.3rem 0.7rem">
                                    {{ $todo->is_completed ? '未完了に戻す' : '完了' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="tmq-empty">TO DO はありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $todos->links() }}
@endsection
