@php
    $actions = $actions ?? [];
@endphp

<div class="page-header d-flex flex-wrap align-items-start justify-content-between gap-3 my-3">
    <div>
        <h1 class="page-title mb-1">{{ $heading }}</h1>
        <p class="text-muted mb-0">{{ $summary }}</p>
    </div>
    @if(!empty($actions))
        <div class="d-flex flex-wrap gap-2">
            @foreach($actions as $action)
                <a href="{{ $action['url'] }}" class="{{ $action['class'] ?? 'btn btn-outline-primary btn-sm' }}">
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    @endif
</div>
