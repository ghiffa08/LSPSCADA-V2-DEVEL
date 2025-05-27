<script>
    $(document).ready(function() {
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
            pendingChanges: false,
            isProcessing: false,
            saveQueue: [],
            csrfName: '<?= csrf_token() ?>',
            csrfHash: '<?= csrf_hash() ?>'
        };

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Utility functions
        function showSuccess(message) {
            showAlert('success', message);
        }

        function showError(message) {
            showAlert('danger', message);
        }

        function showAlert(type, message, duration = 5000) {
            const alertClass = `alert-${type}`;
            const iconClass = type === 'success' ? 'fa-check-circle' :
                type === 'danger' ? 'fa-exclamation-triangle' :
                type === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle';

            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas ${iconClass} mr-2"></i>${message}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `;

            // Remove existing alerts
            $('.alert').remove();

            // Add new alert at the top of the card body
            $('.card-body').first().prepend(alertHtml);

            // Auto remove after duration
            if (duration > 0) {
                setTimeout(() => {
                    $('.alert').fadeOut(() => $('.alert').remove());
                }, duration);
            }
        }

        function updateProgressBar() {
            const totalCheckboxes = $('.method-checkbox').length;
            const checkedCheckboxes = $('.method-checkbox:checked').length;

            if (totalCheckboxes === 0) {
                $('#progress-bar').css('width', '0%').attr('aria-valuenow', 0);
                $('#progress-text').text('0%');
                return;
            }

            const percentage = Math.round((checkedCheckboxes / totalCheckboxes) * 100);
            $('#progress-bar').css('width', percentage + '%').attr('aria-valuenow', percentage);
            $('#progress-text').text(percentage + '%');

            // Update progress bar color
            $('#progress-bar').removeClass('bg-danger bg-warning bg-success')
                .addClass(percentage < 30 ? 'bg-danger' : percentage < 70 ? 'bg-warning' : 'bg-success');
        }

        function resetProgressBar() {
            $('#progress-bar').css('width', '0%').attr('aria-valuenow', 0);
            $('#progress-text').text('0%');
            updateDataStatus('info', 'Menunggu data...');
        }

        function updateDataStatus(type, message) {
            const iconClass = type === 'success' ? 'fa-check text-success' :
                type === 'error' ? 'fa-times text-danger' :
                type === 'warning' ? 'fa-exclamation text-warning' :
                type === 'loading' ? 'fa-spinner fa-spin text-primary' : 'fa-sync text-muted';

            $('#data-status').html(`<i class="fas ${iconClass}"></i> ${message}`);
            $('#data-status-bottom').html(`<i class="fas ${iconClass}"></i> ${message}`);
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Event handlers
        function initEventHandlers() {
            // Skema selection change
            $('#id_skema').on('change', handleSkemaChange);

            // Asesi selection change
            $('#id_asesi').on('change', handleAsesiChange);

            // Tanggal asesmen change
            $('#tanggal_asesmen').on('change', function() {
                $('#form_tanggal_asesmen').val($(this).val());
                saveSettings();
            });

            // Check/uncheck all methods buttons
            $('#checkAllMethods').click(() => handleBulkCheckMethods(true));
            $('#uncheckAllMethods').click(() => handleBulkCheckMethods(false));

            // Method checkbox changes with auto-save
            $(document).on('change', '.method-checkbox', handleMethodCheckboxChange);

            // Keterangan input changes with auto-save
            $(document).on('input', '.keterangan-input', debounce(handleKeteranganChange, 500));

            // Rekomendasi and catatan changes with auto-save
            $('#rekomendasi').on('change', debounce(saveSettings, 300));
            $('#tindak_lanjut').on('input', debounce(saveSettings, 500));
            $('#catatan').on('input', debounce(saveSettings, 500));

            // Form submission
            $('#formRekamanAsesmen').submit(handleFormSubmit);
        }

        // Main functions
        async function handleSkemaChange() {
            state.id_skema = $(this).val();
            state.id_asesmen = $(this).find('option:selected').data('id-asesmen');

            $('#form_id_skema').val(state.id_skema);
            $('#form_id_asesmen').val(state.id_asesmen);
            $('#id_asesi').prop('disabled', true).empty().append('<option value="">-- Memuat Asesi... --</option>');
            $('#form_id_asesi').val('');
            $('#rekamanAsesmenContainer').empty();
            $('#formRekamanAsesmen').hide();
            resetProgressBar();

            if (!state.id_skema) {
                $('#id_asesi').empty().append('<option value="">-- Pilih Skema Terlebih Dahulu --</option>');
                $('#kode_skema').val('');
                return;
            }

            try {
                // Update kode skema display
                const selectedOption = $(this).find('option:selected');
                const kodeSkema = selectedOption.data('kode-skema') || '';
                $('#kode_skema').val(kodeSkema);

                // Load asesi options
                updateDataStatus('loading', 'Memuat data skema...');

                const response = await $.ajax({
                    url: '<?= base_url('asesor/rekaman-asesmen/getSkemaDetails') ?>',
                    type: 'GET',
                    data: {
                        id_skema: state.id_skema,
                        id_asesmen: state.id_asesmen,
                        [state.csrfName]: state.csrfHash
                    },
                    dataType: 'json'
                });

                if (response.success) {
                    populateAsesiDropdown(response.data.asesi);
                    updateDataStatus('success', 'Data berhasil dimuat');
                } else {
                    throw new Error(response.message || 'Gagal memuat data skema');
                }
            } catch (error) {
                console.error('Error in handleSkemaChange:', error);
                showError('Gagal memuat data skema: ' + error.message);
                updateDataStatus('error', 'Gagal memuat data skema');
                resetAsesiDropdown();
            }
        }

        function populateAsesiDropdown(asesiList) {
            const $asesiDropdown = $('#id_asesi').empty().append('<option value="">-- Pilih Asesi --</option>');

            if (asesiList && asesiList.length > 0) {
                asesiList.forEach(asesi => {
                    $asesiDropdown.append(`
                        <option value="${asesi.id_asesi}" data-apl1-id="${asesi.id_apl1 || ''}" data-nama="${asesi.nama_asesi || ''}">
                            ${asesi.nama_asesi} (${asesi.id_asesi})
                        </option>
                    `);
                });
                $asesiDropdown.prop('disabled', false);
            } else {
                $asesiDropdown.append('<option value="">-- Tidak Ada Asesi Tersedia --</option>');
                showError('Tidak ada asesi terdaftar untuk skema ini');
            }
        }

        function resetAsesiDropdown() {
            $('#id_asesi').empty()
                .append('<option value="">-- Pilih Skema Terlebih Dahulu --</option>')
                .prop('disabled', true);
        }

        async function handleAsesiChange() {
            const asesiId = $(this).val();
            const selectedOption = $(this).find('option:selected');

            $('#form_id_asesi').val(asesiId);

            if (asesiId) {
                const apl1Id = selectedOption.data('apl1-id');
                state.id_apl1 = apl1Id;
                $('#form_id_apl1').val(apl1Id);

                if (state.id_skema && asesiId) {
                    await loadRekamanAsesmenData();
                    saveSettings();
                }
            } else {
                $('#formRekamanAsesmen').hide();
                resetProgressBar();
            }
        }

        async function loadRekamanAsesmenData() {
            const id_skema = $('#id_skema').val();
            const id_asesi = $('#id_asesi').val();

            if (!id_skema || !id_asesi) return;

            try {
                $('#loadingState').show();
                $('#formRekamanAsesmen').hide();
                updateDataStatus('loading', 'Memuat data rekaman asesmen...');

                const response = await $.ajax({
                    url: '<?= base_url('asesor/rekaman-asesmen/loadRekamanAsesmen') ?>',
                    type: 'GET',
                    data: {
                        id_skema: id_skema,
                        id_asesmen: state.id_asesmen,
                        id_asesi: id_asesi
                    },
                    dataType: 'json'
                });

                if (response.success) {
                    renderRekamanAsesmenForm(response.rekaman_asesmen, response.existing_data);
                    state.totalUnits = response.totalUnits;
                    updateProgressBar();
                    $('#formRekamanAsesmen').show();
                    updateDataStatus('success', 'Data rekaman asesmen berhasil dimuat');
                } else {
                    throw new Error(response.message || 'Gagal memuat data rekaman asesmen');
                }
            } catch (error) {
                console.error('Error loading rekaman asesmen data:', error);
                const errorMessage = error.responseJSON?.message || 'Terjadi kesalahan saat memuat data';
                showError('Gagal memuat data rekaman asesmen: ' + errorMessage);
                updateDataStatus('error', 'Gagal memuat data rekaman asesmen');
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
                        Tidak ada unit kompetensi yang ditemukan untuk skema ini.
                    </div>
                `;
                $('#rekamanAsesmenContainer').html(html);
                return;
            }

            units.forEach((unit, index) => {
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
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" 
                                               class="custom-control-input method-checkbox" 
                                               id="observasi_${unit.id_unit}" 
                                               name="units[${unit.id_unit}][observasi]" 
                                               value="1"
                                               data-unit-id="${unit.id_unit}"
                                               data-method="observasi"
                                               ${unitExistingData && unitExistingData.observasi ? 'checked' : ''}>
                                        <label class="custom-control-label" for="observasi_${unit.id_unit}">
                                            <i class="fas fa-eye text-info mr-1"></i> Observasi Demonstrasi
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" 
                                               class="custom-control-input method-checkbox" 
                                               id="portofolio_${unit.id_unit}" 
                                               name="units[${unit.id_unit}][portofolio]" 
                                               value="1"
                                               data-unit-id="${unit.id_unit}"
                                               data-method="portofolio"
                                               ${unitExistingData && unitExistingData.portofolio ? 'checked' : ''}>
                                        <label class="custom-control-label" for="portofolio_${unit.id_unit}">
                                            <i class="fas fa-folder text-warning mr-1"></i> Portofolio
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" 
                                               class="custom-control-input method-checkbox" 
                                               id="pihak_ketiga_${unit.id_unit}" 
                                               name="units[${unit.id_unit}][pihak_ketiga]" 
                                               value="1"
                                               data-unit-id="${unit.id_unit}"
                                               data-method="pihak_ketiga"
                                               ${unitExistingData && unitExistingData.pihak_ketiga ? 'checked' : ''}>
                                        <label class="custom-control-label" for="pihak_ketiga_${unit.id_unit}">
                                            <i class="fas fa-users text-success mr-1"></i> Pernyataan Pihak Ketiga
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" 
                                               class="custom-control-input method-checkbox" 
                                               id="tes_lisan_${unit.id_unit}" 
                                               name="units[${unit.id_unit}][tes_lisan]" 
                                               value="1"
                                               data-unit-id="${unit.id_unit}"
                                               data-method="tes_lisan"
                                               ${unitExistingData && unitExistingData.tes_lisan ? 'checked' : ''}>
                                        <label class="custom-control-label" for="tes_lisan_${unit.id_unit}">
                                            <i class="fas fa-comments text-primary mr-1"></i> Pertanyaan Lisan
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" 
                                               class="custom-control-input method-checkbox" 
                                               id="tes_tertulis_${unit.id_unit}" 
                                               name="units[${unit.id_unit}][tes_tertulis]" 
                                               value="1"
                                               data-unit-id="${unit.id_unit}"
                                               data-method="tes_tertulis"
                                               ${unitExistingData && unitExistingData.tes_tertulis ? 'checked' : ''}>
                                        <label class="custom-control-label" for="tes_tertulis_${unit.id_unit}">
                                            <i class="fas fa-pencil-alt text-secondary mr-1"></i> Pertanyaan Tertulis
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" 
                                               class="custom-control-input method-checkbox" 
                                               id="proyek_kerja_${unit.id_unit}" 
                                               name="units[${unit.id_unit}][proyek_kerja]" 
                                               value="1"
                                               data-unit-id="${unit.id_unit}"
                                               data-method="proyek_kerja"
                                               ${unitExistingData && unitExistingData.proyek_kerja ? 'checked' : ''}>
                                        <label class="custom-control-label" for="proyek_kerja_${unit.id_unit}">
                                            <i class="fas fa-project-diagram text-dark mr-1"></i> Proyek Kerja
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" 
                                               class="custom-control-input method-checkbox" 
                                               id="lainnya_${unit.id_unit}" 
                                               name="units[${unit.id_unit}][lainnya]" 
                                               value="1"
                                               data-unit-id="${unit.id_unit}"
                                               data-method="lainnya"
                                               ${unitExistingData && unitExistingData.lainnya ? 'checked' : ''}>
                                        <label class="custom-control-label" for="lainnya_${unit.id_unit}">
                                            <i class="fas fa-ellipsis-h text-muted mr-1"></i> Lainnya
                                        </label>
                                    </div>
                                </div>
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

        function handleMethodCheckboxChange() {
            const $checkbox = $(this);
            const unitId = $checkbox.data('unit-id');
            const method = $checkbox.data('method');
            const isChecked = $checkbox.is(':checked');

            // Visual feedback
            const $card = $checkbox.closest('.card');
            if (isChecked) {
                $card.addClass('border-success');
                setTimeout(() => $card.removeClass('border-success'), 1000);
            }

            // Update progress
            updateProgressBar();

            // Auto-save
            saveMethod(unitId, method, isChecked);
        }

        function handleKeteranganChange() {
            const $textarea = $(this);
            const unitId = $textarea.data('unit-id');
            const keterangan = $textarea.val();

            saveKeterangan(unitId, keterangan);
        }

        function handleBulkCheckMethods(checkAll) {
            $('.method-checkbox').prop('checked', checkAll);
            updateProgressBar();

            // Save all changes
            saveBulkMethods(checkAll);

            // Visual feedback
            if (checkAll) {
                showSuccess('Semua metode berhasil dipilih');
            } else {
                showSuccess('Semua metode berhasil dibatalkan');
            }
        }

        // Auto-save functions
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

                if (response.status === 'success' && response.csrf_hash) {
                    state.csrfHash = response.csrf_hash;
                }
            } catch (error) {
                console.error('Error saving method:', error);
            }
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

                if (response.status === 'success' && response.csrf_hash) {
                    state.csrfHash = response.csrf_hash;
                }
            } catch (error) {
                console.error('Error saving keterangan:', error);
            }
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

                if (response.status === 'success' && response.csrf_hash) {
                    state.csrfHash = response.csrf_hash;
                }
            } catch (error) {
                console.error('Error saving bulk methods:', error);
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

                if (response.csrf_hash) {
                    state.csrfHash = response.csrf_hash;
                }
            } catch (error) {
                console.error('Error saving settings:', error);
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
                        showSuccess(response.message || 'Rekaman asesmen berhasil diselesaikan!');
                        updateDataStatus('success', 'Rekaman asesmen selesai');

                        setTimeout(() => {
                            window.location.href = response.redirect || '<?= base_url('asesor/rekaman-asesmen') ?>';
                        }, 2000);
                    } else {
                        throw new Error(response.message || 'Gagal menyelesaikan rekaman asesmen');
                    }
                } catch (error) {
                    console.error('Form submission error:', error);
                    const errorMessage = error.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data';
                    showError('Gagal menyelesaikan rekaman asesmen: ' + errorMessage);
                    updateDataStatus('error', 'Gagal menyimpan');
                } finally {
                    $('#btnSave').html('<i class="fas fa-save mr-1"></i> Selesaikan Rekaman').attr('disabled', false);
                }
            }
        }

        // Initialize everything
        initEventHandlers();
        updateDataStatus('info', 'Menunggu data...');
    });
</script>