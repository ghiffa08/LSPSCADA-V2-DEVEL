<script>
    $(document).ready(function() {
        'use strict';

        // Initialize select2
        $('.select2').select2({
            placeholder: "Pilih...",
            allowClear: true,
            width: '100%'
        });

        // State management for APL1
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
            $('#id_asesmen').on('change', handleAsesmenChange);

            // APL1 selection change
            $('#id_apl1').on('change', function() {
                const apl1Id = $(this).val();
                const selectedOption = $(this).find('option:selected');

                console.log('APL1 selection changed:', apl1Id);
                $('#form_id_apl1').val(apl1Id);

                if (state.id_asesmen && apl1Id) {
                    const apl1Data = {
                        id_apl1: apl1Id,
                        nama_siswa: selectedOption.text().split(' (')[0],
                        nik: selectedOption.data('nik'),
                        email: selectedOption.data('email'),
                        no_hp: selectedOption.data('phone'),
                        validasi_apl1: selectedOption.data('status')
                    };

                    console.log('APL1 data:', apl1Data);
                    showApl1Info(apl1Data);
                    loadObservasiData();
                    saveSettings();
                    saveSessionState();
                } else {
                    $('#formObservasi').hide();
                    $('#loadingData').hide();
                    hideApl1Info();

                    if (!apl1Id && state.id_asesmen) {
                        $('#initialInstructions').hide();
                        $('#emptyDataMessage').hide();
                    }
                }
            });

            // Helper function to show APL1 info
            function showApl1Info(apl1Data) {
                $('#apl1-nik').text(apl1Data.nik || '-');
                $('#apl1-email').text(apl1Data.email || '-');
                $('#apl1-phone').text(apl1Data.no_hp || '-');

                const statusBadge = $('#apl1-status');
                if (apl1Data.validasi_apl1 === 'validated') {
                    statusBadge.removeClass().addClass('badge badge-success').text('Tervalidasi');
                } else {
                    statusBadge.removeClass().addClass('badge badge-warning').text('Pending');
                }

                $('#apl1-info').show();
                console.log('APL1 info displayed');
            }

            // Helper function to hide APL1 info
            function hideApl1Info() {
                $('#apl1-info').hide();
            }

            // Tanggal observasi change
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
            $(document).on('input', '.keterangan-input', debounce(handleKeteranganChange, 500));

            // Form submission
            $('#formObservasi').submit(handleFormSubmit);
        }

        // Main functions
        async function handleAsesmenChange() {
            const selectedOption = $(this).find('option:selected');
            state.id_asesmen = $(this).val();
            state.id_skema = selectedOption.data('id-skema');

            console.log('Asesmen changed:', state.id_asesmen, 'Skema:', state.id_skema);

            $('#form_id_skema').val(state.id_skema);
            $('#form_id_asesmen').val(state.id_asesmen);
            $('#kode_skema').val(selectedOption.data('kode-skema') || '');

            // Reset APL1 dropdown and hide form
            const $apl1Dropdown = $('#id_apl1');
            $apl1Dropdown.prop('disabled', true)
                .empty()
                .append('<option value="">-- Memuat APL1... --</option>');

            $('#form_id_apl1').val('');
            $('#observasiContainer').empty();
            $('#formObservasi').hide();
            $('#loadingData').hide();
            $('#emptyDataMessage').hide();
            resetProgressBar();

            if (!state.id_asesmen || !state.id_skema) {
                $apl1Dropdown.empty().append('<option value="">-- Pilih Asesmen Terlebih Dahulu --</option>');
                $('#initialInstructions').show();
                return;
            }

            $('#initialInstructions').hide();

            try {
                console.log('Loading APL1 for skema:', state.id_skema);

                const response = await $.ajax({
                    url: '<?= base_url('asesor/api/observasi/getValidatedApl1List') ?>',
                    type: 'GET',
                    data: {
                        id_skema: state.id_skema
                    },
                    dataType: 'json'
                });

                console.log('APL1 response:', response);

                if (response.success) {
                    populateApl1Dropdown(response.data, response);

                    if (response.data && response.data.length > 0) {
                        showSuccess('Data APL1 berhasil dimuat', `Ditemukan ${response.data.length} siswa tervalidasi`);
                    } else {
                        showInfo('Informasi', 'Belum ada siswa (APL1) yang tervalidasi untuk skema ini');
                    }
                } else {
                    showError('Gagal memuat data APL1', response.message || 'Unknown error');
                    $apl1Dropdown.empty().append('<option value="">-- Error memuat data --</option>');
                    $apl1Dropdown.prop('disabled', true);
                }
            } catch (error) {
                console.error('Error loading APL1 data:', error);
                const errorMessage = error.responseJSON?.message || 'Terjadi kesalahan saat memuat data APL1';
                showError('Error Database', errorMessage);

                $apl1Dropdown.empty().append('<option value="">-- Error memuat data --</option>');
                $apl1Dropdown.prop('disabled', true);
            }
        }

        function populateApl1Dropdown(apl1List, response = {}) {
            const $apl1Dropdown = $('#id_apl1');
            $apl1Dropdown.empty();

            if (apl1List && apl1List.length > 0) {
                $apl1Dropdown.append('<option value="">-- Pilih Siswa (APL1) --</option>');

                apl1List.forEach(apl1 => {
                    const displayName = apl1.nama_siswa || 'Nama tidak tersedia';
                    const nik = apl1.nik || 'NIK tidak tersedia';
                    const optionText = `${displayName} (${nik})`;

                    $apl1Dropdown.append(`
                        <option value="${apl1.id_apl1}" 
                                data-nik="${apl1.nik || ''}"
                                data-email="${apl1.email || ''}"
                                data-phone="${apl1.no_hp || ''}"
                                data-status="${apl1.validasi_apl1 || ''}">
                            ${optionText}
                        </option>
                    `);
                });

                $apl1Dropdown.prop('disabled', false);
                $('#emptyDataMessage').hide();
                $apl1Dropdown.trigger('change.select2');

                if (response.message) {
                    showSuccess('Data berhasil dimuat', response.message);
                }

                console.log('APL1 dropdown populated and enabled with', apl1List.length, 'items');
            } else {
                $apl1Dropdown.append('<option value="">-- Belum Ada APL1 Tervalidasi --</option>');
                $apl1Dropdown.prop('disabled', true);
                $('#emptyDataMessage').show();

                if (response.message) {
                    showInfo('Informasi', response.message);
                } else {
                    showInfo('Informasi', 'Belum ada siswa (APL1) yang tervalidasi untuk skema ini.');
                }

                console.log('No APL1 data available, dropdown disabled');
            }
        }

        async function loadObservasiData() {
            const id_asesmen = state.id_asesmen;
            const id_apl1 = $('#id_apl1').val();

            if (!id_asesmen || !id_apl1) {
                console.warn('Missing required data:', {
                    id_asesmen,
                    id_apl1
                });
                return;
            }

            try {
                $('#initialInstructions').hide();
                $('#emptyDataMessage').hide();
                $('#loadingData').show();
                $('#formObservasi').hide();

                console.log('Loading observasi data with:', {
                    id_asesmen,
                    id_apl1,
                    id_skema: state.id_skema
                });

                const response = await $.ajax({
                    url: '<?= base_url('asesor/api/observasi/loadObservasi') ?>',
                    type: 'GET',
                    data: {
                        id_skema: state.id_skema,
                        id_asesmen: id_asesmen,
                        id_apl1: id_apl1
                    },
                    dataType: 'json'
                });

                console.log('loadObservasi response:', response);

                if (response.success) {
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

                            response.observasi = convertObservasiObjectToArray(response.observasi);
                            console.log('Converted observasi to array:', response.observasi);
                        }
                    }

                    if (hasObservasi && response.observasi.length > 0) {
                        renderObservasiTable(response.observasi, response.existing_data);
                        state.totalKUK = response.totalKUK || response.observasi.length;

                        if (response.id_observasi) {
                            state.id_observasi = response.id_observasi;
                            console.log('Loaded existing id_observasi:', state.id_observasi);
                        }

                        updateProgressBar();
                        $('#formObservasi').show();
                        showSuccess('Data berhasil dimuat', `${observasiCount} kriteria unjuk kerja siap untuk observasi`);
                    } else {
                        console.warn('No observasi data found');
                        showInfo('Belum Ada Data Observasi', 'Belum ada unit kompetensi yang tersedia untuk asesmen ini.');
                        $('#formObservasi').hide();
                    }
                } else {
                    showError('Gagal memuat data observasi', response.message);
                }
            } catch (error) {
                console.error('Error loading observasi data:', error);

                let errorMessage = 'Terjadi kesalahan saat memuat data observasi';
                if (error.responseJSON && error.responseJSON.message) {
                    errorMessage = error.responseJSON.message;
                } else if (error.responseJSON && error.responseJSON.messages) {
                    if (error.responseJSON.messages.error) {
                        errorMessage = error.responseJSON.messages.error;
                    } else {
                        errorMessage = JSON.stringify(error.responseJSON.messages);
                    }
                } else if (error.responseText) {
                    try {
                        const errorResponse = JSON.parse(error.responseText);
                        errorMessage = errorResponse.message || errorMessage;
                    } catch (e) {
                        errorMessage = error.responseText;
                    }
                }

                showError('Error Database', errorMessage);
            } finally {
                $('#loadingData').hide();
            }
        }

        function convertObservasiObjectToArray(observasiObject) {
            const observasiArray = [];

            console.log('Converting observasi object:', observasiObject);

            Object.keys(observasiObject).forEach(kelompokKey => {
                const kelompok = observasiObject[kelompokKey];

                if (kelompok.units) {
                    Object.keys(kelompok.units).forEach(unitKey => {
                        const unit = kelompok.units[unitKey];

                        if (unit.elements) {
                            Object.keys(unit.elements).forEach(elemenKey => {
                                const element = unit.elements[elemenKey];

                                if (element.kuks && Array.isArray(element.kuks)) {
                                    element.kuks.forEach(kuk => {
                                        observasiArray.push({
                                            id_kelompok: kelompok.id_kelompok,
                                            nama_kelompok: kelompok.nama_kelompok,
                                            id_unit: unit.id_unit,
                                            kode_unit: unit.kode_unit,
                                            nama_unit: unit.nama_unit,
                                            id_elemen: element.id_elemen,
                                            kode_elemen: element.kode_elemen,
                                            nama_elemen: element.nama_elemen,
                                            id_kuk: kuk.id_kuk,
                                            kode_kuk: kuk.kode_kuk,
                                            kriteria_unjuk_kerja: kuk.nama_kuk
                                        });
                                    });
                                }
                            });
                        }
                    });
                }
            });

            console.log('Converted array length:', observasiArray.length);
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
                id_apl1: $('#form_id_apl1').val(),
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

            console.log('Batch save data:', batchData);

            try {
                const response = await $.ajax({
                    url: '<?= base_url('asesor/api/observasi/save') ?>',
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
                    if (response.token) state.csrfHash = response.token;
                } else {
                    showError('Gagal menyimpan data', response.message);
                }
            } catch (error) {
                console.error('Batch save error:', error);
                let errorMessage = 'Terjadi kesalahan saat menyimpan data';

                if (error.responseJSON && error.responseJSON.messages) {
                    if (error.responseJSON.messages.error) {
                        errorMessage = error.responseJSON.messages.error;
                    } else {
                        errorMessage = JSON.stringify(error.responseJSON.messages);
                    }
                } else if (error.responseJSON && error.responseJSON.message) {
                    errorMessage = error.responseJSON.message;
                }

                showError(errorMessage);
            } finally {
                $btn.html(originalBtnText).attr('disabled', false);
            }
        }

        async function saveSettings() {
            const data = {
                save_type: 'settings',
                id_asesmen: state.id_asesmen,
                id_apl1: $('#form_id_apl1').val(),
                tanggal_observasi: $('#form_tanggal_observasi').val(),
                [state.csrfName]: state.csrfHash
            };

            console.log('Saving settings with data:', data);

            try {
                const response = await $.ajax({
                    url: '<?= base_url('asesor/api/observasi/save') ?>',
                    type: 'POST',
                    data: data,
                    dataType: 'json'
                });

                if (response.success && response.data && response.data.id_observasi) {
                    state.id_observasi = response.data.id_observasi;
                    console.log('Observasi settings saved with ID:', state.id_observasi);
                }

                if (response.token) {
                    state.csrfHash = response.token;
                }

                return response;
            } catch (error) {
                console.error('Error saving settings:', error);
                showError('Gagal menyimpan pengaturan');
                return null;
            }
        }

        // PERBAIKAN: Hapus duplikasi dan perbaiki saveKUK function
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
                id_apl1: $('#form_id_apl1').val(),
                id_kuk: id_kuk,
                kompeten: kompeten,
                keterangan: keterangan,
                tanggal_observasi: $('#form_tanggal_observasi').val(),
                [state.csrfName]: state.csrfHash
            };

            console.log('Saving KUK with data:', data);

            try {
                const response = await $.ajax({
                    url: '<?= base_url('asesor/api/observasi/save') ?>',
                    type: 'POST',
                    data: data,
                    dataType: 'json'
                });

                if (response.token) {
                    state.csrfHash = response.token;
                }

                if (response.success) {
                    console.log('KUK saved successfully:', id_kuk, kompeten);
                    showAutoSaveIndicator();
                } else {
                    console.error('KUK save failed:', response);
                    showError('Gagal menyimpan data KUK: ' + (response.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving KUK:', error);
                if (error.responseJSON && error.responseJSON.messages) {
                    const errorMsg = error.responseJSON.messages.error || JSON.stringify(error.responseJSON.messages);
                    showError('Gagal menyimpan data KUK: ' + errorMsg);
                } else {
                    showError('Gagal menyimpan data KUK');
                }
            }
        }

        async function handleFormSubmit(e) {
            e.preventDefault();

            if (!$('#form_id_skema').val()) {
                showError('Silakan pilih skema terlebih dahulu');
                return;
            }

            if (!$('#form_id_apl1').val()) {
                showError('Silakan pilih APL1 terlebih dahulu');
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
                        url: '<?= base_url('asesor/api/observasi/save') ?>',
                        type: 'POST',
                        data: formData,
                        dataType: 'json'
                    });

                    if (response.success) {
                        showSuccess(response.message || 'Data berhasil disimpan');
                        if (response.token) state.csrfHash = response.token;
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

            const statusText = totalChecked === 0 ? 'Belum ada yang dicentang' :
                progressPercent === 100 ? 'Semua kriteria telah dicentang' :
                `${totalChecked} dari ${state.totalKUK} kriteria dicentang`;

            $('#data-status').html(`<i class="fas fa-info-circle text-info"></i> ${statusText}`);

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

        function saveSessionState() {
            const state_data = {
                id_asesmen: state.id_asesmen,
                id_skema: state.id_skema,
                tanggal_observasi: $('#tanggal_observasi').val(),
                timestamp: Date.now()
            };

            try {
                sessionStorage.setItem('observasi_state_apl1', JSON.stringify(state_data));
            } catch (e) {
                // Storage might be full or disabled
            }
        }

        function showAutoSaveIndicator() {
            const $indicator = $('<div class="auto-save-indicator">').html(
                '<i class="fas fa-check text-success"></i> Tersimpan otomatis'
            ).css({
                position: 'fixed',
                top: '20px',
                right: '20px',
                background: '#fff',
                border: '1px solid #28a745',
                borderRadius: '4px',
                padding: '8px 12px',
                zIndex: 9999,
                fontSize: '14px'
            });

            $('body').append($indicator);

            setTimeout(() => {
                $indicator.fadeOut(() => $indicator.remove());
            }, 2000);
        }

        // Utility functions
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

        // Notification functions
        function showSuccess(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: message,
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                console.log('Success:', message);
            }
        }

        function showError(title, message = '') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: message,
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            } else {
                console.error('Error:', title, message);
            }
        }

        function showInfo(title, message = '') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: title,
                    text: message,
                    confirmButtonText: 'Mengerti',
                    customClass: {
                        confirmButton: 'btn btn-info'
                    }
                });
            } else {
                console.log('Info:', title, message);
            }
        }

        function initializePageState() {
            $('#loadingData').hide();
            $('#formObservasi').hide();
            $('#emptyDataMessage').hide();
            $('#initialInstructions').show();

            if (!$('#id_asesmen').val()) {
                $('#id_apl1').empty().append('<option value="">-- Pilih Asesmen Terlebih Dahulu --</option>').prop('disabled', true);
            }
        }

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
        }

        function trackPerformance(action, startTime) {
            const duration = performance.now() - startTime;
            if (duration > 3000) {
                if (window.console && window.console.warn) {
                    console.warn(`Slow operation detected: ${action} took ${duration}ms`);
                }
            }
        }

        // Initialize with error boundary
        try {
            initEventHandlers();
            initializePageState();

            // Load saved settings if available
            const savedData = sessionStorage.getItem('observasi_state_apl1');
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
                    sessionStorage.removeItem('observasi_state_apl1');
                }
            }
        } catch (error) {
            showError('Initialization Error', 'Terjadi kesalahan saat memuat halaman. Silakan refresh halaman.');
        }
    });
</script>