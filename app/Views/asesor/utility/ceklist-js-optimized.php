<script>
    // Modern ES6+ Optimized Observasi Checklist JavaScript
    $(document).ready(function() {
        'use strict';

        // CLEAR ALL CACHES ON PAGE LOAD
        try {
            // Clear localStorage related to observasi
            Object.keys(localStorage).forEach(key => {
                if (key.includes('observasi') || key.includes('kuk')) {
                    localStorage.removeItem(key);
                }
            });

            // Clear sessionStorage related to observasi
            Object.keys(sessionStorage).forEach(key => {
                if (key.includes('observasi') || key.includes('kuk')) {
                    sessionStorage.removeItem(key);
                }
            });

            // Clear browser cache if available
            if ('caches' in window) {
                caches.keys().then(function(names) {
                    names.forEach(function(name) {
                        if (name.includes('observasi') || name.includes('api')) {
                            caches.delete(name);
                        }
                    });
                });
            }
        } catch (error) {
            // Silent error handling for cache clearing
        }

        // Configuration constants
        const CONFIG = {
            DEBOUNCE_DELAY: 500,
            AUTO_SAVE_INTERVAL: 30000, // 30 seconds
            MAX_RETRY_ATTEMPTS: 3,
            RETRY_DELAY: 1000,
            STORAGE_KEY: 'observasi_state',
            PERFORMANCE_THRESHOLD: 3000, // 3 seconds
            BATCH_SIZE: 50
        };

        // API endpoints
        const API = {
            getAsesi: '<?= base_url('/api/observasi/asesi') ?>',
            loadObservasi: '<?= base_url('/api/observasi/load') ?>',
            saveObservasi: '<?= base_url('/api/observasi/save') ?>',
            batchSave: '<?= base_url('/api/observasi/batch') ?>',
            singleKuk: '<?= base_url('/api/observasi/single') ?>',
            statistics: '<?= base_url('/api/observasi/statistics') ?>',
            progress: '<?= base_url('/api/observasi/progress') ?>'
        };

        // Application state management
        class ObservasiState {
            constructor() {
                this.data = {
                    id_asesmen: '',
                    id_skema: '',
                    id_asesi: '',
                    tanggal_observasi: '',
                    totalKUK: 0,
                    completedKUK: 0,
                    pendingChanges: new Set(),
                    saveQueue: new Map(),
                    isProcessing: false,
                    retryCount: 0,
                    csrfName: '<?= csrf_token() ?>',
                    csrfHash: '<?= csrf_hash() ?>',
                    lastSyncTime: null,
                    offline: false
                };
                this.listeners = new Map();
                this.autoSaveTimer = null;
            }

            set(key, value) {
                const oldValue = this.data[key];
                this.data[key] = value;
                this.notifyListeners(key, value, oldValue);
                this.saveToStorage();
            }

            get(key) {
                return this.data[key];
            }

            update(updates) {
                Object.keys(updates).forEach(key => {
                    this.set(key, updates[key]);
                });
            }

            addListener(key, callback) {
                if (!this.listeners.has(key)) {
                    this.listeners.set(key, new Set());
                }
                this.listeners.get(key).add(callback);
            }

            notifyListeners(key, newValue, oldValue) {
                if (this.listeners.has(key)) {
                    this.listeners.get(key).forEach(callback => {
                        try {
                            callback(newValue, oldValue);
                        } catch (error) {
                            console.error('State listener error:', error);
                        }
                    });
                }
            }

            saveToStorage() {
                try {
                    const stateData = {
                        ...this.data,
                        timestamp: Date.now()
                    };
                    // Remove non-serializable data
                    delete stateData.pendingChanges;
                    delete stateData.saveQueue;
                    sessionStorage.setItem(CONFIG.STORAGE_KEY, JSON.stringify(stateData));
                } catch (error) {
                    console.warn('Failed to save state to storage:', error);
                }
            }

            loadFromStorage() {
                try {
                    const stored = sessionStorage.getItem(CONFIG.STORAGE_KEY);
                    if (stored) {
                        const parsed = JSON.parse(stored);
                        // Only restore if data is less than 1 hour old
                        if (Date.now() - parsed.timestamp < 3600000) {
                            Object.assign(this.data, parsed);
                            return true;
                        }
                    }
                } catch (error) {
                    console.warn('Failed to load state from storage:', error);
                    sessionStorage.removeItem(CONFIG.STORAGE_KEY);
                }
                return false;
            }

            addPendingChange(id) {
                this.data.pendingChanges.add(id);
                this.scheduleAutoSave();
            }

            removePendingChange(id) {
                this.data.pendingChanges.delete(id);
                if (this.data.pendingChanges.size === 0) {
                    this.clearAutoSave();
                }
            }

            scheduleAutoSave() {
                if (this.autoSaveTimer) {
                    clearTimeout(this.autoSaveTimer);
                }
                this.autoSaveTimer = setTimeout(() => {
                    this.processPendingChanges();
                }, CONFIG.AUTO_SAVE_INTERVAL);
            }

            clearAutoSave() {
                if (this.autoSaveTimer) {
                    clearTimeout(this.autoSaveTimer);
                    this.autoSaveTimer = null;
                }
            }

            async processPendingChanges() {
                if (this.data.pendingChanges.size === 0 || this.data.isProcessing) {
                    return;
                }

                const changes = Array.from(this.data.pendingChanges);
                try {
                    await dataManager.saveBatch(changes);
                    this.data.pendingChanges.clear();
                    this.clearAutoSave();
                } catch (error) {
                    console.error('Auto-save failed:', error);
                    // Retry after delay
                    setTimeout(() => this.processPendingChanges(), CONFIG.RETRY_DELAY);
                }
            }
        }

        // Data management class
        class DataManager {
            constructor(state) {
                this.state = state;
                this.requestCache = new Map();
                this.abortControllers = new Map();
            }

            async request(url, options = {}) {
                const requestId = `${options.method || 'GET'}_${url}`;

                // Cancel any pending request with same ID
                if (this.abortControllers.has(requestId)) {
                    this.abortControllers.get(requestId).abort();
                }

                const controller = new AbortController();
                this.abortControllers.set(requestId, controller);

                const defaultOptions = {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.state.get('csrfHash')
                    },
                    signal: controller.signal
                };

                const finalOptions = {
                    ...defaultOptions,
                    ...options
                };

                try {
                    const response = await fetch(url, finalOptions);

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const data = await response.json();

                    // Update CSRF token if provided
                    if (data.csrfHash) {
                        this.state.set('csrfHash', data.csrfHash);
                    }

                    this.abortControllers.delete(requestId);
                    return data;
                } catch (error) {
                    this.abortControllers.delete(requestId);

                    if (error.name === 'AbortError') {
                        throw new Error('Request cancelled');
                    }

                    // Handle network errors
                    if (!navigator.onLine) {
                        this.state.set('offline', true);
                        throw new Error('Network unavailable');
                    }

                    throw error;
                }
            }

            async getAsesiByAsesmen(id_asesmen) {
                const cacheKey = `asesi_${id_asesmen}`;

                if (this.requestCache.has(cacheKey)) {
                    return this.requestCache.get(cacheKey);
                }

                try {
                    const data = await this.request(`${API.getAsesi}?id_asesmen=${id_asesmen}`);

                    if (data.success) {
                        this.requestCache.set(cacheKey, data);
                        // Cache for 5 minutes
                        setTimeout(() => this.requestCache.delete(cacheKey), 300000);
                    }

                    return data;
                } catch (error) {
                    console.error('Error loading asesi:', error);
                    throw error;
                }
            }

            async loadObservasi(params) {
                // FORCE FRESH DATA - Clear any existing cache
                this.invalidateCache();

                try {
                    // Add timestamp for cache busting
                    const timestamp = Date.now();
                    const queryString = new URLSearchParams({
                        ...params,
                        _t: timestamp
                    }).toString();

                    const data = await this.request(`${API.loadObservasi}?${queryString}`, {
                        headers: {
                            'Cache-Control': 'no-cache, no-store, must-revalidate',
                            'Pragma': 'no-cache',
                            'Expires': '0'
                        }
                    });

                    // DO NOT CACHE - Always return fresh data
                    return data;
                } catch (error) {
                    throw error;
                }
            }

            async saveSingle(id_kuk, kompeten, keterangan) {
                const payload = {
                    id_asesmen: this.state.get('id_asesmen'),
                    id_skema: this.state.get('id_skema'),
                    id_asesi: this.state.get('id_asesi'),
                    id_kuk: id_kuk,
                    kompeten: kompeten,
                    keterangan: keterangan,
                    tanggal_observasi: this.state.get('tanggal_observasi')
                };

                try {
                    const data = await this.request(API.singleKuk, {
                        method: 'POST',
                        body: JSON.stringify(payload)
                    });

                    if (data.success) {
                        this.state.removePendingChange(id_kuk);
                    }

                    return data;
                } catch (error) {
                    console.error('Error saving single KUK:', error);
                    throw error;
                }
            }

            async saveBatch(kukIds = null) {
                const items = {};
                const targetKuks = kukIds || Array.from(this.state.get('pendingChanges'));

                targetKuks.forEach(id_kuk => {
                    const $checkbox = $(`.kuk-checkbox[data-id="${id_kuk}"]`);
                    const $keterangan = $checkbox.closest('.kuk-item').find('.keterangan-input');

                    items[id_kuk] = {
                        kompeten: $checkbox.is(':checked') ? 'Y' : 'N',
                        keterangan: $keterangan.val() || ''
                    };
                });

                const payload = {
                    id_asesmen: this.state.get('id_asesmen'),
                    id_skema: this.state.get('id_skema'),
                    id_asesi: this.state.get('id_asesi'),
                    tanggal_observasi: this.state.get('tanggal_observasi'),
                    items: items
                };

                try {
                    const data = await this.request(API.batchSave, {
                        method: 'POST',
                        body: JSON.stringify(payload)
                    });

                    if (data.success) {
                        targetKuks.forEach(id => this.state.removePendingChange(id));
                    }

                    return data;
                } catch (error) {
                    console.error('Error batch saving:', error);
                    throw error;
                }
            }

            async getProgress() {
                const params = {
                    id_asesmen: this.state.get('id_asesmen'),
                    id_asesi: this.state.get('id_asesi')
                };

                try {
                    const queryString = new URLSearchParams(params).toString();
                    return await this.request(`${API.progress}?${queryString}`);
                } catch (error) {
                    console.error('Error getting progress:', error);
                    throw error;
                }
            }

            invalidateCache(pattern = null) {
                if (pattern) {
                    for (const key of this.requestCache.keys()) {
                        if (key.includes(pattern)) {
                            this.requestCache.delete(key);
                        }
                    }
                } else {
                    this.requestCache.clear();
                }
            }
        }

        // UI Manager class
        class UIManager {
            constructor(state) {
                this.state = state;
                this.loadingStates = new Set();
                this.setupEventListeners();
            }

            setupEventListeners() {
                // State change listeners
                this.state.addListener('totalKUK', () => this.updateProgress());
                this.state.addListener('completedKUK', () => this.updateProgress());
                this.state.addListener('offline', (offline) => this.handleOfflineState(offline));
                this.state.addListener('isProcessing', (processing) => this.handleProcessingState(processing));

                // Online/offline detection
                window.addEventListener('online', () => this.state.set('offline', false));
                window.addEventListener('offline', () => this.state.set('offline', true));

                // Form validation on change
                $('#id_asesmen, #id_asesi, #tanggal_observasi').on('change', () => {
                    this.validateForm();
                });
            }

            showLoading(elementId, message = 'Memuat...') {
                this.loadingStates.add(elementId);
                $(`#${elementId}`)
                    .html(`<i class="fas fa-spinner fa-spin mr-2"></i>${message}`)
                    .attr('disabled', true);
            }

            hideLoading(elementId, originalText, originalState = false) {
                this.loadingStates.delete(elementId);
                $(`#${elementId}`)
                    .html(originalText)
                    .attr('disabled', originalState);
            }

            updateProgress() {
                const totalKUK = this.state.get('totalKUK');
                const completedKUK = $('.kuk-checkbox:checked').length;
                const progressPercent = totalKUK > 0 ? Math.round((completedKUK / totalKUK) * 100) : 0;

                // Update progress bar
                const $progressBar = $('#progress-bar');
                $progressBar
                    .css('width', `${progressPercent}%`)
                    .attr('aria-valuenow', progressPercent)
                    .removeClass('bg-secondary bg-warning bg-success');

                // Set appropriate color
                if (progressPercent === 0) {
                    $progressBar.addClass('bg-secondary');
                } else if (progressPercent === 100) {
                    $progressBar.addClass('bg-success');
                } else {
                    $progressBar.addClass('bg-warning');
                }

                // Update text
                $('#progress-text').text(`${progressPercent}%`);

                // Update status
                let statusText, statusClass;
                if (completedKUK === 0) {
                    statusText = 'Belum ada yang dicentang';
                    statusClass = 'text-muted';
                } else if (progressPercent === 100) {
                    statusText = 'Semua kriteria telah dicentang';
                    statusClass = 'text-success';
                } else {
                    statusText = `${completedKUK} dari ${totalKUK} kriteria dicentang`;
                    statusClass = 'text-info';
                }

                $('#data-status').html(`<i class="fas fa-info-circle ${statusClass} mr-1"></i>${statusText}`);

                // Update state
                this.state.set('completedKUK', completedKUK);
            }

            validateForm() {
                const isValid = this.state.get('id_asesmen') &&
                    this.state.get('id_asesi') &&
                    this.state.get('tanggal_observasi');

                $('#btnSave, #checkAll, #uncheckAll').prop('disabled', !isValid);

                return isValid;
            }

            renderObservasiTable(observasi, existingData = {}) {
                const startTime = performance.now();

                try {
                    const fragment = document.createDocumentFragment();
                    let currentGroupings = {
                        kelompok: null,
                        unit: null,
                        elemen: null
                    };
                    let currentContainers = {};

                    observasi.forEach(row => {
                        // Create grouping structure efficiently
                        if (currentGroupings.kelompok !== row.id_kelompok) {
                            currentContainers = this.createKelompokCard(row, fragment);
                            currentGroupings = {
                                kelompok: row.id_kelompok,
                                unit: null,
                                elemen: null
                            };
                        }

                        if (currentGroupings.unit !== row.id_unit) {
                            currentContainers.unitContainer = this.createUnitSection(row, currentContainers.cardBody);
                            currentGroupings.unit = row.id_unit;
                            currentGroupings.elemen = null;
                        }

                        if (currentGroupings.elemen !== row.id_elemen) {
                            currentContainers.elemenContainer = this.createElemenSection(row, currentContainers.unitContainer);
                            currentGroupings.elemen = row.id_elemen;
                        }

                        // Add KUK item
                        if (row.id_kuk) {
                            this.createKUKItem(row, existingData[row.id_kuk] || {}, currentContainers.elemenContainer);
                        }
                    });

                    // Replace content efficiently
                    const $container = $('#observasiContainer');
                    $container.empty().append(fragment);

                    // Update total KUK count
                    const totalKUK = observasi.filter(row => row.id_kuk).length;
                    this.state.set('totalKUK', totalKUK);

                    // Track performance
                    const duration = performance.now() - startTime;
                    if (duration > CONFIG.PERFORMANCE_THRESHOLD) {
                        console.warn(`Slow table render: ${duration}ms for ${observasi.length} items`);
                    }

                } catch (error) {
                    console.error('Error rendering observasi table:', error);
                    notificationManager.showError('Gagal menampilkan data', 'Terjadi kesalahan saat memuat tabel observasi');
                }
            }

            createKelompokCard(row, parent) {
                const card = document.createElement('div');
                card.className = 'card mb-4 shadow-sm';
                card.innerHTML = `
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-layer-group mr-2"></i>
                            ${this.escapeHtml(row.nama_kelompok)}
                        </h5>
                    </div>
                    <div class="card-body p-0"></div>
                `;

                parent.appendChild(card);
                return {
                    card: card,
                    cardBody: card.querySelector('.card-body')
                };
            }

            createUnitSection(row, parent) {
                const unitDiv = document.createElement('div');
                unitDiv.className = 'border-bottom p-3';
                unitDiv.innerHTML = `
                    <h6 class="font-weight-bold d-flex align-items-center">
                        <i class="fas fa-cube text-primary mr-2"></i>
                        <span class="badge badge-light mr-2">${this.escapeHtml(row.kode_unit)}</span>
                        ${this.escapeHtml(row.nama_unit)}
                    </h6>
                `;

                parent.appendChild(unitDiv);
                return unitDiv;
            }

            createElemenSection(row, parent) {
                const elemenDiv = document.createElement('div');
                elemenDiv.className = 'ml-4 mt-3 mb-2';
                elemenDiv.innerHTML = `
                    <div class="font-weight-bold text-muted">
                        <i class="fas fa-list-alt mr-2"></i>
                        ${this.escapeHtml(row.nama_elemen)}
                    </div>
                `;

                parent.appendChild(elemenDiv);
                return elemenDiv;
            }

            createKUKItem(row, existingData, parent) {
                const isChecked = existingData.kompeten === 'Y';
                const keterangan = existingData.keterangan || '';
                const rowClass = isChecked ? 'bg-success text-white' : '';

                const kukItem = document.createElement('div');
                kukItem.className = `kuk-item card mb-2 ml-4 ${rowClass}`;
                kukItem.innerHTML = `
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <div class="d-flex align-items-center">
                                    <div class="custom-control custom-checkbox mr-2">
                                        <input type="checkbox"
                                            class="custom-control-input kuk-checkbox"
                                            id="kuk_${row.id_kuk}"
                                            name="kuk[${row.id_kuk}]"
                                            data-id="${row.id_kuk}"
                                            value="Y"
                                            ${isChecked ? 'checked' : ''}>
                                        <label class="custom-control-label" for="kuk_${row.id_kuk}"></label>
                                    </div>
                                    <label class="mb-0 cursor-pointer" for="kuk_${row.id_kuk}">
                                        ${this.escapeHtml(row.kriteria_unjuk_kerja)}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text ${isChecked ? 'border-light bg-success text-white' : ''}">
                                            <i class="fas fa-comment-dots"></i>
                                        </span>
                                    </div>
                                    <input type="text"
                                        class="form-control keterangan-input ${isChecked ? 'border-light' : ''}"
                                        name="keterangan[${row.id_kuk}]"
                                        placeholder="Catatan observasi..."
                                        value="${this.escapeHtml(keterangan)}"
                                        maxlength="500">
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                parent.appendChild(kukItem);
            }

            handleOfflineState(offline) {
                const $offlineIndicator = $('#offline-indicator');

                if (offline) {
                    if ($offlineIndicator.length === 0) {
                        $('body').prepend(`
                            <div id="offline-indicator" class="alert alert-warning alert-dismissible fade show fixed-top" style="z-index: 9999;">
                                <i class="fas fa-wifi mr-2"></i>
                                Anda sedang offline. Perubahan akan disimpan secara lokal.
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        `);
                    }
                } else {
                    $offlineIndicator.remove();
                    // Sync pending changes when back online
                    if (this.state.get('pendingChanges').size > 0) {
                        this.state.processPendingChanges();
                    }
                }
            }

            handleProcessingState(processing) {
                if (processing) {
                    $('body').addClass('processing');
                    $('.btn').not('.btn-cancel').prop('disabled', true);
                } else {
                    $('body').removeClass('processing');
                    $('.btn').not('.btn-cancel').prop('disabled', false);
                    this.validateForm(); // Re-validate after processing
                }
            }

            escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            showValidationErrors(errors) {
                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // Show new errors
                Object.keys(errors).forEach(field => {
                    const $field = $(`[name="${field}"]`);
                    const errorMessage = Array.isArray(errors[field]) ? errors[field][0] : errors[field];

                    $field
                        .addClass('is-invalid')
                        .after(`<div class="invalid-feedback">${errorMessage}</div>`);
                });
            }
        }

        // Notification manager
        class NotificationManager {
            constructor() {
                this.queue = [];
                this.isProcessing = false;
            }

            async showSuccess(title, message = '', options = {}) {
                return this.show('success', title, message, {
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    ...options
                });
            }

            async showError(title, message = '', options = {}) {
                return this.show('error', title, message, {
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545',
                    ...options
                });
            }

            async showWarning(title, message = '', options = {}) {
                return this.show('warning', title, message, {
                    showCancelButton: true,
                    confirmButtonText: 'Lanjutkan',
                    cancelButtonText: 'Batal',
                    ...options
                });
            }

            async showInfo(title, message = '', options = {}) {
                return this.show('info', title, message, {
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#17a2b8',
                    ...options
                });
            }

            async show(icon, title, text, options = {}) {
                const defaultOptions = {
                    icon,
                    title,
                    text,
                    customClass: {
                        container: 'swal-container',
                        confirmButton: `btn btn-${icon === 'error' ? 'danger' : icon === 'warning' ? 'warning' : 'primary'}`,
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                };

                return Swal.fire({
                    ...defaultOptions,
                    ...options
                });
            }

            toast(icon, title, options = {}) {
                return Swal.fire({
                    icon,
                    title,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    ...options
                });
            }
        }

        // Event handler class
        class EventHandler {
            constructor(state, dataManager, uiManager, notificationManager) {
                this.state = state;
                this.dataManager = dataManager;
                this.uiManager = uiManager;
                this.notificationManager = notificationManager;
                this.debouncedSave = this.debounce(this.saveKUK.bind(this), CONFIG.DEBOUNCE_DELAY);
                this.setupEventListeners();
            }

            setupEventListeners() {
                // Initialize select2
                $('.select2').select2({
                    placeholder: "Pilih...",
                    allowClear: true,
                    width: '100%'
                });

                // Initialize tooltips
                $('[data-toggle="tooltip"]').tooltip();

                // Main event handlers
                $('#id_asesmen').on('change', this.handleAsesmenChange.bind(this));
                $('#id_asesi').on('change', this.handleAsesiChange.bind(this));
                $('#tanggal_observasi').on('change', this.handleTanggalChange.bind(this));

                // Bulk actions
                $('#checkAll').on('click', () => this.handleBulkCheck(true));
                $('#uncheckAll').on('click', () => this.handleBulkCheck(false));

                // KUK interactions
                $(document).on('change', '.kuk-checkbox', this.handleKUKCheckboxChange.bind(this));
                $(document).on('input', '.keterangan-input', this.handleKeteranganChange.bind(this));

                // Form submission
                $('#formObservasi').on('submit', this.handleFormSubmit.bind(this));

                // Keyboard shortcuts
                $(document).on('keydown', this.handleKeyboardShortcuts.bind(this));

                // Auto-save on visibility change
                $(document).on('visibilitychange', this.handleVisibilityChange.bind(this));
            }

            async handleAsesmenChange() {
                const $select = $('#id_asesmen');
                const selectedOption = $select.find('option:selected');
                const id_asesmen = $select.val();
                const id_skema = selectedOption.data('id-skema');

                // Update state
                this.state.update({
                    id_asesmen: id_asesmen,
                    id_skema: id_skema,
                    id_asesi: ''
                });

                // Update form fields
                $('#form_id_skema').val(id_skema);
                $('#form_id_asesmen').val(id_asesmen);
                $('#kode_skema').val(selectedOption.data('kode-skema') || '');

                // Reset UI
                this.resetUI();

                if (!id_asesmen) {
                    $('#id_asesi').empty().append('<option value="">-- Pilih Asesmen Terlebih Dahulu --</option>');
                    $('#initialInstructions').show();
                    return;
                }

                $('#initialInstructions').hide();

                try {
                    this.uiManager.showLoading('id_asesi', 'Memuat Asesi...');

                    const response = await this.dataManager.getAsesiByAsesmen(id_asesmen);

                    if (response.success) {
                        this.populateAsesiDropdown(response.asesi);
                        this.notificationManager.toast('success', 'Data asesi berhasil dimuat');
                    } else {
                        throw new Error(response.message || 'Gagal memuat data asesi');
                    }
                } catch (error) {
                    console.error('Error loading asesi:', error);
                    this.notificationManager.showError('Gagal memuat data asesi', error.message);
                    $('#id_asesi').empty().append('<option value="">-- Error memuat data --</option>');
                } finally {
                    this.uiManager.hideLoading('id_asesi', '<option value="">-- Pilih Asesi --</option>');
                }
            }

            async handleAsesiChange() {
                const id_asesi = $('#id_asesi').val();
                this.state.set('id_asesi', id_asesi);
                $('#form_id_asesi').val(id_asesi);

                if (this.state.get('id_asesmen') && id_asesi) {
                    await this.loadObservasiData();
                } else {
                    this.resetObservasiDisplay();
                }
            }

            handleTanggalChange() {
                const tanggal = $('#tanggal_observasi').val();
                this.state.set('tanggal_observasi', tanggal);
                $('#form_tanggal_observasi').val(tanggal);
            }

            /**
             * Convert observasi object structure to array format for rendering
             */
            convertObservasiObjectToArray(observasiObject) {
                const observasiArray = [];



                Object.keys(observasiObject).forEach(unitKey => {
                    const unit = observasiObject[unitKey];
                    const unitInfo = unit.unit_info;



                    Object.keys(unit.elements || {}).forEach(elemenKey => {
                        const element = unit.elements[elemenKey];
                        const elemenInfo = element.element_info;



                        (element.kuks || []).forEach(kuk => {


                            observasiArray.push({
                                id_kelompok: 1, // Default kelompok ID
                                nama_kelompok: 'Kelompok Utama', // Default grouping
                                id_unit: unitInfo.id_unit,
                                kode_unit: unitInfo.kode_unit,
                                nama_unit: unitInfo.nama_unit,
                                id_elemen: elemenInfo.id_elemen,
                                kode_elemen: elemenInfo.kode_elemen,
                                nama_elemen: elemenInfo.nama_elemen,
                                id_kuk: kuk.id_kuk,
                                kode_kuk: kuk.kode_kuk,
                                kriteria_unjuk_kerja: kuk.nama_kuk
                            });
                        });
                    });
                });


                return observasiArray;
            }

            async loadObservasiData() {
                const params = {
                    id_skema: this.state.get('id_skema'),
                    id_asesmen: this.state.get('id_asesmen'),
                    id_asesi: this.state.get('id_asesi')
                };

                if (!params.id_asesmen || !params.id_asesi) return;

                try {
                    // Show loading state
                    $('#initialInstructions, #emptyDataMessage').hide();
                    $('#loadingData').show();
                    $('#formObservasi').hide();

                    const response = await this.dataManager.loadObservasi(params);



                    if (response.success && response.observasi && Object.keys(response.observasi).length > 0) {
                        // Convert object structure to array for rendering
                        const observasiArray = this.convertObservasiObjectToArray(response.observasi);



                        // Check if we actually have KUKs to display
                        if (observasiArray.length > 0) {
                            this.uiManager.renderObservasiTable(observasiArray, response.existing_data || {});
                            $('#formObservasi').show();
                            this.notificationManager.showSuccess(
                                'Data berhasil dimuat',
                                `${response.totalKUK || observasiArray.length} unit kompetensi siap untuk observasi`
                            );
                        } else {
                            console.warn('No KUKs found in converted array'); // Debug log
                            this.notificationManager.showInfo(
                                'Belum Ada Data Observasi',
                                'Unit kompetensi ditemukan tetapi tidak ada KUK yang dapat ditampilkan.'
                            );
                            $('#formObservasi').hide();
                        }
                    } else {
                        console.warn('Invalid response or empty observasi data:', response); // Debug log
                        this.notificationManager.showInfo(
                            'Belum Ada Data Observasi',
                            'Belum ada unit kompetensi yang tersedia untuk asesmen ini.'
                        );
                        $('#formObservasi').hide();
                    }
                } catch (error) {
                    console.error('Error loading observasi:', error);
                    this.notificationManager.showError('Gagal memuat data observasi', error.message);
                } finally {
                    $('#loadingData').hide();
                }
            }

            handleKUKCheckboxChange(event) {
                const $checkbox = $(event.target);
                const $row = $checkbox.closest('.kuk-item');
                const id_kuk = $checkbox.data('id');
                const isChecked = $checkbox.is(':checked');
                const $keterangan = $row.find('.keterangan-input');

                // Update UI immediately for responsiveness
                $row.toggleClass('bg-success text-white', isChecked);
                $keterangan.toggleClass('border-light', isChecked);
                this.uiManager.updateProgress();

                // Add to pending changes for batch save
                this.state.addPendingChange(id_kuk);

                // Immediate save for better UX
                this.debouncedSave(id_kuk, isChecked ? 'Y' : 'N', $keterangan.val());
            }

            handleKeteranganChange(event) {
                const $input = $(event.target);
                const $row = $input.closest('.kuk-item');
                const id_kuk = $row.find('.kuk-checkbox').data('id');
                const isChecked = $row.find('.kuk-checkbox').is(':checked');

                // Add to pending changes
                this.state.addPendingChange(id_kuk);

                // Debounced save
                this.debouncedSave(id_kuk, isChecked ? 'Y' : 'N', $input.val());
            }

            async saveKUK(id_kuk, kompeten, keterangan) {
                if (this.state.get('offline')) {
                    // Store locally when offline
                    return;
                }

                try {
                    await this.dataManager.saveSingle(id_kuk, kompeten, keterangan);

                    // FORCE REFRESH after successful save
                    setTimeout(() => {
                        this.forceRefreshData();
                    }, 300);

                } catch (error) {
                    // Add back to pending if failed
                    this.state.addPendingChange(id_kuk);
                }
            }

            /**
             * Force refresh data from database
             */
            async forceRefreshData() {
                // Clear all caches
                this.dataManager.invalidateCache();

                // Clear localStorage
                try {
                    Object.keys(localStorage).forEach(key => {
                        if (key.includes('observasi') || key.includes('kuk')) {
                            localStorage.removeItem(key);
                        }
                    });
                } catch (error) {
                    // Silent error handling
                }

                // Reload current data if we have the necessary IDs
                const id_asesmen = this.state.get('id_asesmen');
                const id_asesi = this.state.get('id_asesi');

                if (id_asesmen && id_asesi) {
                    await this.loadObservasiData();
                }
            }

            async handleBulkCheck(checkState) {
                const action = checkState ? 'checkAll' : 'uncheckAll';
                const $btn = $(`#${action}`);
                const originalText = $btn.html();

                try {
                    this.uiManager.showLoading(action, 'Memproses...');

                    // Update UI immediately
                    const $checkboxes = $('.kuk-checkbox');
                    $checkboxes.prop('checked', checkState);
                    $('.kuk-item').toggleClass('bg-success text-white', checkState);
                    $('.keterangan-input').toggleClass('border-light', checkState);
                    this.uiManager.updateProgress();

                    // Prepare batch data
                    const kukIds = $checkboxes.map((_, el) => $(el).data('id')).get();

                    // Save batch
                    const response = await this.dataManager.saveBatch(kukIds);

                    if (response.success) {
                        this.notificationManager.toast(
                            'success',
                            checkState ? 'Semua kriteria berhasil dicentang' : 'Semua centang berhasil dihapus'
                        );
                    } else {
                        throw new Error(response.message || 'Gagal menyimpan data');
                    }
                } catch (error) {
                    console.error('Error bulk check:', error);
                    this.notificationManager.showError('Gagal menyimpan data', error.message);
                } finally {
                    this.uiManager.hideLoading(action, originalText);
                }
            }

            async handleFormSubmit(event) {
                event.preventDefault();

                if (!this.uiManager.validateForm()) {
                    this.notificationManager.showError('Form tidak valid', 'Pastikan semua field wajib telah diisi');
                    return;
                }

                const result = await this.notificationManager.showWarning(
                    'Simpan Ceklis Observasi?',
                    'Pastikan semua kriteria telah diisi dengan benar'
                );

                if (result.isConfirmed) {
                    try {
                        this.state.set('isProcessing', true);
                        this.uiManager.showLoading('btnSave', 'Menyimpan...');

                        // Get all KUK IDs
                        const allKukIds = $('.kuk-checkbox').map((_, el) => $(el).data('id')).get();

                        const response = await this.dataManager.saveBatch(allKukIds);

                        if (response.success) {
                            this.notificationManager.showSuccess('Berhasil', response.message || 'Data berhasil disimpan');
                        } else {
                            throw new Error(response.message || 'Gagal menyimpan data');
                        }
                    } catch (error) {
                        console.error('Error form submit:', error);

                        if (error.message.includes('validation')) {
                            // Handle validation errors
                            const errors = JSON.parse(error.message).errors || {};
                            this.uiManager.showValidationErrors(errors);
                            this.notificationManager.showError('Validasi Gagal', 'Periksa input yang ditandai merah');
                        } else {
                            this.notificationManager.showError('Gagal menyimpan', error.message);
                        }
                    } finally {
                        this.state.set('isProcessing', false);
                        this.uiManager.hideLoading('btnSave', '<i class="fas fa-save mr-1"></i> Simpan');
                    }
                }
            }

            handleKeyboardShortcuts(event) {
                // Ctrl+S to save
                if (event.ctrlKey && event.key === 's') {
                    event.preventDefault();
                    $('#formObservasi').trigger('submit');
                }

                // Ctrl+A to check all
                if (event.ctrlKey && event.key === 'a' && event.target.tagName !== 'INPUT') {
                    event.preventDefault();
                    $('#checkAll').trigger('click');
                }

                // Escape to cancel current operation
                if (event.key === 'Escape') {
                    Swal.close();
                }
            }

            handleVisibilityChange() {
                if (document.visibilityState === 'hidden') {
                    // Save pending changes before page becomes hidden
                    if (this.state.get('pendingChanges').size > 0) {
                        this.state.processPendingChanges();
                    }
                }
            }

            populateAsesiDropdown(asesiList) {
                const $dropdown = $('#id_asesi').empty();

                if (asesiList?.length > 0) {
                    $dropdown.append('<option value="">-- Pilih Asesi --</option>');

                    asesiList.forEach(asesi => {
                        const displayName = asesi.nama || 'Nama tidak tersedia';
                        const nik = asesi.nik || 'NIK tidak tersedia';
                        $dropdown.append(`
                            <option value="${asesi.id_asesi}" data-pengajuan="${asesi.id_pengajuan}">
                                ${this.uiManager.escapeHtml(displayName)} (${this.uiManager.escapeHtml(nik)})
                            </option>
                        `);
                    });

                    $dropdown.prop('disabled', false);
                    $('#emptyDataMessage').hide();
                } else {
                    $dropdown.append('<option value="">-- Belum Ada Asesi Terdaftar --</option>');
                    $dropdown.prop('disabled', true);
                    $('#emptyDataMessage').show();
                }
            }

            resetUI() {
                $('#id_asesi').prop('disabled', true).empty().append('<option value="">-- Memuat Asesi... --</option>');
                $('#form_id_asesi').val('');
                this.resetObservasiDisplay();
            }

            resetObservasiDisplay() {
                $('#observasiContainer').empty();
                $('#formObservasi').hide();
                $('#loadingData').hide();
                $('#emptyDataMessage').hide();
                this.state.update({
                    totalKUK: 0,
                    completedKUK: 0
                });
                this.uiManager.updateProgress();
            }

            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func.apply(this, args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
        }

        // Application initialization
        class ObservasiApp {
            constructor() {
                this.state = new ObservasiState();
                this.dataManager = new DataManager(this.state);
                this.uiManager = new UIManager(this.state);
                this.notificationManager = new NotificationManager();
                this.eventHandler = new EventHandler(
                    this.state,
                    this.dataManager,
                    this.uiManager,
                    this.notificationManager
                );
            }

            async init() {
                try {
                    // Load saved state
                    if (this.state.loadFromStorage()) {
                        this.restoreState();
                    }

                    // Initialize UI state
                    this.initializePageState();

                    // Setup error boundaries
                    this.setupErrorHandling();

                    // Initialize performance monitoring
                    this.setupPerformanceMonitoring();


                } catch (error) {
                    console.error('Failed to initialize application:', error);
                    this.notificationManager.showError(
                        'Initialization Error',
                        'Terjadi kesalahan saat memuat halaman. Silakan refresh halaman.'
                    );
                }
            }

            restoreState() {
                const id_asesmen = this.state.get('id_asesmen');
                const tanggal_observasi = this.state.get('tanggal_observasi');

                if (id_asesmen) {
                    $('#id_asesmen').val(id_asesmen).trigger('change');
                }
                if (tanggal_observasi) {
                    $('#tanggal_observasi').val(tanggal_observasi);
                }
            }

            initializePageState() {
                // Hide loading and form initially
                $('#loadingData, #formObservasi, #emptyDataMessage').hide();
                $('#initialInstructions').show();

                // Reset dropdowns
                if (!$('#id_asesmen').val()) {
                    $('#id_asesi').empty()
                        .append('<option value="">-- Pilih Asesmen Terlebih Dahulu --</option>')
                        .prop('disabled', true);
                }

                // Initialize progress
                this.uiManager.updateProgress();
            }

            setupErrorHandling() {
                // Global error handler
                window.addEventListener('error', (event) => {
                    console.error('Global error:', event.error);
                    this.notificationManager.showError(
                        'Terjadi Kesalahan',
                        'Terjadi kesalahan tidak terduga. Silakan coba lagi.'
                    );
                });

                // Unhandled promise rejection handler
                window.addEventListener('unhandledrejection', (event) => {
                    console.error('Unhandled promise rejection:', event.reason);
                    event.preventDefault();
                });
            }

            setupPerformanceMonitoring() {
                // Monitor long tasks
                if ('PerformanceObserver' in window) {
                    const observer = new PerformanceObserver((list) => {
                        for (const entry of list.getEntries()) {
                            if (entry.duration > 50) { // Log tasks longer than 50ms
                                console.warn(`Long task detected: ${entry.duration}ms`);
                            }
                        }
                    });
                    observer.observe({
                        entryTypes: ['longtask']
                    });
                }

                // Monitor page visibility for performance tracking
                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible') {

                    } else {

                    }
                });
            }
        }

        // Initialize application
        const app = new ObservasiApp();
        app.init();

        // Expose for debugging (only in development)
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            window.ObservasiApp = {
                app,
                state: app.state,
                dataManager: app.dataManager,
                uiManager: app.uiManager
            };
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            app.state.clearAutoSave();
            app.dataManager.invalidateCache();
        });
    });
</script>