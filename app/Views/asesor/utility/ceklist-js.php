<script>
    $(document).ready(function() {
        'use strict';

        // Initialize select2
        $('.select2').select2({
            placeholder: "Pilih...",
            allowClear: true,
            width: '100%'
        });

        // State management
        const state = {
            id_asesmen: '',
            id_skema: '',
            id_observasi: null,
            totalKUK: 0,
            pendingChanges: false,
            saveQueue: [],
            isProcessing: false,
            csrfName: '<?= csrf_token() ?>',
            csrfHash: '<?= csrf_hash() ?>'
        };

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Event handlers
        function initEventHandlers() {
            // Asesmen selection change
            $('#id_asesmen').on('change', handleAsesmenChange); // Asesi selection change
            $('#id_asesi').on('change', function() {
                const asesiId = $(this).val();
                $('#form_id_asesi').val(asesiId);

                if (state.id_asesmen && asesiId) {
                    loadObservasiData();
                    saveSettings();
                    saveSessionState();
                } else {
                    // Hide form and show appropriate message
                    $('#formObservasi').hide();
                    $('#loadingData').hide();

                    if (!asesiId && state.id_asesmen) {
                        // Asesi deselected but assessment is still selected
                        $('#initialInstructions').hide();
                        $('#emptyDataMessage').hide();
                    }
                }
            }); // Tanggal observasi change
            $('#tanggal_observasi').on('change', function() {
                $('#form_tanggal_observasi').val($(this).val());
                saveSettings();
                saveSessionState();
            });

            // Check/uncheck all buttons
            $('#checkAll').on('click', () => handleBulkCheck(true));
            $('#uncheckAll').on('click', () => handleBulkCheck(false));

            // KUK checkbox changes
            $(document).on('change', '.kuk-checkbox', handleKUKCheckboxChange);

            // Keterangan input changes
            $(document).on('input', '.keterangan-input', debounce(handleKeteranganChange, 500)); // Form submission
            $('#formObservasi').submit(handleFormSubmit);
        } // Main functions
        async function handleAsesmenChange() {
            const selectedOption = $(this).find('option:selected');
            state.id_asesmen = $(this).val();
            state.id_skema = selectedOption.data('id-skema');

            $('#form_id_skema').val(state.id_skema);
            $('#form_id_asesmen').val(state.id_asesmen);
            $('#kode_skema').val(selectedOption.data('kode-skema') || '');

            // Reset asesi dropdown and hide form
            $('#id_asesi').prop('disabled', true).empty().append('<option value="">-- Memuat Asesi... --</option>');
            $('#form_id_asesi').val('');
            $('#observasiContainer').empty();
            $('#formObservasi').hide();
            $('#loadingData').hide();
            $('#emptyDataMessage').hide();
            resetProgressBar();

            if (!state.id_asesmen) {
                $('#id_asesi').empty().append('<option value="">-- Pilih Asesmen Terlebih Dahulu --</option>');
                $('#initialInstructions').show();
                return;
            }

            // Hide initial instructions when asesmen is selected
            $('#initialInstructions').hide();

            try {
                const response = await $.ajax({
                    url: '<?= base_url('/asesor/observasi/getAsesiByAsesmen') ?>',
                    type: 'GET',
                    data: {
                        id_asesmen: state.id_asesmen
                    },
                    dataType: 'json'
                });

                if (response.success) {
                    populateAsesiDropdown(response.asesi, response);
                } else {
                    showError('Gagal memuat data asesi', response.message);
                    $('#id_asesi').empty().append('<option value="">-- Error memuat data --</option>');
                }
            } catch (error) {
                console.error('Error loading asesi data:', error);
                const errorMessage = error.responseJSON?.message || 'Terjadi kesalahan saat memuat data asesi';
                showError('Error Database', errorMessage);
                $('#id_asesi').empty().append('<option value="">-- Error memuat data --</option>');
            }
        }

        function populateAsesiDropdown(asesiList, response = {}) {
            const $asesiDropdown = $('#id_asesi').empty();

            if (asesiList && asesiList.length > 0) {
                $asesiDropdown.append('<option value="">-- Pilih Asesi --</option>');

                asesiList.forEach(asesi => {
                    // nama is now alias of nama_lengkap from users table
                    const displayName = asesi.nama || 'Nama tidak tersedia';
                    const nik = asesi.nik || 'NIK tidak tersedia';
                    $asesiDropdown.append(`<option value="${asesi.id_asesi}" data-pengajuan="${asesi.id_pengajuan}">${displayName} (${nik})</option>`);
                });

                $asesiDropdown.prop('disabled', false);
                $('#emptyDataMessage').hide();

                // Show success message if provided
                if (response.message) {
                    showSuccess('Data berhasil dimuat', response.message);
                }
            } else {
                $asesiDropdown.append('<option value="">-- Belum Ada Asesi Terdaftar --</option>');
                $asesiDropdown.prop('disabled', true);
                $('#emptyDataMessage').show();

                // Show informative message
                if (response.message) {
                    showInfo('Informasi', response.message);
                }
            }
        }
        async function loadObservasiData() {
            const id_asesmen = state.id_asesmen;
            const id_asesi = $('#id_asesi').val();

            if (!id_asesmen || !id_asesi) return;

            try {
                // Hide other elements and show loading
                $('#initialInstructions').hide();
                $('#emptyDataMessage').hide();
                $('#loadingData').show();
                $('#formObservasi').hide();

                const response = await $.ajax({
                    url: '<?= base_url('/asesor/observasi/loadObservasi') ?>',
                    type: 'GET',
                    data: {
                        id_skema: state.id_skema,
                        id_asesmen: id_asesmen,
                        id_asesi: id_asesi
                    },
                    dataType: 'json'
                });

                if (response.success) {
                    console.log('API Response:', response); // Debug log

                    // Check if observasi data exists (could be object or array)
                    let hasObservasi = false;
                    let observasiCount = 0;

                    if (response.observasi) {
                        if (Array.isArray(response.observasi)) {
                            hasObservasi = response.observasi.length > 0;
                            observasiCount = response.observasi.length;
                        } else if (typeof response.observasi === 'object') {
                            const keys = Object.keys(response.observasi);
                            hasObservasi = keys.length > 0;
                            observasiCount = response.totalKUK || 0;

                            // Convert object to array format for rendering
                            response.observasi = convertObservasiObjectToArray(response.observasi);
                            console.log('Converted observasi to array:', response.observasi); // Debug log
                        }
                    }

                    if (hasObservasi && response.observasi.length > 0) {
                        renderObservasiTable(response.observasi, response.existing_data);
                        state.totalKUK = response.totalKUK || response.observasi.length;

                        // Try to extract id_observasi from existing_data or response
                        if (response.existing_data && response.existing_data.id_observasi) {
                            state.id_observasi = response.existing_data.id_observasi;
                            console.log('Loaded existing id_observasi:', state.id_observasi);
                        } else if (response.id_observasi) {
                            state.id_observasi = response.id_observasi;
                            console.log('Loaded id_observasi from response:', state.id_observasi);
                        }

                        updateProgressBar();
                        $('#formObservasi').show();
                        showSuccess('Data berhasil dimuat', `${observasiCount} unit kompetensi siap untuk observasi`);
                    } else {
                        console.warn('No observasi data found or conversion failed'); // Debug log
                        showInfo('Belum Ada Data Observasi', 'Belum ada unit kompetensi yang tersedia untuk asesmen ini.');
                        $('#formObservasi').hide();
                    }
                } else {
                    showError('Gagal memuat data observasi', response.message);
                }
            } catch (error) {
                console.error('Error loading observasi data:', error);
                const errorMessage = error.responseJSON?.message || 'Terjadi kesalahan saat memuat data observasi';
                showError('Error Database', errorMessage);
            } finally {
                $('#loadingData').hide();
            }
        }

        /**
         * Convert observasi object structure to array format for rendering
         */
        function convertObservasiObjectToArray(observasiObject) {
            const observasiArray = [];

            console.log('Converting observasi object:', observasiObject); // Debug log

            Object.keys(observasiObject).forEach(unitKey => {
                const unit = observasiObject[unitKey];
                const unitInfo = unit.unit_info;

                console.log('Processing unit:', unitKey, unitInfo); // Debug log

                Object.keys(unit.elements || {}).forEach(elemenKey => {
                    const element = unit.elements[elemenKey];
                    const elemenInfo = element.element_info;

                    console.log('Processing element:', elemenKey, elemenInfo); // Debug log

                    (element.kuks || []).forEach(kuk => {
                        console.log('Processing KUK:', kuk); // Debug log

                        observasiArray.push({
                            id_kelompok: unitInfo.id_kelompok || 1, // Default kelompok ID
                            nama_kelompok: unitInfo.nama_kelompok || 'Kelompok Utama', // Default grouping
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

            console.log('Converted array length:', observasiArray.length); // Debug log
            return observasiArray;
        }

        function renderObservasiTable(observasi, existing_data) {
            let html = '';
            let currentGroupings = {
                kelompok: null,
                unit: null,
                elemen: null
            };

            observasi.forEach(row => {
                // Kelompok header
                if (currentGroupings.kelompok !== row.id_kelompok) {
                    if (currentGroupings.elemen) html += '</div>';
                    if (currentGroupings.unit) html += '</div>';
                    if (currentGroupings.kelompok) html += '</div></div>';

                    currentGroupings = {
                        kelompok: row.id_kelompok,
                        unit: null,
                        elemen: null
                    };

                    html += `
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0"><i class="fas fa-layer-group mr-2"></i>${escapeHtml(row.nama_kelompok)}</h5>
                        </div>
                        <div class="card-body p-0">`;
                }

                // Unit header
                if (currentGroupings.unit !== row.id_unit) {
                    if (currentGroupings.elemen) html += '</div>';
                    if (currentGroupings.unit) html += '</div>';

                    currentGroupings.unit = row.id_unit;
                    currentGroupings.elemen = null;

                    html += `
                    <div class="border-bottom p-3">
                        <h6 class="font-weight-bold d-flex align-items-center">
                            <i class="fas fa-cube text-primary mr-2"></i>
                            <span class="badge badge-light mr-2">${escapeHtml(row.kode_unit)}</span>
                            ${escapeHtml(row.nama_unit)}
                        </h6>`;
                }

                // Elemen header
                if (currentGroupings.elemen !== row.id_elemen) {
                    if (currentGroupings.elemen) html += '</div>';

                    currentGroupings.elemen = row.id_elemen;
                    html += `
                    <div class="ml-4 mt-3 mb-2">
                        <div class="font-weight-bold text-muted">
                            <i class="fas fa-list-alt mr-2"></i>${escapeHtml(row.nama_elemen)}
                        </div>`;
                }

                // KUK items
                if (row.id_kuk) {
                    const kukId = row.id_kuk;
                    const existing = existing_data[kukId] || {};
                    const isChecked = existing.kompeten === 'Y';
                    const keterangan = existing.keterangan || '';
                    const rowClass = isChecked ? 'bg-success text-white' : '';

                    html += `
                    <div class="kuk-item card mb-2 ml-4 ${rowClass}">
                        <div class="card-body py-2">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <div class="d-flex align-items-center">
                                        <div class="custom-control custom-checkbox mr-2">
                                            <input type="checkbox"
                                                class="custom-control-input kuk-checkbox"
                                                id="kuk_${kukId}"
                                                name="kuk[${kukId}]"
                                                data-id="${kukId}"
                                                value="Y"
                                                ${isChecked ? 'checked' : ''}>
                                            <label class="custom-control-label" for="kuk_${kukId}"></label>
                                        </div>
                                        <label class="mb-0" for="kuk_${kukId}">${escapeHtml(row.kriteria_unjuk_kerja)}</label>
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
                                            class="form-control form-control keterangan-input ${isChecked ? 'border-light' : ''}"
                                            name="keterangan[${kukId}]"
                                            placeholder="Catatan observasi..."
                                            value="${escapeHtml(keterangan)}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                }
            });

            // Close any open containers
            if (currentGroupings.elemen) html += '</div>';
            if (currentGroupings.unit) html += '</div>';
            if (currentGroupings.kelompok) html += '</div></div>';

            $('#observasiContainer').html(html);
        }

        function handleKUKCheckboxChange() {
            const $row = $(this).closest('.kuk-item');
            const isChecked = $(this).is(':checked');
            const id_kuk = $(this).data('id');
            const $keterangan = $row.find('.keterangan-input');

            $row.toggleClass('bg-success text-white', isChecked);
            $keterangan.toggleClass('border-light', isChecked);
            updateProgressBar();

            saveKUK(id_kuk, isChecked ? 'Y' : 'N', $keterangan.val());
        }

        function handleKeteranganChange() {
            const $row = $(this).closest('.kuk-item');
            const id_kuk = $row.find('.kuk-checkbox').data('id');
            const isChecked = $row.find('.kuk-checkbox').is(':checked');

            saveKUK(id_kuk, isChecked ? 'Y' : 'N', $(this).val());
        }

        async function handleBulkCheck(checkState) {
            const $btn = $(checkState ? '#checkAll' : '#uncheckAll');
            const originalBtnText = $btn.html();

            $btn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').attr('disabled', true);

            // Ensure observasi settings are saved first to get id_observasi
            if (!state.id_observasi) {
                console.log('No id_observasi found for batch operation, saving settings first...');
                const settingsResult = await saveSettings();
                if (!settingsResult || !settingsResult.success || !state.id_observasi) {
                    showError('Gagal menyimpan pengaturan observasi. Diperlukan sebelum operasi batch.');
                    $btn.html(originalBtnText).attr('disabled', false);
                    return;
                }
            }

            const $checkboxes = $('.kuk-checkbox');
            $checkboxes.prop('checked', checkState);

            $('.kuk-item').toggleClass('bg-success text-white', checkState);
            $('.keterangan-input').toggleClass('border-light', checkState);
            updateProgressBar();

            const batchData = {
                save_type: 'batch',
                id_observasi: state.id_observasi,
                id_asesmen: parseInt(state.id_asesmen),
                id_skema: parseInt(state.id_skema),
                id_asesi: $('#form_id_asesi').val(),
                tanggal_observasi: $('#form_tanggal_observasi').val(),
                items: {}
            };

            $checkboxes.each(function() {
                const id_kuk = $(this).data('id');
                const $keterangan = $(this).closest('.kuk-item').find('.keterangan-input');
                batchData.items[id_kuk] = {
                    kompeten: checkState ? 'Y' : 'N',
                    keterangan: $keterangan.val()
                };
            });

            try {
                const response = await $.ajax({
                    url: '<?= base_url('/asesor/observasi/save') ?>',
                    type: 'POST',
                    data: JSON.stringify(batchData),
                    contentType: 'application/json',
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.success) {
                    showSuccess(checkState ? 'Semua kriteria berhasil dicentang' : 'Semua centang berhasil dihapus');
                    if (response.csrfHash) state.csrfHash = response.csrfHash;
                } else {
                    showError('Gagal menyimpan data', response.message);
                }
            } catch (error) {
                showError('Terjadi kesalahan saat menyimpan data');
            } finally {
                $btn.html(originalBtnText).attr('disabled', false);
            }
        }

        async function saveSettings() {
            const data = {
                save_type: 'settings',
                id_asesmen: state.id_asesmen,
                id_asesi: $('#form_id_asesi').val(),
                tanggal_observasi: $('#form_tanggal_observasi').val(),
                [state.csrfName]: state.csrfHash
            };

            try {
                const response = await $.ajax({
                    url: '<?= base_url('/asesor/observasi/save') ?>',
                    type: 'POST',
                    data: data,
                    dataType: 'json'
                });

                if (response.success && response.data && response.data.id_observasi) {
                    // Store the id_observasi for future KUK saves
                    state.id_observasi = response.data.id_observasi;
                    console.log('Observasi settings saved with ID:', state.id_observasi);
                }

                if (response.csrfHash) {
                    state.csrfHash = response.csrfHash;
                }

                return response;
            } catch (error) {
                console.error('Error saving settings:', error);
                showError('Gagal menyimpan pengaturan');
                return null;
            }
        }

        async function saveKUK(id_kuk, kompeten, keterangan) {
            // Ensure observasi settings are saved first to get id_observasi
            if (!state.id_observasi) {
                console.log('No id_observasi found, saving settings first...');
                const settingsResult = await saveSettings();
                if (!settingsResult || !settingsResult.success || !state.id_observasi) {
                    showError('Gagal menyimpan pengaturan observasi. Diperlukan sebelum menyimpan KUK.');
                    return;
                }
            }

            const data = {
                save_type: 'kuk',
                id_observasi: state.id_observasi,
                id_asesmen: state.id_asesmen,
                id_skema: state.id_skema,
                id_asesi: $('#form_id_asesi').val(),
                id_kuk: id_kuk,
                kompeten: kompeten,
                keterangan: keterangan,
                tanggal_observasi: $('#form_tanggal_observasi').val(),
                [state.csrfName]: state.csrfHash
            };

            try {
                const response = await $.ajax({
                    url: '<?= base_url('/asesor/observasi/save') ?>',
                    type: 'POST',
                    data: data,
                    dataType: 'json'
                });

                if (response.csrfHash) {
                    state.csrfHash = response.csrfHash;
                }

                if (response.success) {
                    console.log('KUK saved successfully:', id_kuk, kompeten);
                } else {
                    console.error('KUK save failed:', response);
                    showError('Gagal menyimpan data KUK: ' + (response.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving KUK:', error);
                showError('Gagal menyimpan data KUK');
            }
        }

        async function handleFormSubmit(e) {
            e.preventDefault();

            if (!$('#form_id_skema').val()) {
                showError('Silakan pilih skema terlebih dahulu');
                return;
            }

            if (!$('#form_id_asesi').val()) {
                showError('Silakan pilih asesi terlebih dahulu');
                return;
            }

            const result = await Swal.fire({
                title: 'Simpan Ceklis Observasi?',
                text: 'Pastikan semua kriteria telah diisi dengan benar',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal'
            });

            if (result.isConfirmed) {
                $('#btnSave').html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').attr('disabled', true);

                try {
                    const formData = $('#formObservasi').serializeArray();
                    formData.push({
                        name: 'save_type',
                        value: 'full'
                    });
                    formData.push({
                        name: state.csrfName,
                        value: state.csrfHash
                    });

                    const response = await $.ajax({
                        url: '<?= base_url('/asesor/observasi/save') ?>',
                        type: 'POST',
                        data: formData,
                        dataType: 'json'
                    });

                    if (response.success) {
                        showSuccess(response.message || 'Data berhasil disimpan');
                        if (response.csrfHash) state.csrfHash = response.csrfHash;
                    } else {
                        showError(response.message || 'Gagal menyimpan data');
                    }
                } catch (error) {
                    const errorMessage = error.responseJSON?.messages || 'Terjadi kesalahan saat menyimpan data';
                    showError(errorMessage);
                } finally {
                    $('#btnSave').html('<i class="fas fa-save mr-1"></i> Simpan').attr('disabled', false);
                }
            }
        }

        // UI helpers
        function updateProgressBar() {
            const totalChecked = $('.kuk-checkbox:checked').length;
            const progressPercent = state.totalKUK > 0 ? Math.round((totalChecked / state.totalKUK) * 100) : 0;

            $('#progress-bar').css('width', `${progressPercent}%`).attr('aria-valuenow', progressPercent);
            $('#progress-text').text(`${progressPercent}%`);

            // Update status text
            const statusText = totalChecked === 0 ? 'Belum ada yang dicentang' :
                progressPercent === 100 ? 'Semua kriteria telah dicentang' :
                `${totalChecked} dari ${state.totalKUK} kriteria dicentang`;

            $('#data-status').html(`<i class="fas fa-info-circle text-info"></i> ${statusText}`);

            // Update progress bar color
            const progressBar = $('#progress-bar');
            progressBar.removeClass('bg-warning bg-success');
            if (progressPercent === 100) {
                progressBar.addClass('bg-success');
            } else if (progressPercent > 0) {
                progressBar.addClass('bg-warning');
            }
        }

        function resetProgressBar() {
            $('#progress-bar').css('width', '0%').attr('aria-valuenow', 0);
            $('#progress-text').text('0%');
            $('#data-status').html('<i class="fas fa-sync text-muted"></i> Menunggu data...');
            state.totalKUK = 0;
        }

        // Session state management for better UX
        function saveSessionState() {
            const state_data = {
                id_asesmen: state.id_asesmen,
                id_skema: state.id_skema,
                tanggal_observasi: $('#tanggal_observasi').val(),
                timestamp: Date.now()
            };

            try {
                sessionStorage.setItem('observasi_state', JSON.stringify(state_data));
            } catch (e) {
                // Storage might be full or disabled
            }
        }

        // Utility functions optimized for production
        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function debounce(func, wait) {
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

        // Optimized notification functions
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: message,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }

        function showError(title, message = '') {
            Swal.fire({
                icon: 'error',
                title: title,
                text: message,
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
        }

        function showSuccess(title, message = '') {
            Swal.fire({
                icon: 'success',
                title: title,
                text: message,
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        }

        function showInfo(title, message = '') {
            Swal.fire({
                icon: 'info',
                title: title,
                text: message,
                confirmButtonText: 'Mengerti',
                customClass: {
                    confirmButton: 'btn btn-info'
                }
            });
        }

        // Initialize page state
        function initializePageState() {
            // Hide loading and form initially
            $('#loadingData').hide();
            $('#formObservasi').hide();
            $('#emptyDataMessage').hide();

            // Show initial instructions
            $('#initialInstructions').show();

            // Reset dropdowns to default state
            if (!$('#id_asesmen').val()) {
                $('#id_asesi').empty().append('<option value="">-- Pilih Asesmen Terlebih Dahulu --</option>').prop('disabled', true);
            }
        }

        // Network error handler
        function handleNetworkError(error) {
            const isNetworkError = !error.status || error.status === 0;
            const isServerError = error.status >= 500;

            if (isNetworkError) {
                showError('Koneksi Terputus', 'Periksa koneksi internet Anda dan coba lagi.');
            } else if (isServerError) {
                showError('Server Error', 'Terjadi kesalahan pada server. Silakan coba lagi nanti.');
            } else {
                const message = error.responseJSON?.message || 'Terjadi kesalahan. Silakan coba lagi.';
                showError('Error', message);
            }
        } // Performance monitoring (production safe)
        function trackPerformance(action, startTime) {
            const duration = performance.now() - startTime;
            if (duration > 3000) { // Log slow operations
                // In production, this could send to analytics
                if (window.console && window.console.warn) {
                    console.warn(`Slow operation detected: ${action} took ${duration}ms`);
                }
            }
        }

        // Initialize page state
        function initializePageState() {
            // Hide loading and form initially
            $('#loadingData').hide();
            $('#formObservasi').hide();
            $('#emptyDataMessage').hide();

            // Show initial instructions
            $('#initialInstructions').show();

            // Reset dropdowns to default state
            if (!$('#id_asesmen').val()) {
                $('#id_asesi').empty().append('<option value="">-- Pilih Asesmen Terlebih Dahulu --</option>').prop('disabled', true);
            }
        }

        // Initialize with error boundary
        try {
            initEventHandlers();

            // Initialize page state
            initializePageState();

            // Load saved settings if available
            const savedData = sessionStorage.getItem('observasi_state');
            if (savedData) {
                try {
                    const parsed = JSON.parse(savedData);
                    if (parsed.id_asesmen) {
                        $('#id_asesmen').val(parsed.id_asesmen).trigger('change');
                    }
                    if (parsed.tanggal_observasi) {
                        $('#tanggal_observasi').val(parsed.tanggal_observasi);
                    }
                } catch (e) {
                    // Silent fail for session storage
                    sessionStorage.removeItem('observasi_state');
                }
            }
        } catch (error) {
            showError('Initialization Error', 'Terjadi kesalahan saat memuat halaman. Silakan refresh halaman.');
        }
    });
</script>