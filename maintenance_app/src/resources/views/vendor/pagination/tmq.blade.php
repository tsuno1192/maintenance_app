@if ($paginator->hasPages())
    <nav class="tmq-pagination" role="navigation" aria-label="ページネーション">
        @if ($paginator->onFirstPage())
            <span class="is-disabled">前へ</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">前へ</a>
        @endif

        <span class="tmq-pagination__status">
            {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
        </span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">次へ</a>
        @else
            <span class="is-disabled">次へ</span>
        @endif
    </nav>
@endif
