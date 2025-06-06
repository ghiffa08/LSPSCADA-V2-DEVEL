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
            // Skema selection change
            $('#id_skema').on('change', handleSkemaChange);

            // Asesi selection change
            $('#id_asesi').on('change', function() {
                $('#form_id_asesi').val($(this).val());
                if ($('#id_skema').val() && $(this).val()) {
                    loadObservasiData();
                    saveSettings();
                } else {
                    $('#formObservasi').hide();
                }
            });

            // Tanggal observasi change
            $('#tanggal_observasi').on('change', function() {
                $('#form_tanggal_observasi').val($(this).val());
                saveSettings();
            });

            // Check/uncheck all buttons
            $('#checkAll').click(() => handleBulkCheck(true));
            $('#uncheckAll').click(() => handleBulkCheck(false));

            // KUK checkbox changes
            $(document).on('change', '.kuk-checkbox', handleKUKCheckboxChange);

            // Keterangan input changes
            $(document).on('input', '.keterangan-input', debounce(handleKeteranganChange, 500));

            // Form submission
            $('#formObservasi').submit(handleFormSubmit);
        }

        // Main functions
        async function handleSkemaChange() {
            state.id_skema = $(this).val();
            state.id_asesmen = $(this).find('option:selected').data('id-asesmen');

            $('#form_id_skema').val(state.id_skema);
            $('#form_id_asesmen').val(state.id_asesmen);
            $('#id_asesi').prop('disabled', true).empty().append('<option value="">-- Memuat Asesi... --</option>');
            $('#form_id_asesi').val('');
            $('#observasiContainer').empty();
            $('#formObservasi').hide();
            resetProgressBar();

            if (!state.id_skema) {
                $('#id_asesi').empty().append('<option value="">-- Pilih Skema Terlebih Dahulu --</option>');
                $('#kode_skema').val('');
                return;
            }

            try {
                const response = await $.ajax({
                    url: '<?= base_url('/asesor/observasi/getSkemaDetails') ?>',
                    type: 'GET',
                    data: {
                        id_skema: state.id_skema
                    },
                    dataType: 'json'
                });

                if (response.success) {
                    $('#kode_skema').val(response.skema.kode_skema || '');
                    populateAsesiDropdown(response.asesi);
                } else {
                    showError('Gagal memuat detail skema', response.message);
                }
            } catch (error) {
                showError('Terjadi kesalahan saat memuat data');
            }
        }

        function populateAsesiDropdown(asesiList) {
            const $asesiDropdown = $('#id_asesi').empty().append('<option value="">-- Pilih Asesi --</option>');

            if (asesiList && asesiList.length > 0) {
                asesiList.forEach(asesi => {
                    $asesiDropdown.append(`<option value="${asesi.id_asesi}">${asesi.nama_lengkap}</option>`);
                });
                $asesiDropdown.prop('disabled', false);
            } else {
                $asesiDropdown.append('<option value="">-- Tidak Ada Asesi Tersedia --</option>');
            }
        }

        async function loadObservasiData() {
            const id_skema = $('#id_skema').val();
            const id_asesi = $('#id_asesi').val();

            if (!id_skema || !id_asesi) return;

            try {
                $('#loadingData').show();
                $('#formObservasi').hide();

                const response = await $.ajax({
                    url: '<?= base_url('/asesor/observasi/loadObservasi') ?>',
                    type: 'GET',
                    data: {
                        id_skema: id_skema,
                        id_asesmen: state.id_asesmen,
                        id_asesi: id_asesi
                    },
                    dataType: 'json'
                });

                if (response.success) {
                    renderObservasiTable(response.observasi, response.existing_data);
                    state.totalKUK = response.totalKUK;
                    updateProgressBar();
                    $('#formObservasi').show();
                } else {
                    showError('Gagal memuat data observasi', response.message);
                }
            } catch (error) {
                const errorMessage = error.responseJSON?.messages || 'Terjadi kesalahan saat memuat data';
                showError(errorMessage);
            } finally {
                $('#loadingData').hide();
            }
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

            const $checkboxes = $('.kuk-checkbox');
            $checkboxes.prop('checked', checkState);

            $('.kuk-item').toggleClass('bg-success text-white', checkState);
            $('.keterangan-input').toggleClass('border-light', checkState);
            updateProgressBar();

            const batchData = {
                save_type: 'batch',
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

                if (response.csrfHash) {
                    state.csrfHash = response.csrfHash;
                }
            } catch (error) {
                showError('Gagal menyimpan pengaturan');
            }
        }

        async function saveKUK(id_kuk, kompeten, keterangan) {
            const data = {
                save_type: 'kuk',
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
            } catch (error) {
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
            const checkedKUK = $('.kuk-checkbox:checked').length;
            if (state.totalKUK === 0) return;

            const percentage = Math.round((checkedKUK / state.totalKUK) * 100);
            const $progressBar = $('#progress-bar');

            $progressBar.css('width', percentage + '%')
                .attr('aria-valuenow', percentage)
                .removeClass('bg-danger bg-warning bg-info bg-success')
                .addClass(
                    percentage < 25 ? 'bg-danger' :
                    percentage < 50 ? 'bg-warning' :
                    percentage < 75 ? 'bg-info' : 'bg-success'
                );

            $('#progress-text').text(`${percentage}% (${checkedKUK}/${state.totalKUK})`);
        }

        function resetProgressBar() {
            $('#progress-bar').css('width', '0%').attr('aria-valuenow', 0).removeClass('bg-danger bg-warning bg-info bg-success');
            $('#progress-text').text('0%');
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
            return function() {
                const context = this,
                    args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        function showSuccess(message) {
            Swal.fire('Berhasil', message, 'success');
        }

        function showError(title, message = '') {
            Swal.fire(title, message, 'error');
        }

        // Initialize
        initEventHandlers();
    });
</script>