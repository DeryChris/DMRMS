@props(['route', 'icon', 'label', 'badge' => null])
@php
    $active = request()->routeIs($route);
@endphp
<a href="{{ route($route) }}" 
   class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group relative"
   x-bind:class="sidebarCollapsed ? 'justify-center' : ''"
   @class([
       'text-white bg-white/15 shadow-sm' => $active,
       'text-white/60 hover:text-white hover:bg-white/10' => !$active,
   ])>
    @if($active)
    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gaf-khaki rounded-r-full shadow-[0_0_6px_rgba(212,175,55,0.5)]"></span>
    @endif
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
    </svg>
    <span x-show="!sidebarCollapsed" class="flex-1 truncate">{{ $label }}</span>
    @if($badge && !sidebarCollapsed)
    <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $badge['class'] ?? 'bg-white/20 text-white' }}">{{ $badge['text'] }}</span>
    @endif
</a>