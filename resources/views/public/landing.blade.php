@extends('layouts.app')

@section('title', 'Home - Ghana Armed Forces')

@push('styles')
<style>
.slide-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 1.2s ease, transform 1.2s ease; }
.slide-img.anim-fade { animation: bgFade 5s ease forwards; }
.slide-img.anim-zoom { animation: bgZoom 6s ease forwards; }
.slide-img.anim-slide-right { animation: bgSlideRight 5s ease forwards; }
.slide-img.anim-slide-up { animation: bgSlideUp 5s ease forwards; }
@keyframes bgFade { 0% { opacity: 0.08; } 15% { opacity: 0.35; } 85% { opacity: 0.35; } 100% { opacity: 0.08; } }
@keyframes bgZoom { 0% { opacity: 0.08; transform: scale(1); } 10% { opacity: 0.35; transform: scale(1); } 90% { opacity: 0.35; transform: scale(1.15); } 100% { opacity: 0.08; transform: scale(1.2); } }
@keyframes bgSlideRight { 0% { opacity: 0.08; transform: translateX(-30px); } 15% { opacity: 0.35; transform: translateX(0); } 85% { opacity: 0.35; transform: translateX(0); } 100% { opacity: 0.08; transform: translateX(30px); } }
@keyframes bgSlideUp { 0% { opacity: 0.08; transform: translateY(30px); } 15% { opacity: 0.35; transform: translateY(0); } 85% { opacity: 0.35; transform: translateY(0); } 100% { opacity: 0.08; transform: translateY(-30px); } }
.slide-indicator { transition: all 0.5s ease; }
</style>
@endpush

@section('hero')
<div class="flag-strip"></div>
<div class="text-white relative overflow-hidden" x-data="heroSlideshow()">
    <div class="absolute inset-0" style="background:rgb(20,92,49);">
        <template x-for="(slide, i) in slides" :key="i">
            <img :src="slide.src" :class="['slide-img', slide.cls]" :alt="slide.alt" x-show="i === currentIndex || i === prevIndex">
        </template>
    </div>
    <div class="absolute inset-0 z-[5]" style="background:rgba(20,92,49,0.12);pointer-events:none;"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 relative z-10">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="md:w-1/2 text-center md:text-left mb-8 md:mb-0">
                <div class="portal-badge mb-6 mx-auto md:mx-0 w-fit">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    Official Recruitment Portal
                </div>
                <h1 class="font-heading font-bold text-3xl md:text-5xl leading-tight mb-4">Ghana Armed Forces<br>Recruitment Portal</h1>
                <p class="text-gray-300 text-lg mb-8">Join the ranks of the brave. Apply now for the current recruitment cycle.</p>
                <div x-data="{ countdown: { days: 0, hours: 0, minutes: 0, seconds: 0 }, init() {
                    let end = new Date();
                    end.setDate(end.getDate() + 30);
                    setInterval(() => {
                        let diff = end - new Date();
                        if(diff <= 0) return;
                        this.countdown.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                        this.countdown.hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
                        this.countdown.minutes = Math.floor((diff / (1000 * 60)) % 60);
                        this.countdown.seconds = Math.floor((diff / 1000) % 60);
                    }, 1000);
                }}">
                    <div class="flex space-x-4 justify-center md:justify-start mb-8">
                        <div class="text-center"><span class="text-3xl font-heading font-bold text-gaf-khaki" x-text="countdown.days">0</span><p class="text-xs text-gray-400">Days</p></div>
                        <div class="text-center"><span class="text-3xl font-heading font-bold text-gaf-khaki" x-text="countdown.hours">0</span><p class="text-xs text-gray-400">Hours</p></div>
                        <div class="text-center"><span class="text-3xl font-heading font-bold text-gaf-khaki" x-text="countdown.minutes">0</span><p class="text-xs text-gray-400">Min</p></div>
                        <div class="text-center"><span class="text-3xl font-heading font-bold text-gaf-khaki" x-text="countdown.seconds">0</span><p class="text-xs text-gray-400">Sec</p></div>
                    </div>
                </div>
                <a href="{{ route('register') }}" class="inline-block bg-gaf-khaki text-gaf-dark-green px-8 py-4 rounded-lg font-heading font-bold text-lg hover:bg-yellow-500 transition transform hover:scale-105 shadow-lg">Apply Now</a>
            </div>
            <div class="md:w-1/2 flex justify-center">
                <img src="{{ asset('assets/images/hero/img1.png') }}" alt="GAF Recruitment" class="w-full max-w-md rounded-xl shadow-2xl border-4 border-gaf-khaki/30">
            </div>
        </div>
    </div>
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex space-x-2">
        <template x-for="(slide, i) in slides" :key="'dot'+i">
            <button @click="goTo(i)" :class="['w-2.5 h-2.5 rounded-full slide-indicator', i === currentIndex ? 'bg-gaf-khaki w-6' : 'bg-white/40 hover:bg-white/70']"></button>
        </template>
    </div>
</div>
<script>
function heroSlideshow() {
    const anims = ['anim-fade', 'anim-zoom', 'anim-slide-right', 'anim-slide-up'];
    return {
        slides: @json($images),
        currentIndex: 0,
        prevIndex: -1,
        timer: null,
        init() {
            this.slides.forEach((s, i) => s.cls = anims[i % anims.length]);
            this.start();
        },
        start() {
            this.timer = setInterval(() => { this.next(); }, 5000);
        },
        stop() {
            if (this.timer) { clearInterval(this.timer); this.timer = null; }
        },
        next() {
            this.prevIndex = this.currentIndex;
            this.currentIndex = (this.currentIndex + 1) % this.slides.length;
        },
        goTo(i) {
            if (i === this.currentIndex) return;
            this.stop();
            this.prevIndex = this.currentIndex;
            this.currentIndex = i;
            this.start();
        }
    }
}
</script>
@endsection

@section('content')
<div x-data="{
    stats: { total: 0, shortlisted: 0, screening: 0, selected: 0 },
    init() {
        let targets = { total: 15420, shortlisted: 3200, screening: 1800, selected: 750 };
        let duration = 2000, steps = 60, interval = duration / steps;
        let step = 0;
        let timer = setInterval(() => {
            step++;
            this.stats.total = Math.round((targets.total / steps) * step);
            this.stats.shortlisted = Math.round((targets.shortlisted / steps) * step);
            this.stats.screening = Math.round((targets.screening / steps) * step);
            this.stats.selected = Math.round((targets.selected / steps) * step);
            if(step >= steps) clearInterval(timer);
        }, interval);
    }
}" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 text-center border-t-4 border-gaf-red">
            <p class="text-4xl font-heading font-bold text-gaf-green" x-text="stats.total.toLocaleString()">0</p>
            <p class="text-sm text-gray-500 mt-1">Total Applicants</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 text-center border-t-4 border-gaf-khaki">
            <p class="text-4xl font-heading font-bold text-gaf-green" x-text="stats.shortlisted.toLocaleString()">0</p>
            <p class="text-sm text-gray-500 mt-1">Shortlisted</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 text-center border-t-4 border-gaf-green">
            <p class="text-4xl font-heading font-bold text-gaf-green" x-text="stats.screening.toLocaleString()">0</p>
            <p class="text-sm text-gray-500 mt-1">Screening</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 text-center border-t-4 border-gaf-green">
            <p class="text-4xl font-heading font-bold text-gaf-green" x-text="stats.selected.toLocaleString()">0</p>
            <p class="text-sm text-gray-500 mt-1">Selected</p>
        </div>
    </div>
</div>

<div class="bg-gray-100 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="font-heading font-bold text-3xl text-center text-gaf-green mb-4">Check Your Eligibility</h2>
        <p class="text-gray-500 text-center text-sm mb-12">Quickly check if you meet the basic requirements before applying.</p>
        <div x-data="{ age: '', nationality: '', education: '', result: null }" class="max-w-lg mx-auto bg-white rounded-xl shadow-lg p-8">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Age</label>
                    <select x-model="age" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-green">
                        <option value="">Select...</option>
                        <option value="under18">Under 18</option>
                        <option value="18-25">18 - 25</option>
                        <option value="26-35">26 - 35</option>
                        <option value="over35">Over 35</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                    <select x-model="nationality" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-green">
                        <option value="">Select...</option>
                        <option value="ghanaian">Ghanaian</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Education Level</label>
                    <select x-model="education" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-green">
                        <option value="">Select...</option>
                        <option value="below_ssce">Below SSCE/WASSCE</option>
                        <option value="ssce">SSCE/WASSCE</option>
                        <option value="tertiary">Tertiary</option>
                    </select>
                </div>
                <button @click="result = (age === '18-25' || age === '26-35') && nationality === 'ghanaian' && (education === 'ssce' || education === 'tertiary')" class="w-full bg-gaf-green text-white py-3 rounded-lg font-semibold hover:bg-gaf-dark-green transition">Check Eligibility</button>
            </div>
            <div x-show="result !== null" x-cloak class="mt-6 p-4 rounded-lg" :class="result ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <p class="font-semibold text-center" :class="result ? 'text-green-700' : 'text-red-700'" x-text="result ? 'You may be eligible! Proceed to apply.' : 'Sorry, you do not meet the basic requirements.'"></p>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h2 class="font-heading font-bold text-3xl text-center text-gaf-green mb-4">How It Works</h2>
    <p class="text-gray-500 text-center text-sm mb-12">Follow these simple steps to complete your application.</p>
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div class="text-center"><div class="w-14 h-14 bg-gaf-red rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg"><span class="text-white font-heading font-bold text-lg">1</span></div><p class="text-sm font-semibold">Register</p></div>
        <div class="text-center"><div class="w-14 h-14 bg-gaf-green rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg"><span class="text-white font-heading font-bold text-lg">2</span></div><p class="text-sm font-semibold">Submit Application</p></div>
        <div class="text-center"><div class="w-14 h-14 bg-gaf-green rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg"><span class="text-white font-heading font-bold text-lg">3</span></div><p class="text-sm font-semibold">Eligibility Check</p></div>
        <div class="text-center"><div class="w-14 h-14 bg-gaf-khaki rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg"><span class="text-gray-900 font-heading font-bold text-lg">4</span></div><p class="text-sm font-semibold">Shortlisting</p></div>
        <div class="text-center"><div class="w-14 h-14 bg-gaf-red rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg"><span class="text-white font-heading font-bold text-lg">5</span></div><p class="text-sm font-semibold">Screening</p></div>
        <div class="text-center"><div class="w-14 h-14 bg-gaf-green rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg"><span class="text-white font-heading font-bold text-lg">6</span></div><p class="text-sm font-semibold">Final Selection</p></div>
    </div>
</div>

<div class="bg-gray-100 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-center gap-8">
            <div class="flex items-center space-x-2 bg-white px-6 py-3 rounded-lg shadow"><svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg><span class="text-sm font-medium">No Middlemen</span></div>
            <div class="flex items-center space-x-2 bg-white px-6 py-3 rounded-lg shadow"><svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-sm font-medium">Free Application</span></div>
            <div class="flex items-center space-x-2 bg-white px-6 py-3 rounded-lg shadow"><svg class="w-6 h-6 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg><span class="text-sm font-medium">Ghana Government</span></div>
        </div>
    </div>
</div>
@endsection