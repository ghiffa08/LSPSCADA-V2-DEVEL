<script>
    $(document).ready(function() {
        'use strict';

        // --- KONFIGURASI DAN STATE ---
        const config = {
            baseUrl: '<?= rtrim(base_url(), '/') ?>',
            endpoints: {
                getApl1: 'asesor/rekaman-asesmen/getAsesiByAsesmen',
                loadRekaman: 'asesor/rekaman-asesmen/loadRekamanAsesmen',
                store: 'asesor/rekaman-asesmen/store'
            }
        };

        const state = {
            id_asesmen: null,
            id_apl1: null,
            id_skema: null,
            id_rekaman: null,
            csrfHash: '<?= csrf_hash() ?>',
            csrfName: '<?= csrf_token() ?>'
        };

        // --- INISIALISASI & EVENT BINDING ---
        function init() {
            $('.select2').select2({
                width: '100%',
                placeholder: "-- Silakan Pilih --"
            });
            bindEvents();
            initializePageState();
        }

        function bindEvents() {
            $('#id_asesmen').on('change', handleAsesmenChange);
            $('#id_apl1').on('change', handleApl1Change);
            $('#formRekamanAsesmen').on('submit', handleFormSubmit);
            $('#checkAllMethod').on('click', () => handleBulkMethodCheck(true));
            $('#clearAllMethod').on('click', () => handleBulkMethodCheck(false));
            $(document).on('change', '.method-checkbox', debounce(handleMethodChange, 500));
            $(document).on('input', 'input[name="rekomendasi"], textarea', debounce(handleFinalDataChange, 750));
        }

        // --- LOGIKA UTAMA: PEMILIHAN & PEMUATAN DATA ---

        function handleAsesmenChange() {
            const selectedOption = $(this).find('option:selected');
            state.id_asesmen = $(this).val();
            state.id_skema = selectedOption.data('id-skema');
            $('#info-skema-text').text(`${selectedOption.data('nama-skema') || ''} (${selectedOption.data('kode-skema') || ''})`);
            
            if (state.id_asesmen) {
                loadApl1Options(state.id_asesmen);
            } else {
                initializePageState();
            }
        }

        function handleApl1Change() {
            state.id_apl1 = $(this).val();
            if (state.id_apl1) {
                const selectedOption = $(this).find('option:selected');
                updateApl1InfoDisplay(selectedOption.data());
                loadRekamanData();
            } else {
                initializePageState();
            }
        }

     function loadApl1Options(asesmenId) {
            const $apl1Select = $('#id_apl1');
            $apl1Select.prop('disabled', true).html('<option value="">Memuat...</option>').trigger('change.select2');

            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getApl1}`,
                type: 'GET',
                data: { id_asesmen: asesmenId },
                dataType: 'json',
                success: function(response) {
                    // PERBAIKAN DI SINI: Menggunakan response.data, bukan response.asesi
                    if (response.success && response.data && response.data.length > 0) {
                        let options = '<option value="">-- Pilih Asesi --</option>';
                        // DAN DI SINI:
                        response.data.forEach(function(apl1) {
                            options += `<option value="${apl1.id_apl1}" 
                                data-nama="${apl1.nama_asesi || ''}" data-nik="${apl1.nik || ''}"
                                data-email="${apl1.email || ''}" data-validation="${apl1.status_pengajuan || ''}">
                                ${apl1.nama_asesi || 'Nama Kosong'} (${apl1.nik || 'NIK Kosong'})
                            </option>`;
                        });
                        $apl1Select.html(options).prop('disabled', false);
                    } else {
                        $apl1Select.html('<option value="">-- Belum Ada Asesi --</option>');
                        showEmptyDataMessage();
                    }
                    $apl1Select.trigger('change.select2');
                },
                error: function() {
                    showError('Gagal Memuat Asesi');
                    $apl1Select.html('<option value="">-- Gagal Memuat --</option>').trigger('change.select2');
                }
            });
        }
        
    function loadRekamanData() {
            showLoading();
            $.get(`${config.baseUrl}/${config.endpoints.loadRekaman}`, { id_apl1: state.id_apl1 })
                .done(response => {
                    if (response.success) {
                        state.id_rekaman = response.data.rekaman ? response.data.rekaman.id : null;
                        $('#form_id_rekaman').val(state.id_rekaman);
                        
                        renderRekamanStruktur(response.data);

                        // --- PERBAIKAN DI SINI ---
                        // Mengisi nilai untuk form rekomendasi, tindak lanjut, dan komentar
                        if (response.data.rekaman_data) {
                            const rekaman = response.data.rekaman_data;
                            // Set radio button Rekomendasi
                            $(`input[name="rekomendasi"][value="${rekaman.rekomendasi}"]`).prop('checked', true);
                            // Set textarea
                            $('textarea[name="tindak_lanjut"]').val(rekaman.tindak_lanjut || '');
                            $('textarea[name="komentar"]').val(rekaman.komentar || '');
                        }
                        
                        showForm();
                    } else {
                        showError(response.message);
                        initializePageState();
                    }
                })
                .fail(() => {
                    showError('Gagal memuat struktur rekaman');
                    initializePageState();
                });
        }
        
        // --- LOGIKA SIMPAN (SINGLE, BULK, FINAL) ---
        
   /**
         * SINGLE-CHECK AUTO-SAVE (Diperbaiki)
         * Mengirim payload dengan key 'method_key' dan 'method_value'
         * agar sesuai dengan yang diharapkan Controller.
         */
        async function handleMethodChange() {
            const checkbox = $(this);
            const payload = {
                save_type: 'auto_save_unit',
                id_apl1: state.id_apl1,
                id_unit: checkbox.data('unit-id'),
                // Perbaikan utama ada di 2 baris ini:
                method_key: checkbox.data('method-key'), 
                method_value: checkbox.is(':checked') ? 1 : 0
            };
            
            const result = await sendSaveRequest(payload);

            // Setelah auto-save pertama berhasil, simpan id_rekaman yang baru
            if (result.success && result.data && result.data.id_rekaman) {
                state.id_rekaman = result.data.id_rekaman;
            }
        }

        async function handleBulkMethodCheck(checkState) {
            if (!state.id_apl1) {
                return showError('Pilih asesi terlebih dahulu.');
            }
            // Pastikan record master sudah ada sebelum bulk save
            if (!state.id_rekaman) {
                showToast('info', 'Membuat sesi rekaman...');
                const firstCheckbox = $('.method-checkbox:first');
                if (firstCheckbox.length === 0) return showError('Tidak ada unit kompetensi untuk disimpan.');
                
                // Panggil auto-save untuk membuat record & mendapatkan id_rekaman
                const initResult = await autoSaveUnitMethod(
                    firstCheckbox.data('unit-id'),
                    firstCheckbox.data('method-key'),
                    firstCheckbox.is(':checked')
                );

                if (!state.id_rekaman) { // Periksa kembali state setelah percobaan membuat record
                    return showError('Gagal memulai sesi rekaman. Coba centang satu kotak secara manual terlebih dahulu.');
                }
            }
            
            // Lanjutkan proses bulk save
            showToast('info', 'Menyimpan semua perubahan...');
            $('.method-checkbox').prop('checked', checkState); // Ubah UI

            const kompetensiData = {};
            $('.unit-section').each(function() {
                const unitId = $(this).find('.method-checkbox').first().data('unit-id');
                const methods = {};
                $(this).find('.method-checkbox').each(function() {
                    methods[$(this).data('method-key')] = checkState ? 1 : 0;
                });
                kompetensiData[unitId] = methods;
            });

            const payload = {
                save_type: 'batch_save_units',
                id_apl1: state.id_apl1,
                id_rekaman: state.id_rekaman,
                kompetensi: kompetensiData
            };
            await sendSaveRequest(payload);
        }
        
        async function handleFinalDataChange() {
            if (!state.id_rekaman) return;
            const payload = {
                save_type: 'full_save',
                id_rekaman: state.id_rekaman,
                id_apl1: state.id_apl1,
                rekomendasi: $('input[name="rekomendasi"]:checked').val(),
                tindak_lanjut: $('textarea[name="tindak_lanjut"]').val(),
                komentar: $('textarea[name="komentar"]').val(),
            };
            await sendSaveRequest(payload, false);
        }

        async function sendSaveRequest(payload, showSuccessToast = true) {
            showToast('info', 'Menyimpan...');
            payload[state.csrfName] = state.csrfHash;

            try {
                const response = await $.ajax({
                    url: `${config.baseUrl}/${config.endpoints.store}`,
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    dataType: 'json'
                });
                
                if (response.csrf_hash) {
                    state.csrfHash = response.csrf_hash;
                }
                if (showSuccessToast) showToast('success', 'Tersimpan!');
                return { success: true, data: response.data };
            } catch (error) {
                const response = error.responseJSON;
                if (response && response.csrf_hash) {
                    state.csrfHash = response.csrf_hash;
                }
                showToast('error', 'Gagal menyimpan!');
                console.error("Save failed:", response || error);
                return { success: false, message: response?.message || 'Gagal menyimpan.' };
            }
        }

        async function handleFormSubmit(e) {
            e.preventDefault();
            const result = await Swal.fire({
                title: 'Finalisasi Rekaman?', text: "Anda yakin ingin menyimpan final rekaman ini?",
                icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Simpan!', cancelButtonText: 'Batal'
            });
            if (result.isConfirmed) {
                const $saveBtn = $('#btnSave');
                const originalText = $saveBtn.html();
                $saveBtn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
                
                const payload = {
                    save_type: 'full_save',
                    id_apl1: state.id_apl1,
                    id_rekaman: state.id_rekaman,
                    rekomendasi: $('input[name="rekomendasi"]:checked').val(),
                    tindak_lanjut: $('textarea[name="tindak_lanjut"]').val(),
                    komentar: $('textarea[name="komentar"]').val(),
                };
                const saveResult = await sendSaveRequest(payload, false);

                if (saveResult.success) {
                    Swal.fire('Berhasil!', 'Rekaman asesmen telah difinalisasi.', 'success');
                    setTimeout(() => window.location.href = `${config.baseUrl}/asesor/rekaman-asesmen`, 1500);
                } else {
                    showError(saveResult.message);
                    $saveBtn.html(originalText).prop('disabled', false);
                }
            }
        }

    /**
         * FUNGSI RENDER TAMPILAN (Disederhanakan tanpa Kelompok Kerja)
         */
        function renderRekamanStruktur(data) {
            const container = $('#rekamanAsesmenContainer');

            if (!data.units || data.units.length === 0) {
                container.html('<div class="alert alert-warning">Struktur unit kompetensi tidak ditemukan untuk skema ini.</div>');
                return;
            }
            
            let html = '';

            // Langsung loop melalui setiap unit
            data.units.forEach(function(unit) {
                const existingUnitData = data.existing_data ? (data.existing_data[unit.id_unit] || {}) : {};
                // Panggil fungsi render untuk setiap unit
                html += renderUnitCard(unit, existingUnitData);
            });

            container.html(html);
        }

        function renderUnitCard(unit, existingData) {
            const methods = [
                { key: 'metode_observasi', label: 'Observasi' }, { key: 'metode_portofolio', label: 'Portofolio' },
                { key: 'metode_pihak_ketiga', label: 'Pihak Ketiga' }, { key: 'metode_lisan', label: 'Lisan' },
                { key: 'metode_tertulis', label: 'Tertulis' }, { key: 'metode_proyek', label: 'Proyek' },
                { key: 'metode_lainnya', label: 'Lainnya' }
            ];
            let methodsHtml = methods.map(method => {
                const isChecked = existingData[method.key] ? 'checked' : '';
                return `<div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input method-checkbox" 
                               id="${method.key}_${unit.id_unit}" data-unit-id="${unit.id_unit}" 
                               data-method-key="${method.key}" ${isChecked}>
                        <label class="custom-control-label" for="${method.key}_${unit.id_unit}">${method.label}</label>
                    </div>
                </div>`;
            }).join('');

            return `<div class="card mb-3 unit-section">
                <div class="card-header"><h6 class="mb-0"><span class="badge badge-light mr-2">${unit.kode_unit}</span>${unit.nama_unit}</h6></div>
                <div class="card-body"><div class="row">${methodsHtml}</div></div>
            </div>`;
        }

        function initializePageState() {
            $('#loadingState, #formRekamanAsesmen, #emptyDataMessage, #apl1-info').hide();
            $('#initialInstructions').show();
            $('#id_apl1').prop('disabled', true).html('<option value="">-- Pilih Asesmen --</option>').trigger('change.select2');
            $('#info-skema-text').text('Pilih Asesmen');
        }
        function showLoading() {
            $('#loadingState').show();
            $('#initialInstructions, #formRekamanAsesmen, #emptyDataMessage, #apl1-info').hide();
        }
        function showForm() {
            $('#formRekamanAsesmen, #apl1-info').show();
            $('#initialInstructions, #loadingState, #emptyDataMessage').hide();
        }
        function showEmptyDataMessage() {
            $('#emptyDataMessage').show();
            $('#initialInstructions, #loadingState, #formRekamanAsesmen, #apl1-info').hide();
        }
        function updateApl1InfoDisplay(data) {
            $('#info-nama').text(data.nama || '-');
            $('#info-nik').text(data.nik || '-');
            $('#info-email').text(data.email || '-');
            $('#info-validasi').text(data.validation || '-');
        }
        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true });
        function showToast(icon, title) { Toast.fire({ icon, title }); }
        function showSuccess(title) { Swal.fire({ icon: 'success', title: title, text: 'Halaman akan dialihkan.' }); }
        function showError(title, message = '') { Swal.fire({ icon: 'error', title: 'Oops...', text: title + ' ' + message }); }
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
        
        init();
    });
</script>