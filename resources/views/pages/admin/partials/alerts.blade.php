@php
    $alerts = $alerts ?? [];
@endphp

@if(!empty($alerts))
    @foreach($alerts as $alert)
        <div class="alert {{ $alert['class'] ?? 'alert-info' }} mb-3" role="alert">
            {{ $alert['text'] }}
        </div>
    @endforeach
@endif
