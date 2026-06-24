<style>
.guidelines-scroll::-webkit-scrollbar { display: none; }
.guidelines-scroll { -ms-overflow-style: none; scrollbar-width: none; }
</style>
<div x-data="{ open: false }">
    <button @click="open = true" {{ $attributes->merge(['class' => 'text-gaf-khaki hover:text-yellow-500 underline text-sm font-medium transition']) }}>
        {{ $linkText ?? 'View Eligibility Guidelines' }}
    </button>
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="open = false">
        <div class="absolute inset-0 bg-black/70" @click="open = false"></div>
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] overflow-y-auto z-10 guidelines-scroll">
            <div class="sticky top-0 bg-gaf-green text-white px-6 py-4 rounded-t-2xl flex items-center justify-between">
                <h2 class="font-heading font-bold text-lg">Eligibility Guidelines</h2>
                <button @click="open = false" class="text-white/80 hover:text-white transition p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-6 text-sm text-gray-700">
                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
                    <p class="font-semibold text-amber-800">Important Notice</p>
                    <p class="text-amber-700 mt-1">These are general guidelines. Specific requirements may vary by recruitment cycle. Always check the current cycle details before applying.</p>
                </div>

                <div>
                    <h3 class="font-heading font-bold text-gaf-green text-base mb-3 flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> Age Requirements</h3>
                    <ul class="space-y-2 pl-7 list-disc">
                        <li>Minimum age: <strong>{{ config('recruitment.age_min') }} years</strong></li>
                        <li>Regular recruitment: <strong>{{ config('recruitment.age_min') }} - {{ config('recruitment.age_max_regular') }} years</strong></li>
                        <li>Tradesmen: <strong>{{ config('recruitment.age_min') }} - {{ config('recruitment.age_max_tradesmen') }} years</strong></li>
                        <li>Officer cadre: <strong>{{ config('recruitment.age_min') }} - {{ config('recruitment.age_max_officer') }} years</strong></li>
                        <li>Age is calculated as at the date of application submission.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-heading font-bold text-gaf-green text-base mb-3 flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Nationality</h3>
                    <ul class="space-y-2 pl-7 list-disc">
                        <li>Must be a <strong>{{ config('recruitment.nationality') }}</strong> by birth or naturalisation.</li>
                        <li>Must possess a valid Ghana Card (National ID).</li>
                        <li>Non-Ghanaians are not eligible for enlistment.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-heading font-bold text-gaf-green text-base mb-3 flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg> Educational Requirements</h3>
                    <ul class="space-y-2 pl-7 list-disc">
                        <li><strong>Regular/Soldier:</strong> Minimum of WASSCE/SSCE with at least 3 credits including English and Mathematics.</li>
                        <li><strong>Tradesmen:</strong> WASSCE/SSCE plus relevant trade certificate from a recognised institution.</li>
                        <li><strong>Officer:</strong> Bachelor's degree (minimum Second Class Lower) from a recognised university.</li>
                        <li>All certificates must be from accredited institutions.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-heading font-bold text-gaf-green text-base mb-3 flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg> Physical Requirements</h3>
                    <ul class="space-y-2 pl-7 list-disc">
                        <li>Minimum height (Male): <strong>{{ config('recruitment.height_min_male') }}m</strong></li>
                        <li>Minimum height (Female): <strong>{{ config('recruitment.height_min_female') }}m</strong></li>
                        <li>Must be physically and medically fit for military service.</li>
                        <li>No severe visual or hearing impairments.</li>
                        <li>Body mass index (BMI) must be within acceptable range (18.5 - 30).</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-heading font-bold text-gaf-green text-base mb-3 flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Required Documents</h3>
                    <ul class="space-y-2 pl-7 list-disc">
                        <li>Birth Certificate (issued by the Births and Deaths Registry).</li>
                        <li>National ID (Ghana Card).</li>
                        <li>WASSCE/SSCE Certificate or equivalent.</li>
                        <li>Passport-sized photographs (white background).</li>
                        <li>Medical fitness report from a recognised military or government hospital.</li>
                        <li>Police clearance certificate.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-heading font-bold text-gaf-green text-base mb-3 flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Other Requirements</h3>
                    <ul class="space-y-2 pl-7 list-disc">
                        <li>Must have no criminal record.</li>
                        <li>Must not have been dismissed from any previous employment (especially security services).</li>
                        <li>Must be of good character and recommended by two reputable referees.</li>
                        <li>Must not be a member of any prohibited organisation.</li>
                        <li>Must be willing to serve anywhere in Ghana.</li>
                    </ul>
                </div>

                <div class="bg-gaf-green/5 border border-gaf-green/20 rounded-lg p-4">
                    <p class="text-xs text-gray-500">For full details, refer to the official Ghana Armed Forces Recruitment Regulations or contact the nearest GAF Recruitment Centre.</p>
                </div>
            </div>
        </div>
    </div>
</div>
