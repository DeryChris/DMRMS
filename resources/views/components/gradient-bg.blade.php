@props(['from' => '#14532d', 'via' => null, 'to' => '#0f2f1f', 'class' => '', 'angle' => '135'])

@php
    $gradient = $via
        ? "linear-gradient({$angle}deg, {$from} 0%, {$via} 50%, {$to} 100%)"
        : "linear-gradient({$angle}deg, {$from} 0%, {$to} 100%)";
@endphp

<div class="relative {{ $class }}" style="background: {{ $gradient }};">
    {{ $slot }}
</div>
