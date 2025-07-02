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
            id_skema: '',
            id_asesmen: '',
            id_asesi: '',
            id_apl1: '',
            totalUnits: 0,
            csrfName: '<?= csrf_token() ?>',
            csrfHash: '<?= csrf_hash() ?>'
        };

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // === UI Notification Helper Functions (Swal, konsisten dengan observasi) ===
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

        // === Progress Bar ===
        function updateProgressBar() {
            const total = $('.method-checkbox').length;
            const checked = $('.method-checkbox:checked').length;
            const percent = total > 0 ? Math.round((checked / total) * 100) : 0;

            $('#progress-bar').css('width', percent + '%').attr('aria-valuenow', percent);
            $('#progress-text').text(percent + '%');

            let statusText = checked === 0 ? 'Belum ada metode dipilih' :
                percent === 100 ? 'Semua metode telah dipilih' :
                `${checked} dari ${total} metode dipilih`;

            $('#data-status').html(`<i class="fas fa-info-circle text-info"></i> ${statusText}`);

            // Bar color
            const progressBar = $('#progress-bar');
            progressBar.removeClass('bg-warning bg-success');
            if (percent === 100) {
                progressBar.addClass('bg-success');
            } else if (percent > 0) {
                progressBar.addClass('bg-warning');
            }
        }

        function resetProgressBar() {
            $('#progress-bar').css('width', '0%').attr('aria-valuenow', 0);
            $('#progress-text').text('0%');
            $('#data-status').html('<i class="fas fa-sync text-muted"></i> Menunggu data...');
            state.totalUnits = 0;
        }

        // === Event Handlers ===
        function initEventHandlers() {
            // Asesmen selection change (root filter)
            $('#id_asesmen').on('change', handleAsesmenChange);

            // Asesi selection change
            $('#id_asesi').on('change', handleAsesiChange);

            // Tanggal asesmen change
            $('#tanggal_asesmen').on('change', function() {
                $('#form_tanggal_asesmen').val($(this).val());
                saveSettings();
            });

            // Check/uncheck all methods
            $('#checkAllMethods').on('click', () => handleBulkCheckMethods(true));
            $('#uncheckAllMethods').on('click', () => handleBulkCheckMethods(false));

            // Method checkbox changes with auto-save
            $(document).on('change', '.method-checkbox', handleMethodCheckboxChange);

            // Keterangan input changes with auto-save
            $(document).on('input', '.keterangan-input', debounce(handleKeteranganChange, 500));

            // Rekomendasi/tindak lanjut/catatan auto-save
            $('#rekomendasi').on('change', debounce(saveSettings, 300));
            $('#tindak_lanjut').on('input', debounce(saveSettings, 500));
            $('#catatan').on('input', debounce(saveSettings, 500));

            // Form submission
            $('#formRekamanAsesmen').submit(handleFormSubmit);
        }

        // === Main Functions ===
        async function handleAsesmenChange() {
            const selectedOption = $(this).find('option:selected');
            state.id_asesmen = $(this).val();
            state.id_skema = selectedOption.data('id-skema');
            $('#form_id_asesmen').val(state.id_asesmen);
            $('#form_id_skema').val(state.id_skema);
            $('#kode_skema').val(selectedOption.data('kode-skema') || '');

            // Reset asesi dropdown and form
            $('#id_asesi').prop('disabled', true).empty().append('<option value="">-- Memuat Asesi... --</option>');
            $('#form_id_asesi').val('');
            $('#rekamanAsesmenContainer').empty();
            $('#formRekamanAsesmen').hide();
            resetProgressBar();

            if (!state.id_asesmen) {
                $('#id_asesi').empty().append('<option value="">-- Pilih Asesmen Terlebih Dahulu --</option>');
                $('#initialInstructions').show();
                return;
            }

            $('#initialInstructions').hide();
            try {
                const response = await $.ajax({
                    url: '<?= base_url('asesor/rekaman-asesmen/getAsesiByAsesmen') ?>',
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
                    $asesiDropdown.append(`<option value="${asesi.id_asesi}" data-apl1-id="${asesi.id_apl1 || ''}">${escapeHtml(asesi.nama || 'Nama tidak tersedia')}</option>`);
                });
                $asesiDropdown.prop('disabled', false);
                $('#emptyDataMessage').hide();
                if (response.message) showSuccess('Data berhasil dimuat', response.message);
            } else {
                $asesiDropdown.append('<option value="">-- Belum Ada Asesi Terdaftar --</option>');
                $asesiDropdown.prop('disabled', true);
                $('#emptyDataMessage').show();
                if (response.message) showInfo('Informasi', response.message);
            }
        }

        async function handleAsesiChange() {
            const asesiId = $(this).val();
            const selectedOption = $(this).find('option:selected');
            $('#form_id_asesi').val(asesiId);

            if (asesiId && state.id_asesmen) {
                state.id_apl1 = selectedOption.data('apl1-id');
                $('#form_id_apl1').val(state.id_apl1);
                await loadRekamanAsesmenData();
                saveSettings();
            } else {
                $('#formRekamanAsesmen').hide();
                resetProgressBar();
            }
        }

        async function loadRekamanAsesmenData() {
            if (!state.id_asesmen || !$('#id_asesi').val()) return;

            $('#initialInstructions').hide();
            $('#emptyDataMessage').hide();
            $('#loadingState').show();
            $('#formRekamanAsesmen').hide();

            try {
                const response = await $.ajax({
                    url: '<?= base_url('asesor/rekaman-asesmen/loadRekamanAsesmen') ?>',
                    type: 'GET',
                    data: {
                        id_asesmen: state.id_asesmen,
                        id_skema: state.id_skema,
                        id_asesi: $('#id_asesi').val()
                    },
                    dataType: 'json'
                });

                if (response.success && response.rekaman_asesmen && response.rekaman_asesmen.length > 0) {
                    renderRekamanAsesmenForm(response.rekaman_asesmen, response.existing_data);
                    state.totalUnits = response.totalUnits || response.rekaman_asesmen.length;
                    updateProgressBar();
                    $('#formRekamanAsesmen').show();
                    showSuccess('Data berhasil dimuat', `${state.totalUnits} unit kompetensi tersedia`);
                } else {
                    showInfo('Belum Ada Data Unit', 'Belum ada unit kompetensi untuk asesmen ini.');
                    $('#formRekamanAsesmen').hide();
                }
            } catch (error) {
                const errorMessage = error.responseJSON?.message || 'Terjadi kesalahan saat memuat data';
                showError('Error Database', errorMessage);
            } finally {
                $('#loadingState').hide();
            }
        }

        function renderRekamanAsesmenForm(units, existingData) {
            let html = '';
            if (!units || units.length === 0) {
                html = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Tidak ada unit kompetensi yang ditemukan untuk asesmen ini.
                    </div>
                `;
                $('#rekamanAsesmenContainer').html(html);
                return;
            }
            units.forEach(unit => {
                const unitExistingData = existingData ?
                    existingData.find(item => item.id_unit === unit.id_unit) : null;
                html += `
                    <div class="card mb-3 border-left-primary">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 font-weight-bold">
                                <i class="fas fa-certificate text-primary mr-2"></i>
                                ${escapeHtml(unit.kode_unit)} - ${escapeHtml(unit.nama_unit)}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="font-weight-bold mb-3">
                                        <i class="fas fa-tasks text-primary mr-1"></i>
                                        Metode Asesmen yang Digunakan:
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                ${renderMethodCheckbox(unit, unitExistingData, 'observasi', 'fa-eye text-info', 'Observasi Demonstrasi')}
                                ${renderMethodCheckbox(unit, unitExistingData, 'portofolio', 'fa-folder text-warning', 'Portofolio')}
                                ${renderMethodCheckbox(unit, unitExistingData, 'pihak_ketiga', 'fa-users text-success', 'Pernyataan Pihak Ketiga')}
                                ${renderMethodCheckbox(unit, unitExistingData, 'tes_lisan', 'fa-comments text-primary', 'Pertanyaan Lisan')}
                            </div>
                            <div class="row">
                                ${renderMethodCheckbox(unit, unitExistingData, 'tes_tertulis', 'fa-pencil-alt text-secondary', 'Pertanyaan Tertulis')}
                                ${renderMethodCheckbox(unit, unitExistingData, 'proyek_kerja', 'fa-project-diagram text-dark', 'Proyek Kerja')}
                                ${renderMethodCheckbox(unit, unitExistingData, 'lainnya', 'fa-ellipsis-h text-muted', 'Lainnya')}
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Keterangan</label>
                                        <textarea class="form-control keterangan-input"
                                            name="units[${unit.id_unit}][keterangan]"
                                            data-unit-id="${unit.id_unit}"
                                            rows="2"
                                            placeholder="Keterangan tambahan untuk unit ini (opsional)">${unitExistingData ? unitExistingData.keterangan || '' : ''}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#rekamanAsesmenContainer').html(html);
        }

        function renderMethodCheckbox(unit, unitExistingData, methodName, iconClass, label) {
            const checked = unitExistingData && unitExistingData[methodName] ? 'checked' : '';
            return `
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox"
                            class="custom-control-input method-checkbox"
                            id="${methodName}_${unit.id_unit}"
                            name="units[${unit.id_unit}][${methodName}]"
                            value="1"
                            data-unit-id="${unit.id_unit}"
                            data-method="${methodName}"
                            ${checked}>
                        <label class="custom-control-label" for="${methodName}_${unit.id_unit}">
                            <i class="fas ${iconClass} mr-1"></i> ${label}
                        </label>
                    </div>
                </div>
            `;
        }

        // Bulk check/uncheck all
        async function handleBulkCheckMethods(checkState) {
            const $btn = $(checkState ? '#checkAllMethods' : '#uncheckAllMethods');
            const originalBtnText = $btn.html();

            $btn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').attr('disabled', true);

            $('.method-checkbox').prop('checked', checkState);
            updateProgressBar();

            await saveBulkMethods(checkState);

            showSuccess(checkState ? 'Semua metode berhasil dipilih' : 'Semua metode berhasil dibatalkan');
            $btn.html(originalBtnText).attr('disabled', false);
        }

        async function saveBulkMethods(checkAll) {
            const batchData = {
                save_type: 'bulk_methods',
                id_asesmen: state.id_asesmen,
                id_skema: state.id_skema,
                id_asesi: $('#form_id_asesi').val(),
                id_apl1: state.id_apl1,
                check_all: checkAll,
                [state.csrfName]: state.csrfHash
            };

            try {
                const response = await $.ajax({
                    url: '<?= base_url('asesor/rekaman-asesmen/saveBulkMethods') ?>',
                    type: 'POST',
                    data: JSON.stringify(batchData),
                    contentType: 'application/json',
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (response.status === 'success' && response.csrf_hash) state.csrfHash = response.csrf_hash;
            } catch (error) {
                console.error('Error saving bulk methods:', error);
            }
        }

        // Checkbox/keterangan with auto-save
        async function handleMethodCheckboxChange() {
            const $checkbox = $(this);
            const unitId = $checkbox.data('unit-id');
            const method = $checkbox.data('method');
            const isChecked = $checkbox.is(':checked');
            updateProgressBar();
            await saveMethod(unitId, method, isChecked);
        }
        async function saveMethod(unitId, method, isChecked) {
            const data = {
                save_type: 'method',
                id_asesmen: state.id_asesmen,
                id_skema: state.id_skema,
                id_asesi: $('#form_id_asesi').val(),
                id_apl1: state.id_apl1,
                id_unit: unitId,
                method: method,
                value: isChecked ? 1 : 0,
                [state.csrfName]: state.csrfHash
            };
            try {
                const response = await $.ajax({
                    url: '<?= base_url('asesor/rekaman-asesmen/saveMethod') ?>',
                    type: 'POST',
                    data: data,
                    dataType: 'json'
                });
                if (response.status === 'success' && response.csrf_hash) state.csrfHash = response.csrf_hash;
            } catch (error) {
                console.error('Error saving method:', error);
            }
        }

        async function handleKeteranganChange() {
            const $textarea = $(this);
            const unitId = $textarea.data('unit-id');
            const keterangan = $textarea.val();
            await saveKeterangan(unitId, keterangan);
        }
        async function saveKeterangan(unitId, keterangan) {
            const data = {
                save_type: 'keterangan',
                id_asesmen: state.id_asesmen,
                id_asesi: $('#form_id_asesi').val(),
                id_apl1: state.id_apl1,
                id_unit: unitId,
                keterangan: keterangan,
                [state.csrfName]: state.csrfHash
            };
            try {
                const response = await $.ajax({
                    url: '<?= base_url('asesor/rekaman-asesmen/saveKeterangan') ?>',
                    type: 'POST',
                    data: data,
                    dataType: 'json'
                });
                if (response.status === 'success' && response.csrf_hash) state.csrfHash = response.csrf_hash;
            } catch (error) {
                console.error('Error saving keterangan:', error);
            }
        }

        async function saveSettings() {
            const data = {
                save_type: 'settings',
                id_asesmen: state.id_asesmen,
                id_apl1: state.id_apl1,
                tanggal_asesmen: $('#tanggal_asesmen').val(),
                rekomendasi: $('#rekomendasi').val(),
                tindak_lanjut: $('#tindak_lanjut').val(),
                catatan: $('#catatan').val(),
                [state.csrfName]: state.csrfHash
            };
            try {
                const response = await $.ajax({
                    url: '<?= base_url('asesor/rekaman-asesmen/saveGeneral') ?>',
                    type: 'POST',
                    data: data,
                    dataType: 'json'
                });
                if (response.csrf_hash) state.csrfHash = response.csrf_hash;
            } catch (error) {
                console.error('Error saving settings:', error);
            }
        }

        // Form final submit
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
            if (!$('#rekomendasi').val()) {
                showError('Silakan pilih rekomendasi terlebih dahulu');
                $('#rekomendasi').focus();
                return;
            }

            const result = await Swal.fire({
                title: 'Selesaikan Rekaman Asesmen?',
                text: 'Pastikan semua metode asesmen telah diisi dengan benar',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Selesaikan',
                cancelButtonText: 'Batal'
            });

            if (result.isConfirmed) {
                $('#btnSave').html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyelesaikan...').attr('disabled', true);

                try {
                    const formData = $('#formRekamanAsesmen').serializeArray();
                    formData.push({
                        name: 'save_type',
                        value: 'complete'
                    });
                    formData.push({
                        name: state.csrfName,
                        value: state.csrfHash
                    });

                    const response = await $.ajax({
                        url: '<?= base_url('asesor/rekaman-asesmen/complete') ?>',
                        type: 'POST',
                        data: formData,
                        dataType: 'json'
                    });

                    if (response.status === 'success') {
                        showSuccess('Berhasil', response.message || 'Rekaman asesmen berhasil diselesaikan!');
                        setTimeout(() => {
                            window.location.href = response.redirect || '<?= base_url('asesor/rekaman-asesmen') ?>';
                        }, 2000);
                    } else {
                        showError('Gagal', response.message || 'Gagal menyelesaikan rekaman asesmen');
                    }
                } catch (error) {
                    const errorMessage = error.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data';
                    showError('Gagal', errorMessage);
                } finally {
                    $('#btnSave').html('<i class="fas fa-save mr-1"></i> Selesaikan Rekaman').attr('disabled', false);
                }
            }
        }

        function initializePageState() {
            $('#loadingState').hide();
            $('#formRekamanAsesmen').hide();
            $('#emptyDataMessage').hide();

            $('#initialInstructions').show();
            if (!$('#id_asesmen').val()) {
                $('#id_asesi').empty().append('<option value="">-- Pilih Asesmen Terlebih Dahulu --</option>').prop('disabled', true);
            }
        }

        try {
            initEventHandlers();
            initializePageState();
        } catch (error) {
            showError('Initialization Error', 'Terjadi kesalahan saat memuat halaman. Silakan refresh.');
        }
    });
</script>