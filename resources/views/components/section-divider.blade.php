@props(['type' => 'wave', 'color' => '#14532d', 'class' => ''])

@php
    $colorAttr = urlencode($color);
@endphp

@switch($type)
    @case('peak')
    <div class="w-full overflow-hidden leading-none {{ $class }}">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="w-full h-16 md:h-20">
            <path d="M0,0 C300,60 600,0 900,40 C1000,55 1100,45 1200,50 L1200,120 L0,120 Z" fill="{{ $color }}" fill-opacity="0.08"/>
            <path d="M0,10 C200,40 400,0 800,30 C1000,45 1100,35 1200,40 L1200,120 L0,120 Z" fill="{{ $color }}" fill-opacity="0.05"/>
        </svg>
    </div>
    @break

    @case('step')
    <div class="w-full overflow-hidden leading-none {{ $class }}">
        <svg viewBox="0 0 1200 100" preserveAspectRatio="none" class="w-full h-14 md:h-16">
            <path d="M0,0 L200,100 L400,0 L600,100 L800,0 L1000,100 L1200,0 L1200,100 L0,100 Z" fill="{{ $color }}" fill-opacity="0.06"/>
        </svg>
    </div>
    @break

    @case('blob')
    <div class="w-full overflow-hidden leading-none {{ $class }}">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="w-full h-20 md:h-24">
            <path d="M0,40 C200,0 400,120 600,60 C800,0 1000,100 1200,40 L1200,120 L0,120 Z" fill="{{ $color }}" fill-opacity="0.07"/>
            <ellipse cx="600" cy="70" rx="400" ry="30" fill="{{ $color }}" fill-opacity="0.04"/>
        </svg>
    </div>
    @break

    @default
    <div class="w-full overflow-hidden leading-none {{ $class }}">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="w-full h-16 md:h-20">
            <path d="M0,30 C100,60 200,0 300,30 C400,60 500,0 600,30 C700,60 800,0 900,30 C1000,60 1100,0 1200,30 L1200,120 L0,120 Z" fill="{{ $color }}" fill-opacity="0.06"/>
            <path d="M0,50 C150,20 300,80 450,40 C600,0 750,70 900,40 C1050,10 1150,50 1200,50 L1200,120 L0,120 Z" fill="{{ $color }}" fill-opacity="0.04"/>
        </svg>
    </div>
    @endswitch
