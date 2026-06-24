export default function eligibilityChecker() {
    return {
        age: '',
        nationality: '',
        education: '',
        height: '',
        weight: '',
        hasTattoos: false,
        hasCriminalRecord: false,
        errors: {},
        result: null,
        checking: false,

        validEducations: [
            'none',
            'primary',
            'jhs',
            'shs',
            'diploma',
            'bachelor',
            'master',
            'doctorate',
        ],

        validate() {
            this.errors = {};

            if (!this.age || isNaN(this.age)) {
                this.errors.age = 'Age is required.';
            } else {
                const ageNum = parseInt(this.age);
                if (ageNum < 18 || ageNum > 30) {
                    this.errors.age = 'Age must be between 18 and 30 years.';
                }
            }

            if (!this.nationality || this.nationality.trim() === '') {
                this.errors.nationality = 'Nationality is required.';
            } else if (this.nationality.toLowerCase() !== 'ghanaian' && this.nationality.toLowerCase() !== 'ghana') {
                this.errors.nationality = 'You must be a Ghanaian citizen to apply.';
            }

            if (!this.education) {
                this.errors.education = 'Education level is required.';
            } else if (!this.validEducations.includes(this.education)) {
                this.errors.education = 'Invalid education level selected.';
            } else if (['none', 'primary'].includes(this.education)) {
                this.errors.education = 'Minimum education requirement is JHS.';
            }

            if (!this.height || isNaN(this.height)) {
                this.errors.height = 'Height is required.';
            } else {
                const h = parseFloat(this.height);
                if (h < 1.5) {
                    this.errors.height = 'Minimum height is 1.5m.';
                } else if (h > 2.5) {
                    this.errors.height = 'Height seems unrealistic. Please verify.';
                }
            }

            if (this.weight && !isNaN(this.weight)) {
                const w = parseFloat(this.weight);
                if (w < 40) {
                    this.errors.weight = 'Minimum weight is 40kg.';
                } else if (w > 150) {
                    this.errors.weight = 'Weight seems unrealistic. Please verify.';
                }
            }

            return Object.keys(this.errors).length === 0;
        },

        async check() {
            if (!this.validate()) return;
            this.checking = true;
            try {
                const response = await window.axios.post('/api/eligibility/check', {
                    age: parseInt(this.age),
                    nationality: this.nationality,
                    education: this.education,
                    height: parseFloat(this.height),
                    weight: this.weight ? parseFloat(this.weight) : null,
                    has_tattoos: this.hasTattoos,
                    has_criminal_record: this.hasCriminalRecord,
                });
                this.result = response.data;
            } catch (e) {
                this.result = {
                    eligible: false,
                    confidence: 0,
                    reasons: ['Unable to verify eligibility at this time. Please try again later.'],
                };
            } finally {
                this.checking = false;
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

        educationLabel(value) {
            const labels = {
                none: 'No Formal Education',
                primary: 'Primary',
                jhs: 'JHS',
                shs: 'SHS',
                diploma: 'Diploma',
                bachelor: 'Bachelor\'s Degree',
                master: 'Master\'s Degree',
                doctorate: 'Doctorate',
            };
            return labels[value] || value;
        },
    };
}
