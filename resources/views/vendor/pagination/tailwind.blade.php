
<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
    <div class="flex justify-between flex-1 sm:hidden">
        @if ($paginator->previousPageUrl())
           
            <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium bg-dark text-white">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if (session('answer'))
            <a onclick="submitDelete()" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium bg-danger text-white">
                Excluir
            </a>
            <a onclick="submitAnswer()" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium bg-success text-white">
                Responder
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium bg-dark text-white">
                {!! __('pagination.next') !!}
            </a>
        @endif
    </div>
</nav>

