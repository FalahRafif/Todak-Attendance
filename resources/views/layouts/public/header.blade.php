@php
    $rawGuestMenu = config('role_access.guest.menu', []);
    $guestCta = config('role_access.guest.cta', []);
    $guestMenuSections = $rawGuestMenu;

    // Backward compatibility for old flat structure: [ ['label' => ...], ... ]
    if (!empty($guestMenuSections) && isset($guestMenuSections[0]['label'])) {
        $guestMenuSections = [
            [
                'section' => 'Main',
                'items' => $guestMenuSections,
            ],
        ];
    }

    $buildGuestMenuUrl = static function (array $item): string {
        $routeName = $item['route'] ?? 'home';
        $fragment = trim((string) ($item['fragment'] ?? ''));
        $url = route($routeName);

        return $fragment !== '' ? "{$url}#{$fragment}" : $url;
    };
@endphp

<header class="public-header">
    <div class="container header-inner">
        <a class="brand" href="{{ url('/') }}" aria-label="Beranda Etherno">
            @php
                $icon = public_path('assets/etherno/public/icon_trans_2.png');
                $fallback = public_path('assets/images/photos/aboutmain.jpg');
            @endphp
            @if(file_exists($icon))
                <img src="{{ asset('assets/etherno/public/icon_trans_2.png') }}" alt="Etherno" class="brand-logo">
            @elseif(file_exists($fallback))
                <img src="{{ asset('assets/images/photos/aboutmain.jpg') }}" alt="Etherno" class="brand-logo">
            @else
                <span class="brand-text">Etherno</span>
            @endif
        </a>

        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="public-menu" aria-label="Buka menu navigasi">
            <span class="menu-toggle-bar" aria-hidden="true"></span>
            <span class="menu-toggle-bar" aria-hidden="true"></span>
            <span class="menu-toggle-bar" aria-hidden="true"></span>
        </button>

        <div class="header-menu" id="public-menu">
            <nav class="nav small-uppercase" aria-label="Navigasi utama">
                @foreach ($guestMenuSections as $section)
                    @foreach (($section['items'] ?? []) as $item)
                        @if (($item['type'] ?? 'link') === 'dropdown' && !empty($item['items']))
                            <details class="nav-dropdown">
                                <summary>{{ $item['label'] ?? '' }}</summary>
                                <div class="nav-dropdown-menu">
                                    @foreach ($item['items'] as $dropdownItem)
                                        <a href="{{ $buildGuestMenuUrl($dropdownItem) }}">{{ $dropdownItem['label'] ?? '' }}</a>
                                    @endforeach
                                </div>
                            </details>
                        @else
                            <a href="{{ $buildGuestMenuUrl($item) }}">{{ $item['label'] ?? '' }}</a>
                        @endif
                    @endforeach
                @endforeach
            </nav>

            @php
                $ctaLabel = $guestCta['label'] ?? 'Booking Sekarang';
                $ctaAriaLabel = $guestCta['aria_label'] ?? $ctaLabel;
                $ctaRoute = $guestCta['route'] ?? 'booking.page';
            @endphp
            <a class="cta header-cta" href="{{ route($ctaRoute) }}" aria-label="{{ $ctaAriaLabel }}">{{ $ctaLabel }}</a>
        </div>
    </div>
</header>
