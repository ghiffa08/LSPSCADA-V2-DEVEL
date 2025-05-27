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
            totalKomponen: <?= count($komponenList ?? []) ?>,
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
                    saveSettings();
                    resetForm();
                } else {
                    $('#formFeedback').hide();
                }
            });

            // Date changes
            $('#tanggal_mulai').on('change', function() {
                $('#form_tanggal_mulai').val($(this).val());
                saveSettings();
            });

            $('#tanggal_selesai').on('change', function() {
                $('#form_tanggal_selesai').val($(this).val());
                saveSettings();
            });

            // Check/uncheck all buttons
            $('#checkAll').click(() => handleBulkCheck('Ya'));
            $('#uncheckAll').click(() => handleBulkCheck('Tidak'));

            // Radio button changes
            $(document).on('change', '.feedback-radio', handleRadioChange);

            // Komentar input changes
            $(document).on('input', '.komentar-input', debounce(handleKomentarChange, 500));

            // Catatan lain input changes
            $(document).on('input', '#catatan_lain', debounce(handleCatatanLainChange, 500));

            // Form submission
            $('#formFeedback').submit(handleFormSubmit);
        }

        // Main functions
        async function handleSkemaChange() {
            state.id_skema = $(this).val();
            state.id_asesmen = $(this).find('option:selected').data('id-asesmen');

            $('#form_id_skema').val(state.id_skema);
            $('#id_asesi').prop('disabled', true).empty().append('<option value="">-- Memuat Asesi... --</option>');
            $('#form_id_asesi').val('');
            resetForm();
            resetProgressBar();

            if (!state.id_skema) {
                $('#id_asesi').empty().append('<option value="">-- Pilih Skema Terlebih Dahulu --</option>');
                $('#kode_skema').val('');
                return;
            }

            try {
                const response = await $.ajax({
                    url: '<?= base_url('/asesor/feedback/getSkemaDetails') ?>',
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

        function resetForm() {
            // Reset all radio buttons
            $('.feedback-radio').prop('checked', false);

            // Reset all text inputs
            $('.komentar-input').val('');

            // Reset catatan lain
            $('#catatan_lain').val('');

            // Show the form if needed
            $('#formFeedback').show();
            $('#loadingData').hide();
        }

        function handleRadioChange() {
            const id_komponen = $(this).data('id');
            const jawaban = $(this).val();
            updateProgressBar();

            saveKomponen(id_komponen, jawaban);
        }

        function handleKomentarChange() {
            const $row = $(this).closest('tr');
            const id_komponen = $row.find('.feedback-radio:checked').data('id');
            const komentar = $(this).val();

            if (id_komponen) {
                const jawaban = $row.find('.feedback-radio:checked').val();
                saveKomponen(id_komponen, jawaban, komentar);
            }
        }

        function handleCatatanLainChange() {
            saveSettings();
        }

        async function handleBulkCheck(value) {
            const $btn = $(value === 'Ya' ? '#checkAll' : '#uncheckAll');
            const originalBtnText = $btn.html();

            $btn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').attr('disabled', true);

            // Check all Ya or Tidak radio buttons
            if (value === 'Ya') {
                $('input[id^="ya_"]').prop('checked', true);
            } else {
                $('input[id^="tidak_"]').prop('checked', true);
            }

            updateProgressBar();

            const batchData = {
                id_skema: parseInt(state.id_skema),
                id_asesi: $('#form_id_asesi').val(),
                tanggal_mulai: $('#form_tanggal_mulai').val(),
                tanggal_selesai: $('#form_tanggal_selesai').val(),
                catatan_lain: $('#catatan_lain').val(),
                komponen: {},
                komentar: {}
            };

            // Get all komponen values
            $('.feedback-radio:checked').each(function() {
                const id = $(this).data('id');
                const jawaban = $(this).val();
                const komentar = $(`input[name="komentar[${id}]"]`).val();

                batchData.komponen[id] = jawaban;
                batchData.komentar[id] = komentar;
            });

            try {
                const response = await $.ajax({
                    url: '<?= base_url('/asesor/feedback/save') ?>',
                    type: 'POST',
                    data: JSON.stringify(batchData),
                    contentType: 'application/json',
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.success) {
                    showSuccess(value === 'Ya' ? 'Semua komponen berhasil diset ke Ya' : 'Semua komponen berhasil diset ke Tidak');
                    if (response.token) state.csrfHash = response.token;
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
                id_skema: state.id_skema,
                id_asesi: $('#form_id_asesi').val(),
                tanggal_mulai: $('#form_tanggal_mulai').val(),
                tanggal_selesai: $('#form_tanggal_selesai').val(),
                catatan_lain: $('#catatan_lain').val(),
                [state.csrfName]: state.csrfHash
            };

            try {
                const response = await $.ajax({
                    url: '<?= base_url('/asesor/feedback/save') ?>',
                    type: 'POST',
                    data: data,
                    dataType: 'json'
                });

                if (response.token) {
                    state.csrfHash = response.token;
                }
            } catch (error) {
                showError('Gagal menyimpan pengaturan');
            }
        }

        async function saveKomponen(id_komponen, jawaban, komentar = '') {
            const data = {
                id_skema: state.id_skema,
                id_asesi: $('#form_id_asesi').val(),
                tanggal_mulai: $('#form_tanggal_mulai').val(),
                tanggal_selesai: $('#form_tanggal_selesai').val(),
                catatan_lain: $('#catatan_lain').val(),
                komponen: {},
                komentar: {},
                [state.csrfName]: state.csrfHash
            };

            data.komponen[id_komponen] = jawaban;
            data.komentar[id_komponen] = komentar;

            try {
                const response = await $.ajax({
                    url: '<?= base_url('/asesor/feedback/save') ?>',
                    type: 'POST',
                    data: data,
                    dataType: 'json'
                });

                if (response.token) {
                    state.csrfHash = response.token;
                }
            } catch (error) {
                showError('Gagal menyimpan data komponen');
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
                title: 'Simpan Umpan Balik Asesi?',
                text: 'Pastikan semua komponen telah diisi dengan benar',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal'
            });

            if (result.isConfirmed) {
                $('#btnSave').html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').attr('disabled', true);

                try {
                    const formData = new FormData($('#formFeedback')[0]);
                    formData.append(state.csrfName, state.csrfHash);

                    const response = await $.ajax({
                        url: '<?= base_url('/asesor/feedback/save') ?>',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json'
                    });

                    if (response.success) {
                        showSuccess(response.message || 'Umpan balik berhasil disimpan');
                        if (response.token) state.csrfHash = response.token;

                        // Redirect after successful save
                        setTimeout(() => {
                            window.location.href = '<?= base_url('asesmen') ?>';
                        }, 1500);
                    } else {
                        showError(response.message || 'Gagal menyimpan data');
                    }
                } catch (error) {
                    const errorMessage = error.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data';
                    showError(errorMessage);
                } finally {
                    $('#btnSave').html('<i class="fas fa-save mr-1"></i> Simpan').attr('disabled', false);
                }
            }
        }

        // UI helpers
        function updateProgressBar() {
            const checkedKomponen = $('.feedback-radio:checked').length;
            if (state.totalKomponen === 0) return;

            const percentage = Math.round((checkedKomponen / state.totalKomponen) * 100);
            const $progressBar = $('#progress-bar');

            $progressBar.css('width', percentage + '%')
                .attr('aria-valuenow', percentage)
                .removeClass('bg-danger bg-warning bg-info bg-success')
                .addClass(
                    percentage < 25 ? 'bg-danger' :
                    percentage < 50 ? 'bg-warning' :
                    percentage < 75 ? 'bg-info' : 'bg-success'
                );

            $('#progress-text').text(`${percentage}% (${checkedKomponen}/${state.totalKomponen})`);
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
        
        // Initialize event handlers
        initEventHandlers();
    });
</script>
