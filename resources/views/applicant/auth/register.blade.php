@php use App\Models\Cycle; $hasActiveCycles = Cycle::where('status', 'active')->exists(); @endphp

<x-applicant-guest-layout title="Create Account" subtitle="Enter your voucher and personal details to begin">

    @if(!$hasActiveCycles)
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:16px 18px;margin-bottom:16px;text-align:center;">
        <div style="font-size:13px;font-weight:700;color:#92400e;margin-bottom:4px;">No Active Recruitment Cycles</div>
        <p style="font-size:12px;color:#b45309;margin:0;">There is no active recruitment cycle at the moment. Registration is unavailable until a new cycle opens. Please check back later.</p>
    </div>
    @endif

    <form method="POST" action="{{ route('applicant.register') }}"
          x-data="{
            phoneVal: '{{ old('contact_number') }}',
            phoneErr: '',
            phoneOk: false,
            phoneTouched: false,
            altPhoneVal: '{{ old('alternative_contact') }}',
            altPhoneErr: '',
            altPhoneOk: false,
            altPhoneTouched: false,
            emailVal: '{{ old('email') }}',
            emailErr: '',
            emailSugg: '',
            emailOk: false,
            emailTouched: false,

            onPhoneInput(el) {
                el.value = el.value.replace(/\D/g, '').substring(0, 10);
                this.phoneVal = el.value;
            },
            onPhoneBlur() {
                this.phoneTouched = true;
                if (!this.phoneVal) { this.phoneErr = 'Phone number is required'; this.phoneOk = false; return; }
                if (this.phoneVal.length !== 10) {
                    this.phoneErr = 'Phone number must be exactly 10 digits';
                    this.phoneOk = false;
                } else {
                    this.phoneErr = '';
                    this.phoneOk = true;
                }
            },
            onAltPhoneInput(el) {
                el.value = el.value.replace(/\D/g, '').substring(0, 10);
                this.altPhoneVal = el.value;
            },
            onAltPhoneBlur() {
                this.altPhoneTouched = true;
                if (!this.altPhoneVal) { this.altPhoneErr = ''; this.altPhoneOk = false; return; }
                if (this.altPhoneVal.length !== 10) {
                    this.altPhoneErr = 'Alternative phone must be exactly 10 digits';
                    this.altPhoneOk = false;
                } else {
                    this.altPhoneErr = '';
                    this.altPhoneOk = true;
                }
            },
            onEmailBlur() {
                this.emailTouched = true;
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!this.emailVal) { this.emailErr = 'Email is required'; this.emailSugg = ''; this.emailOk = false; return; }
                if (!re.test(this.emailVal)) {
                    this.emailErr = 'Please enter a valid email address';
                    this.emailSugg = '';
                    this.emailOk = false;
                    return;
                }
                const typos = {
                    'gmail.con':'gmail.com','gmail.cm':'gmail.com','gmial.com':'gmail.com','gmal.com':'gmail.com',
                    'yahoo.cm':'yahoo.com','yahoo.con':'yahoo.com',
                    'hotmail.cm':'hotmail.com','hotmail.con':'hotmail.com','hotmial.com':'hotmail.com',
                    'outlok.com':'outlook.com','outloo.com':'outlook.com',
                    'yhoo.com':'yahoo.com','gmil.com':'gmail.com',
                };
                const domain = this.emailVal.split('@')[1];
                if (domain && typos[domain]) {
                    this.emailSugg = 'Did you mean @' + typos[domain] + '?';
                } else {
                    this.emailSugg = '';
                }
                this.emailErr = '';
                this.emailOk = true;
            }
          }"
          style="display:flex;flex-direction:column;gap:14px;">
        @csrf

        <div style="border:2px dashed #e2e8f0;border-radius:14px;padding:16px;background:#fafdfb;">
            <p style="font-size:11px;font-weight:700;color:#4a7a65;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.5px;">Voucher Details</p>
            <a href="{{ route('voucher.buy') }}"
               style="display:flex;align-items:center;gap:8px;margin-bottom:12px;padding:10px 14px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;border-radius:10px;font-size:13px;font-weight:600;color:#166534;text-decoration:none;transition:all 0.2s;"
               onmouseover="this.style.background='linear-gradient(135deg,#dcfce7,#bbf7d0)'"
               onmouseout="this.style.background='linear-gradient(135deg,#f0fdf4,#dcfce7)'">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span>Don't have a voucher? <strong>Purchase one</strong></span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="margin-left:auto;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label for="serial_number" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Serial Number</label>
                    <input id="serial_number" type="text" name="serial_number" value="{{ old('serial_number', request('serial')) }}" placeholder="DMRMS-XXXXXXXX" required
                           style="width:100%;padding:10px 14px;border:2px solid {{ $errors->has('serial_number') ? '#dc2626' : '#e2e8f0' }};border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           onblur="this.style.borderColor='{{ $errors->has('serial_number') ? '#dc2626' : '#e2e8f0' }}';this.style.boxShadow='none'">
                    <x-input-error :messages="$errors->get('serial_number')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
                </div>
                <div>
                    <label for="pin_code" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">PIN Code</label>
                    <input id="pin_code" type="text" name="pin_code" value="{{ old('pin_code', request('pin')) }}" placeholder="Enter your PIN" required
                           oninput="this.value = this.value.replace(/\D/g, '')"
                           style="width:100%;padding:10px 14px;border:2px solid #e2e8f0;border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    <x-input-error :messages="$errors->get('pin_code')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
                </div>
            </div>
            <x-input-error :messages="$errors->get('voucher')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
        </div>

        <p style="font-size:11px;font-weight:700;color:#4a7a65;margin:2px 0 6px;text-transform:uppercase;letter-spacing:0.5px;">Personal Details</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div style="{{ $errors->has('first_name') ? 'border:1px solid #dc2626;border-radius:10px;padding:1px;' : '' }}">
                    <label for="first_name" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">First Name</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" placeholder="John" required
                           oninput="this.value = this.value.replace(/[0-9]/g, '')"
                           style="width:100%;padding:10px 14px;border:2px solid {{ $errors->has('first_name') ? '#dc2626' : '#e2e8f0' }};border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           onblur="this.style.borderColor='{{ $errors->has('first_name') ? '#dc2626' : '#e2e8f0' }}';this.style.boxShadow='none'">
                    <x-input-error :messages="$errors->get('first_name')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
                </div>
                <div style="{{ $errors->has('last_name') ? 'border:1px solid #dc2626;border-radius:10px;padding:1px;' : '' }}">
                    <label for="last_name" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Last Name</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Doe" required
                           oninput="this.value = this.value.replace(/[0-9]/g, '')"
                           style="width:100%;padding:10px 14px;border:2px solid {{ $errors->has('last_name') ? '#dc2626' : '#e2e8f0' }};border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           onblur="this.style.borderColor='{{ $errors->has('last_name') ? '#dc2626' : '#e2e8f0' }}';this.style.boxShadow='none'">
                    <x-input-error :messages="$errors->get('last_name')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
                </div>
                <div>
                    <label for="other_names" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Other Names</label>
                    <input id="other_names" type="text" name="other_names" value="{{ old('other_names') }}" placeholder="Middle names (optional)"
                           oninput="this.value = this.value.replace(/[0-9]/g, '')"
                           style="width:100%;padding:10px 14px;border:2px solid {{ $errors->has('other_names') ? '#dc2626' : '#e2e8f0' }};border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           onblur="this.style.borderColor='{{ $errors->has('other_names') ? '#dc2626' : '#e2e8f0' }}';this.style.boxShadow='none'">
                    <x-input-error :messages="$errors->get('other_names')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
                </div>
                <div>
                    <label for="contact_number" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Phone Number</label>
                    <input id="contact_number" type="tel" name="contact_number"
                           x-ref="phoneInput"
                           :value="phoneVal"
                           @input="onPhoneInput($event.target)"
                           @blur="onPhoneBlur()"
                           @focus="$event.target.style.borderColor='#5fa489';$event.target.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           placeholder="0244000000" required
                           :style="'width:100%;padding:10px 14px;border:2px solid ' + (phoneTouched && phoneErr ? '#dc2626' : (phoneTouched && phoneOk ? '#16a34a' : ({{ $errors->has('contact_number') ? "'#dc2626'" : "'#e2e8f0'" }}))) + ';border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;'">
                    <template x-if="phoneTouched && phoneErr">
                        <p style="font-size:11px;color:#dc2626;margin-top:2px;" x-text="phoneErr"></p>
                    </template>
                    <template x-if="!phoneTouched || !phoneErr">
                        <x-input-error :messages="$errors->get('contact_number')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
                    </template>
                </div>
                <div>
                    <label for="alternative_contact" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Alternative Phone</label>
                    <input id="alternative_contact" type="tel" name="alternative_contact"
                           x-ref="altPhoneInput"
                           :value="altPhoneVal"
                           @input="onAltPhoneInput($event.target)"
                           @blur="onAltPhoneBlur()"
                           @focus="$event.target.style.borderColor='#5fa489';$event.target.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           placeholder="0244000001"
                           :style="'width:100%;padding:10px 14px;border:2px solid ' + (altPhoneTouched && altPhoneErr ? '#dc2626' : (altPhoneTouched && altPhoneOk ? '#16a34a' : ({{ $errors->has('alternative_contact') ? "'#dc2626'" : "'#e2e8f0'" }}))) + ';border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;'">
                    <template x-if="altPhoneTouched && altPhoneErr">
                        <p style="font-size:11px;color:#dc2626;margin-top:2px;" x-text="altPhoneErr"></p>
                    </template>
                    <template x-if="!altPhoneTouched || !altPhoneErr">
                        <x-input-error :messages="$errors->get('alternative_contact')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
                    </template>
                </div>
                <div style="{{ $errors->has('date_of_birth') ? 'border:1px solid #dc2626;border-radius:10px;padding:1px;' : '' }}">
                    <label for="date_of_birth" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Date of Birth</label>
                    <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                           style="width:100%;padding:10px 14px;border:2px solid {{ $errors->has('date_of_birth') ? '#dc2626' : '#e2e8f0' }};border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           onblur="this.style.borderColor='{{ $errors->has('date_of_birth') ? '#dc2626' : '#e2e8f0' }}';this.style.boxShadow='none'">
                    <x-input-error :messages="$errors->get('date_of_birth')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
                </div>
                <div style="{{ $errors->has('gender') ? 'border:1px solid #dc2626;border-radius:10px;padding:1px;' : '' }}">
                    <label for="gender" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Gender</label>
                    <select id="gender" name="gender" required
                            style="width:100%;padding:10px 14px;border:2px solid {{ $errors->has('gender') ? '#dc2626' : '#e2e8f0' }};border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                            onblur="this.style.borderColor='{{ $errors->has('gender') ? '#dc2626' : '#e2e8f0' }}';this.style.boxShadow='none'">
                        <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select</option>
                        <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
                </div>
                <div>
                    <label for="email" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Email Address</label>
                    <input id="email" type="email" name="email"
                           x-ref="emailInput"
                           :value="emailVal"
                           @input="emailVal = $event.target.value"
                           @blur="onEmailBlur()"
                           @focus="$event.target.style.borderColor='#5fa489';$event.target.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           placeholder="your@email.com" required
                           :style="'width:100%;padding:10px 14px;border:2px solid ' + (emailTouched && emailErr ? '#dc2626' : (emailTouched && emailOk ? '#16a34a' : ({{ $errors->has('email') ? "'#dc2626'" : "'#e2e8f0'" }}))) + ';border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;'">
                    <template x-if="emailTouched && emailErr">
                        <p style="font-size:11px;color:#dc2626;margin-top:2px;" x-text="emailErr"></p>
                    </template>
                    <template x-if="emailTouched && emailSugg">
                        <p style="font-size:11px;color:#eab308;margin-top:2px;" x-text="emailSugg"></p>
                    </template>
                    <template x-if="!emailTouched || (!emailErr && !emailSugg)">
                        <x-input-error :messages="$errors->get('email')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
                    </template>
                </div>
            </div>

        <p style="font-size:11px;font-weight:700;color:#4a7a65;margin:2px 0 6px;text-transform:uppercase;letter-spacing:0.5px;">Password</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div style="{{ $errors->has('password') ? 'border:1px solid #dc2626;border-radius:10px;padding:1px;' : '' }}">
                <label for="password" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Create Password</label>
                <div style="position:relative;" x-data="{ showPassword: false }">
                    <input id="password" :type="showPassword ? 'text' : 'password'" name="password" placeholder="Min 8 characters" required autocomplete="new-password"
                           style="width:100%;padding:10px 14px;border:2px solid {{ $errors->has('password') ? '#dc2626' : '#e2e8f0' }};border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           onblur="this.style.borderColor='{{ $errors->has('password') ? '#dc2626' : '#e2e8f0' }}';this.style.boxShadow='none'">
                    <button type="button" @click="showPassword = !showPassword"
                            style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:#94a3b8;display:flex;align-items:center;justify-content:center;"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'" tabindex="-1">
                        <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
            </div>
            <div style="{{ $errors->has('password_confirmation') ? 'border:1px solid #dc2626;border-radius:10px;padding:1px;' : '' }}">
                <label for="password_confirmation" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Confirm Password</label>
                <div style="position:relative;" x-data="{ showPassword: false }">
                    <input id="password_confirmation" :type="showPassword ? 'text' : 'password'" name="password_confirmation" placeholder="Confirm password" required autocomplete="new-password"
                           style="width:100%;padding:10px 14px;border:2px solid {{ $errors->has('password_confirmation') ? '#dc2626' : '#e2e8f0' }};border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           onblur="this.style.borderColor='{{ $errors->has('password_confirmation') ? '#dc2626' : '#e2e8f0' }}';this.style.boxShadow='none'">
                    <button type="button" @click="showPassword = !showPassword"
                            style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:#94a3b8;display:flex;align-items:center;justify-content:center;"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'" tabindex="-1">
                        <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
            </div>
        </div>

        <button type="submit"
                style="width:100%;padding:14px;background:linear-gradient(135deg,#14532d,#166534);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all 0.3s ease;box-shadow:0 4px 16px rgba(20,83,45,0.3);margin-top:4px;"
                onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 28px rgba(20,83,45,0.4)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 16px rgba(20,83,45,0.3)'"
                onmousedown="this.style.transform='scale(0.98)'"
                onmouseup="this.style.transform='translateY(-2px)'">
            Create Account
        </button>

        <p style="text-align:center;font-size:13px;color:#64748b;margin:0;">
            Already have an account?
            <a href="{{ route('applicant.login') }}" style="color:#14532d;font-weight:700;text-decoration:none;transition:color 0.2s;"
               onmouseover="this.style.color='#0f2f1f'" onmouseout="this.style.color='#14532d'">Log in</a>
        </p>

        <p style="text-align:center;font-size:12px;color:#94a3b8;margin:0;border-top:1px solid #e2e8f0;padding-top:12px;">
            <a href="{{ route('login') }}" style="color:#64748b;text-decoration:none;transition:color 0.2s;"
               onmouseover="this.style.color='#14532d'" onmouseout="this.style.color='#64748b'">Admin Login</a>
        </p>
    </form>
</x-applicant-guest-layout>
