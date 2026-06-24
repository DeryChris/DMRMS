@extends('layouts.applicant')

@section('title', 'Application Form - Ghana Armed Forces')

@section('content')
<div x-data="{
    step: 1,
    totalsteps: 4,
    steps: [
        { label: 'Personal', key: 'personal' },
        { label: 'Education', key: 'education' },
        { label: 'Health', key: 'health' },
        { label: 'Documents', key: 'documents' }
    ],
    form: {
        personal: { name: '', dob: '', gender: '', marital: '', phone: '', email: '', address: '', region: '', district: '', nationality: 'Ghanaian', national_id: '' },
        education: { institution: '', qualification: '', year: '', cert_number: '', level: '' },
        health: { height: '', weight: '', conditions: [], criminal: '', fitness: '' },
        documents: { birth_cert: null, national_id_doc: null, certificate: null, passport: null, medical: null, police_clearance: null }
    },
    agreed: false,
    next() { if(this.step < this.totalsteps) this.step++; },
    prev() { if(this.step > 1) this.step--; },
    savedraft() { localStorage.setItem('application_draft', JSON.stringify(this.form)); alert('Draft saved!'); },
    init() {
        let saved = localStorage.getItem('application_draft');
        if(saved) { try { this.form = JSON.parse(saved); } catch(e) {} }
        setInterval(() => { localStorage.setItem('application_draft', JSON.stringify(this.form)); }, 30000);
    }
}" class="max-w-4xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
        <div>
            <h1 class="font-heading font-bold text-2xl text-gray-800">Application Form</h1>
            <p class="text-gray-500 text-sm mt-1">Complete all steps to submit your application.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-800 max-w-xs">
                <p class="font-semibold mb-1">Quick Tips</p>
                <ul class="space-y-1 list-disc pl-4">
                    <li>Fields marked * are required</li>
                    <li>You can save your draft and continue later</li>
                    <li>Ensure documents are clear and legible</li>
                </ul>
            </div>
            <x-guidelines-overlay link-text="Eligibility Guidelines" class="whitespace-nowrap" />
        </div>
    </div>

    <div class="step-bar-wrapper" style="background:#f7f9fa;padding:10px 0;border-bottom:1px solid #cbd5e1;margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:0;">
            <template x-for="(s, i) in steps" :key="i">
                <div style="display:flex;align-items:center;gap:8px;flex:1;">
                    <div :class="['step-num', step >= i + 1 ? 'active' : '']" x-text="i + 1"></div>
                    <span :class="['step-label', step >= i + 1 ? 'active' : '']" x-text="s.label"></span>
                    <div x-show="i < steps.length - 1" class="step-connector"></div>
                </div>
            </template>
        </div>
    </div>

    <div class="card !rounded-none">
        <div class="form-card-header">
            <h2 x-text="'Step ' + step + ' of ' + totalsteps + ' — ' + steps[step - 1].label"></h2>
        </div>
        <div class="p-8">
        <div x-show="step === 1" x-cloak>
            <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Personal Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label><input type="text" x-model="form.personal.name" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label><input type="date" x-model="form.personal.dob" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Gender</label><select x-model="form.personal.gender" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"><option value="">Select</option><option value="male">Male</option><option value="female">Female</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Marital Status</label><select x-model="form.personal.marital" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"><option value="">Select</option><option value="single">Single</option><option value="married">Married</option><option value="divorced">Divorced</option><option value="widowed">Widowed</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label><input type="tel" x-model="form.personal.phone" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" x-model="form.personal.email" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Address</label><textarea x-model="form.personal.address" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></textarea></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Region</label><select x-model="form.personal.region" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"><option value="">Select</option><option>Greater Accra</option><option>Ashanti</option><option>Eastern</option><option>Western</option><option>Central</option><option>Northern</option><option>Upper East</option><option>Upper West</option><option>Volta</option><option>Bono</option><option>Ahafo</option><option>Bono East</option><option>Oti</option><option>North East</option><option>Savannah</option><option>Western North</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">District</label><input type="text" x-model="form.personal.district" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label><input type="text" x-model="form.personal.nationality" readonly class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm bg-gray-50"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">National ID (Ghana Card)</label><input type="text" x-model="form.personal.national_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
            </div>
        </div>

        <div x-show="step === 2" x-cloak>
            <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Educational Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Institution</label><input type="text" x-model="form.education.institution" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Qualification</label><input type="text" x-model="form.education.qualification" placeholder="e.g. WASSCE, BSc" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Year of Completion</label><input type="number" x-model="form.education.year" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Certificate Number</label><input type="text" x-model="form.education.cert_number" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Education Level</label><select x-model="form.education.level" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"><option value="">Select</option><option value="ssce">SSCE/WASSCE</option><option value="diploma">Diploma</option><option value="degree">Bachelor's Degree</option><option value="masters">Master's Degree</option><option value="phd">PhD</option></select></div>
            </div>
        </div>

        <div x-show="step === 3" x-cloak>
            <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Physical & Health</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Height (m)</label><input type="number" step="0.01" x-model="form.health.height" placeholder="e.g. 1.75" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label><input type="number" step="0.1" x-model="form.health.weight" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Health Conditions</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center space-x-2 text-sm"><input type="checkbox" value="Asthma" x-model="form.health.conditions" class="rounded border-gray-300"><span>Asthma</span></label>
                        <label class="flex items-center space-x-2 text-sm"><input type="checkbox" value="Diabetes" x-model="form.health.conditions" class="rounded border-gray-300"><span>Diabetes</span></label>
                        <label class="flex items-center space-x-2 text-sm"><input type="checkbox" value="Hypertension" x-model="form.health.conditions" class="rounded border-gray-300"><span>Hypertension</span></label>
                        <label class="flex items-center space-x-2 text-sm"><input type="checkbox" value="Epilepsy" x-model="form.health.conditions" class="rounded border-gray-300"><span>Epilepsy</span></label>
                        <label class="flex items-center space-x-2 text-sm"><input type="checkbox" value="Hearing Loss" x-model="form.health.conditions" class="rounded border-gray-300"><span>Hearing Loss</span></label>
                        <label class="flex items-center space-x-2 text-sm"><input type="checkbox" value="Vision Impairment" x-model="form.health.conditions" class="rounded border-gray-300"><span>Vision Impairment</span></label>
                    </div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Criminal Record Declaration</label><select x-model="form.health.criminal" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"><option value="">Select</option><option value="no">No criminal record</option><option value="yes">I have a criminal record</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Fitness Self-Assessment</label><select x-model="form.health.fitness" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"><option value="">Select</option><option value="excellent">Excellent</option><option value="good">Good</option><option value="average">Average</option><option value="poor">Poor</option></select></div>
            </div>
        </div>

        <div x-show="step === 4" x-cloak>
            <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Document Upload</h2>
            <div class="space-y-4">
                @php $docs = ['birth_cert' => 'Birth Certificate', 'national_id_doc' => 'National ID', 'certificate' => 'WASSCE/SSCE Certificate', 'passport' => 'Passport Photograph', 'medical' => 'Medical Report', 'police_clearance' => 'Police Clearance']; @endphp
                @foreach($docs as $key => $label)
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gaf-khaki transition cursor-pointer"
                     @dragover.prevent @drop.prevent="form.documents.{{ $key }} = $event.dataTransfer.files[0]">
                    <input type="file" class="hidden" :id="'file_{{ $key }}'" @change="form.documents.{{ $key }} = $event.target.files[0]">
                    <label :for="'file_{{ $key }}'" class="cursor-pointer">
                        <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-sm font-medium text-gray-700">{{ $label }}</p>
                        <p class="text-xs text-gray-400">Drag & drop or click to browse</p>
                        <p class="text-xs text-green-600 mt-1" x-show="form.documents.{{ $key }}" x-text="form.documents.{{ $key }}?.name"></p>
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        <div x-show="step === 5" x-cloak>
            <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Review Your Application</h2>
            <div class="space-y-4 text-sm">
                <h3 class="font-semibold text-gray-700">Personal Info</h3>
                <div class="grid grid-cols-2 gap-2 p-4 bg-gray-50 rounded-lg">
                    <p><span class="text-gray-500">Name:</span> <span x-text="form.personal.name"></span></p>
                    <p><span class="text-gray-500">DOB:</span> <span x-text="form.personal.dob"></span></p>
                    <p><span class="text-gray-500">Gender:</span> <span x-text="form.personal.gender"></span></p>
                    <p><span class="text-gray-500">Phone:</span> <span x-text="form.personal.phone"></span></p>
                </div>
                <label class="flex items-center space-x-3 mt-4">
                    <input type="checkbox" x-model="agreed" class="rounded border-gray-300">
                    <span class="text-sm text-gray-600">I confirm that all the information provided is true and accurate to the best of my knowledge.</span>
                </label>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between mt-6">
        <button @click="savedraft()" class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Save Draft</button>
        <div class="flex space-x-3">
            <button x-show="step > 1" @click="prev()" class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Previous</button>
            <button x-show="step < totalsteps" @click="next()" class="px-6 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Next</button>
            <button x-show="step === totalsteps" :disabled="!agreed" class="px-6 py-3 bg-gaf-red text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition disabled:opacity-50" :class="!agreed ? 'cursor-not-allowed' : ''">Submit Application</button>
        </div>
    </div>
</div>
@endsection