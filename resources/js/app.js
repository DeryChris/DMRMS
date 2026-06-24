import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

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

    Alpine.data('notificationBell', () => ({
        open: false,
        showAll: false,
        loading: true,
        allLoading: false,
        items: [],
        allItems: [],
        expanded: null,
        allExpanded: null,
        unread: 0,
        total: 0,
        allPage: 1,
        allHasMore: false,

        init() {
            this.fetch();
        },

        toggle() {
            this.open = !this.open;
            if (this.open && this.items.length === 0) {
                this.fetch();
            }
        },

        async fetch() {
            this.loading = true;
            try {
                const res = await fetch('/notifications/fetch?per_page=5');
                const json = await res.json();
                this.items = json.data;
                this.unread = json.meta.unread_count;
            } catch (e) {
                // silent
            }
            this.loading = false;
        },

        async toggleExpand(i, item) {
            if (this.expanded === i) {
                this.expanded = null;
                return;
            }
            this.expanded = i;
            if (!item.read_at) {
                item.read_at = new Date().toISOString();
                this.unread = Math.max(0, this.unread - 1);
                try {
                    await fetch(`/notifications/${item.id}/read`, {
                        method: 'PUT',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                    });
                } catch (e) {
                    // silent
                }
            }
        },

        async expandAll(i, item) {
            if (this.allExpanded === i) {
                this.allExpanded = null;
                return;
            }
            this.allExpanded = i;
            if (!item.read_at) {
                item.read_at = new Date().toISOString();
                this.unread = Math.max(0, this.unread - 1);
                try {
                    await fetch(`/notifications/${item.id}/read`, {
                        method: 'PUT',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                    });
                } catch (e) {
                    // silent
                }
            }
        },

        async markAllRead() {
            try {
                await fetch('/notifications/read-all', {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                });
                this.items.forEach(item => item.read_at = new Date().toISOString());
                this.unread = 0;
            } catch (e) {
                // silent
            }
        },

        async loadMore() {
            this.allPage++;
            this.allLoading = true;
            try {
                const res = await fetch(`/notifications/fetch?per_page=10&page=${this.allPage}`);
                const json = await res.json();
                this.allItems = [...this.allItems, ...json.data];
                this.allHasMore = json.meta.current_page < json.meta.last_page;
            } catch (e) {
                // silent
            }
            this.allLoading = false;
        },

        timeAgo(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            const now = new Date();
            const diff = Math.floor((now - d) / 1000);
            if (diff < 60) return 'just now';
            if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
            if (diff < 2592000) return Math.floor(diff / 86400) + 'd ago';
            return d.toLocaleDateString();
        },

        initAll() {
            this.allPage = 1;
            this.allItems = [];
            this.allLoading = true;
            this.allHasMore = false;
            fetch('/notifications/fetch?per_page=10')
                .then(r => r.json())
                .then(json => {
                    this.allItems = json.data;
                    this.allHasMore = json.meta.current_page < json.meta.last_page;
                    this.total = json.meta.total;
                })
                .finally(() => { this.allLoading = false; });
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

Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.plugins.legend.position = 'bottom';

window.Alpine = Alpine;
Alpine.start();
