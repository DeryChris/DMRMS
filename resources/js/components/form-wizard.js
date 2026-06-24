export default function formWizard() {
    return {
        currentStep: 1,
        totalSteps: 4,
        formData: {
            personal: {
                first_name: '',
                last_name: '',
                date_of_birth: '',
                gender: '',
                nationality: 'Ghanaian',
                phone: '',
                email: '',
            },
            education: {
                highest_level: '',
                institution: '',
                year_completed: '',
                certificate_number: '',
            },
            military: {
                branch_preference: '',
                has_prior_service: false,
                prior_rank: '',
                medical_conditions: '',
                is_physically_fit: true,
            },
            documents: {
                birth_certificate: null,
                education_certificate: null,
                national_id: null,
                passport_photo: null,
            },
        },
        errors: {},
        saved: false,
        submitting: false,
        submitted: false,

        get progress() {
            return (this.currentStep / this.totalSteps) * 100;
        },

        get stepLabel() {
            const labels = ['Personal Information', 'Education Details', 'Military Background', 'Documents & Review'];
            return labels[this.currentStep - 1] || '';
        },

        next() {
            if (!this.validateStep()) return;
            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.autoSave();
            }
        },

        prev() {
            if (this.currentStep > 1) this.currentStep--;
        },

        goToStep(step) {
            if (step >= 1 && step <= this.totalSteps && step <= this.currentStep + 1) {
                this.currentStep = step;
            }
        },

        validateStep() {
            this.errors = {};
            const step = this.currentStep;

            if (step === 1) {
                const p = this.formData.personal;
                if (!p.first_name?.trim()) this.errors['personal.first_name'] = 'First name is required.';
                if (!p.last_name?.trim()) this.errors['personal.last_name'] = 'Last name is required.';
                if (!p.date_of_birth) this.errors['personal.date_of_birth'] = 'Date of birth is required.';
                if (!p.gender) this.errors['personal.gender'] = 'Gender is required.';
                if (!p.phone?.trim()) this.errors['personal.phone'] = 'Phone number is required.';
                if (p.phone && !/^0\d{9}$/.test(p.phone)) this.errors['personal.phone'] = 'Invalid phone format. Use 0XXXXXXXXX.';
                if (p.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(p.email)) this.errors['personal.email'] = 'Invalid email format.';
            } else if (step === 2) {
                const e = this.formData.education;
                if (!e.highest_level) this.errors['education.highest_level'] = 'Highest education level is required.';
                if (!e.institution?.trim()) this.errors['education.institution'] = 'Institution name is required.';
                if (!e.year_completed) this.errors['education.year_completed'] = 'Year completed is required.';
                if (e.year_completed && (parseInt(e.year_completed) < 1960 || parseInt(e.year_completed) > new Date().getFullYear())) {
                    this.errors['education.year_completed'] = 'Invalid year.';
                }
            } else if (step === 3) {
                const m = this.formData.military;
                if (!m.branch_preference) this.errors['military.branch_preference'] = 'Branch preference is required.';
                if (m.has_prior_service && !m.prior_rank?.trim()) this.errors['military.prior_rank'] = 'Prior rank is required.';
            }

            return Object.keys(this.errors).length === 0;
        },

        autoSave() {
            const debounced = localStorage.getItem('formWizardAutoSave');
            if (debounced) {
                clearTimeout(parseInt(debounced));
            }
            const id = setTimeout(() => {
                try {
                    localStorage.setItem('formWizardData', JSON.stringify(this.formData));
                    localStorage.setItem('formWizardStep', this.currentStep.toString());
                    this.saved = true;
                    setTimeout(() => { this.saved = false; }, 2000);
                    window.axios.post('/api/applications/auto-save', {
                        form_data: this.formData,
                        current_step: this.currentStep,
                    }).catch(() => {});
                } catch (e) {
                    console.warn('Auto-save failed:', e);
                }
            }, 500);
            localStorage.setItem('formWizardAutoSave', id.toString());
        },

        loadSaved() {
            const saved = localStorage.getItem('formWizardData');
            const step = localStorage.getItem('formWizardStep');
            if (saved) {
                try {
                    this.formData = JSON.parse(saved);
                } catch (e) {
                    localStorage.removeItem('formWizardData');
                }
            }
            if (step) {
                const s = parseInt(step);
                if (s >= 1 && s <= this.totalSteps) this.currentStep = s;
            }
        },

        async submit() {
            if (!this.validateStep()) return;
            this.submitting = true;
            try {
                const response = await window.axios.post('/api/applications', this.formData);
                localStorage.removeItem('formWizardData');
                localStorage.removeItem('formWizardStep');
                this.submitted = true;
                this.submittedId = response.data.id;
            } catch (e) {
                this.errors = e.response?.data?.errors || { submit: ['Failed to submit application. Please try again.'] };
            } finally {
                this.submitting = false;
            }
        },

        init() {
            this.loadSaved();
        },
    };
}
