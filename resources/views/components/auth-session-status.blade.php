@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-gaf-green']) }}>
        {{ $status }}
    </div>
@endif
