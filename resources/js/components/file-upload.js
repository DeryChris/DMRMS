export default function fileUpload() {
    return {
        files: [],
        dragging: false,
        maxSize: 2 * 1024 * 1024,
        allowedTypes: ['image/jpeg', 'image/png', 'application/pdf'],
        errors: [],
        uploading: false,

        get totalSize() {
            return this.files.reduce((acc, f) => acc + f.size, 0);
        },

        get hasFiles() {
            return this.files.length > 0;
        },

        get canUpload() {
            return this.hasFiles && !this.uploading && this.errors.length === 0;
        },

        handleDrop(e) {
            this.dragging = false;
            e.preventDefault();
            const droppedFiles = Array.from(e.dataTransfer?.files || []);
            this.addFiles(droppedFiles);
        },

        handleDragOver(e) {
            e.preventDefault();
            this.dragging = true;
        },

        handleDragLeave(e) {
            e.preventDefault();
            this.dragging = false;
        },

        handleInput(e) {
            const selectedFiles = Array.from(e.target?.files || []);
            this.addFiles(selectedFiles);
            e.target.value = '';
        },

        addFiles(newFiles) {
            newFiles.forEach(file => {
                if (file.size > this.maxSize) {
                    this.errors.push(`${file.name} exceeds the 2MB file size limit.`);
                    return;
                }
                if (!this.allowedTypes.includes(file.type)) {
                    this.errors.push(`${file.name} has an invalid type. Allowed: JPEG, PNG, PDF.`);
                    return;
                }
                if (this.files.some(f => f.name === file.name)) {
                    this.errors.push(`${file.name} has already been added.`);
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.files.push({
                        id: Date.now().toString(36) + Math.random().toString(36).slice(2),
                        name: file.name,
                        size: file.size,
                        type: file.type,
                        preview: file.type.startsWith('image/') ? e.target.result : null,
                        file: file,
                        uploaded: false,
                    });
                };
                reader.readAsDataURL(file);
            });
        },

        removeFile(index) {
            this.files.splice(index, 1);
        },

        clearAll() {
            this.files = [];
            this.errors = [];
        },

        formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(2) + ' MB';
        },

        fileIcon(type) {
            if (type === 'application/pdf') return 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z';
            return 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z';
        },

        async uploadFiles() {
            if (!this.canUpload) return;
            this.uploading = true;
            this.errors = [];
            const formData = new FormData();
            this.files.forEach((f, i) => {
                formData.append(`files[${i}]`, f.file, f.name);
            });
            formData.append('total', this.files.length.toString());
            try {
                const response = await window.axios.post('/api/documents/upload', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                    onUploadProgress: (e) => {
                        const pct = Math.round((e.loaded / e.total) * 100);
                        this.files.forEach(f => { f.progress = pct; });
                    },
                });
                this.files.forEach(f => { f.uploaded = true; });
                return response.data;
            } catch (e) {
                this.errors.push('Upload failed. Please try again.');
                throw e;
            } finally {
                this.uploading = false;
            }
        },

        init() {
            document.addEventListener('dragover', (e) => e.preventDefault());
            document.addEventListener('drop', (e) => e.preventDefault());
        },
    };
}
