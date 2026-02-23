
<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
    <div class="flex justify-between flex-1 sm:hidden">
        @if (session('answer'))
            @if ($paginator->previousPageUrl())
                <button type="button" onclick="window.location.href='{{ $paginator->previousPageUrl() }}'" class="relative inline-flex items-center px-4 py-2 text-sm font-medium bg-dark text-white">
                    Anterior
                </button>
            @endif
            @if (session('question_delete'))
                <button type="button" onclick="submitDelete()" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium bg-danger text-white border-danger">
                    Excluir
                </button>
            @endif
            <button type="button" onclick="submitAnswer()" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium bg-success text-white border-success {{ $paginator->hasMorePages() ? ' ' : 'confirm' }}">
                {{ $paginator->hasMorePages() ? 'Responder' : 'Finalizar' }}
            </button>
            @if ($paginator->hasMorePages())
                <button type="button" onclick="submitEmpty()" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium bg-dark text-white border-dark">
                    Pular
                </button>
            @endif
        @else 
            @if ($paginator->previousPageUrl())
                <button type="button" onclick="window.location.href='{{ $paginator->previousPageUrl() }}'" class="relative inline-flex items-center px-4 py-2 text-sm font-medium bg-dark text-white">
                    Anterior
                </button>
            @endif
            @if ($paginator->hasMorePages())
                <button type="button" onclick="window.location.href='{{ $paginator->nextPageUrl() }}'" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium bg-dark text-white">
                    Próxima
                </button>
            @endif
        @endif
    </div>
</nav>

