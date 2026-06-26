@props(['name' => 'topography', 'opacity' => '0.03', 'color' => '#14532d', 'class' => ''])

@php
    $patterns = [
        'topography' => '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><g fill="' . $color . '" fill-opacity="' . $opacity . '"><path d="M0 0 L200 0 L200 200 L0 200 Z M30 30 Q60 10 100 30 Q140 50 170 30"/><path d="M10 80 Q40 60 80 80 Q120 100 160 80"/><path d="M20 130 Q60 110 100 130 Q140 150 180 130"/><path d="M5 170 Q50 150 100 170 Q150 190 195 170"/></g></svg>',
        'hexagons' => '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="104" viewBox="0 0 120 104"><g fill="' . $color . '" fill-opacity="' . $opacity . '"><polygon points="30,2 60,18 60,52 30,68 0,52 0,18"/><polygon points="90,2 120,18 120,52 90,68 60,52 60,18"/><polygon points="0,52 30,68 30,102 0,118 -30,102 -30,68"/><polygon points="60,52 90,68 90,102 60,118 30,102 30,68"/><polygon points="120,52 150,68 150,102 120,118 90,102 90,68"/></g></svg>',
        'jigsaw' => '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><g fill="' . $color . '" fill-opacity="' . $opacity . '"><path d="M0 0 H50 V50 H0 Z M50 0 H100 V25 Q75 25 75 50 H50 Z M0 50 H25 Q25 75 50 75 V100 H0 Z M50 50 H100 V100 H50 Z"/></g></svg>',
        'circuit-board' => '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><g fill="' . $color . '" fill-opacity="' . $opacity . '"><circle cx="10" cy="10" r="2"/><circle cx="50" cy="10" r="2"/><circle cx="90" cy="10" r="2"/><circle cx="10" cy="50" r="2"/><circle cx="50" cy="50" r="3"/><circle cx="90" cy="50" r="2"/><circle cx="10" cy="90" r="2"/><circle cx="50" cy="90" r="2"/><circle cx="90" cy="90" r="2"/><line x1="10" y1="10" x2="50" y2="10" stroke="' . $color . '" stroke-opacity="' . $opacity . '" stroke-width="0.5"/><line x1="50" y1="10" x2="90" y2="10" stroke="' . $color . '" stroke-opacity="' . $opacity . '" stroke-width="0.5"/><line x1="10" y1="10" x2="10" y2="50" stroke="' . $color . '" stroke-opacity="' . $opacity . '" stroke-width="0.5"/><line x1="10" y1="50" x2="10" y2="90" stroke="' . $color . '" stroke-opacity="' . $opacity . '" stroke-width="0.5"/><line x1="90" y1="10" x2="90" y2="50" stroke="' . $color . '" stroke-opacity="' . $opacity . '" stroke-width="0.5"/><line x1="50" y1="50" x2="50" y2="90" stroke="' . $color . '" stroke-opacity="' . $opacity . '" stroke-width="0.5"/><line x1="90" y1="50" x2="90" y2="90" stroke="' . $color . '" stroke-opacity="' . $opacity . '" stroke-width="0.5"/><line x1="10" y1="90" x2="50" y2="90" stroke="' . $color . '" stroke-opacity="' . $opacity . '" stroke-width="0.5"/><line x1="50" y1="90" x2="90" y2="90" stroke="' . $color . '" stroke-opacity="' . $opacity . '" stroke-width="0.5"/></g></svg>',
        'camo-lite' => '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><g fill="' . $color . '" fill-opacity="' . $opacity . '"><circle cx="30" cy="30" r="20"/><circle cx="170" cy="40" r="25"/><circle cx="100" cy="170" r="30"/><circle cx="60" cy="140" r="15"/><circle cx="150" cy="130" r="18"/><circle cx="20" cy="100" r="22"/><circle cx="180" cy="160" r="12"/><circle cx="80" cy="80" r="10"/><circle cx="130" cy="20" r="14"/><ellipse cx="100" cy="100" rx="35" ry="20"/><ellipse cx="40" cy="180" rx="20" ry="12"/><ellipse cx="160" cy="90" rx="25" ry="15"/></g></svg>',
    ];

    $svg = $patterns[$name] ?? $patterns['topography'];
    $encoded = svg_encode($svg);
@endphp

<div class="relative {{ $class }}">
    <div class="absolute inset-0 pointer-events-none" style="background-image: url('data:image/svg+xml;base64,{{ $encoded }}'); background-repeat: repeat; background-size: auto;"></div>
    <div class="relative z-10">
        {{ $slot }}
    </div>
</div>
