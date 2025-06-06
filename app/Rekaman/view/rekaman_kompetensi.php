<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-clipboard-check text-primary mr-2"></i>CEKLIS KOMPETENSI ASESMEN</h4>
                <div>
                    <button class="btn btn-light btn-sm" type="button" data-toggle="collapse" data-target="#collapseInfo">
                        <i class="fas fa-info-circle"></i> Info
                    </button>
                </div>
            </div>

            <div class="collapse" id="collapseInfo">
                <div class="card-body bg-light border-top border-bottom">
                    <div class="alert alert-info mb-0">
                        <h6 class="font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Petunjuk Penggunaan</h6>
                        <ul class="mb-0 pl-3">
                            <li>Pilih skema dan asesi untuk memulai penilaian kompetensi.</li>
                            <li>Tandai metode asesmen yang digunakan untuk setiap unit kompetensi.</li>
                            <li>Pengisian akan disimpan secara otomatis saat Anda melakukan perubahan.</li>
                            <li>Pastikan untuk memberikan tanda tangan setelah menyelesaikan penilaian.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Assessment Selection -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-bookmark text-primary mr-1"></i>Nama Skema</label>
                            <select name="id_skema" id="id_skema" class="form-control select2" required>
                                <option value="">-- Pilih Skema --</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-hashtag text-primary mr-1"></i>Kode Skema</label>
                            <input type="text" class="form-control bg-light" id="kode_skema" value="" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-user text-success mr-1"></i>Nama Asesi</label>
                            <select name="id_asesi" id="id_asesi" class="form-control select2" required disabled>
                                <option value="">-- Pilih Asesi --</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-calendar text-info mr-1"></i>Tanggal Asesmen</label>
                            <input type="date" class="form-control" id="tanggal_asesmen" name="tanggal_asesmen" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <!-- Loading indicator -->
                <div id="loadingKompetensi" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat data kompetensi...</p>
                </div>

                <!-- Assessment Form -->
                <div id="formKompetensi" style="display: none;">
                    <form id="kompetensiForm">
                        <input type="hidden" id="id_rekaman" name="id_rekaman">

                        <!-- Progress indicator -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><i class="fas fa-chart-pie text-info mr-2"></i>Progress Penilaian</h6>
                                            <span class="badge badge-info" id="progressText">0 dari 0 Unit</span>
                                        </div>
                                        <div class="progress mt-2" style="height: 8px;">
                                            <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assessment methods legend -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="font-weight-bold mb-3"><i class="fas fa-info-circle text-primary mr-2"></i>Metode Asesmen</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="fas fa-eye text-success mr-2"></i><strong>Observasi:</strong> Pengamatan langsung</li>
                                                    <li><i class="fas fa-folder text-warning mr-2"></i><strong>Portofolio:</strong> Kumpulan bukti kerja</li>
                                                    <li><i class="fas fa-users text-info mr-2"></i><strong>Pihak Ketiga:</strong> Testimoni/referensi</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="fas fa-comments text-primary mr-2"></i><strong>Lisan:</strong> Wawancara/tanya jawab</li>
                                                    <li><i class="fas fa-pen text-secondary mr-2"></i><strong>Tertulis:</strong> Tes tertulis</li>
                                                    <li><i class="fas fa-project-diagram text-danger mr-2"></i><strong>Proyek:</strong> Hasil karya/project</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Units container -->
                        <div id="unitsContainer">
                            <!-- Units will be loaded here dynamically -->
                        </div>

                        <!-- Signatures section -->
                        <div class="row mt-5" id="signatureSection" style="display: none;">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-signature text-primary mr-2"></i>Tanda Tangan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="ttd_asesor" class="font-weight-bold">
                                                        <i class="fas fa-signature text-primary mr-1"></i>Tanda Tangan Asesor
                                                    </label>
                                                    <div class="signature-container border rounded p-3 text-center bg-light">
                                                        <canvas id="ttd_asesor_canvas" width="350" height="150" class="border bg-white"></canvas>
                                                        <br>
                                                        <button type="button" class="btn btn-sm btn-warning mt-2" onclick="clearSignature('asesor')">
                                                            <i class="fas fa-eraser"></i> Hapus
                                                        </button>
                                                    </div>
                                                    <input type="hidden" name="ttd_asesor" id="ttd_asesor">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="ttd_asesi" class="font-weight-bold">
                                                        <i class="fas fa-signature text-success mr-1"></i>Tanda Tangan Asesi
                                                    </label>
                                                    <div class="signature-container border rounded p-3 text-center bg-light">
                                                        <canvas id="ttd_asesi_canvas" width="350" height="150" class="border bg-white"></canvas>
                                                        <br>
                                                        <button type="button" class="btn btn-sm btn-warning mt-2" onclick="clearSignature('asesi')">
                                                            <i class="fas fa-eraser"></i> Hapus
                                                        </button>
                                                    </div>
                                                    <input type="hidden" name="ttd_asesi" id="ttd_asesi">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <button type="button" class="btn btn-secondary" id="btnReset">
                                            <i class="fas fa-undo mr-1"></i>Reset Form
                                        </button>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-info mr-2" id="btnAutoSave" disabled>
                                            <i class="fas fa-save mr-1"></i>Auto Save: OFF
                                        </button>
                                        <button type="submit" class="btn btn-success" id="btnSave">
                                            <i class="fas fa-check mr-1"></i>Simpan Penilaian
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('js') ?>
<!-- Signature Pad -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
    $(document).ready(function() {
        'use strict';

        // Configuration
        const config = {
            baseUrl: '<?= base_url() ?>',
            endpoints: {
                getAsesi: 'asesor/rekaman-kompetensi/getAsesi',
                loadKompetensi: 'asesor/rekaman-kompetensi/loadKompetensi',
                save: 'asesor/rekaman-kompetensi/save',
                saveUnit: 'asesor/rekaman-kompetensi/saveUnit',
                getById: 'asesor/rekaman-kompetensi/getById',
                delete: 'asesor/rekaman-kompetensi/delete'
            }
        };

        // State management
        const state = {
            selectedSkema: null,
            selectedAsesi: null,
            kompetensiData: null,
            autoSave: false,
            totalUnits: 0,
            assessedUnits: 0,
            existingData: {}
        };

        // Signature pad instances
        let asesorSignaturePad, asesiSignaturePad;

        /**
         * Initialize the module
         */
        function init() {
            initSignaturePads();
            initFormHandling();
            bindEvents();
            loadSkemaOptions();
        }

        /**
         * Initialize signature pads
         */
        function initSignaturePads() {
            const asesorCanvas = document.getElementById('ttd_asesor_canvas');
            const asesiCanvas = document.getElementById('ttd_asesi_canvas');

            if (asesorCanvas && asesiCanvas) {
                asesorSignaturePad = new SignaturePad(asesorCanvas);
                asesiSignaturePad = new SignaturePad(asesiCanvas);
            }
        }

        /**
         * Initialize form handling
         */
        function initFormHandling() {
            // Handle form submission
            $('#kompetensiForm').on('submit', function(e) {
                e.preventDefault();
                saveAssessment();
            });

            // Handle skema selection
            $('#id_skema').on('change', function() {
                const skemaId = $(this).val();
                const skemaText = $(this).find('option:selected').text();
                const kodeSkema = $(this).find('option:selected').data('kode');

                state.selectedSkema = skemaId;
                $('#kode_skema').val(kodeSkema || '');

                if (skemaId) {
                    loadAsesiOptions(skemaId);
                } else {
                    resetAsesiSelection();
                }
            });

            // Handle asesi selection
            $('#id_asesi').on('change', function() {
                const asesiId = $(this).val();
                state.selectedAsesi = asesiId;

                if (asesiId && state.selectedSkema) {
                    loadKompetensiData();
                } else {
                    hideKompetensiForm();
                }
            });

            // Auto save toggle
            $('#btnAutoSave').on('click', function() {
                state.autoSave = !state.autoSave;
                updateAutoSaveButton();
            });

            // Reset button
            $('#btnReset').on('click', function() {
                resetForm();
            });
        }

        /**
         * Bind event handlers
         */
        function bindEvents() {
            // Handle checkbox changes for auto-save
            $(document).on('change', 'input[type="checkbox"]', function() {
                if (state.autoSave) {
                    saveCurrentUnit($(this));
                }
                updateProgress();
            });
        }

        /**
         * Load skema options
         */
        function loadSkemaOptions() {
            $.ajax({
                url: `${config.baseUrl}/api/get-skema-asesor`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let options = '<option value="">-- Pilih Skema --</option>';
                        response.data.forEach(function(item) {
                            options += `<option value="${item.id_skema}" data-kode="${item.kode_skema}">${item.nama_skema}</option>`;
                        });
                        $('#id_skema').html(options);
                    }
                },
                error: function() {
                    showError('Gagal memuat data skema');
                }
            });
        }

        /**
         * Load asesi options based on selected skema
         */
        function loadAsesiOptions(skemaId) {
            $('#id_asesi').prop('disabled', true).html('<option value="">Loading...</option>');

            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getAsesi}`,
                type: 'GET',
                data: {
                    id_skema: skemaId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let options = '<option value="">-- Pilih Asesi --</option>';
                        response.data.forEach(function(item) {
                            options += `<option value="${item.id_asesi}">${item.nama_lengkap}</option>`;
                        });
                        $('#id_asesi').html(options).prop('disabled', false);
                    } else {
                        showError(response.message);
                        resetAsesiSelection();
                    }
                },
                error: function() {
                    showError('Gagal memuat data asesi');
                    resetAsesiSelection();
                }
            });
        }

        /**
         * Reset asesi selection
         */
        function resetAsesiSelection() {
            $('#id_asesi').html('<option value="">-- Pilih Asesi --</option>').prop('disabled', true);
            hideKompetensiForm();
        }

        /**
         * Load kompetensi data
         */
        function loadKompetensiData() {
            showLoading();

            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.loadKompetensi}`,
                type: 'GET',
                data: {
                    id_skema: state.selectedSkema,
                    id_asesi: state.selectedAsesi
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        state.kompetensiData = response.data;
                        state.existingData = response.existing_data || {};
                        renderKompetensiForm();
                        updateProgress();
                        showKompetensiForm();
                    } else {
                        showError(response.message);
                        hideKompetensiForm();
                    }
                },
                error: function() {
                    showError('Gagal memuat data kompetensi');
                    hideKompetensiForm();
                },
                complete: function() {
                    hideLoading();
                }
            });
        }

        /**
         * Render kompetensi form
         */
        function renderKompetensiForm() {
            const container = $('#unitsContainer');
            let html = '';

            if (state.kompetensiData && state.kompetensiData.length > 0) {
                state.totalUnits = state.kompetensiData.length;

                state.kompetensiData.forEach(function(unit, index) {
                    const unitData = state.existingData[unit.id_unit] || {};

                    html += `
                    <div class="card mb-4" data-unit-id="${unit.id_unit}">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-cube text-primary mr-2"></i>
                                    ${unit.kode_unit} - ${unit.nama_unit}
                                </h5>
                                <span class="badge badge-secondary unit-status" id="status-${unit.id_unit}">
                                    Belum Dinilai
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="font-weight-bold mb-3">
                                        <i class="fas fa-clipboard-list text-info mr-2"></i>Metode Asesmen yang Digunakan:
                                    </h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input assessment-method" type="checkbox" 
                                               name="observasi[${unit.id_unit}]" value="1" 
                                               id="observasi_${unit.id_unit}"
                                               ${unitData.observasi ? 'checked' : ''}>
                                        <label class="form-check-label" for="observasi_${unit.id_unit}">
                                            <i class="fas fa-eye text-success mr-2"></i>Observasi (Pengamatan langsung)
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input assessment-method" type="checkbox" 
                                               name="portofolio[${unit.id_unit}]" value="1" 
                                               id="portofolio_${unit.id_unit}"
                                               ${unitData.portofolio ? 'checked' : ''}>
                                        <label class="form-check-label" for="portofolio_${unit.id_unit}">
                                            <i class="fas fa-folder text-warning mr-2"></i>Portofolio (Kumpulan bukti kerja)
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input assessment-method" type="checkbox" 
                                               name="pihak_ketiga[${unit.id_unit}]" value="1" 
                                               id="pihak_ketiga_${unit.id_unit}"
                                               ${unitData.pihak_ketiga ? 'checked' : ''}>
                                        <label class="form-check-label" for="pihak_ketiga_${unit.id_unit}">
                                            <i class="fas fa-users text-info mr-2"></i>Pihak Ketiga (Testimoni/referensi)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input assessment-method" type="checkbox" 
                                               name="lisan[${unit.id_unit}]" value="1" 
                                               id="lisan_${unit.id_unit}"
                                               ${unitData.lisan ? 'checked' : ''}>
                                        <label class="form-check-label" for="lisan_${unit.id_unit}">
                                            <i class="fas fa-comments text-primary mr-2"></i>Lisan (Wawancara/tanya jawab)
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input assessment-method" type="checkbox" 
                                               name="tertulis[${unit.id_unit}]" value="1" 
                                               id="tertulis_${unit.id_unit}"
                                               ${unitData.tertulis ? 'checked' : ''}>
                                        <label class="form-check-label" for="tertulis_${unit.id_unit}">
                                            <i class="fas fa-pen text-secondary mr-2"></i>Tertulis (Tes tertulis)
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input assessment-method" type="checkbox" 
                                               name="proyek[${unit.id_unit}]" value="1" 
                                               id="proyek_${unit.id_unit}"
                                               ${unitData.proyek ? 'checked' : ''}>
                                        <label class="form-check-label" for="proyek_${unit.id_unit}">
                                            <i class="fas fa-project-diagram text-danger mr-2"></i>Proyek (Hasil karya/project)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input assessment-method" type="checkbox" 
                                               name="lainnya[${unit.id_unit}]" value="1" 
                                               id="lainnya_${unit.id_unit}"
                                               ${unitData.lainnya ? 'checked' : ''}>
                                        <label class="form-check-label" for="lainnya_${unit.id_unit}">
                                            <i class="fas fa-ellipsis-h text-dark mr-2"></i>Lainnya
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                });
            } else {
                html = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Tidak ada unit kompetensi yang ditemukan untuk skema ini.
                </div>
            `;
            }

            container.html(html);

            // Update unit status after rendering
            updateUnitStatus();
        }

        /**
         * Update unit status indicators
         */
        function updateUnitStatus() {
            state.assessedUnits = 0;

            $('[data-unit-id]').each(function() {
                const unitId = $(this).data('unit-id');
                const checkedCount = $(this).find('input[type="checkbox"]:checked').length;
                const statusElement = $(this).find(`#status-${unitId}`);

                if (checkedCount > 0) {
                    statusElement.removeClass('badge-secondary').addClass('badge-success').text('Sudah Dinilai');
                    state.assessedUnits++;
                } else {
                    statusElement.removeClass('badge-success').addClass('badge-secondary').text('Belum Dinilai');
                }
            });
        }

        /**
         * Update progress
         */
        function updateProgress() {
            updateUnitStatus();

            const percentage = state.totalUnits > 0 ? (state.assessedUnits / state.totalUnits) * 100 : 0;

            $('#progressText').text(`${state.assessedUnits} dari ${state.totalUnits} Unit`);
            $('#progressBar').css('width', percentage + '%');

            // Show/hide signature section
            if (state.assessedUnits > 0) {
                $('#signatureSection').show();
            } else {
                $('#signatureSection').hide();
            }
        }

        /**
         * Save current unit (for auto-save)
         */
        function saveCurrentUnit(changedElement) {
            const unitCard = changedElement.closest('[data-unit-id]');
            const unitId = unitCard.data('unit-id');

            const unitData = {
                id_unit: unitId,
                id_skema: state.selectedSkema,
                id_asesi: state.selectedAsesi,
                tanggal_asesmen: $('#tanggal_asesmen').val(),
                observasi: unitCard.find(`input[name="observasi[${unitId}]"]`).is(':checked') ? 1 : 0,
                portofolio: unitCard.find(`input[name="portofolio[${unitId}]"]`).is(':checked') ? 1 : 0,
                pihak_ketiga: unitCard.find(`input[name="pihak_ketiga[${unitId}]"]`).is(':checked') ? 1 : 0,
                lisan: unitCard.find(`input[name="lisan[${unitId}]"]`).is(':checked') ? 1 : 0,
                tertulis: unitCard.find(`input[name="tertulis[${unitId}]"]`).is(':checked') ? 1 : 0,
                proyek: unitCard.find(`input[name="proyek[${unitId}]"]`).is(':checked') ? 1 : 0,
                lainnya: unitCard.find(`input[name="lainnya[${unitId}]"]`).is(':checked') ? 1 : 0
            };

            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.saveUnit}`,
                type: 'POST',
                data: unitData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show brief success indication
                        showBriefSuccess();
                    }
                },
                error: function() {
                    // Silently handle auto-save errors
                    console.warn('Auto-save failed for unit', unitId);
                }
            });
        }

        /**
         * Save complete assessment
         */
        function saveAssessment() {
            // Collect all form data
            const formData = {
                id_skema: state.selectedSkema,
                id_asesi: state.selectedAsesi,
                tanggal_asesmen: $('#tanggal_asesmen').val(),
                ttd_asesor: asesorSignaturePad.isEmpty() ? '' : asesorSignaturePad.toDataURL(),
                ttd_asesi: asesiSignaturePad.isEmpty() ? '' : asesiSignaturePad.toDataURL(),
                units: []
            };

            // Collect unit data
            $('[data-unit-id]').each(function() {
                const unitId = $(this).data('unit-id');
                const unitData = {
                    id_unit: unitId,
                    observasi: $(this).find(`input[name="observasi[${unitId}]"]`).is(':checked') ? 1 : 0,
                    portofolio: $(this).find(`input[name="portofolio[${unitId}]"]`).is(':checked') ? 1 : 0,
                    pihak_ketiga: $(this).find(`input[name="pihak_ketiga[${unitId}]"]`).is(':checked') ? 1 : 0,
                    lisan: $(this).find(`input[name="lisan[${unitId}]"]`).is(':checked') ? 1 : 0,
                    tertulis: $(this).find(`input[name="tertulis[${unitId}]"]`).is(':checked') ? 1 : 0,
                    proyek: $(this).find(`input[name="proyek[${unitId}]"]`).is(':checked') ? 1 : 0,
                    lainnya: $(this).find(`input[name="lainnya[${unitId}]"]`).is(':checked') ? 1 : 0
                };
                formData.units.push(unitData);
            });

            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.save}`,
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                dataType: 'json',
                beforeSend: function() {
                    $('#btnSave').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
                },
                success: function(response) {
                    if (response.success) {
                        showSuccess(response.message);
                        // Optionally redirect or refresh
                    } else {
                        showError(response.message);
                    }
                },
                error: function() {
                    showError('Terjadi kesalahan saat menyimpan data');
                },
                complete: function() {
                    $('#btnSave').prop('disabled', false).html('<i class="fas fa-check mr-1"></i>Simpan Penilaian');
                }
            });
        }

        /**
         * Update auto save button
         */
        function updateAutoSaveButton() {
            const button = $('#btnAutoSave');
            if (state.autoSave) {
                button.removeClass('btn-info').addClass('btn-success')
                    .html('<i class="fas fa-save mr-1"></i>Auto Save: ON');
            } else {
                button.removeClass('btn-success').addClass('btn-info')
                    .html('<i class="fas fa-save mr-1"></i>Auto Save: OFF');
            }
        }

        /**
         * Show/hide loading
         */
        function showLoading() {
            $('#loadingKompetensi').show();
            $('#formKompetensi').hide();
        }

        function hideLoading() {
            $('#loadingKompetensi').hide();
        }

        /**
         * Show/hide kompetensi form
         */
        function showKompetensiForm() {
            $('#formKompetensi').show();
        }

        function hideKompetensiForm() {
            $('#formKompetensi').hide();
        }

        /**
         * Clear signature
         */
        window.clearSignature = function(type) {
            if (type === 'asesor') {
                asesorSignaturePad.clear();
            } else if (type === 'asesi') {
                asesiSignaturePad.clear();
            }
        };

        /**
         * Reset form
         */
        function resetForm() {
            $('#id_skema').val('').trigger('change');
            $('#id_asesi').val('');
            $('#kode_skema').val('');
            $('#tanggal_asesmen').val('<?= date('Y-m-d') ?>');

            state.selectedSkema = null;
            state.selectedAsesi = null;
            state.kompetensiData = null;
            state.existingData = {};

            if (asesorSignaturePad) asesorSignaturePad.clear();
            if (asesiSignaturePad) asesiSignaturePad.clear();

            hideKompetensiForm();
            resetAsesiSelection();
        }

        /**
         * Show success message
         */
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        }

        /**
         * Show brief success (for auto-save)
         */
        function showBriefSuccess() {
            // Show a brief toast notification
            const toast = $(`
            <div class="alert alert-success alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 200px;" role="alert">
                <i class="fas fa-check mr-2"></i>Tersimpan otomatis
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `);

            $('body').append(toast);

            setTimeout(function() {
                toast.alert('close');
            }, 2000);
        }

        /**
         * Show error message
         */
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: message
            });
        }

        // Initialize the module
        init();
    });
</script>

<?= $this->endSection(); ?>