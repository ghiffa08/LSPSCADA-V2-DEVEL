<script>
    $(document).ready(function() {
        'use strict';

        // Configuration
        const config = {
            baseUrl: '<?= base_url() ?>',
            endpoints: {
                getAsesi: 'asesor/rekaman-asesmen/getAsesiByAsesmen',
                loadRekaman: 'asesor/rekaman-asesmen/loadRekamanAsesmen',
                store: 'asesor/rekaman-asesmen/store'
            }
        };

        // CSRF Token
        let csrf_token = '<?= csrf_token() ?>';
        let csrf_hash = '<?= csrf_hash() ?>';

        // State management
        const state = {
            selectedAsesmen: null,
            selectedAsesi: null,
            selectedPengajuan: null,
            totalUnits: 0,
            assessedUnits: 0
        };

        // Initialize
        function init() {
            bindEvents();
        }

        // Bind event handlers
        function bindEvents() {
            // Asesmen selection
            $('#id_asesmen').on('change', function() {
                const asesmenId = $(this).val();
                state.selectedAsesmen = asesmenId;

                if (asesmenId) {
                    loadAsesiOptions(asesmenId);
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

                console.log('Asesi changed - asesiId:', asesiId, 'pengajuanId:', pengajuanId);

                if (asesiId && pengajuanId && state.selectedAsesmen) {
                    loadRekamanData();
                } else {
                    hideRekamanForm();
                    showInitialInstructions();
                }
            });

            // Form submission
            $('#formRekamanAsesmen').on('submit', function(e) {
                e.preventDefault();
                saveRekamanAsesmen();
            });

            // Bulk actions
            $('#checkAllMethods').on('click', function() {
                $('input[type="checkbox"]').prop('checked', true).trigger('change');
                updateProgress();
            });

            $('#uncheckAllMethods').on('click', function() {
                $('input[type="checkbox"]').prop('checked', false).trigger('change');
                updateProgress();
            });
        }

        // PERBAIKAN: Load asesi options
        function loadAsesiOptions(asesmenId) {
            $('#id_asesi').prop('disabled', true).html('<option value="">Loading...</option>');
            hideEmptyDataMessage();

            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getAsesi}`,
                type: 'GET',
                data: {
                    id_asesmen: asesmenId
                },
                dataType: 'json',
                success: function(response) {
                    console.log('getAsesiByAsesmen:', response);

                    // PERBAIKAN: Check response structure yang benar
                    if (response.success && response.asesi && Array.isArray(response.asesi)) {
                        let options = '<option value="">-- Pilih Asesi --</option>';

                        if (response.asesi.length > 0) {
                            response.asesi.forEach(function(asesi) {
                                // PERBAIKAN: Pastikan semua field tersedia
                                const pengajuanId = asesi.id_pengajuan;
                                const namaAsesi = asesi.nama_asesi || asesi.nama_lengkap;
                                const username = asesi.username || asesi.nik;
                                const displayText = `${namaAsesi} (${username})`;

                                // VALIDASI: Pastikan pengajuanId ada
                                if (pengajuanId) {
                                    options += `<option value="${asesi.id_asesi}" data-id-pengajuan="${pengajuanId}">${displayText}</option>`;
                                } else {
                                    console.warn('Missing pengajuan ID for asesi:', asesi);
                                }
                            });

                            $('#id_asesi').html(options).prop('disabled', false);
                            hideEmptyDataMessage();
                            showInitialInstructions();
                        } else {
                            $('#id_asesi').html('<option value="">-- Tidak ada asesi --</option>');
                            showEmptyDataMessage();
                        }
                    } else {
                        console.error('Invalid response structure:', response);
                        $('#id_asesi').html('<option value="">-- Error loading data --</option>');
                        showError('Gagal memuat data asesi: ' + (response.message || 'Format response tidak valid'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading asesi:', error);
                    $('#id_asesi').html('<option value="">-- Error loading --</option>');
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

            console.log('Loading rekaman data with:', {
                asesmen: state.selectedAsesmen,
                asesi: state.selectedAsesi,
                pengajuan: state.selectedPengajuan
            });

            // Set hidden form fields
            $('#form_id_asesmen').val(state.selectedAsesmen);
            $('#form_id_asesi').val(state.selectedAsesi);
            $('#form_id_pengajuan').val(state.selectedPengajuan);
            $('#form_tanggal_asesmen').val($('#tanggal_asesmen').val());

            // Get skema data from asesmen selection
            const skemaData = $('#id_asesmen option:selected');
            $('#form_id_skema').val(skemaData.data('id-skema'));

            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.loadRekaman}`,
                type: 'GET',
                data: {
                    id_skema: skemaData.data('id-skema'),
                    id_asesmen: state.selectedAsesmen,
                    id_asesi: state.selectedAsesi
                },
                dataType: 'json',
                success: function(response) {
                    hideLoading();

                    if (response.success) {
                        renderRekamanForm(response);
                        showRekamanForm();
                        updateProgress();
                    } else {
                        showError(response.message || 'Gagal memuat data rekaman');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    console.error('Error loading rekaman:', error);
                    showError('Gagal memuat data rekaman: ' + error);
                }
            });
        }

        // Render rekaman form
        function renderRekamanForm(data) {
            const container = $('#rekamanAsesmenContainer');
            let html = '';

            state.totalUnits = data.totalUnits || 0;

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
                    const existingData = data.existing_data && data.existing_data[unitId] ? data.existing_data[unitId] : {};

                    html += `<tr data-unit-id="${unitId}">`;
                    html += `<td><strong>${unit.kode_unit}</strong><br><small>${unit.nama_unit}</small></td>`;

                    // Method checkboxes
                    const methods = ['observasi', 'portofolio', 'pihak_ketiga', 'lisan', 'tertulis', 'proyek', 'lainnya'];
                    methods.forEach(function(method) {
                        const checked = existingData[method] ? 'checked' : '';
                        html += `<td class="text-center">`;
                        html += `<input type="checkbox" name="kompetensi[${unitId}][${method}]" value="1" ${checked} onchange="updateProgress()">`;
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

            // PERBAIKAN: Populate rekomendasi section yang sudah ada di template HTML
            // Jangan buat baru, tapi isi yang sudah ada
            if (data.existing_recommendation) {
                $('#rekomendasi').val(data.existing_recommendation.rekomendasi || '');
                $('#tindak_lanjut').val(data.existing_recommendation.tindak_lanjut || '');
                $('#catatan').val(data.existing_recommendation.catatan || '');
            }

            updateUnitStatus();
        }

        // Save complete rekaman
        function saveRekamanAsesmen() {
            const idPengajuan = $('#form_id_pengajuan').val();
            const rekomendasi = $('#rekomendasi').val();

            console.log('Form validation - id_pengajuan:', idPengajuan, 'rekomendasi:', rekomendasi);

            if (!idPengajuan) {
                showError('ID Pengajuan tidak valid. Silakan pilih ulang asesi.');
                return;
            }

            if (!rekomendasi) {
                showError('Rekomendasi wajib diisi.');
                return;
            }

            const formData = new FormData($('#formRekamanAsesmen')[0]);
            formData.set('id_pengajuan', idPengajuan);

            // Debug: Log form data
            console.log('Form submission data:');
            for (let [key, value] of formData.entries()) {
                console.log(key, ':', value);
            }

            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.store}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showSuccess(response.message || 'Rekaman asesmen berhasil disimpan');
                        // Optionally reload data or redirect
                    } else {
                        showError(response.message || 'Gagal menyimpan rekaman');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error saving rekaman:', error);
                    showError('Terjadi kesalahan saat menyimpan: ' + error);
                }
            });
        }

        // Update progress
        function updateProgress() {
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
            hideRekamanForm();
            hideEmptyDataMessage(); // TAMBAH: Hide empty message
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

        function showSuccess(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: message,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                alert('Berhasil: ' + message);
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

        // Initialize the module
        init();
    });
</script>