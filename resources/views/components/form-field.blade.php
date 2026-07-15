@props([
    'name' => '',
    'label' => '',
    'type' => 'text',
    'value' => '',
    'options' => [],
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'help' => '',
    'max' => null,
    'min' => null,
    'step' => null,
    'rows' => 3,
    'multiple' => false,
])

@php
    $hasError = $errors->has($name);
    $borderClass = $hasError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-gaf-green focus:ring-gaf-green';
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)<span class="text-red-500 ml-0.5">*</span>@endif
        </label>
    @endif

    @if($type === 'select')
        <select
            name="{{ $name }}"
            id="{{ $name }}"
            @if($disabled) disabled @endif
            @if($multiple) multiple @endif
            {{ $attributes->merge(['class' => 'w-full rounded-lg px-4 py-3 text-sm border ' . $borderClass . ' bg-white shadow-sm']) }}
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach($options as $val => $optLabel)
                <option value="{{ $val }}" {{ old($name, $value) == $val ? 'selected' : '' }}>{{ $optLabel }}</option>
            @endforeach
        </select>

    @elseif($type === 'textarea')
        <textarea
            name="{{ $name }}"
            id="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            @if($disabled) disabled @endif
            {{ $attributes->merge(['class' => 'w-full rounded-lg px-4 py-3 text-sm border ' . $borderClass . ' shadow-sm']) }}
        >{{ old($name, $value) }}</textarea>

    @else
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if($disabled) disabled @endif
            @if($max) max="{{ $max }}" @endif
            @if($min) min="{{ $min }}" @endif
            @if($step) step="{{ $step }}" @endif
            {{ $attributes->merge(['class' => 'w-full rounded-lg px-4 py-3 text-sm border ' . $borderClass . ' shadow-sm']) }}
        >
    @endif

    @if($help && !$hasError)
        <p class="text-gray-500 text-xs mt-1">{{ $help }}</p>
    @endif

    @error($name)
        <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>
