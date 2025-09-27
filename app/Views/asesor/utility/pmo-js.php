<script>
    $(document).ready(function() {
        const form = $('#formPmo');
        const loadingIndicator = $('#loadingData');
        const pmoContainer = $('#pmoContainer');
        const progressBar = $('#progress-bar');
        const progressText = $('#progress-text');
        const dataStatus = $('#data-status');
        const btnSave = $('#btnSave');

        // Get IDs from the hidden inputs in the form
        const idSkema = $('input[name="id_skema"]').val();
        const idPengajuan = $('input[name="id_pengajuan"]').val();

        let totalQuestions = 0;

        /**
         * Load PMO questions and any existing answers from the server.
         */
        function loadPmoData() {
            loadingIndicator.show();
            form.hide();
            dataStatus.html('<i class="fas fa-sync fa-spin text-muted"></i> Memuat...');

            $.ajax({
                url: "<?= site_url('api/pmo/loadPmo') ?>",
                type: 'GET',
                dataType: 'json',
                data: {
                    id_skema: idSkema,
                    id_pengajuan: idPengajuan // Send id_pengajuan
                },
                success: function(response) {
                    if (response.success) {
                        renderPmoStructure(response.struktur);
                        populateExistingData(response.pmo_data, response.existing_jawaban);
                        updateProgress();
                        loadingIndicator.hide();
                        form.fadeIn(300);
                        dataStatus.html('<i class="fas fa-check text-success"></i> Data termuat');
                    } else {
                        handleAjaxError(null, 'Error: ' + response.message);
                    }
                },
                error: handleAjaxError
            });
        }

        /**
         * Render the entire PMO checklist structure from the server response.
         */
        function renderPmoStructure(struktur) {
            let html = '';
            totalQuestions = 0;

            // Simplified rendering logic (adjust if your structure is more complex)
            struktur.kelompok_kerja.forEach(kelompok => {
                kelompok.units.forEach(unit => {
                    html += `<div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 font-weight-bold text-primary">${unit.kode_unit} - ${unit.nama_unit}</h6>
                            </div>
                            <div class="card-body">`;
                    unit.elemen.forEach(elemen => {
                        elemen.kuk.forEach(kuk => {
                            kuk.pertanyaan_list.forEach(pertanyaan => {
                                totalQuestions++;
                                html += renderQuestion(pertanyaan, kuk.kriteria_unjuk_kerja);
                            });
                        });
                    });
                    html += `   </div>
                         </div>`;
                });
            });
            pmoContainer.html(html);
        }

        /**
         * Render a single question block.
         */
        function renderQuestion(pertanyaan, kukText) {
            const qId = pertanyaan.id_pertanyaan;
            return `
            <div class="pmo-question mb-4 p-3 border rounded" data-question-id="${qId}">
                <p class="font-weight-bold mb-1">KUK: <span class="font-weight-normal">${kukText}</span></p>
                <p class="text-info mb-2">${pertanyaan.pertanyaan}</p>
                <div class="d-flex align-items-center">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-outline-success btn-sm">
                            <input type="radio" name="jawaban[${qId}][pencapaian]" value="Ya" autocomplete="off"> Ya
                        </label>
                        <label class="btn btn-outline-danger btn-sm">
                            <input type="radio" name="jawaban[${qId}][pencapaian]" value="Tidak" autocomplete="off"> Tidak
                        </label>
                    </div>
                </div>
            </div>
        `;
        }

        /**
         * Fill the form with data that has already been saved.
         */
        function populateExistingData(pmoData, existingJawaban) {
            if (pmoData) {
                $('#id_pmo').val(pmoData.id_pmo);
                $('#tanggal_observasi').val(pmoData.tanggal_observasi);
                $('#catatan').val(pmoData.catatan);
            }

            for (const qId in existingJawaban) {
                const jawaban = existingJawaban[qId];
                const questionDiv = $(`.pmo-question[data-question-id="${qId}"]`);
                if (questionDiv.length) {
                    const radio = questionDiv.find(`input[name="jawaban[${qId}][pencapaian]"][value="${jawaban.pencapaian}"]`);
                    if (radio.length) {
                        radio.prop('checked', true).closest('label').addClass('active');
                    }
                }
            }
        }

        /**
         * Update the progress bar based on answered questions.
         */
        function updateProgress() {
            const answeredCount = pmoContainer.find('input[type="radio"]:checked').length;
            const percentage = totalQuestions > 0 ? Math.round((answeredCount / totalQuestions) * 100) : 0;

            progressBar.css('width', percentage + '%').attr('aria-valuenow', percentage);
            progressText.text(percentage + '%');
        }

        /**
         * Handle form submission via AJAX.
         */
        form.on('submit', function(e) {
            e.preventDefault();
            btnSave.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

            const formData = $(this).serialize();

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#id_pmo').val(response.id_pmo); // Update PMO ID if it's newly created
                        Swal.fire('Berhasil!', response.message, 'success');
                        dataStatus.html('<i class="fas fa-save text-success"></i> Tersimpan');
                    } else {
                        Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                    }
                },
                error: handleAjaxError,
                complete: function() {
                    btnSave.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Final Ceklis PMO');
                }
            });
        });

        function handleAjaxError(jqXHR, textStatus) {
            loadingIndicator.html('<p class="text-danger">Gagal memuat data. Silakan coba lagi.</p>');
            dataStatus.html('<i class="fas fa-times-circle text-danger"></i> Gagal');
            const message = jqXHR?.responseJSON?.message || 'Terjadi kesalahan jaringan atau server.';
            Swal.fire('Error', message, 'error');
        }

        // Event listeners
        $('#checkAllPmo').on('click', () => {
            pmoContainer.find('input[value="Ya"]').prop('checked', true).closest('label').addClass('active');
            pmoContainer.find('input[value="Tidak"]').prop('checked', false).closest('label').removeClass('active');
            updateProgress();
        });

        $('#uncheckAllPmo').on('click', () => {
            pmoContainer.find('input[type="radio"]').prop('checked', false).closest('label').removeClass('active');
            updateProgress();
        });

        pmoContainer.on('change', 'input[type="radio"]', updateProgress);

        // Initial load
        loadPmoData();
    });
</script>