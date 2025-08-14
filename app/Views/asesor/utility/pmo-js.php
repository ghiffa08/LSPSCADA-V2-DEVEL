<script>
$(document).ready(function() {
    'use strict';

    // Initialize select2 - SAMA SEPERTI OBSERVASI
    $('.select2').select2({
        placeholder: "Pilih...",
        allowClear: true,
        width: '100%'
    });

    // Configuration - SAMA SEPERTI OBSERVASI
    const config = {
        baseUrl: '<?= base_url() ?>',
        endpoints: {
            getAsesi: 'asesor/pmo/getAsesiByAsesmen', // SAMA URL PATTERN
            loadPMO: 'asesor/pmo/loadPMO',
            store: 'asesor/pmo/store'
        }
    };

    // State management - SAMA SEPERTI OBSERVASI
    const state = {
        selectedAsesmen: null,
        selectedAsesi: null,
        selectedPengajuan: null,
        selectedSkema: null,
        totalQuestions: 0,
        answeredQuestions: 0,
        autoSaveTimeout: null,
        isLoading: false,
        csrfHash: '<?= csrf_hash() ?>'
    };

    // Event handlers - SAMA SEPERTI OBSERVASI
    function bindEvents() {
        // Asesmen selection change - SAMA SEPERTI OBSERVASI
        $('#id_asesmen').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            state.selectedAsesmen = $(this).val();
            state.selectedSkema = selectedOption.data('id-skema');

            $('#form_id_skema').val(state.selectedSkema);
            $('#form_id_asesmen').val(state.selectedAsesmen);
            $('#kode_skema').val(selectedOption.data('kode-skema') || '');

            // Reset asesi dropdown and hide form
            $('#id_asesi').prop('disabled', true).empty().append('<option value="">-- Memuat Asesi... --</option>');
            $('#form_id_asesi').val('');
            resetForm();

            if (!state.selectedAsesmen) {
                $('#id_asesi').empty().append('<option value="">-- Pilih Asesmen Terlebih Dahulu --</option>');
                showInitialInstructions();
                return;
            }

            // Hide initial instructions when asesmen is selected
            hideInitialInstructions();
            loadAsesiOptions(state.selectedAsesmen);
        });

        // Asesi selection change - SAMA SEPERTI OBSERVASI
        $('#id_asesi').on('change', function() {
            const asesiId = $(this).val();
            state.selectedAsesi = asesiId;

            const selectedOption = $(this).find('option:selected');
            const pengajuanId = selectedOption.data('id-pengajuan');
            state.selectedPengajuan = pengajuanId;

            $('#form_id_asesi').val(asesiId);
            $('#form_id_pengajuan').val(pengajuanId);

            if (asesiId && pengajuanId && state.selectedAsesmen) {
                $('#btnMuatPMO').prop('disabled', false);
                saveSessionState();
            } else {
                $('#btnMuatPMO').prop('disabled', true);
                resetForm();
            }
        });

        // Tanggal PMO change - AUTO SAVE
        $('#tanggal_pmo').on('change', function() {
            $('#form_tanggal_pmo').val($(this).val());
            saveSettings();
            saveSessionState();
        });

        // Load PMO button
        $('#btnMuatPMO').on('click', function() {
            if (!state.selectedSkema || !state.selectedPengajuan) {
                Swal.fire('Error', 'Silakan pilih asesmen dan asesi terlebih dahulu', 'error');
                return;
            }
            loadPMO();
        });
    }

    // Load asesi options - SAMA PERSIS SEPERTI OBSERVASI
    async function loadAsesiOptions(asesmenId) {
        $('#id_asesi').prop('disabled', true).html('<option value="">Loading...</option>');
        hideEmptyDataMessage();

        try {
            const response = await $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getAsesi}`,
                type: 'GET',
                data: { id_asesmen: asesmenId },
                dataType: 'json'
            });

            populateAsesiDropdown(response.asesi, response);
        } catch (error) {
            console.error('Error loading asesi:', error);
            handleLoadAsesiError(error);
        }
    }

    // Populate asesi dropdown - SAMA SEPERTI OBSERVASI
    function populateAsesiDropdown(asesiList, response = {}) {
        const $asesiDropdown = $('#id_asesi').empty();

        if (asesiList && asesiList.length > 0) {
            $asesiDropdown.append('<option value="">-- Pilih Asesi --</option>');
            
            asesiList.forEach(function(asesi) {
                $asesiDropdown.append(
                    `<option value="${asesi.id_asesi}" data-id-pengajuan="${asesi.id_pengajuan}">
                        ${escapeHtml(asesi.nama_asesi)} - ${escapeHtml(asesi.nik)}
                    </option>`
                );
            });
            
            $asesiDropdown.prop('disabled', false);
            
            if (response.message) {
                showInfo('Info', response.message);
            }
        } else {
            $asesiDropdown.append('<option value="">-- Tidak ada asesi tersedia --</option>');
            showEmptyDataMessage();
            
            if (response.message) {
                showInfo('Tidak Ada Data', response.message);
            }
        }
    }

    // Handle load asesi error - SAMA SEPERTI OBSERVASI
    function handleLoadAsesiError(error) {
        console.error('Load asesi error:', error);
        
        $('#id_asesi')
            .empty()
            .append('<option value="">-- Error memuat asesi --</option>')
            .prop('disabled', true);

        let errorMessage = 'Terjadi kesalahan saat memuat data asesi';
        
        if (error.responseJSON && error.responseJSON.message) {
            errorMessage = error.responseJSON.message;
        } else if (error.status === 0) {
            errorMessage = 'Koneksi terputus. Periksa koneksi internet Anda.';
        } else if (error.status >= 500) {
            errorMessage = 'Server mengalami gangguan. Silakan coba lagi nanti.';
        }

        showError('Error Database', errorMessage);
    }

    // Utility functions
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showError(title, message) {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message,
            confirmButtonText: 'OK'
        });
    }

    function showInfo(title, message) {
        Swal.fire({
            icon: 'info',
            title: title,
            text: message,
            confirmButtonText: 'OK'
        });
    }

    // UI Helper functions - SAMA SEPERTI OBSERVASI
    function showInitialInstructions() {
        $('#initialInstructions').show();
        $('#emptyDataMessage').hide();
    }

    function hideInitialInstructions() {
        $('#initialInstructions').hide();
    }

    function showEmptyDataMessage() {
        $('#emptyDataMessage').show();
        $('#initialInstructions').hide();
    }

    function hideEmptyDataMessage() {
        $('#emptyDataMessage').hide();
    }

    function resetForm() {
        $('#pmoForm').hide();
        $('#progressCard').hide();
        $('#questionContainer').empty();
        $('#catatan_asesor').val('');
        $('#btnGeneratePDF').hide();
    }

    function saveSessionState() {
        const state_data = {
            id_asesmen: state.selectedAsesmen,
            id_asesi: state.selectedAsesi,
            id_pengajuan: state.selectedPengajuan,
            tanggal_pmo: $('#tanggal_pmo').val()
        };

        try {
            sessionStorage.setItem('pmo_state', JSON.stringify(state_data));
        } catch (e) {
            console.warn('Cannot save session state:', e);
        }
    }

    // Initialize page state - SAMA SEPERTI OBSERVASI
    function initializePageState() {
        $('#loadingOverlay').hide();
        $('#pmoForm').hide();
        $('#emptyDataMessage').hide();
        $('#progressCard').hide();
        $('#initialInstructions').show();

        if (!$('#id_asesmen').val()) {
            $('#id_asesi').empty().append('<option value="">-- Pilih Asesmen Terlebih Dahulu --</option>').prop('disabled', true);
        }
    }

    // Initialize
    try {
        bindEvents();
        initializePageState();

        console.log('PMO form initialized successfully');
    } catch (error) {
        console.error('PMO initialization error:', error);
        showError('Initialization Error', 'Terjadi kesalahan saat memuat halaman. Silakan refresh halaman.');
    }
});
</script>