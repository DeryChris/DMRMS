@props(['count' => 3, 'colors' => null, 'class' => ''])

@php
    $defaultColors = ['#D4AF37', '#14532d', '#9B2226', '#166534', '#5fa489'];
    $palette = $colors ? explode(',', $colors) : $defaultColors;
@endphp

<div class="absolute inset-0 pointer-events-none overflow-hidden {{ $class }}">
    @for($i = 0; $i < $count; $i++)
        @php
            $size = rand(40, 120);
            $top = rand(0, 100);
            $left = rand(0, 100);
            $delay = $i * 2;
            $duration = rand(6, 12);
            $color = $palette[$i % count($palette)];
            $shape = rand(0, 2);
        @endphp
        @if($shape === 0)
        <div class="absolute rounded-full opacity-10 animate-float"
             style="width: {{ $size }}px; height: {{ $size }}px; top: {{ $top }}%; left: {{ $left }}%; background: {{ $color }}; animation-delay: {{ $delay }}s; animation-duration: {{ $duration }}s;"></div>
        @elseif($shape === 1)
        <div class="absolute opacity-8 animate-float"
             style="width: {{ $size }}px; height: {{ $size }}px; top: {{ $top }}%; left: {{ $left }}%; border: 2px solid {{ $color }}; border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; animation-delay: {{ $delay }}s; animation-duration: {{ $duration }}s;"></div>
        @else
        <div class="absolute opacity-5 animate-float"
             style="width: {{ $size * 0.7 }}px; height: {{ $size * 0.7 }}px; top: {{ $top }}%; left: {{ $left }}%; background: {{ $color }}; clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%); animation-delay: {{ $delay }}s; animation-duration: {{ $duration }}s;"></div>
        @endif
    @endfor
</div>
