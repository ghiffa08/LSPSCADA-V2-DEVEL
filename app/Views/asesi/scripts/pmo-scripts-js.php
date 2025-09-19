<script>
    $(document).ready(function() {
        'use strict';

        const state = {
            id_apl1: '<?= esc($apl1_data['id_apl1']) ?>',
            id_skema: '<?= esc($id_skema) ?>',
            id_pmo: null,
            totalPertanyaan: 0,
            csrfName: '<?= csrf_token() ?>',
            csrfHash: '<?= csrf_hash() ?>'
        };

        // Load PMO data on page load
        loadPmoData();

        // Attach event listener for form submission
        $('#formPmo').submit(handleFormSubmit);

        // --- FUNGSI BARU: Debounced autosave ---
        // Fungsi ini akan dipanggil untuk menyimpan semua perubahan secara otomatis
        const debouncedAutosave = debounce(async function() {
            // Pastikan data penting ada sebelum menyimpan
            if (!$('[name="id_asesor"]').val()) {
                console.warn('Autosave dilewati: ID Asesor tidak ditemukan.');
                return;
            }

            // Kumpulkan semua data form saat ini
            const formData = $('#formPmo').serialize();

            try {
                const response = await $.ajax({
                    url: '<?= site_url('api/pmo/save') ?>',
                    type: 'POST',
                    data: formData, // Kirim semua data form
                    dataType: 'json'
                });

                if (response.success) {
                    if (response.id_pmo && !state.id_pmo) {
                        state.id_pmo = response.id_pmo;
                        $('#id_pmo').val(state.id_pmo);
                    }
                    // Perbarui token CSRF untuk permintaan berikutnya
                    if (response.token) {
                        state.csrfHash = response.token;
                        $(`[name="${state.csrfName}"]`).val(response.token);
                    }
                    showAutoSaveIndicator();
                } else {
                    showError('Autosave Gagal', response.message);
                }
            } catch (error) {
                console.error('Autosave error:', error);
                showError('Autosave Gagal', 'Gagal terhubung ke server.');
            }
        }, 1500); // Tunggu 1.5 detik setelah input terakhir sebelum menyimpan

        // --- PERUBAHAN: Event listener untuk memicu autosave ---
        $(document).on('change', 'input[type="radio"]', function() {
            debouncedAutosave();
            updateProgressBar();
        });

        $(document).on('input', 'textarea', function() { // Mencakup essay dan catatan
            debouncedAutosave();
            updateProgressBar();
        });

        // --- FUNGSI BARU: Event listener untuk Check/Uncheck All ---
        // Pastikan Anda menambahkan tombol dengan ID #checkAllPmo dan #uncheckAllPmo di file view Anda
        $(document).on('click', '#checkAllPmo', () => handleBulkCheck(true));
        $(document).on('click', '#uncheckAllPmo', () => handleBulkCheck(false));


        async function loadPmoData() {
            try {
                const response = await $.ajax({
                    url: '<?= site_url('api/pmo/loadPmo') ?>',
                    type: 'GET',
                    data: {
                        id_skema: state.id_skema,
                        id_apl1: state.id_apl1
                    },
                    dataType: 'json'
                });

                if (response.success) {
                    state.id_pmo = response.pmo_data?.id_pmo || null;
                    $('#id_pmo').val(state.id_pmo);
                    $('#catatan').val(response.pmo_data?.catatan || '');
                    if (response.pmo_data?.tanggal_observasi) {
                        $('#tanggal_observasi').val(response.pmo_data.tanggal_observasi);
                    }
                    renderPmoChecklist(response.struktur, response.existing_jawaban);
                    $('#formPmo').show();
                } else {
                    showError('Gagal Memuat Data', response.message || 'Tidak dapat mengambil struktur ceklis PMO.');
                }
            } catch (error) {
                console.error('Error loading PMO data:', error);
                showError('Kesalahan Jaringan', 'Gagal terhubung ke server untuk memuat data.');
            } finally {
                $('#loadingData').hide();
            }
        }

        function renderPmoChecklist(struktur, existingJawaban) {
            let html = '';
            state.totalPertanyaan = 0;

            if (!struktur || !struktur.kelompok_kerja || struktur.kelompok_kerja.length === 0) {
                $('#pmoContainer').html('<div class="alert alert-warning">Tidak ada pertanyaan PMO yang dikonfigurasi untuk skema ini.</div>');
                return;
            }

            struktur.kelompok_kerja.forEach(kelompok => {
                html += `<div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-layer-group mr-2"></i>${escapeHtml(kelompok.nama_kelompok)}</h5>
                    </div>
                    <div class="card-body p-0">`;

                kelompok.units.forEach(unit => {
                    html += `<div class="p-3 border-bottom">
                        <h6 class="font-weight-bold">
                            <i class="fas fa-cube text-primary mr-2"></i>
                            <span class="badge badge-light mr-2">${escapeHtml(unit.kode_unit)}</span>
                            ${escapeHtml(unit.nama_unit)}
                        </h6>`;

                    unit.elemen.forEach(elemen => {
                        html += `<div class="ml-4 mt-3">
                            <div class="font-weight-bold text-muted">
                                <i class="fas fa-list-alt mr-2"></i>${escapeHtml(elemen.nama_elemen)}
                            </div>`;

                        elemen.kuk.forEach(kuk => {
                            html += `<div class="ml-4 mt-2">
                                <p class="mb-1"><strong>KUK ${escapeHtml(kuk.kode_kuk)}:</strong> ${escapeHtml(kuk.kriteria_unjuk_kerja)}</p>`;

                            kuk.pertanyaan_list.forEach(pertanyaan => {
                                state.totalPertanyaan++;
                                const jawaban = existingJawaban[pertanyaan.id_pertanyaan] || {};
                                html += renderPertanyaan(pertanyaan, jawaban);
                            });

                            html += `</div>`; // close kuk
                        });
                        html += `</div>`; // close elemen
                    });
                    html += `</div>`; // close unit
                });
                html += `</div></div>`; // close card
            });

            $('#pmoContainer').html(html);
            updateProgressBar();
        }

        function renderPertanyaan(pertanyaan, jawaban) {
            const id = pertanyaan.id_pertanyaan;
            let inputHtml = `<div class="card my-3 bg-light border">
                <div class="card-body">
                    <p class="font-weight-bold">${escapeHtml(pertanyaan.pertanyaan)}</p>
                    <div class="row">
                        <div class="col-md-12">`;

            switch (pertanyaan.jenis_jawaban) {
                case 'YA_TIDAK':
                    const checkedY = jawaban.jawaban_ya_tidak === 'Y' ? 'checked' : '';
                    const checkedN = jawaban.jawaban_ya_tidak === 'N' ? 'checked' : '';
                    inputHtml += `
                        <div class="form-group mb-0">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="yt_y_${id}" name="jawaban[${id}][jawaban_ya_tidak]" class="custom-control-input" value="Y" ${checkedY}>
                                <label class="custom-control-label" for="yt_y_${id}">Ya</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="yt_n_${id}" name="jawaban[${id}][jawaban_ya_tidak]" class="custom-control-input" value="N" ${checkedN}>
                                <label class="custom-control-label" for="yt_n_${id}">Tidak</label>
                            </div>
                        </div>`;
                    break;

                case 'PILIHAN_GANDA':
                    inputHtml += `<div class="form-group mb-0">`;
                    pertanyaan.pilihan.forEach(p => {
                        const checked = jawaban.jawaban_pilihan == p.id_pilihan ? 'checked' : '';
                        inputHtml += `
                            <div class="custom-control custom-radio">
                                <input type="radio" id="pg_${p.id_pilihan}" name="jawaban[${id}][jawaban_pilihan]" class="custom-control-input" value="${p.id_pilihan}" ${checked}>
                                <label class="custom-control-label" for="pg_${p.id_pilihan}">${escapeHtml(p.pilihan)}</label>
                            </div>`;
                    });
                    inputHtml += `</div>`;
                    break;

                case 'ESSAY':
                    const essayText = jawaban.jawaban_essay || '';
                    inputHtml += `<textarea name="jawaban[${id}][jawaban_essay]" class="form-control" rows="3" placeholder="Tuliskan jawaban Anda...">${escapeHtml(essayText)}</textarea>`;
                    break;
            }

            inputHtml += `</div>
                    </div>
                </div>
            </div>`;
            return inputHtml;
        }

        // --- FUNGSI BARU: Menangani Check/Uncheck All ---
        function handleBulkCheck(checkAll) {
            const actionText = checkAll ? 'Mencentang Semua' : 'Menghapus Centang';
            const $btn = $(checkAll ? '#checkAllPmo' : '#uncheckAllPmo');
            const originalBtnText = $btn.html();

            $btn.html(`<i class="fas fa-spinner fa-spin"></i> ${actionText}...`).attr('disabled', true);

            if (checkAll) {
                // Centang semua radio button 'Ya' untuk pertanyaan YA_TIDAK
                $('input[name$="[jawaban_ya_tidak]"][value="Y"]').prop('checked', true);
            } else {
                // Hapus centang semua radio button YA_TIDAK
                $('input[name$="[jawaban_ya_tidak]"]').prop('checked', false);
            }

            // Perbarui progress bar dan panggil autosave
            updateProgressBar();
            debouncedAutosave();

            // Kembalikan tombol ke keadaan semula setelah beberapa saat
            setTimeout(() => {
                $btn.html(originalBtnText).attr('disabled', false);
            }, 1500); // Sesuaikan dengan durasi debounce
        }

        async function handleFormSubmit(e) {
            e.preventDefault();
            const $btn = $('#btnSave');
            const originalBtnText = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').attr('disabled', true);

            try {
                const formData = $(this).serialize();
                const response = await $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json'
                });

                if (response.success) {
                    showSuccess('Berhasil', 'Semua data ceklis PMO telah berhasil disimpan.');
                } else {
                    showError('Gagal Menyimpan', response.message || 'Terjadi kesalahan saat menyimpan data.');
                }
            } catch (error) {
                console.error('Form submission error:', error);
                showError('Kesalahan Server', 'Tidak dapat terhubung ke server.');
            } finally {
                $btn.html(originalBtnText).attr('disabled', false);
            }
        }

        function updateProgressBar() {
            if (state.totalPertanyaan === 0) return;

            const answeredQuestions = new Set();
            $('input:radio:checked, textarea[name*="[jawaban_essay]"]').each(function() {
                if ($(this).is(':radio') || $(this).val().trim() !== '') {
                    const name = $(this).attr('name');
                    const id = name.match(/\[(\d+)\]/)[1];
                    answeredQuestions.add(id);
                }
            });

            const answeredCount = answeredQuestions.size;
            const progressPercent = Math.round((answeredCount / state.totalPertanyaan) * 100);

            $('#progress-bar').css('width', `${progressPercent}%`).attr('aria-valuenow', progressPercent);
            $('#progress-text').text(`${progressPercent}%`);
            $('#data-status').html(`<i class="fas fa-check-circle text-info"></i> ${answeredCount} dari ${state.totalPertanyaan} pertanyaan terjawab`);
        }

        function showAutoSaveIndicator() {
            let indicator = $('#autosave-indicator');
            if (indicator.length === 0) {
                indicator = $('<div id="autosave-indicator" style="position: fixed; top: 20px; right: 20px; background-color: #28a745; color: white; padding: 10px 15px; border-radius: 5px; z-index: 1050; display: none;"></div>');
                $('body').append(indicator);
            }
            indicator.html('<i class="fas fa-check-circle mr-2"></i>Tersimpan otomatis').fadeIn();
            setTimeout(() => {
                indicator.fadeOut();
            }, 2000);
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function escapeHtml(str) {
            return str ? String(str).replace(/[&<>"']/g, match => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[match]) : '';
        }

        function showSuccess(title, message) {
            Swal.fire({
                icon: 'success',
                title: title,
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        }

        function showError(title, message) {
            Swal.fire({
                icon: 'error',
                title: title,
                text: message
            });
        }
    });
</script>