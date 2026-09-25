@if($paginator->hasPages())
<nav class="flex items-center justify-between rounded-xl border border-[#e2e8f0] bg-white px-4 py-3 shadow-sm" aria-label="Pagination">
    <div class="text-sm text-[#64748b]">
        Showing <span class="font-semibold text-[#1e293b]">{{ $paginator->firstItem() }}</span>
        to <span class="font-semibold text-[#1e293b]">{{ $paginator->lastItem() }}</span>
        of <span class="font-semibold text-[#1e293b]">{{ $paginator->total() }}</span> results
    </div>
    <div class="flex items-center gap-1">
        {{-- Previous --}}
        @if($paginator->onFirstPage())
        <span class="inline-flex h-8 w-8 items-center justify-center rounded border border-[#e2e8f0] text-[#94a3b8] cursor-not-allowed">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded border border-[#e2e8f0] text-[#1e293b] hover:bg-[#f8fafc] transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </a>
        @endif

        {{-- Page Numbers --}}
        @foreach($elements as $element)
            @if(is_string($element))
            <span class="px-2 text-sm text-[#94a3b8]">...</span>
            @elseif(is_array($element))
                @foreach($element as $page => $url)
                    @if($page == $paginator->currentPage())
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded bg-[#2563eb] text-xs font-bold text-white">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="inline-flex h-8 w-8 items-center justify-center rounded border border-[#e2e8f0] text-xs font-medium text-[#1e293b] hover:bg-[#f8fafc] transition-colors">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded border border-[#e2e8f0] text-[#1e293b] hover:bg-[#f8fafc] transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        </a>
        @else
        <span class="inline-flex h-8 w-8 items-center justify-center rounded border border-[#e2e8f0] text-[#94a3b8] cursor-not-allowed">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        </span>
        @endif
    </div>
</nav>
@endif
