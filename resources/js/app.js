import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import QRCode from 'qrcode';

Alpine.data('flashToast', (type, msg) => ({
    show: true,
    type: type,
    msg: msg,
    init() {
        if (this.msg) {
            setTimeout(() => { this.show = false; }, 4000);
        }
    },
}));

Alpine.data('documentViewer', (doc, admin, docs, initialIndex) => ({
    show: false,
    doc: doc,
    admin: admin,
    documents: docs,
    currentIndex: initialIndex,
    zoomed: false,

    get isImage() {
        return this.doc.mime_type && this.doc.mime_type.startsWith('image/');
    },

    get isPdf() {
        return this.doc.mime_type === 'application/pdf';
    },

    open() {
        this.show = true;
        document.body.style.overflow = 'hidden';
    },

    close() {
        this.show = false;
        this.zoomed = false;
        document.body.style.overflow = '';
    },

    toggleZoom() {
        this.zoomed = !this.zoomed;
    },

    prevDoc() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.doc = this.documents[this.currentIndex];
            this.zoomed = false;
        }
    },

    nextDoc() {
        if (this.currentIndex < this.documents.length - 1) {
            this.currentIndex++;
            this.doc = this.documents[this.currentIndex];
            this.zoomed = false;
        }
    },
}));

document.addEventListener('alpine:init', () => {
    Alpine.data('eligibilityChecker', () => ({
        age: '',
        nationality: '',
        education: '',
        height: '',
        weight: '',
        hasTattoos: false,
        hasCriminalRecord: false,
        errors: {},
        result: null,

        validate() {
            this.errors = {};
            if (!this.age || this.age < 18 || this.age > 30) {
                this.errors.age = 'Age must be between 18 and 30.';
            }
            if (!this.nationality || this.nationality !== 'Ghanaian') {
                this.errors.nationality = 'Must be a Ghanaian citizen.';
            }
            if (!this.education) {
                this.errors.education = 'Education level is required.';
            }
            if (!this.height || this.height < 1.5) {
                this.errors.height = 'Minimum height is 1.5m.';
            }
            return Object.keys(this.errors).length === 0;
        },

        async check() {
            if (!this.validate()) return;
            try {
                const response = await window.axios.post('/api/eligibility/check', {
                    age: this.age,
                    nationality: this.nationality,
                    education: this.education,
                    height: this.height,
                    weight: this.weight,
                    has_tattoos: this.hasTattoos,
                    has_criminal_record: this.hasCriminalRecord,
                });
                this.result = response.data;
            } catch (e) {
                this.result = { eligible: false, errors: ['Service unavailable. Please try again.'] };
            }
        },

        reset() {
            this.age = '';
            this.nationality = '';
            this.education = '';
            this.height = '';
            this.weight = '';
            this.hasTattoos = false;
            this.hasCriminalRecord = false;
            this.errors = {};
            this.result = null;
        },
    }));

    Alpine.data('formWizard', () => ({
        currentStep: 1,
        totalSteps: 4,
        formData: {},
        errors: {},
        saved: false,

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

        get progress() {
            return (this.currentStep / this.totalSteps) * 100;
        },

        validateStep() {
            this.errors = {};
            return true;
        },

        autoSave() {
            localStorage.setItem('formWizardData', JSON.stringify(this.formData));
            this.saved = true;
            setTimeout(() => { this.saved = false; }, 2000);
        },

        loadSaved() {
            const saved = localStorage.getItem('formWizardData');
            if (saved) {
                try {
                    this.formData = JSON.parse(saved);
                } catch (e) {
                    localStorage.removeItem('formWizardData');
                }
            }
        },

        async submit() {
            try {
                const response = await window.axios.post('/api/applications', this.formData);
                localStorage.removeItem('formWizardData');
                window.location.href = '/applications/' + response.data.id;
            } catch (e) {
                this.errors = e.response?.data?.errors || { submit: ['Submission failed.'] };
            }
        },

        init() {
            this.loadSaved();
        },
    }));

    Alpine.data('docUpload', () => ({
        dragging: false,
        docType: '',
        fileName: '',
        fileSize: '',
        fileType: '',
        fileObj: null,

        get hasFile() {
            return this.fileObj !== null;
        },

        get canSubmit() {
            return this.docType && this.hasFile;
        },

        handleDrop(e) {
            this.dragging = false;
            const file = e.dataTransfer?.files?.[0];
            if (file) this.setFile(file);
        },

        handleInput(e) {
            const file = e.target?.files?.[0];
            if (file) this.setFile(file);
        },

        setFile(file) {
            const maxSize = 5 * 1024 * 1024;
            const allowed = ['.pdf', '.jpg', '.jpeg', '.png'];
            const ext = '.' + file.name.split('.').pop().toLowerCase();
            if (!allowed.includes(ext)) return;
            if (file.size > maxSize) return;
            this.fileObj = file;
            this.fileName = file.name;
            this.fileType = file.type;
            this.fileSize = file.size < 1048576
                ? (file.size / 1024).toFixed(1) + ' KB'
                : (file.size / 1048576).toFixed(2) + ' MB';
            const dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById('fileInput').files = dt.files;
        },

        clearFile() {
            this.fileObj = null;
            this.fileName = '';
            this.fileSize = '';
            this.fileType = '';
            document.getElementById('fileInput').value = '';
        },
    })),

    Alpine.data('fileUpload', () => ({
        files: [],
        dragging: false,
        maxSize: 2 * 1024 * 1024,
        allowedTypes: ['image/jpeg', 'image/png', 'application/pdf'],
        errors: [],

        handleDrop(e) {
            this.dragging = false;
            const droppedFiles = Array.from(e.dataTransfer?.files || []);
            this.addFiles(droppedFiles);
        },

        handleInput(e) {
            const selectedFiles = Array.from(e.target?.files || []);
            this.addFiles(selectedFiles);
        },

        addFiles(newFiles) {
            this.errors = [];
            newFiles.forEach(file => {
                if (file.size > this.maxSize) {
                    this.errors.push(`${file.name} exceeds 2MB limit.`);
                    return;
                }
                if (!this.allowedTypes.includes(file.type)) {
                    this.errors.push(`${file.name} must be JPEG, PNG, or PDF.`);
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.files.push({
                        name: file.name,
                        size: file.size,
                        type: file.type,
                        preview: file.type.startsWith('image/') ? e.target.result : null,
                        file: file,
                    });
                };
                reader.readAsDataURL(file);
            });
        },

        removeFile(index) {
            this.files.splice(index, 1);
        },

        formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        },
    }));

    Alpine.data('chatbotWidget', () => ({
        open: false,
        messages: [{ role: 'bot', text: 'Welcome! How can I help you with your recruitment application?' }],
        input: '',
        loading: false,

        toggle() {
            this.open = !this.open;
        },

        async send() {
            if (!this.input.trim() || this.loading) return;
            const userMessage = this.input;
            this.messages.push({ role: 'user', text: userMessage });
            this.input = '';
            this.loading = true;
            try {
                const response = await window.axios.post('/api/ai/chat', {
                    message: userMessage,
                    session_id: localStorage.getItem('chatSessionId'),
                });
                this.messages.push({ role: 'bot', text: response.data.response });
                if (response.data.session_id) {
                    localStorage.setItem('chatSessionId', response.data.session_id);
                }
            } catch (e) {
                this.messages.push({ role: 'bot', text: 'Sorry, I am having trouble connecting. Please try again.' });
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('countUp', () => ({
        value: 0,
        target: 0,
        duration: 2000,
        startTime: null,
        rafId: null,

        init() {
            this.target = parseInt(this.$el.textContent) || 0;
            this.$el.textContent = '0';
            this.observe();
        },

        observe() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.countUp();
                        observer.disconnect();
                    }
                });
            });
            observer.observe(this.$el);
        },

        countUp() {
            this.startTime = performance.now();
            const animate = (currentTime) => {
                const elapsed = currentTime - this.startTime;
                const progress = Math.min(elapsed / this.duration, 1);
                this.value = Math.floor(progress * this.target);
                this.$el.textContent = this.value.toLocaleString();
                if (progress < 1) {
                    this.rafId = requestAnimationFrame(animate);
                } else {
                    this.$el.textContent = this.target.toLocaleString();
                }
            };
            this.rafId = requestAnimationFrame(animate);
        },

        destroy() {
            if (this.rafId) cancelAnimationFrame(this.rafId);
        },
    }));

    Alpine.data('statusTimeline', () => ({
        statuses: [
            { key: 'submitted', label: 'Application Submitted', date: null, icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
            { key: 'screened', label: 'Preliminary Screening', date: null, icon: 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0' },
            { key: 'shortlisted', label: 'Shortlisted', date: null, icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
            { key: 'interview', label: 'Interview', date: null, icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
            { key: 'medical', label: 'Medical Assessment', date: null, icon: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z' },
            { key: 'accepted', label: 'Final Decision', date: null, icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
        ],

        init() {
            this.fetchStatus();
        },

        async fetchStatus() {
            try {
                const id = this.$el.dataset.applicationId;
                const response = await window.axios.get(`/api/applications/${id}/timeline`);
                response.data.statuses.forEach(s => {
                    const match = this.statuses.find(st => st.key === s.key);
                    if (match) match.date = s.date;
                });
            } catch (e) {
                // silent
            }
        },

        get activeIndex() {
            const lastActive = this.statuses.filter(s => s.date).length;
            return Math.max(0, lastActive - 1);
        },
    }));
});

    Alpine.data('verificationCard', () => ({
        qrDataUrl: null,
        qrCanvas: null,
        code: '',
        copyLabel: 'Copy Code',
        name: '',
        gafId: '',
        appDate: '',
        appTime: '',
        appVenue: '',

        init() {
            this.code = this.$el.dataset.code || '';
            this.name = this.$el.dataset.name || '';
            this.gafId = this.$el.dataset.gaf || '';
            this.appDate = this.$el.dataset.date || '';
            this.appTime = this.$el.dataset.time || '';
            this.appVenue = this.$el.dataset.venue || '';

            if (this.code) {
                QRCode.toDataURL(this.code, { width: 200, margin: 2 })
                    .then(url => { this.qrDataUrl = url; })
                    .catch(() => { this.qrDataUrl = null; });

                const cvs = document.createElement('canvas');
                QRCode.toCanvas(cvs, this.code, { width: 200, margin: 2 })
                    .then(() => { this.qrCanvas = cvs; })
                    .catch(() => { this.qrCanvas = null; });
            }
        },

        copyCode() {
            if (!this.code) return;
            navigator.clipboard.writeText(this.code).then(() => {
                this.copyLabel = 'Copied!';
                setTimeout(() => { this.copyLabel = 'Copy Code'; }, 2000);
            }).catch(() => {
                const ta = document.createElement('textarea');
                ta.value = this.code;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                this.copyLabel = 'Copied!';
                setTimeout(() => { this.copyLabel = 'Copy Code'; }, 2000);
            });
        },

        downloadCard() {
            if (!this.qrCanvas || !this.code) return;
            const w = 420;
            const h = 600;
            const c = document.createElement('canvas');
            c.width = w;
            c.height = h;
            const ctx = c.getContext('2d');

            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, w, h);

            ctx.fillStyle = '#14532d';
            ctx.fillRect(0, 0, w, 60);
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 18px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('GHANA ARMED FORCES', w / 2, 28);
            ctx.font = '11px sans-serif';
            ctx.fillText('RECRUITMENT SCREENING CARD', w / 2, 48);

            const qrSize = 180;
            const qx = (w - qrSize) / 2;
            ctx.drawImage(this.qrCanvas, qx, 80);

            ctx.fillStyle = '#6b7280';
            ctx.font = '10px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('VERIFICATION CODE', w / 2, 290);

            ctx.fillStyle = '#111827';
            ctx.font = 'bold 28px "Courier New", monospace';
            ctx.textAlign = 'center';
            ctx.fillText(this.code, w / 2, 330);

            ctx.strokeStyle = '#e5e7eb';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(30, 355);
            ctx.lineTo(w - 30, 355);
            ctx.stroke();

            ctx.fillStyle = '#374151';
            ctx.font = '13px sans-serif';
            ctx.textAlign = 'left';
            let ly = 380;
            if (this.name) { ctx.fillText('Name: ' + this.name, 40, ly); ly += 24; }
            if (this.gafId) { ctx.fillText('GAF ID: ' + this.gafId, 40, ly); ly += 24; }
            if (this.appDate) { ctx.fillText('Date: ' + this.appDate, 40, ly); ly += 24; }
            if (this.appTime) { ctx.fillText('Time: ' + this.appTime, 40, ly); ly += 24; }
            if (this.appVenue) {
                ctx.font = '12px sans-serif';
                ctx.fillText('Venue: ' + this.appVenue, 40, ly);
            }

            ctx.fillStyle = '#9ca3af';
            ctx.font = '9px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('Present this card at the screening centre', w / 2, h - 25);

            const link = document.createElement('a');
            link.download = 'screening-card.png';
            link.href = c.toDataURL('image/png');
            link.click();
        },
    }));

    Alpine.data('autoSaveForm', (saveUrl, formKey) => ({
        saveStatus: '',
        saveTimer: null,
        lastSaved: null,

        triggerAutoSave(data) {
            if (this.saveTimer) clearTimeout(this.saveTimer);
            this.saveStatus = 'unsaved';
            this.saveTimer = setTimeout(() => this.doAutoSave(data), 2000);
        },

        async doAutoSave(data) {
            this.saveStatus = 'saving';
            try {
                await window.axios.post(saveUrl, data);
                this.saveStatus = 'saved';
                this.lastSaved = new Date();
                setTimeout(() => { if (this.saveStatus === 'saved') this.saveStatus = ''; }, 3000);
            } catch (e) {
                this.saveStatus = 'error';
                setTimeout(() => { if (this.saveStatus === 'error') this.saveStatus = ''; }, 5000);
            }
        },

        get saveLabel() {
            const labels = { saving: 'Saving...', saved: 'Saved', error: 'Save failed', unsaved: 'Unsaved changes' };
            return labels[this.saveStatus] || '';
        },

        draftSave(data) {
            if (!formKey) return;
            if (this.saveTimer) clearTimeout(this.saveTimer);
            this.saveStatus = 'unsaved';
            this.saveTimer = setTimeout(() => {
                try {
                    localStorage.setItem('draft_' + formKey, JSON.stringify(data));
                    this.saveStatus = 'saved';
                    setTimeout(() => { if (this.saveStatus === 'saved') this.saveStatus = ''; }, 3000);
                } catch (e) {
                    this.saveStatus = 'error';
                }
            }, 1500);
        },

        draftLoad() {
            if (!formKey) return null;
            try {
                const raw = localStorage.getItem('draft_' + formKey);
                return raw ? JSON.parse(raw) : null;
            } catch (e) {
                localStorage.removeItem('draft_' + formKey);
                return null;
            }
        },

        draftClear() {
            if (formKey) localStorage.removeItem('draft_' + formKey);
        },
    }));

    Alpine.data('screeningForm', () => ({
        currentStep: 0,
        code: '',
        applicant: null,
        saving: false,
        error: '',
        stepMsg: '',
        autoSaveStatus: '',
        saveTimer: null,
        success: false,
        finalStatus: '',
        steps: ['Verify', 'Medical', 'Fitness', 'Interview', 'Review'],
        form: {
            medical: {
                blood_pressure: '', heart_rate: '', vision_left: '', vision_right: '',
                hearing_test: '', height_cm: '', weight_kg: '', bmi: '',
                medical_status: '', notes: '',
            },
            fitness: {
                run_time_seconds: '', push_ups: '', sit_ups: '', pull_ups: '',
                shuttle_run: '', fitness_grade: '', fitness_score: '', notes: '',
            },
            interview: {
                communication: '5', confidence: '5', appearance: '5',
                knowledge: '5', attitude: '5', interview_score: '',
                interview_decision: '', notes: '',
            },
        },

        init() {
            this.$watch('form.medical', () => this.triggerAutoSave('medical'), { deep: true });
            this.$watch('form.fitness', () => this.triggerAutoSave('fitness'), { deep: true });
            this.$watch('form.interview', () => this.triggerAutoSave('interview'), { deep: true });

            this.$watch('form.medical.height_cm', () => this.calcBmi());
            this.$watch('form.medical.weight_kg', () => this.calcBmi());
        },

        calcBmi() {
            const h = parseFloat(this.form.medical.height_cm);
            const w = parseFloat(this.form.medical.weight_kg);
            if (h > 0 && w > 0) {
                this.form.medical.bmi = Math.round((w / (h * h)) * 10) / 10;
            }
        },

        triggerAutoSave(step) {
            if (!this.applicant || this.currentStep === 0 || this.currentStep === 4) return;
            const stepIndex = { medical: 1, fitness: 2, interview: 3 }[step];
            if (this.currentStep !== stepIndex) return;
            if (this.saveTimer) clearTimeout(this.saveTimer);
            this.autoSaveStatus = 'unsaved';
            this.saveTimer = setTimeout(() => this.doAutoSave(step), 3000);
        },

        async doAutoSave(step, retries = 1) {
            this.autoSaveStatus = 'saving';
            const endpoints = {
                medical: '/admin/screening/save-medical',
                fitness: '/admin/screening/save-fitness',
                interview: '/admin/screening/save-interview',
            };
            try {
                await window.axios.post(endpoints[step], {
                    application_id: this.applicant.application_id,
                    ...this.form[step],
                });
                this.autoSaveStatus = 'saved';
                setTimeout(() => { if (this.autoSaveStatus === 'saved') this.autoSaveStatus = ''; }, 3000);
            } catch (e) {
                if (retries > 0) {
                    setTimeout(() => this.doAutoSave(step, retries - 1), 2000);
                } else {
                    this.autoSaveStatus = 'error';
                    setTimeout(() => { if (this.autoSaveStatus === 'error') this.autoSaveStatus = ''; }, 5000);
                }
            }
        },

        async verifyCode() {
            this.error = '';
            this.applicant = null;
            if (!this.code.trim()) return;
            this.saving = true;
            try {
                const res = await window.axios.post('/admin/screening/verify-code', { code: this.code });
                this.applicant = res.data;
                this.code = '';
            } catch (e) {
                this.error = e.response?.data?.error || 'Verification failed.';
            } finally {
                this.saving = false;
            }
        },

        async saveMedical() {
            if (!this.form.medical.medical_status) { this.error = 'Please select a medical status.'; return; }
            this.error = '';
            this.stepMsg = '';
            this.saving = true;
            try {
                await window.axios.post('/admin/screening/save-medical', {
                    application_id: this.applicant.application_id,
                    ...this.form.medical,
                });
                this.stepMsg = 'Medical results saved.';
                this.currentStep = 2;
            } catch (e) {
                this.error = e.response?.data?.message || 'Failed to save medical results.';
            } finally {
                this.saving = false;
            }
        },

        async saveFitness() {
            if (!this.form.fitness.fitness_score) { this.error = 'Please enter a fitness score.'; return; }
            this.error = '';
            this.stepMsg = '';
            this.saving = true;
            try {
                await window.axios.post('/admin/screening/save-fitness', {
                    application_id: this.applicant.application_id,
                    ...this.form.fitness,
                });
                this.stepMsg = 'Fitness results saved.';
                this.currentStep = 3;
            } catch (e) {
                this.error = e.response?.data?.message || 'Failed to save fitness results.';
            } finally {
                this.saving = false;
            }
        },

        async saveInterview() {
            if (!this.form.interview.interview_decision) { this.error = 'Please select an interview decision.'; return; }
            this.error = '';
            this.stepMsg = '';
            this.saving = true;
            try {
                const res = await window.axios.post('/admin/screening/save-interview', {
                    application_id: this.applicant.application_id,
                    ...this.form.interview,
                });
                this.finalStatus = res.data.status || 'completed';
                this.currentStep = 4;
            } catch (e) {
                this.error = e.response?.data?.message || 'Failed to save interview results.';
            } finally {
                this.saving = false;
            }
        },
    }));

Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.plugins.legend.position = 'bottom';

window.Alpine = Alpine;
Alpine.start();
