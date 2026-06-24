@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-gaf-green focus:ring-gaf-green rounded-md shadow-sm']) }}>
