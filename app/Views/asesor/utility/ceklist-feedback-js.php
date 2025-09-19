<script>
    $(document).ready(function() {
        'use strict';

        // Inisialisasi select2
        $('.select2').select2({
            placeholder: "Pilih...",
            allowClear: true,
            width: '100%'
        });

        // State Management
        const state = {
            id_asesmen: '',
            id_skema: '',
            id_feedback: null,
            csrfName: '<?= csrf_token() ?>',
            csrfHash: '<?= csrf_hash() ?>'
        };

        // --- EVENT HANDLERS ---
        $('#id_asesmen').on('change', handleAsesmenChange);
        $('#id_apl1').on('change', handleApl1Change);
        $('#formFeedback').submit(handleFormSubmit);

        // --- FUNGSI UTAMA ---

        // 1. Saat Asesmen dipilih -> Load daftar Siswa (APL1)
        async function handleAsesmenChange() {
            const selectedOption = $(this).find('option:selected');
            state.id_asesmen = $(this).val();
            state.id_skema = selectedOption.data('id-skema');
            $('#form_id_skema').val(state.id_skema);

            const $apl1Dropdown = $('#id_apl1');
            resetForm();

            if (!state.id_asesmen) {
                $apl1Dropdown.prop('disabled', true).empty().append('<option value="">-- Pilih Asesmen Dulu --</option>');
                $('#initialInstructions').show();
                return;
            }

            $apl1Dropdown.prop('disabled', true).empty().append('<option value="">-- Memuat Siswa... --</option>');
            $('#initialInstructions').hide();

            try {
                // API call untuk mendapatkan daftar APL1 yang valid
                const response = await $.ajax({
                    url: '<?= base_url('api/feedback-asesi/get-skema-details') ?>', // Menggunakan API feedback
                    type: 'GET',
                    data: {
                        id_skema: state.id_skema
                    },
                    dataType: 'json'
                });

                if (response.success && response.apl1_list.length > 0) {
                    populateApl1Dropdown(response.apl1_list);
                } else {
                    $apl1Dropdown.prop('disabled', true).empty().append('<option value="">-- Tidak Ada Siswa --</option>');
                    $('#emptyDataMessage').show();
                }
            } catch (error) {
                console.error('Error loading APL1:', error);
                showError('Gagal memuat daftar siswa.');
                $apl1Dropdown.prop('disabled', true).empty().append('<option value="">-- Gagal Memuat --</option>');
            }
        }

        // 2. Saat Siswa dipilih -> Load form feedback
        function handleApl1Change() {
            const id_apl1 = $(this).val();
            $('#form_id_asesi').val(id_apl1); // id_asesi di form = id_apl1

            if (!id_apl1) {
                resetForm();
                return;
            }
            loadFeedbackData();
        }

        // 3. Load komponen dan data feedback yang sudah ada
        async function loadFeedbackData() {
            const id_apl1 = $('#id_apl1').val();
            if (!state.id_skema || !id_apl1) return;

            $('#loadingData').show();
            $('#formFeedback').hide();

            try {
                const response = await $.ajax({
                    url: '<?= base_url('api/feedback-asesi/load-feedback') ?>',
                    type: 'GET',
                    data: {
                        id_skema: state.id_skema,
                        id_apl1: id_apl1
                    },
                    dataType: 'json'
                });

                if (response.success) {
                    state.id_feedback = response.id_feedback;
                    $('#form_id_feedback').val(response.id_feedback);
                    $('#catatan_lain').val(response.feedback?.catatan_lain || '');

                    renderFeedbackForm(response.komponen, response.existing_data);
                    $('#formFeedback').show();
                } else {
                    showError('Gagal memuat data feedback.', response.message);
                }
            } catch (error) {
                console.error('Error loading feedback data:', error);
                showError('Terjadi kesalahan saat memuat form.');
            } finally {
                $('#loadingData').hide();
            }
        }

        // 4. Render HTML untuk form feedback
        function renderFeedbackForm(komponenList, existingData) {
            let html = '<table class="table table-bordered table-striped">';
            html += `
                <thead class="thead-light">
                    <tr>
                        <th style="width: 60%;">Komponen</th>
                        <th style="width: 15%;" class="text-center">Jawaban</th>
                        <th>Komentar</th>
                    </tr>
                </thead>
                <tbody>
            `;

            komponenList.forEach(item => {
                const existing = existingData[item.id_komponen] || {};
                const checkedYa = existing.jawaban == '1' ? 'checked' : '';
                const checkedTidak = existing.jawaban == '0' ? 'checked' : '';
                const komentar = existing.komentar || '';

                html += `
                    <tr>
                        <td>${escapeHtml(item.pernyataan)}</td>
                        <td>
                            <div class="d-flex justify-content-center">
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="jawaban_y_${item.id_komponen}" name="jawaban[${item.id_komponen}]" class="custom-control-input" value="1" ${checkedYa} required>
                                    <label class="custom-control-label" for="jawaban_y_${item.id_komponen}">Ya</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="jawaban_n_${item.id_komponen}" name="jawaban[${item.id_komponen}]" class="custom-control-input" value="0" ${checkedTidak} required>
                                    <label class="custom-control-label" for="jawaban_n_${item.id_komponen}">Tidak</label>
                                </div>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="komentar[${item.id_komponen}]" class="form-control form-control-sm" placeholder="Komentar..." value="${escapeHtml(komentar)}">
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            $('#feedbackContainer').html(html);
        }

        // 5. Submit form
        async function handleFormSubmit(e) {
            e.preventDefault();
            const $btn = $('#btnSave');
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

            // Tambahkan tanggal ke form data sebelum submit
            let formData = $(this).serializeArray();
            formData.push({
                name: 'tanggal_mulai',
                value: $('#tanggal_mulai').val()
            });
            formData.push({
                name: 'tanggal_selesai',
                value: $('#tanggal_selesai').val()
            });

            try {
                const response = await $.ajax({
                    url: '<?= base_url('api/feedback-asesi/save') ?>',
                    type: 'POST',
                    data: $.param(formData), // Menggunakan $.param untuk serialisasi yang benar
                    dataType: 'json'
                });

                if (response.success) {
                    state.id_feedback = response.id_feedback;
                    $('#form_id_feedback').val(response.id_feedback);
                    showSuccess(response.message || 'Feedback berhasil disimpan!');
                } else {
                    showError('Gagal menyimpan.', response.message);
                }
            } catch (error) {
                console.error('Error saving form:', error);
                showError('Terjadi kesalahan saat menyimpan data.');
            } finally {
                $btn.html('<i class="fas fa-save"></i> Simpan Feedback').prop('disabled', false);
            }
        }

        // --- FUNGSI HELPERS ---
        function populateApl1Dropdown(apl1List) {
            const $apl1Dropdown = $('#id_apl1');
            $apl1Dropdown.prop('disabled', false).empty().append('<option value="">-- Pilih Siswa --</option>');
            apl1List.forEach(apl1 => {
                $apl1Dropdown.append(`<option value="${apl1.id_apl1}">${escapeHtml(apl1.nama_siswa)} (${escapeHtml(apl1.nik)})</option>`);
            });
            $apl1Dropdown.trigger('change.select2');
        }

        function resetForm() {
            $('#formFeedback').hide();
            $('#feedbackContainer').empty();
            $('#emptyDataMessage').hide();
            $('#loadingData').hide();
            if (!$('#id_asesmen').val()) {
                $('#initialInstructions').show();
            }
        }

        function escapeHtml(str) {
            return str ? $('<div>').text(str).html() : '';
        }

        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: message,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }

        function showError(title, message = '') {
            Swal.fire({
                icon: 'error',
                title: title,
                text: message
            });
        }

        // Inisialisasi halaman
        resetForm();
    });
</script>