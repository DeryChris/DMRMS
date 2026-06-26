@props(['name' => 'empty', 'class' => '', 'size' => null])

@php
    $svgService = app(\App\Services\Media\SvgService::class);
    $svg = $svgService->illustration($name);
    $sizeStyle = $size ? "width: {$size}px; height: {$size}px;" : '';
@endphp

<div class="illustration-container {{ $class }}" style="{{ $sizeStyle }}">{!! $svg !!}</div>
