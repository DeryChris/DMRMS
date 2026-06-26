@props(['class' => '', 'padding' => 'p-6'])

<div class="relative overflow-hidden rounded-xl border border-white/20 shadow-lg {{ $padding }} {{ $class }}" style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
    <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 50%, rgba(255,255,255,0.05) 100%);"></div>
    <div class="relative z-10">
        {{ $slot }}
    </div>
</div>
