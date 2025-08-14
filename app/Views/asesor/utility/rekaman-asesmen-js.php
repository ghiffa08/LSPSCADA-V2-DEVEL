<script>
    $(document).ready(function() {
        'use strict';

        // Initialize select2
        $('.select2').select2({
            placeholder: "Pilih...",
            allowClear: true,
            width: '100%'
        });

        // Configuration - PERBAIKAN: URL endpoints yang benar
        const config = {
            baseUrl: '<?= base_url() ?>',
            endpoints: {
                getAsesi: 'asesor/rekaman-asesmen/getAsesiByAsesmen',
                loadRekaman: 'asesor/rekaman-asesmen/loadRekamanAsesmen',
                store: 'asesor/rekaman-asesmen/store', // PERBAIKAN: Endpoint store
                saveSettings: 'asesor/rekaman-asesmen/store',
                saveMethod: 'asesor/rekaman-asesmen/store',
                saveBatch: 'asesor/rekaman-asesmen/store',
                saveRecommendation: 'asesor/rekaman-asesmen/store',
                saveComplete: 'asesor/rekaman-asesmen/store'
            }
        };

        // State management
        const state = {
            selectedAsesmen: null,
            selectedAsesi: null,
            selectedPengajuan: null,
            totalUnits: 0,
            assessedUnits: 0,
            id_rekaman: null,
            isProcessing: false,
            csrfHash: '<?= csrf_hash() ?>'
        };

        // PERBAIKAN: Define updateProgress as global function
        window.updateProgress = function() {
            updateUnitStatus();

            const percentage = state.totalUnits > 0 ? (state.assessedUnits / state.totalUnits) * 100 : 0;

            $('#progress-text').text(Math.round(percentage) + '%');
            $('#progress-bar').css('width', percentage + '%');

            if (state.assessedUnits === 0) {
                $('#data-status').html('<i class="fas fa-hourglass-start text-warning"></i> Belum ada data');
            } else if (state.assessedUnits === state.totalUnits) {
                $('#data-status').html('<i class="fas fa-check-circle text-success"></i> Semua unit terisi');
            } else {
                $('#data-status').html(`<i class="fas fa-clock text-info"></i> ${state.assessedUnits}/${state.totalUnits} unit terisi`);
            }
        };

        // Initialize
        function init() {
            bindEvents();
            initializePageState();
        }

        // Bind event handlers
        function bindEvents() {
            // Asesmen selection
            $('#id_asesmen').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                state.selectedAsesmen = $(this).val();

                $('#form_id_skema').val(selectedOption.data('id-skema'));
                $('#form_id_asesmen').val(state.selectedAsesmen);
                $('#kode_skema').val(selectedOption.data('kode-skema') || '');

                if (state.selectedAsesmen) {
                    loadAsesiOptions(state.selectedAsesmen);
                } else {
                    resetAsesiSelection();
                }
            });

            // Asesi selection
            $('#id_asesi').on('change', function() {
                const asesiId = $(this).val();
                state.selectedAsesi = asesiId;

                const selectedOption = $(this).find('option:selected');
                const pengajuanId = selectedOption.data('id-pengajuan');
                state.selectedPengajuan = pengajuanId;

                $('#form_id_asesi').val(asesiId);
                $('#form_id_pengajuan').val(pengajuanId);

                if (asesiId && pengajuanId && state.selectedAsesmen) {
                    loadRekamanData();
                } else {
                    hideRekamanForm();
                    showInitialInstructions();
                }
            });

            // Tanggal asesmen change - AUTO SAVE
            $('#tanggal_asesmen').on('change', function() {
                $('#form_tanggal_asesmen').val($(this).val());
                saveSettings();
            });

            // Form submission
            $('#formRekamanAsesmen').on('submit', function(e) {
                e.preventDefault();
                saveCompleteRekaman();
            });

            // Bulk actions
            $('#checkAllMethods').on('click', function() {
                handleBulkCheck(true);
            });

            $('#uncheckAllMethods').on('click', function() {
                handleBulkCheck(false);
            });

            // Event delegation untuk checkbox yang akan dibuat dinamis - AUTO SAVE
            $(document).on('change', 'input[type="checkbox"][name^="kompetensi"]', function() {
                const $checkbox = $(this);
                const unitId = $checkbox.data('unit-id') || $checkbox.attr('name').match(/\[(\d+)\]/)[1];
                const method = $checkbox.data('method') || $checkbox.attr('name').match(/\[([^\]]+)\]$/)[1];
                const isChecked = $checkbox.is(':checked');

                console.log(`Checkbox changed: Unit ${unitId}, Method ${method}, Checked ${isChecked}`); // Debug log

                // Auto save single method
                saveSingleMethod(unitId, method, isChecked);

                window.updateProgress();
            });

            // Event delegation untuk textarea rekomendasi - AUTO SAVE dengan debounce
            $(document).on('change', '#rekomendasi', function() {
                saveRecommendation();
            });

            $(document).on('input', '#tindak_lanjut, #catatan', debounce(function() {
                saveRecommendation();
            }, 1000));
        }

        // Load asesi options
        function loadAsesiOptions(asesmenId) {
            $('#id_asesi').prop('disabled', true).html('<option value="">Loading...</option>');
            hideEmptyDataMessage();

            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getAsesi}`, // PERBAIKAN: URL yang benar
                type: 'GET',
                data: {
                    id_asesmen: asesmenId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.asesi && response.asesi.length > 0) {
                        let options = '<option value="">-- Pilih Asesi --</option>';
                        response.asesi.forEach(function(asesi) {
                            const displayName = asesi.nama_lengkap || asesi.nama_asesi || asesi.nama || 'Nama tidak tersedia';
                            const nik = asesi.nik || 'NIK tidak tersedia';
                            options += `<option value="${asesi.id_asesi}" data-id-pengajuan="${asesi.id_pengajuan}">${displayName} (${nik})</option>`;
                        });
                        $('#id_asesi').html(options).prop('disabled', false);
                        hideEmptyDataMessage();
                    } else {
                        $('#id_asesi').html('<option value="">-- Belum Ada Asesi Terdaftar --</option>').prop('disabled', true);
                        showEmptyDataMessage();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading asesi:', error);
                    console.error('XHR status:', xhr.status);
                    console.error('Response text:', xhr.responseText);
                    $('#id_asesi').html('<option value="">-- Error memuat data --</option>').prop('disabled', true);
                    showError('Gagal memuat data asesi: ' + error);
                }
            });
        }

        // Load rekaman data
        function loadRekamanData() {
            showLoading();

            if (!state.selectedAsesmen || !state.selectedAsesi || !state.selectedPengajuan) {
                showError('Data tidak lengkap untuk memuat rekaman');
                hideLoading();
                return;
            }

            // PERBAIKAN: Get skema data dari asesmen selection
            const skemaData = $('#id_asesmen option:selected');
            const id_skema = skemaData.data('id-skema');

            // PERBAIKAN: URL yang benar dengan parameter yang diperlukan
            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.loadRekaman}`,
                type: 'GET',
                data: {
                    id_skema: id_skema,
                    id_asesmen: state.selectedAsesmen,
                    id_asesi: state.selectedAsesi,
                    id_pengajuan: state.selectedPengajuan
                },
                dataType: 'json',
                success: function(response) {
                    hideLoading();

                    if (response.success) {
                        renderRekamanForm(response);
                        showRekamanForm();

                        // Initialize settings save after loading data
                        saveSettings();

                        window.updateProgress();
                    } else {
                        showError(response.message || 'Gagal memuat data rekaman');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    console.error('Error loading rekaman:', error);
                    console.error('XHR status:', xhr.status);
                    console.error('Response text:', xhr.responseText);
                    showError('Gagal memuat data rekaman: ' + error);
                }
            });
        }

        // Render rekaman form - PERBAIKAN: Handle existing data dengan benar
        function renderRekamanForm(data) {
            const container = $('#rekamanAsesmenContainer');
            let html = '';

            state.totalUnits = data.totalUnits || 0;

            console.log('Rendering form with data:', data); // Debug log
            console.log('Existing data:', data.existing_data); // Debug log

            if (data.rekaman_asesmen && data.rekaman_asesmen.length > 0) {
                html = '<div class="table-responsive">';
                html += '<table class="table table-bordered table-hover">';
                html += '<thead class="thead-light">';
                html += '<tr>';
                html += '<th width="30%">Unit Kompetensi</th>';
                html += '<th width="10%" class="text-center">Observasi</th>';
                html += '<th width="10%" class="text-center">Portofolio</th>';
                html += '<th width="10%" class="text-center">Pihak Ketiga</th>';
                html += '<th width="10%" class="text-center">Lisan</th>';
                html += '<th width="10%" class="text-center">Tertulis</th>';
                html += '<th width="10%" class="text-center">Proyek</th>';
                html += '<th width="10%" class="text-center">Lainnya</th>';
                html += '</tr>';
                html += '</thead>';
                html += '<tbody>';

                data.rekaman_asesmen.forEach(function(unit) {
                    const unitId = unit.id_unit;
                    // PERBAIKAN: Handle existing data dengan key yang benar
                    const existingData = data.existing_data && data.existing_data[unitId] ? data.existing_data[unitId] : {};

                    console.log(`Unit ${unitId} existing data:`, existingData); // Debug log

                    html += `<tr data-unit-id="${unitId}">`;
                    html += `<td><strong>${unit.kode_unit}</strong><br><small>${unit.nama_unit}</small></td>`;

                    // Method checkboxes - PERBAIKAN: Periksa nilai dengan benar
                    const methods = ['observasi', 'portofolio', 'pihak_ketiga', 'lisan', 'tertulis', 'proyek', 'lainnya'];
                    methods.forEach(function(method) {
                        // PERBAIKAN: Periksa nilai dengan tepat (1 = checked, 0 atau undefined = unchecked)
                        const isChecked = existingData[method] === 1;
                        const checked = isChecked ? 'checked' : '';

                        console.log(`Unit ${unitId}, Method ${method}: value=${existingData[method]}, checked=${checked}`); // Debug log

                        html += `<td class="text-center">`;
                        html += `<input type="checkbox" name="kompetensi[${unitId}][${method}]" value="1" ${checked} data-unit-id="${unitId}" data-method="${method}">`;
                        html += `</td>`;
                    });

                    html += '</tr>';
                });

                html += '</tbody>';
                html += '</table>';
                html += '</div>';
            } else {
                html = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Tidak ada unit kompetensi ditemukan untuk skema ini.
                </div>
                `;
            }

            container.html(html);

            // PERBAIKAN: Populate rekomendasi section dengan data yang benar
            if (data.existing_recommendation) {
                $('#rekomendasi').val(data.existing_recommendation.rekomendasi || '');
                $('#tindak_lanjut').val(data.existing_recommendation.tindak_lanjut || '');
                $('#catatan').val(data.existing_recommendation.komentar || '');
            }

            updateUnitStatus();
        }

        // AUTO SAVE: Save settings
        async function saveSettings() {
            if (!state.selectedPengajuan) {
                return;
            }

            const data = {
                save_type: 'settings',
                id_pengajuan: state.selectedPengajuan,
                tanggal_asesmen: $('#tanggal_asesmen').val(),
                [state.csrfName]: state.csrfHash
            };

            try {
                const response = await $.ajax({
                    url: `${config.baseUrl}/${config.endpoints.store}`, // PERBAIKAN: URL yang benar
                    type: 'POST',
                    data: data,
                    dataType: 'json'
                });

                if (response.success) {
                    if (response.data && response.data.id_rekaman) {
                        state.id_rekaman = response.data.id_rekaman;
                    }
                    if (response.csrf_hash) state.csrfHash = response.csrf_hash;
                } else {
                    console.error('Save settings failed:', response.message);
                }
            } catch (error) {
                console.error('Error saving settings:', error);
            }
        }

        // AUTO SAVE: Save single method - PERBAIKAN: URL dan data yang benar
        async function saveSingleMethod(unitId, method, value) {
            if (!state.selectedPengajuan) {
                return;
            }

            console.log(`Saving single method: Unit ${unitId}, Method ${method}, Value ${value}`);

            const data = {
                save_type: 'method',
                id_pengajuan: state.selectedPengajuan,
                id_unit: unitId,
                method: method,
                value: value ? 1 : 0,
                [state.csrfName]: state.csrfHash
            };

            try {
                const response = await $.ajax({
                    url: `${config.baseUrl}/${config.endpoints.store}`, // PERBAIKAN: URL yang benar
                    type: 'POST',
                    data: data,
                    dataType: 'json'
                });

                console.log('Save single method response:', response);

                if (response.success) {
                    console.log(`Successfully saved: Unit ${unitId}, Method ${method}, Value ${data.value}`);
                    if (response.csrf_hash) state.csrfHash = response.csrf_hash;
                } else {
                    console.error('Failed to save single method:', response.message);
                }
            } catch (error) {
                console.error('Error saving method:', error);
                console.error('XHR status:', error.status);
                console.error('Response text:', error.responseText);
            }
        }

        // AUTO SAVE: Save recommendation - PERBAIKAN: URL yang benar
        async function saveRecommendation() {
            if (!state.selectedPengajuan) {
                return;
            }

            const data = {
                save_type: 'recommendation',
                id_pengajuan: state.selectedPengajuan,
                rekomendasi: $('#rekomendasi').val(),
                komentar: $('#catatan').val(),
                tindak_lanjut: $('#tindak_lanjut').val(),
                [state.csrfName]: state.csrfHash
            };

            try {
                const response = await $.ajax({
                    url: `${config.baseUrl}/${config.endpoints.store}`, // PERBAIKAN: URL yang benar
                    type: 'POST',
                    data: data,
                    dataType: 'json'
                });

                if (response.csrf_hash) state.csrfHash = response.csrf_hash;
            } catch (error) {
                console.error('Error saving recommendation:', error);
            }
        }

        // Bulk check handler - PERBAIKAN: URL yang benar
        async function handleBulkCheck(checkState) {
            const $btn = $(checkState ? '#checkAllMethods' : '#uncheckAllMethods');
            const originalBtnText = $btn.html();

            $btn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').attr('disabled', true);

            // Ensure settings are saved first
            if (!state.id_rekaman) {
                await saveSettings();
                if (!state.id_rekaman) {
                    showError('Gagal menyimpan pengaturan rekaman');
                    $btn.html(originalBtnText).attr('disabled', false);
                    return;
                }
            }

            const $checkboxes = $('input[type="checkbox"][name^="kompetensi"]');
            $checkboxes.prop('checked', checkState);
            window.updateProgress();

            const batchData = {
                save_type: 'batch',
                id_pengajuan: state.selectedPengajuan,
                items: {}
            };

            $checkboxes.each(function() {
                const name = $(this).attr('name');
                const unitMatch = name.match(/\[(\d+)\]/);
                const methodMatch = name.match(/\[([^\]]+)\]$/);

                if (unitMatch && methodMatch) {
                    const unitId = unitMatch[1];
                    const method = methodMatch[1];

                    if (!batchData.items[unitId]) {
                        batchData.items[unitId] = {};
                    }

                    if (checkState) {
                        batchData.items[unitId][method] = 1;
                    }
                }
            });

            // Add CSRF token
            batchData[state.csrfName] = state.csrfHash;

            try {
                const response = await $.ajax({
                    url: `${config.baseUrl}/${config.endpoints.store}`, // PERBAIKAN: URL yang benar
                    type: 'POST',
                    data: JSON.stringify(batchData),
                    contentType: 'application/json',
                    dataType: 'json'
                });

                if (response.success) {
                    showSuccess(response.message);
                    if (response.csrf_hash) state.csrfHash = response.csrf_hash;
                } else {
                    showError(response.message || 'Gagal menyimpan data batch');
                }
            } catch (error) {
                console.error('Batch save error:', error);
                showError('Terjadi kesalahan saat menyimpan data batch');
            } finally {
                $btn.html(originalBtnText).attr('disabled', false);
            }
        }

        // Save complete rekaman
        function saveCompleteRekaman() {
            const rekomendasi = $('#rekomendasi').val();

            if (!rekomendasi) {
                showError('Rekomendasi wajib diisi sebelum menyelesaikan rekaman.');
                return;
            }

            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin ingin menyelesaikan rekaman asesmen ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Selesaikan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Final save with completion status
                    const data = {
                        save_type: 'recommendation',
                        id_pengajuan: state.selectedPengajuan,
                        rekomendasi: $('#rekomendasi').val(),
                        komentar: $('#catatan').val(),
                        tindak_lanjut: $('#tindak_lanjut').val(),
                        status: 'completed'
                    };

                    $.ajax({
                        url: `${config.baseUrl}/${config.endpoints.store}`,
                        type: 'POST',
                        data: data,
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Rekaman asesmen telah diselesaikan',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    // Redirect or refresh
                                    window.location.href = config.baseUrl + '/asesor/rekaman-asesmen';
                                });
                            } else {
                                showError(response.message);
                            }
                        },
                        error: function() {
                            showError('Terjadi kesalahan saat menyelesaikan rekaman');
                        }
                    });
                }
            });
        }

        // Update unit status
        function updateUnitStatus() {
            state.assessedUnits = 0;

            $('[data-unit-id]').each(function() {
                const hasChecked = $(this).find('input[type="checkbox"]:checked').length > 0;
                if (hasChecked) {
                    state.assessedUnits++;
                }
            });
        }

        // Reset asesi selection
        function resetAsesiSelection() {
            $('#id_asesi').html('<option value="">-- Pilih Asesmen Terlebih Dahulu --</option>').prop('disabled', true);
            state.selectedAsesi = null;
            state.selectedPengajuan = null;
            state.id_rekaman = null;
            hideRekamanForm();
            hideEmptyDataMessage();
            showInitialInstructions();
        }

        // UI Helper functions
        function showLoading() {
            $('#loadingState').show();
            $('#formRekamanAsesmen').hide();
            $('#initialInstructions').hide();
            $('#emptyDataMessage').hide();
        }

        function hideLoading() {
            $('#loadingState').hide();
        }

        function showRekamanForm() {
            $('#formRekamanAsesmen').show();
            $('#initialInstructions').hide();
            $('#emptyDataMessage').hide();
        }

        function hideRekamanForm() {
            $('#formRekamanAsesmen').hide();
        }

        function showInitialInstructions() {
            $('#initialInstructions').show();
            $('#emptyDataMessage').hide();
        }

        function showEmptyDataMessage() {
            $('#emptyDataMessage').show();
            $('#initialInstructions').hide();
        }

        function hideEmptyDataMessage() {
            $('#emptyDataMessage').hide();
        }

        function initializePageState() {
            $('#loadingState').hide();
            $('#formRekamanAsesmen').hide();
            $('#emptyDataMessage').hide();
            $('#initialInstructions').show();

            if (!$('#id_asesmen').val()) {
                $('#id_asesi').html('<option value="">-- Pilih Asesmen Terlebih Dahulu --</option>').prop('disabled', true);
            }
        }

        function showSuccess(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: message,
                    timer: 2000,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
            } else {
                console.log('Success: ' + message);
            }
        }

        function showError(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: message
                });
            } else {
                alert('Error: ' + message);
            }
        }

        // Debounce function for auto-save
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

        // Initialize the module
        init();
    });
</script>