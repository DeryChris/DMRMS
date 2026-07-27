@extends('layouts.app')

@section('title', 'Buy Voucher - Ghana Armed Forces')

@php $unsplashPhoto = $unsplashPhoto ?? unsplash_hero(); @endphp

@section('hero')
<div class="relative overflow-hidden" style="min-height:200px;">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $unsplashPhoto['regular_url'] ?? '' }}');"></div>
    <div class="absolute inset-0" style="background:linear-gradient(135deg, rgba(20,83,45,0.9) 0%, rgba(15,47,31,0.85) 70%, rgba(155,34,38,0.75) 100%);"></div>
    <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center">
        <h1 class="font-heading font-bold text-3xl text-white mb-2">Purchase Recruitment Voucher</h1>
        <p class="text-gaf-khaki/80">Get your unique serial number and PIN to begin your application</p>
    </div>
    @if($unsplashPhoto && ($unsplashPhoto['attribution']['name'] ?? '') !== 'Unsplash')
    <div class="absolute bottom-2 right-4 z-20 text-xs text-white/40">
        Photo by <a href="{{ ($unsplashPhoto['attribution']['link'] ?? '#') }}?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">{{ $unsplashPhoto['attribution']['name'] ?? 'Unknown' }}</a> on <a href="https://unsplash.com/?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">Unsplash</a>
    </div>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    @php $hasActiveCycles = $activeCycles->isNotEmpty(); @endphp

    @if(!$hasActiveCycles)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6 text-center">
        <svg class="w-10 h-10 text-amber-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <h2 class="font-heading font-bold text-lg text-amber-800 mb-1">No Active Recruitment Cycles</h2>
        <p class="text-amber-700 text-sm">There is no active recruitment cycle at the moment. Voucher purchases are unavailable until a new cycle opens. Please check back later or follow our announcements.</p>
        <a href="{{ route('landing') }}" class="inline-block mt-3 text-sm text-amber-700 hover:text-amber-900 underline font-medium">Return to Homepage</a>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">{{ session('error') }}</div>
    @endif

    {{-- Main Form Container --}}
    <div x-data="voucherForm()"
         x-init="initForm()"
         class="bg-white/90 glass-strong rounded-xl shadow-md border border-gray-200 p-8">

        {{-- STEP 1: FORM DETAILS --}}
        <div x-show="step === 'form'" x-cloak>

            <form method="POST" action="{{ route('voucher.purchase') }}"
                  @submit.prevent="submitForm"
                  class="space-y-6"
                  novalidate>
                @csrf

                {{-- Cycle Selection --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Recruitment Cycle <span class="text-red-500">*</span>
                        <span x-show="touched.cycle_id && !validCycle" class="text-red-500 text-xs ml-2">Please select a cycle</span>
                    </label>
                    @error('cycle_id') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror
                    @if($activeCycles->isEmpty())
                        <div class="bg-gray-50 rounded-lg p-4 text-center text-gray-400 text-sm">No active recruitment cycles available for purchase.</div>
                    @else
                        <div class="space-y-3">
                            @foreach($activeCycles as $cycle)
                            <label class="block border rounded-lg overflow-hidden cursor-pointer hover:border-gaf-khaki transition"
                                   :class="cycle_id === '{{ $cycle->id }}' ? 'border-gaf-khaki ring-2 ring-gaf-khaki' : 'border-gray-200'">
                                <div class="card-gradient-header bg-gaf-green">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-white">{{ $cycle->name }}</span>
                                        <span class="text-white font-bold text-lg">GHS {{ number_format($cycle->voucher_price ?? config('recruitment.voucher_costs.regular', 50), 2) }}</span>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="flex items-start">
                                        <input type="radio" name="cycle_id" value="{{ $cycle->id }}"
                                               x-model="cycle_id"
                                               @change="touched.cycle_id = true"
                                               class="mt-1 text-gaf-khaki focus:ring-gaf-khaki">
                                        <div class="ml-3 flex-1">
                                            <p class="text-xs text-gray-400 mt-0.5">Code: {{ $cycle->cycle_code }} &middot; Deadline: {{ $cycle->application_deadline?->format('M d, Y H:i') }}</p>
                                            <div class="flex flex-wrap gap-1.5 mt-2">
                                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded">{{ number_format($cycle->total_vacancies) }} vacancies</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Personal Info --}}
                <div class="border-t pt-6 mb-6">
                    <h2 class="font-heading font-semibold text-base text-gray-800 mb-4">Your Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Full Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="purchaser_name" x-model="purchaser_name"
                                   @input="touched.purchaser_name = true; filterName()"
                                   @blur="touched.purchaser_name = true"
                                   placeholder="John Doe"
                                   :class="inputClass('purchaser_name')"
                                   class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki transition">
                            <p x-show="touched.purchaser_name && !validName" class="text-red-500 text-xs mt-1">
                                <template x-if="purchaser_name.length === 0">Full name is required</template>
                                <template x-if="purchaser_name.length > 0 && purchaser_name.length < 2">Name must be at least 2 characters</template>
                                <template x-if="purchaser_name.length >= 2 && !/^[A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF\s'\-]+$/.test(purchaser_name)">Only letters, spaces, hyphens, and apostrophes allowed</template>
                            </p>
                            @error('purchaser_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="purchaser_email" x-model="purchaser_email"
                                   @input="touched.purchaser_email = true"
                                   @blur="touched.purchaser_email = true"
                                   placeholder="john@example.com"
                                   :class="inputClass('purchaser_email')"
                                   class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki transition">
                            <p x-show="touched.purchaser_email && !validEmail" class="text-red-500 text-xs mt-1">
                                <template x-if="purchaser_email.length === 0">Email address is required</template>
                                <template x-if="purchaser_email.length > 0 && !validEmail">Please enter a valid email address</template>
                            </p>
                            @error('purchaser_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                            <input type="tel" name="purchaser_phone" x-model="purchaser_phone"
                                   @input="touched.purchaser_phone = true; filterPhone()"
                                   @blur="touched.purchaser_phone = true"
                                   placeholder="0244000000"
                                   :class="inputClass('purchaser_phone')"
                                   class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki transition">
                            <p x-show="touched.purchaser_phone && !validPhone" class="text-red-500 text-xs mt-1">
                                <template x-if="purchaser_phone.length === 0">Phone number is required</template>
                                <template x-if="purchaser_phone.length > 0 && !/^\d{10}$/.test(purchaser_phone)">Phone must be exactly 10 digits</template>
                            </p>
                            @error('purchaser_phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="border-t pt-6 mb-6">
                    <h2 class="font-heading font-semibold text-base text-gray-800 mb-4">Payment Method</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                            <select name="payment_method" x-model="payment_method"
                                    @change="touched.payment_method = true; onPaymentMethodChange()"
                                    class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki transition"
                                    :class="inputClass('payment_method')">
                                <option value="">-- Select --</option>
                                @foreach($paymentMethods as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <p x-show="touched.payment_method && !validPayment" class="text-red-500 text-xs mt-1">Please select a payment method</p>
                            @error('payment_method') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Mobile Money: Provider + Phone --}}
                    <div x-show="payment_method === 'mobile_money'" x-cloak class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Mobile Money Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Provider <span class="text-red-500">*</span></label>
                                <select x-model="momo_provider" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki transition border-gray-300">
                                    <option value="">-- Select Provider --</option>
                                    <option value="mtn">MTN MoMo</option>
                                    <option value="atl">AirtelTigo Money</option>
                                    <option value="vod">Telecel Cash (Vodafone)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">MoMo Phone Number <span class="text-red-500">*</span></label>
                                <input type="tel" x-model="momo_phone"
                                       @input="momo_phone = $event.target.value.replace(/\D/g, '').substring(0, 10)"
                                       placeholder="0551234987"
                                       class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki transition border-gray-300">
                                <p class="text-xs text-gray-400 mt-1">Enter the 10-digit phone number registered with your mobile money</p>
                            </div>
                        </div>
                    </div>

                    {{-- Card: PaystackPop popup (standard approach) --}}
                    <div x-show="payment_method === 'card'" x-cloak class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Card Payment</h3>
                        <p class="text-xs text-gray-500">When you click "Pay with Card", a secure Paystack payment popup will appear where you can enter your card details.</p>
                        <p x-show="cardError" class="text-red-500 text-xs mt-2" x-text="cardError"></p>
                    </div>

                    {{-- Bank Transfer: Bank Selection --}}
                    <div x-show="payment_method === 'bank_transfer'" x-cloak class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Bank Transfer</h3>
                        <p class="text-xs text-gray-500 mb-3">Select your bank to generate payment details. You will then transfer the exact amount to the provided account.</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Your Bank <span class="text-red-500">*</span></label>
                            <select x-model="bank_code" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki transition border-gray-300">
                                <option value="">-- Select Bank --</option>
                                <option value="044">Access Bank Ghana</option>
                                <option value="035">ADB Bank</option>
                                <option value="209">Absa Bank Ghana</option>
                                <option value="013">Bank of Ghana</option>
                                <option value="034">CalBank</option>
                                <option value="406">Consolidated Bank Ghana</option>
                                <option value="057">Ecobank Ghana</option>
                                <option value="221">Energbank</option>
                                <option value="047">FBNBank Ghana</option>
                                <option value="089">Fidelity Bank Ghana</option>
                                <option value="083">First Atlantic Bank</option>
                                <option value="050">First National Bank Ghana</option>
                                <option value="052">GCB Bank</option>
                                <option value="114">Ghana Pay</option>
                                <option value="071">GHL Bank</option>
                                <option value="062">GTBank Ghana</option>
                                <option value="053">National Investment Bank</option>
                                <option value="361">OmniBank</option>
                                <option value="210">Prudential Bank</option>
                                <option value="042">Republic Bank Ghana</option>
                                <option value="072">Sahel Sahara Bank</option>
                                <option value="024">Societe Generale Ghana</option>
                                <option value="058">Stanbic Bank Ghana</option>
                                <option value="045">Standard Chartered Bank</option>
                                <option value="049">UBA Ghana</option>
                                <option value="030">Universal Merchant Bank</option>
                                <option value="056">Zenith Bank</option>
                            </select>
                        </div>
                    </div>

                    {{-- Bank Deposit: Info message --}}
                    <div x-show="payment_method === 'bank_deposit'" x-cloak class="mt-4 p-4 bg-amber-50 rounded-lg border border-amber-200">
                        <p class="text-sm text-amber-700">
                            <span class="font-semibold">Bank Deposit</span> — This is a manual payment method. After submitting, your voucher will be activated immediately. You are expected to deposit the amount at any GAF-recognized bank branch and keep the receipt.
                        </p>
                    </div>
                </div>

                {{-- Submit Button (works for all methods) --}}
                <button type="submit"
                        :disabled="!allValid || submitting"
                        class="w-full py-4 rounded-lg font-heading font-bold text-lg transition shadow-lg"
                        :class="(allValid && !submitting) ? 'bg-gaf-green text-white hover:bg-gaf-dark-green cursor-pointer' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                        {{ $activeCycles->isEmpty() ? 'disabled' : '' }}>
                    <span x-show="!submitting" x-text="submitButtonText"></span>
                    <span x-show="submitting" class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Processing...
                    </span>
                </button>
            </form>
        </div>

        {{-- STEP 2: PROCESSING PAYMENT --}}
        <div x-show="step === 'processing'" x-cloak class="text-center py-8">
            {{-- MoMo Processing --}}
            <template x-if="payment_method === 'mobile_money'">
                <div>
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h2 class="font-heading font-bold text-xl text-gray-800 mb-2">Check Your Phone</h2>
                    <p class="text-gray-600 mb-2" x-text="displayText || 'Please check your mobile money app for a payment request.'"></p>
                    <p class="text-sm text-gray-500 mb-2">
                        Provider: <span class="font-semibold" x-text="momoProviderLabel(momo_provider)"></span> &middot;
                        Phone: <span class="font-semibold" x-text="momo_phone"></span>
                    </p>

                    {{-- OTP input for Telecel --}}
                    <div x-show="otpRequired" x-cloak class="mt-4 max-w-sm mx-auto">
                        <p class="text-sm text-gray-600 mb-2">Enter the OTP sent to your phone to complete the payment:</p>
                        <div class="flex gap-2">
                            <input type="text" x-model="otpCode" placeholder="Enter OTP"
                                   class="flex-1 border rounded-lg px-4 py-3 text-sm text-center tracking-widest text-lg font-bold focus:ring-2 focus:ring-gaf-khaki border-gray-300">
                            <button @click="submitOtp()" :disabled="!otpCode || otpCode.length < 4"
                                    class="px-4 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition disabled:bg-gray-300 disabled:cursor-not-allowed">
                                Verify
                            </button>
                        </div>
                    </div>

                    {{-- Countdown --}}
                    <div class="mt-4">
                        <p class="text-xs text-gray-400">
                            Waiting for payment confirmation...
                            <span class="font-mono font-bold" x-text="formatCountdown()"></span>
                        </p>
                    </div>

                    {{-- Cancel / Retry --}}
                    <div class="mt-6">
                        <button @click="cancelPayment()" class="text-sm text-red-500 hover:text-red-700 underline">Cancel Payment</button>
                    </div>
                </div>
            </template>

            {{-- Card Processing --}}
            <template x-if="payment_method === 'card'">
                <div>
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-blue-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </div>
                    <h2 class="font-heading font-bold text-xl text-gray-800 mb-2">Processing Card Payment</h2>
                    <p class="text-gray-600">Please wait while we process your payment...</p>
                </div>
            </template>

            {{-- Bank Transfer Processing --}}
            <template x-if="payment_method === 'bank_transfer'">
                <div>
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <h2 class="font-heading font-bold text-xl text-gray-800 mb-4">Bank Transfer Details</h2>

                    <div x-show="bankDetails" class="bg-white border border-gray-200 rounded-xl p-6 max-w-md mx-auto text-left space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">Bank:</span>
                            <span class="font-semibold" x-text="bankDetails.bank_name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">Account Name:</span>
                            <span class="font-semibold" x-text="bankDetails.account_name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">Account Number:</span>
                            <span class="font-mono font-bold text-lg text-gaf-green" x-text="bankDetails.account_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">Amount:</span>
                            <span class="font-bold">GHS <span x-text="bankDetails.amount"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">Deadline:</span>
                            <span class="text-sm text-red-500" x-text="bankDetails.deadline"></span>
                        </div>
                    </div>

                    <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-4 max-w-md mx-auto text-left text-sm text-amber-700">
                        <p class="font-semibold mb-1">Instructions:</p>
                        <ol class="list-decimal list-inside space-y-1 text-xs">
                            <li>Transfer the exact amount to the account above.</li>
                            <li>Use your preferred banking app or visit a bank branch.</li>
                            <li>Once the transfer is complete, we will verify it automatically.</li>
                            <li>This page will update once your payment is confirmed.</li>
                        </ol>
                    </div>

                    <div class="mt-6">
                        <p class="text-sm text-gray-500 mb-2">Waiting for payment confirmation...</p>
                        <p class="text-xs text-gray-400">This usually takes a few minutes after transfer.</p>
                        <button @click="cancelPayment()" class="mt-4 text-sm text-red-500 hover:text-red-700 underline">Cancel</button>
                    </div>
                </div>
            </template>
        </div>

        {{-- STEP 3: SUCCESS --}}
        <div x-show="step === 'success'" x-cloak class="text-center py-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            </div>
            <h2 class="font-heading font-bold text-xl text-gray-800 mb-2">Payment Successful!</h2>
            <p class="text-gray-600 mb-6">Your voucher is being prepared. Redirecting to confirmation...</p>

            {{-- Payment receipt summary --}}
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 max-w-sm mx-auto text-left text-sm space-y-2" x-show="paymentReceipt">
                <div class="flex justify-between">
                    <span class="text-gray-500">Reference:</span>
                    <span class="font-mono font-semibold text-xs" x-text="paymentReceipt.reference"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Amount Paid:</span>
                    <span class="font-semibold">GHS <span x-text="paymentReceipt.amount"></span></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Channel:</span>
                    <span x-text="paymentReceipt.channel_label"></span>
                </div>
            </div>

            <div class="mt-4">
                <svg class="w-6 h-6 mx-auto animate-spin text-gaf-green" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </div>
        </div>

        {{-- STEP 4: FAILED --}}
        <div x-show="step === 'failed'" x-cloak class="text-center py-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h2 class="font-heading font-bold text-xl text-gray-800 mb-2">Payment Failed</h2>
            <p class="text-gray-600 mb-1" x-text="errorMessage || 'Your payment could not be processed.'"></p>
            <p class="text-xs text-gray-400 mb-6" x-text="errorDetail || ''"></p>
            <div class="flex gap-3 justify-center">
                <button @click="resetForm()" class="px-6 py-3 bg-gaf-green text-white rounded-lg font-semibold hover:bg-gaf-dark-green transition">Try Again</button>
                <a href="{{ route('voucher.buy') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</a>
            </div>
        </div>

        {{-- Step Indicator (only on form step) --}}
        <div x-show="step === 'form'">
            <p class="text-xs text-gray-400 text-center mt-3">By purchasing, you agree to the terms and conditions of the Ghana Armed Forces recruitment process.</p>
        </div>
    </div>

    {{-- Voucher Lookup Overlay (button + modal in one) --}}
    <div class="mt-8 text-center" x-data="{
        showLookup: false,
        lookupEmail: '',
        lookupTouched: false,
        lookupLoading: false,
        lookupResults: null,
        lookupError: '',
        async doLookup() {
            if (!this.lookupEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.lookupEmail)) return;
            this.lookupLoading = true;
            this.lookupError = '';
            try {
                const res = await fetch('{{ route("voucher.lookup") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: new URLSearchParams({ lookup_email: this.lookupEmail }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    this.lookupError = data.message || 'Lookup failed.';
                } else {
                    this.lookupResults = data.vouchers;
                    this.lookupEmailSearched = data.email;
                }
            } catch (e) {
                this.lookupError = 'Network error. Please try again.';
            }
            this.lookupLoading = false;
        }
    }">
        <button @click="showLookup = true; lookupEmail = ''; lookupResults = null; lookupError = ''; lookupTouched = false" class="text-sm text-gaf-green hover:text-gaf-dark-green font-semibold py-2 transition">
            Already purchased? Check your voucher
        </button>

        {{-- Lookup Overlay Modal (form + results together) --}}
        <div x-show="showLookup" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5);">
            <div @click.away="showLookup = false" class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[80vh] overflow-y-auto p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-heading font-semibold text-lg text-gray-800">Check Your Voucher</h3>
                    <button @click="showLookup = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>

                {{-- Search form (shown until results come back) --}}
                <template x-if="!lookupResults && !lookupLoading">
                    <div>
                        <p class="text-sm text-gray-500 mb-4">Enter the email address you used during purchase to look up your voucher details.</p>
                        <form method="POST" action="{{ route('voucher.lookup') }}" @submit.prevent="doLookup()">
                            <input type="email" x-model="lookupEmail"
                                   @input="lookupTouched = true"
                                   @blur="lookupTouched = true"
                                   placeholder="Your email address"
                                   required
                                   class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki transition mb-3"
                                   :class="lookupTouched ? (lookupEmail && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(lookupEmail) ? 'border-green-500' : 'border-red-500') : 'border-gray-300'">
                            <button type="submit"
                                    class="w-full px-5 py-3 rounded-lg text-sm font-semibold transition"
                                    :class="lookupEmail && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(lookupEmail) ? 'bg-gaf-green text-white hover:bg-gaf-dark-green' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                    :disabled="!lookupEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(lookupEmail)">
                                Look up
                            </button>
                        </form>
                        <p x-show="lookupError" x-cloak class="text-red-500 text-xs mt-2" x-text="lookupError"></p>
                    </div>
                </template>

                {{-- Loading --}}
                <template x-if="lookupLoading">
                    <div class="text-center py-10">
                        <svg class="w-8 h-8 mx-auto mb-3 text-gaf-green animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <p class="text-sm text-gray-500">Searching for your vouchers...</p>
                    </div>
                </template>

                {{-- Results --}}
                <template x-if="lookupResults && !lookupLoading">
                    <div>
                        <template x-if="lookupResults.length === 0">
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-gray-500 text-sm">No vouchers found for <strong x-text="lookupEmailSearched"></strong>.</p>
                                <p class="text-gray-400 text-xs mt-1">Make sure you enter the email used during purchase.</p>
                            </div>
                        </template>

                        <template x-if="lookupResults.length > 0">
                            <div class="space-y-4">
                                <p class="text-sm text-gray-500">Found <strong x-text="lookupResults.length"></strong> voucher<span x-text="lookupResults.length !== 1 ? 's' : ''"></span> for <strong x-text="lookupEmailSearched"></strong>:</p>
                                <template x-for="v in lookupResults" :key="v.id">
                                    <div class="border rounded-lg p-4" :class="v.status === 'available' ? 'border-green-200 bg-green-50/50' : (v.status === 'used' ? 'border-blue-200 bg-blue-50/50' : 'border-gray-200')">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="font-heading font-semibold text-sm text-gray-700" x-text="v.cycle_name"></span>
                                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                                                  :class="v.status === 'available' ? 'bg-green-100 text-green-700' : (v.status === 'used' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600')"
                                                  x-text="v.status.charAt(0).toUpperCase() + v.status.slice(1)"></span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                                            <div><span class="text-gray-400">Serial:</span> <span class="font-mono font-semibold text-gray-700" x-text="v.serial_number"></span></div>
                                            <div><span class="text-gray-400">PIN:</span> <span class="font-mono font-semibold text-gray-700" x-text="v.pin_code"></span></div>
                                            <div><span class="text-gray-400">Purchased:</span> <span class="text-gray-600" x-text="v.purchased_at"></span></div>
                                            <div><span class="text-gray-400">Expires:</span> <span class="text-gray-600" x-text="v.expires_at"></span></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div class="mt-5 flex justify-between items-center">
                            <button @click="lookupResults = null; lookupEmail = ''; lookupError = ''" class="text-sm text-gray-500 hover:text-gray-700 underline">Search again</button>
                            <button @click="showLookup = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">Close</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

{{-- Paystack.js scripts --}}
<script src="https://js.paystack.co/v1/inline.js"></script>

<script>
function voucherForm() {
    return {
        // -- Form Data --
        cycle_id: '{{ old("cycle_id", $selectedCycleId ?? "") }}',
        purchaser_name: '{{ old("purchaser_name") }}',
        purchaser_email: '{{ old("purchaser_email") }}',
        purchaser_phone: '{{ old("purchaser_phone") }}',
        payment_method: '{{ old("payment_method") }}',

        // -- Payment Channel Specific --
        momo_provider: '',
        momo_phone: '',
        bank_code: '',

        // -- Flow State --
        step: 'form',          // form | processing | success | failed
        submitting: false,

        // -- Card (PaystackPop popup) --
        cardError: '',
        cardPopupOpen: false,
        cardInitDone: false,

        // -- Processing State --
        paymentId: null,
        voucherId: null,
        reference: null,
        displayText: '',
        otpRequired: false,
        otpCode: '',
        countdown: 180,
        countdownTimer: null,
        pollTimer: null,
        bankDetails: null,
        errorMessage: '',
        errorDetail: '',
        paymentReceipt: null,

        // -- Touched State --
        touched: {
            cycle_id: false,
            purchaser_name: false,
            purchaser_email: false,
            purchaser_phone: false,
            payment_method: false,
        },

        // -- Initialization --
        initForm() {},

        // -- Computed --
        get submitButtonText() {
            const labels = {
                mobile_money: 'Pay with Mobile Money',
                card: 'Pay with Card',
                bank_transfer: 'Get Account Details',
                bank_deposit: 'Confirm Bank Deposit',
            };
            return labels[this.payment_method] || 'Purchase Voucher';
        },

        // -- Input Filters --
        filterName() {
            this.purchaser_name = this.purchaser_name.replace(/[^A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF\s'\-]/g, '');
        },
        filterPhone() {
            this.purchaser_phone = this.purchaser_phone.replace(/\D/g, '').substring(0, 10);
        },

        // -- Common: Initialize Payment via AJAX --
        async initPayment() {
            const payload = {
                cycle_id: this.cycle_id,
                purchaser_name: this.purchaser_name,
                purchaser_email: this.purchaser_email,
                purchaser_phone: this.purchaser_phone,
                payment_method: this.payment_method,
            };

            if (this.payment_method === 'mobile_money') {
                payload.momo_provider = this.momo_provider;
                payload.momo_phone = this.momo_phone;
            } else if (this.payment_method === 'card') {
                // Card: initPayment returns reference+amount for PaystackPop popup
            } else if (this.payment_method === 'bank_transfer') {
                payload.bank_code = this.bank_code;
            }

            try {
                const res = await fetch('{{ route("voucher.init-payment") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    this.step = 'failed';
                    this.errorMessage = data.message || 'Payment could not be initiated.';
                    this.errorDetail = data.gateway_response || '';
                    this.submitting = false;
                    return;
                }

                this.paymentId = data.payment_id;
                this.reference = data.reference;
                this.displayText = data.display_text || '';
                if (data.voucher_id) this.voucherId = data.voucher_id;

                // Handle channel-specific response
                if (this.payment_method === 'bank_transfer' && data.bank_details) {
                    this.bankDetails = data.bank_details;
                }

                // Handle OTP (Telecel)
                if (data.status === 'send_otp') {
                    this.otpRequired = true;
                }

                // Handle instant success — voucher was already created by server
                if (data.status === 'success' && data.voucher_id) {
                    this.voucherId = data.voucher_id;
                    this.step = 'success';
                    this.paymentReceipt = {
                        reference: data.reference,
                        amount: this.getCyclePrice(),
                        channel_label: this.getChannelLabel(this.payment_method),
                    };
                    this.submitting = false;
                    this.redirectToConfirmation();
                    return;
                }

                // For card: open PaystackPop popup with full amount
                if (this.payment_method === 'card') {
                    this.submitting = false;
                    this.openCardPopup(data);
                    return;
                }

                // For MoMo and Bank Transfer: show processing state + poll
                this.step = 'processing';
                this.submitting = false;
                this.startPolling();

                if (this.payment_method === 'mobile_money') {
                    this.startCountdown();
                }

            } catch (e) {
                this.step = 'failed';
                this.errorMessage = 'Network error. Please check your connection and try again.';
                this.errorDetail = '';
                this.submitting = false;
            }
        },

        // -- Open PaystackPop popup for card payments --
        openCardPopup(data) {
            const pubKey = '{{ config('services.paystack.public_key') }}';
            if (!pubKey) {
                this.step = 'failed';
                this.errorMessage = 'Payment service not configured.';
                return;
            }

            this.cardPopupOpen = true;

            const handler = PaystackPop.setup({
                key: pubKey,
                email: this.purchaser_email,
                amount: this.getCyclePricePesewas(),
                currency: 'GHS',
                ref: data.reference,
                metadata: {
                    payment_id: this.paymentId,
                },
                callback: (response) => {
                    // Payment completed in popup — now verify server-side
                    this.cardPopupOpen = false;
                    this.submitting = true;
                    this.verifyCardPayment(response.reference);
                },
                onClose: () => {
                    this.cardPopupOpen = false;
                    this.submitting = false;
                    // User closed popup — redirect back to form
                    this.step = 'failed';
                    this.errorMessage = 'Card payment was cancelled.';
                }
            });

            handler.openIframe();
        },

        // -- Verify card payment after popup success --
        async verifyCardPayment(reference) {
            // Poll payment status until verified
            this.step = 'processing';
            this.startPolling();
        },

        // -- Polling --
        startPolling() {
            if (this.pollTimer) clearInterval(this.pollTimer);
            this.pollTimer = setInterval(async () => {
                try {
                    const res = await fetch(`/payment/${this.paymentId}/status`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await res.json();

                    if (data.status === 'success') {
                        this.handlePaymentSuccess(data);
                    } else if (data.status === 'failed') {
                        this.handlePaymentFailed(data.gateway_response || 'Payment was not completed.');
                    }
                } catch (e) {
                    // Silently retry
                }
            }, 5000);
        },

        handlePaymentSuccess(data = {}) {
            if (this.pollTimer) clearInterval(this.pollTimer);
            if (this.countdownTimer) clearInterval(this.countdownTimer);

            // Server may return voucher_id now that payment succeeded
            if (data.voucher_id) this.voucherId = data.voucher_id;

            this.step = 'success';
            this.paymentReceipt = {
                reference: this.reference,
                amount: this.getCyclePrice(),
                channel_label: this.getChannelLabel(this.payment_method),
            };
            this.submitting = false;

            this.redirectToConfirmation();
        },

        handlePaymentFailed(message) {
            if (this.pollTimer) clearInterval(this.pollTimer);
            if (this.countdownTimer) clearInterval(this.countdownTimer);

            this.step = 'failed';
            this.errorMessage = message || 'Payment was not completed.';
        },

        redirectToConfirmation() {
            setTimeout(() => {
                window.location.href = `{{ url('voucher') }}/${this.voucherId}/confirmation`;
            }, 2000);
        },

        // -- Countdown (MoMo 180s) --
        startCountdown() {
            this.countdown = 180;
            if (this.countdownTimer) clearInterval(this.countdownTimer);
            this.countdownTimer = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) {
                    clearInterval(this.countdownTimer);
                    this.handlePaymentFailed('Payment timed out. Please try again.');
                }
            }, 1000);
        },

        formatCountdown() {
            const m = Math.floor(this.countdown / 60);
            const s = this.countdown % 60;
            return `${m}:${s.toString().padStart(2, '0')}`;
        },

        // -- OTP Submission (Telecel) --
        async submitOtp() {
            if (!this.otpCode || this.otpCode.length < 4) return;

            try {
                const res = await fetch('{{ route("voucher.submit-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ reference: this.reference, otp: this.otpCode }),
                });

                const data = await res.json();

                if (data.success && data.status === 'success') {
                    this.handlePaymentSuccess();
                } else if (!data.success) {
                    this.errorMessage = data.message || 'Invalid OTP. Please try again.';
                }
            } catch (e) {
                this.errorMessage = 'Network error. Please try again.';
            }
        },

        // -- Cancel --
        cancelPayment() {
            if (this.pollTimer) clearInterval(this.pollTimer);
            if (this.countdownTimer) clearInterval(this.countdownTimer);
            this.resetForm();
        },

        // -- Reset --
        resetForm() {
            if (this.pollTimer) clearInterval(this.pollTimer);
            if (this.countdownTimer) clearInterval(this.countdownTimer);
            this.step = 'form';
            this.submitting = false;
            this.cardError = '';
            this.paymentId = null;
            this.voucherId = null;
            this.reference = null;
            this.displayText = '';
            this.otpRequired = false;
            this.otpCode = '';
            this.bankDetails = null;
            this.errorMessage = '';
            this.errorDetail = '';
            this.paymentReceipt = null;
            this.countdown = 180;
            this.momo_provider = '';
            this.momo_phone = '';
            this.bank_code = '';
        },

        // -- Form Submit --
        async submitForm(e) {
            Object.keys(this.touched).forEach(k => this.touched[k] = true);
            if (!this.allValid) return;

            // Bank Deposit: traditional form POST
            if (this.payment_method === 'bank_deposit') {
                this.submitting = true;
                e.target.submit();
                return;
            }

            e.preventDefault();
            this.submitting = true;

            // All Paystack channels: call initPayment
            await this.initPayment();
        },

        // -- Validation --
        get validCycle()   { return this.cycle_id !== ''; },
        get validName()    { return /^[A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF\s'\-]{2,}$/.test(this.purchaser_name); },
        get validEmail()   { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.purchaser_email); },
        get validPhone()   { return /^\d{10}$/.test(this.purchaser_phone); },
        get validPayment() { return this.payment_method !== ''; },
        get validMomo() {
            if (this.payment_method !== 'mobile_money') return true;
            return this.momo_provider !== '' && /^\d{10}$/.test(this.momo_phone);
        },
        get validBankTransfer() {
            if (this.payment_method !== 'bank_transfer') return true;
            return this.bank_code !== '';
        },

        get allValid() {
            return this.validCycle && this.validName && this.validEmail && this.validPhone
                && this.validPayment && this.validMomo && this.validBankTransfer;
        },

        // -- CSS Classes --
        inputClass(field) {
            if (!this.touched[field]) return 'border-gray-300';
            const validators = {
                cycle_id: 'validCycle',
                purchaser_name: 'validName',
                purchaser_email: 'validEmail',
                purchaser_phone: 'validPhone',
                payment_method: 'validPayment',
            };
            return this[validators[field]] ? 'border-green-500' : 'border-red-500';
        },

        // -- Helpers --
        getCyclePrice() {
            const checked = document.querySelector('input[name="cycle_id"]:checked');
            if (!checked) return '0.00';
            const label = checked.closest('label');
            const priceEl = label?.querySelector('.text-white.font-bold');
            return priceEl?.textContent?.replace('GHS ', '')?.trim() || '0.00';
        },

        getCyclePricePesewas() {
            const price = parseFloat(this.getCyclePrice());
            return Math.round(price * 100);
        },

        getChannelLabel(channel) {
            const labels = {
                mobile_money: 'Mobile Money',
                card: 'Debit/Credit Card',
                bank_transfer: 'Bank Transfer',
                bank_deposit: 'Bank Deposit',
            };
            return labels[channel] || channel;
        },

        momoProviderLabel(provider) {
            const labels = { mtn: 'MTN MoMo', atl: 'AirtelTigo Money', vod: 'Telecel Cash (Vodafone)' };
            return labels[provider] || provider;
        },
    };
}
</script>
@endsection
