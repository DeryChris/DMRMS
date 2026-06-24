@props(['title' => '', 'value' => 0, 'icon' => '', 'color' => 'gaf-green', 'trend' => null])

<div x-data="{
    displayValue: 0,
    target: {{ $value }},
    init() {
        let duration = 1500, steps = 60, interval = duration / steps, step = 0;
        let timer = setInterval(() => {
            step++;
            this.displayValue = Math.round((this.target / steps) * step);
            if(step >= steps) clearInterval(timer);
        }, interval);
    }
}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $title }}</p>
            <p class="text-3xl font-heading font-bold mt-1" style="color: var(--{{ $color }})" x-text="displayValue.toLocaleString()">0</p>
        </div>
        @if($icon)
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: color-mix(in srgb, var(--{{ $color }}) 15%, transparent)">
                {!! $icon !!}
            </div>
        @endif
    </div>
    @if($trend !== null)
        <div class="mt-2 flex items-center space-x-1">
            <svg class="w-4 h-4 {{ $trend >= 0 ? 'text-green-500' : 'text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $trend >= 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}"/>
            </svg>
            <span class="text-xs font-medium {{ $trend >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ abs($trend) }}%</span>
            <span class="text-xs text-gray-400">vs last month</span>
        </div>
    @endif
</div>
