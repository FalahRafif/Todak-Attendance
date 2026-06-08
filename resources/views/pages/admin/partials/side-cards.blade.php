@php
    $cards = $cards ?? [];
@endphp

@if(!empty($cards))
    @foreach($cards as $card)
        <div class="card custom-card {{ $loop->last ? 'mb-0' : 'mb-3' }}">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $card['title'] }}</h5>
            </div>
            <div class="card-body">
                @if(!empty($card['items']))
                    <ul class="list-unstyled mb-0">
                        @foreach($card['items'] as $item)
                            <li class="d-flex justify-content-between gap-2 py-2 border-bottom">
                                <span class="text-muted">{{ $item['label'] }}</span>
                                <span class="{{ $item['class'] ?? 'text-dark text-end' }}">{{ $item['value'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if(!empty($card['lines']))
                    @foreach($card['lines'] as $line)
                        <p class="{{ $loop->last ? 'mb-0' : 'mb-2' }}">{{ $line }}</p>
                    @endforeach
                @endif

                @if(!empty($card['bullets']))
                    <ul class="mb-0 ps-3">
                        @foreach($card['bullets'] as $bullet)
                            <li class="{{ $loop->last ? 'mb-0' : 'mb-2' }}">{{ $bullet }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(!empty($card['actions']))
                    <div class="d-flex flex-wrap gap-2 {{ !empty($card['items']) || !empty($card['lines']) || !empty($card['bullets']) ? 'mt-3' : '' }}">
                        @foreach($card['actions'] as $action)
                            <a href="{{ $action['url'] }}" class="{{ $action['class'] ?? 'btn btn-outline-primary btn-sm' }}">
                                {{ $action['label'] }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endforeach
@endif
