<?= $this->extend("layouts/landingpage/layout-2") ?>
<?= $this->section("content"); ?>
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-comment-dots text-primary mr-2"></i>FR.IA.06. UMPAN BALIK DARI ASESI</h4>
                <div>
                    <button class="btn btn-light btn-sm" type="button" data-toggle="collapse" data-target="#collapseInfo">
                        <i class="fas fa-info-circle"></i> Info
                    </button>
                </div>
            </div>

            <div class="collapse" id="collapseInfo">
                <div class="card-body bg-light border-top border-bottom">
                    <div class="alert alert-info mb-0">
                        <h6 class="font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Petunjuk Pengisian</h6>
                        <ul class="mb-0 pl-3">
                            <li>Formulir ini digunakan untuk memberikan umpan balik terhadap proses asesmen yang telah Anda jalani.</li>
                            <li>Pengisian umpan balik ini akan disimpan otomatis saat Anda melakukan perubahan.</li>
                            <li>Jawablah setiap pernyataan dengan memilih "Ya" atau "Tidak".</li>
                            <li>Berikan komentar tambahan jika diperlukan pada kolom yang tersedia.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="card border-left-primary mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold"><i class="fas fa-user text-primary mr-1"></i>Nama Asesi</label>
                                <input type="text" class="form-control bg-light" value="<?= esc($pengajuan['nama_asesi']) ?? '-' ?>" readonly>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold"><i class="fas fa-user-tie text-primary mr-1"></i>Nama Asesor</label>
                                <input type="text" class="form-control bg-light" value="<?= esc($pengajuan['nama_asesor']) ?? '-' ?>" readonly>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold"><i class="fas fa-bookmark text-primary mr-1"></i>Skema Sertifikasi</label>
                                <input type="text" class="form-control bg-light" value="<?= esc($pengajuan['nama_skema']) ?? '-' ?>" readonly>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold"><i class="fas fa-hashtag text-primary mr-1"></i>Kode Skema</label>
                                <input type="text" class="form-control bg-light" value="<?= esc($pengajuan['kode_skema']) ?? '-' ?>" readonly>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold"><i class="fas fa-calendar-alt text-primary mr-1"></i>Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggal_mulai_display" value="<?= $feedback['tanggal_mulai'] ?? date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold"><i class="fas fa-calendar-check text-primary mr-1"></i>Tanggal Selesai</label>
                                <input type="date" class="form-control" id="tanggal_selesai_display" value="<?= $feedback['tanggal_selesai'] ?? date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="progress-container" class="card mb-4 border-left-primary">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="font-weight-bold mb-0"><i class="fas fa-tasks text-primary mr-1"></i> Kemajuan Pengisian</label>
                            <div>
                                <span id="progress-text" class="badge badge-primary px-2 py-1">0%</span>
                                <span id="data-status" class="ml-2 text-nowrap">
                                    <i class="fas fa-sync text-muted"></i> Menunggu data...
                                </span>
                            </div>
                        </div>
                        <div class="progress" style="height: 15px;">
                            <div id="progress-bar" class="progress-bar progress-bar-striped" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <form action="<?= base_url('/api/feedback-asesi/save') ?>" method="POST" id="formFeedback">
                    <?= csrf_field() ?>

                    <input type="hidden" name="id_pengajuan" value="<?= esc($pengajuan['id_pengajuan']) ?>">
                    <input type="hidden" name="id_asesi" value="<?= esc($pengajuan['id_asesi']) ?>">
                    <input type="hidden" name="id_asesor" value="<?= esc($pengajuan['id_asesor']) ?>">
                    <input type="hidden" name="id_skema" value="<?= esc($pengajuan['id_skema']) ?>">

                    <input type="hidden" name="tanggal_mulai" id="form_tanggal_mulai" value="<?= $feedback['tanggal_mulai'] ?? date('Y-m-d') ?>">
                    <input type="hidden" name="tanggal_selesai" id="form_tanggal_selesai" value="<?= $feedback['tanggal_selesai'] ?? date('Y-m-d') ?>">

                    <div class="d-flex justify-content-between mb-3">
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" id="checkAll">
                                <i class="fas fa-check-double mr-1"></i> Ya Semua
                            </button>
                            <button type="button" class="btn btn-warning" id="uncheckAll">
                                <i class="fas fa-times mr-1"></i> Tidak Semua
                            </button>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-success" id="btnSave">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-light">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <div id="feedbackContainer" class="card shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="50%">Pernyataan</th>
                                        <th width="15%" class="text-center">Jawaban</th>
                                        <th width="30%">Komentar</th>
                                    </tr>
                                </thead>
                                <tbody id="feedbackTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold text-primary">Catatan Lain</h6>
                        </div>
                        <div class="card-body">
                            <textarea name="catatan_lain" id="catatan_lain" class="form-control" rows="4" placeholder="Masukkan catatan tambahan jika ada..."><?= esc($feedback['catatan_lain'] ?? '') ?></textarea>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // PENJELASAN: Data diambil langsung dari variabel PHP yang di-render oleh controller.
        // Tidak ada lagi AJAX call untuk memuat data awal. Ini lebih cepat & efisien.
        const komponenData = <?= json_encode($komponen) ?>;
        const existingAnswers = <?= json_encode($existingAnswers) ?>;
        const totalKomponen = komponenData.length;

        /**
         * Merender form feedback berdasarkan data komponen dan jawaban yang ada.
         */
        function renderFeedbackForm(komponen, existingData) {
            const tableBody = $('#feedbackTableBody');
            tableBody.empty();

            if (komponen.length === 0) {
                tableBody.append('<tr><td colspan="4" class="text-center">Komponen feedback tidak ditemukan.</td></tr>');
                return;
            }

            komponen.forEach(function(item, index) {
                const rowNumber = index + 1;
                const itemId = item.id_komponen;
                const existingItem = existingData[itemId] || {};
                const jawaban = existingItem.jawaban || '';
                const komentar = existingItem.komentar || '';

                // PERBAIKAN: name input radio diubah menjadi 'jawaban[...]' agar sesuai dengan struktur data detail di controller
                const row = `
                <tr>
                    <td class="text-center align-middle">${rowNumber}</td>
                    <td class="align-middle">${item.pernyataan}</td>
                    <td class="text-center align-middle">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-outline-success ${jawaban === 'Y' ? 'active' : ''}">
                                <input type="radio" name="jawaban[${itemId}]" value="Y" ${jawaban === 'Y' ? 'checked' : ''}> Ya
                            </label>
                            <label class="btn btn-outline-danger ${jawaban === 'T' ? 'active' : ''}">
                                <input type="radio" name="jawaban[${itemId}]" value="T" ${jawaban === 'T' ? 'checked' : ''}> Tidak
                            </label>
                        </div>
                    </td>
                    <td class="align-middle">
                        <textarea class="form-control feedback-komentar" name="komentar[${itemId}]" rows="1" placeholder="Komentar...">${komentar}</textarea>
                    </td>
                </tr>`;
                tableBody.append(row);
            });

            updateProgress();
            attachEventListeners();
        }

        /**
         * Memasang event listener ke elemen-elemen form.
         */
        function attachEventListeners() {
            // Event untuk input tanggal (display) yang akan mengupdate input tanggal (hidden)
            $('#tanggal_mulai_display, #tanggal_selesai_display').on('change', function() {
                const targetId = $(this).attr('id').replace('_display', '');
                $('#form_' + targetId).val($(this).val());
                autoSave();
            });

            // Event untuk auto-save pada input di dalam tabel
            $('#feedbackTableBody').on('change', 'input[type=radio]', function() {
                updateProgress();
                autoSave();
            });
            $('#feedbackTableBody').on('blur', '.feedback-komentar', autoSave);
            $('#catatan_lain').on('blur', autoSave);

            // Event untuk tombol toolbar
            $('#checkAll').on('click', function() {
                $('input[value="Y"]').prop('checked', true).parent().addClass('active');
                $('input[value="T"]').prop('checked', false).parent().removeClass('active');
                updateProgress();
                autoSave();
            });

            $('#uncheckAll').on('click', function() {
                $('input[value="Y"]').prop('checked', false).parent().removeClass('active');
                $('input[value="T"]').prop('checked', true).parent().addClass('active');
                updateProgress();
                autoSave();
            });

            // Event untuk submit form manual
            $('#formFeedback').on('submit', function(e) {
                e.preventDefault();
                submitForm(this);
            });
        }

        /**
         * Memperbarui progress bar pengisian.
         */
        function updateProgress() {
            if (totalKomponen === 0) return;

            const filled = $('input[type=radio]:checked').length;
            const percent = Math.round((filled / totalKomponen) * 100);

            $('#progress-bar').css('width', percent + '%').attr('aria-valuenow', percent);
            $('#progress-text').text(percent + '%');

            const $progressBar = $('#progress-bar');
            $progressBar.removeClass('bg-danger bg-warning bg-primary bg-success');

            if (percent === 100) {
                $('#data-status').html('<i class="fas fa-check-circle text-success"></i> Semua terisi');
                $progressBar.addClass('bg-success');
            } else if (percent > 50) {
                $('#data-status').html(`<i class="fas fa-spinner text-primary"></i> Terisi ${filled} dari ${totalKomponen}`);
                $progressBar.addClass('bg-primary');
            } else if (percent > 0) {
                $('#data-status').html(`<i class="fas fa-spinner text-warning"></i> Terisi ${filled} dari ${totalKomponen}`);
                $progressBar.addClass('bg-warning');
            } else {
                $('#data-status').html('<i class="fas fa-exclamation-circle text-danger"></i> Belum ada yang diisi');
                $progressBar.addClass('bg-danger');
            }
        }

        // Debounce function untuk mencegah autoSave terlalu sering dipanggil
        let autoSaveTimeout;

        function autoSave() {
            clearTimeout(autoSaveTimeout);
            $('#data-status').html('<i class="fas fa-sync fa-spin text-primary"></i> Menyimpan...');

            autoSaveTimeout = setTimeout(() => {
                $.ajax({
                    url: $('#formFeedback').attr('action'),
                    type: 'POST',
                    data: $('#formFeedback').serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('input[name="<?= csrf_token() ?>"]').val(response.token);
                            $('#data-status').html('<i class="fas fa-check text-success"></i> Data tersimpan');
                            setTimeout(() => updateProgress(), 2000);
                        } else {
                            $('#data-status').html('<i class="fas fa-times-circle text-danger"></i> Gagal simpan');
                        }
                    },
                    error: function() {
                        $('#data-status').html('<i class="fas fa-times-circle text-danger"></i> Error');
                    }
                });
            }, 1000); // Jeda 1 detik sebelum menyimpan
        }

        /**
         * Mengirim form secara manual saat tombol simpan ditekan.
         */
        function submitForm(form) {
            const btnSave = $('#btnSave');
            const btnText = btnSave.html();
            btnSave.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: $(form).serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Umpan balik berhasil disimpan.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            window.location.href = '<?= base_url('dashboard') ?>';
                        });
                    } else {
                        Swal.fire('Gagal', response.message || 'Gagal menyimpan data.', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Terjadi kesalahan sistem. Coba lagi nanti.', 'error');
                    console.error(xhr.responseText);
                },
                complete: function() {
                    btnSave.html(btnText).prop('disabled', false);
                }
            });
        }

        // Inisialisasi
        renderFeedbackForm(komponenData, existingAnswers);

    });
</script>
<?= $this->endSection(); ?>