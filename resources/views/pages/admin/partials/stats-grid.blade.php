@php
    $stats = $stats ?? [];
    $toneClasses = [
        'primary' => 'text-primary',
        'success' => 'text-success',
        'warning' => 'text-warning',
        'danger' => 'text-danger',
        'info' => 'text-info',
    ];
@endphp

@if(!empty($stats))
    <div class="row g-3 mb-3">
        @foreach($stats as $stat)
            @php
                $tone = $stat['tone'] ?? null;
            @endphp
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card custom-card mb-0 h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">{{ $stat['label'] }}</p>
                        <h4 class="mb-1 {{ $tone && isset($toneClasses[$tone]) ? $toneClasses[$tone] : '' }}">
                            {{ $stat['value'] }}
                        </h4>
                        @if(!empty($stat['hint']))
                            <small class="text-muted d-block">{{ $stat['hint'] }}</small>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
