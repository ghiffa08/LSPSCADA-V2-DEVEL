<?= $this->extend("layouts/asesi/layout-app"); ?>
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
                <!-- Informasi Asesi & Skema (readonly) -->
                <div class="card border-left-primary mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-user text-primary mr-1"></i>Nama Asesi</label>
                                    <input type="text" class="form-control bg-light" id="nama_asesi" value="<?= user()->fullname ?? '-' ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-user-tie text-primary mr-1"></i>Nama Asesor</label>
                                    <input type="text" class="form-control bg-light" id="nama_asesor" value="<?= $asesmen['nama_asesor'] ?? '-' ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-bookmark text-primary mr-1"></i>Skema Sertifikasi</label>
                                    <input type="text" class="form-control bg-light" id="nama_skema" value="<?= $asesmen['nama_skema'] ?? '-' ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-hashtag text-primary mr-1"></i>Kode Skema</label>
                                    <input type="text" class="form-control bg-light" id="kode_skema" value="<?= $asesmen['kode_skema'] ?? '-' ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-calendar-alt text-primary mr-1"></i>Tanggal Mulai</label>
                                    <input type="date" class="form-control" name="tanggal_mulai" id="tanggal_mulai" value="<?= $feedback['tanggal_mulai'] ?? date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-calendar-check text-primary mr-1"></i>Tanggal Selesai</label>
                                    <input type="date" class="form-control" name="tanggal_selesai" id="tanggal_selesai" value="<?= $feedback['tanggal_selesai'] ?? date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
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

                <!-- Loading Indicator -->
                <div id="loadingData" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data umpan balik...</p>
                </div>

                <!-- Form Feedback -->
                <form action="<?= base_url('/api/feedback-asesi/save') ?>" method="POST" id="formFeedback" style="display: none;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_feedback" id="form_id_feedback" value="<?= $feedback['id_feedback'] ?? '' ?>">
                    <input type="hidden" name="id_asesmen" id="form_id_asesmen" value="<?= $asesmen['id_asesmen'] ?? '' ?>">
                    <input type="hidden" name="id_skema" id="form_id_skema" value="<?= $asesmen['id_skema'] ?? '' ?>">
                    <input type="hidden" name="id_asesi" id="form_id_asesi" value="<?= $id_asesi ?>">
                    <input type="hidden" name="id_asesor" id="form_id_asesor" value="<?= $asesmen['id_asesor'] ?? '' ?>">
                    <input type="hidden" name="tanggal_mulai" id="form_tanggal_mulai" value="<?= $feedback['tanggal_mulai'] ?? date('Y-m-d') ?>">
                    <input type="hidden" name="tanggal_selesai" id="form_tanggal_selesai" value="<?= $feedback['tanggal_selesai'] ?? date('Y-m-d') ?>">

                    <!-- Toolbar Buttons -->
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

                    <!-- Feedback Container -->
                    <div id="feedbackContainer" class="card shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="50%">Pernyataan</th>
                                        <th width="15%">Jawaban</th>
                                        <th width="30%">Komentar</th>
                                    </tr>
                                </thead>
                                <tbody id="feedbackTableBody">
                                    <!-- Content will be generated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Catatan Lain Section -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold text-primary">Catatan Lain</h6>
                        </div>
                        <div class="card-body">
                            <textarea name="catatan_lain" id="catatan_lain" class="form-control" rows="4" placeholder="Masukkan catatan tambahan jika ada..."><?= $feedback['catatan_lain'] ?? '' ?></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-footer bg-white">
                <div class="text-muted text-center">
                    <i class="fas fa-comment-alt mr-1"></i> Umpan balik ini penting untuk peningkatan kualitas layanan asesmen.
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('js') ?>
<script>
$(document).ready(function() {
    // Variables
    let feedbackData = {};
    let totalKomponen = 0;
    let komponen = [];
    const id_asesi = $('#form_id_asesi').val();
    const id_skema = $('#form_id_skema').val();
    const id_feedback = $('#form_id_feedback').val();

    // Initial data loading
    loadKomponenFeedback();

    // Observe date changes
    $('#tanggal_mulai, #tanggal_selesai').on('change', function() {
        const id = $(this).attr('id');
        const formId = 'form_' + id;
        const value = $(this).val();
        $('#' + formId).val(value);

        // If this is an auto-save, trigger it
        autoSave();
    });

    // Load komponen feedback
    function loadKomponenFeedback() {
        // Show loading indicator
        $('#loadingData').show();
        $('#formFeedback').hide();
        $('#progress-container').show();
        $('#data-status').html('<i class="fas fa-sync fa-spin text-primary"></i> Memuat data...');

        // Get komponen list from API
        $.ajax({
            url: '<?= base_url('api/feedback-asesi/get-komponen') ?>',
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    komponen = response.komponen;
                    totalKomponen = komponen.length;

                    // If feedback ID exists, load existing data
                    if (id_feedback) {
                        loadExistingFeedback(id_feedback);
                    } else {
                        // Check if there's already feedback for this asesi and skema
                        checkExistingFeedback();
                    }
                } else {
                    showError('Gagal memuat komponen umpan balik');
                }
            },
            error: function(xhr) {
                showError('Terjadi kesalahan saat memuat komponen');
                console.error(xhr.responseText);
            }
        });
    }

    // Check if feedback already exists
    function checkExistingFeedback() {
        $.ajax({
            url: '<?= base_url('api/feedback-asesi/check-existing') ?>',
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                id_asesi: id_asesi,
                id_skema: id_skema
            },
            success: function(response) {
                if (response.success) {
                    if (response.exists && response.id_feedback) {
                        // Set the feedback ID and load existing data
                        $('#form_id_feedback').val(response.id_feedback);
                        loadExistingFeedback(response.id_feedback);
                    } else {
                        // No existing data, render empty form
                        renderFeedbackForm(komponen, {});
                    }
                } else {
                    showError('Gagal memeriksa data umpan balik');
                }
            },
            error: function(xhr) {
                showError('Terjadi kesalahan saat memeriksa data');
                console.error(xhr.responseText);
            }
        });
    }

    // Load existing feedback data
    function loadExistingFeedback(id_feedback) {
        $.ajax({
            url: '<?= base_url('api/feedback-asesi/load-feedback') ?>',
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                id_feedback: id_feedback
            },
            success: function(response) {
                if (response.success) {
                    const feedback = response.feedback || {};

                    // Update form fields
                    $('#catatan_lain').val(feedback.catatan_lain || '');

                    if (feedback.tanggal_mulai) {
                        $('#tanggal_mulai').val(feedback.tanggal_mulai);
                        $('#form_tanggal_mulai').val(feedback.tanggal_mulai);
                    }

                    if (feedback.tanggal_selesai) {
                        $('#tanggal_selesai').val(feedback.tanggal_selesai);
                        $('#form_tanggal_selesai').val(feedback.tanggal_selesai);
                    }

                    // Render the form with existing data
                    renderFeedbackForm(komponen, response.existing_data || {});
                } else {
                    showError('Gagal memuat data umpan balik');
                }
            },
            error: function(xhr) {
                showError('Terjadi kesalahan saat memuat data');
                console.error(xhr.responseText);
            }
        });
    }

    // Render feedback form with components and existing data
    function renderFeedbackForm(komponen, existingData) {
        const tableBody = $('#feedbackTableBody');
        tableBody.empty();

        // Generate rows for each komponen
        komponen.forEach(function(item, index) {
            const rowNumber = index + 1;
            const itemId = item.id_komponen;
            const existingItem = existingData[itemId] || {};
            const existingJawaban = existingItem.jawaban || '';
            const existingKomentar = existingItem.komentar || '';

            // Create row HTML
            const row = `
                <tr data-id="${itemId}">
                    <td class="text-center">${rowNumber}</td>
                    <td>${item.pernyataan}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-outline-success ${existingJawaban === 'Y' ? 'active' : ''}">
                                <input type="radio" name="komponen[${itemId}]" value="Y" ${existingJawaban === 'Y' ? 'checked' : ''}> Ya
                            </label>
                            <label class="btn btn-outline-danger ${existingJawaban === 'T' ? 'active' : ''}">
                                <input type="radio" name="komponen[${itemId}]" value="T" ${existingJawaban === 'T' ? 'checked' : ''}> Tidak
                            </label>
                        </div>
                    </td>
                    <td>
                        <textarea class="form-control feedback-komentar" name="komentar[${itemId}]" rows="1" placeholder="Komentar (opsional)">${existingKomentar}</textarea>
                    </td>
                </tr>
            `;

            tableBody.append(row);
        });

        // Hide loading indicator and show form
        $('#loadingData').hide();
        $('#formFeedback').show();

        // Update progress
        updateProgress();

        // Attach change event to radio buttons for auto-save and progress update
        $('input[type=radio]').on('change', function() {
            updateProgress();
            autoSave();
        });

        // Attach blur event to textareas for auto-save
        $('.feedback-komentar').on('blur', function() {
            autoSave();
        });

        // Attach blur event to textarea for catatan_lain
        $('#catatan_lain').on('blur', function() {
            autoSave();
        });
    }

    // Update progress bar
    function updateProgress() {
        const filled = $('input[type=radio]:checked').length;
        const percent = totalKomponen ? Math.round((filled / totalKomponen) * 100) : 0;

        $('#progress-bar').css('width', percent + '%').attr('aria-valuenow', percent);
        $('#progress-text').text(percent + '%');

        // Update status text
        if (percent === 0) {
            $('#data-status').html('<i class="fas fa-exclamation-circle text-warning"></i> Belum ada yang diisi');
        } else if (percent < 100) {
            $('#data-status').html(`<i class="fas fa-spinner text-primary"></i> Terisi ${filled} dari ${totalKomponen}`);
        } else {
            $('#data-status').html('<i class="fas fa-check-circle text-success"></i> Semua terisi');
        }

        // Change progress bar color based on percentage
        const $progressBar = $('#progress-bar');
        $progressBar.removeClass('bg-danger bg-warning bg-primary bg-success');

        if (percent < 25) {
            $progressBar.addClass('bg-danger');
        } else if (percent < 50) {
            $progressBar.addClass('bg-warning');
        } else if (percent < 100) {
            $progressBar.addClass('bg-primary');
        } else {
            $progressBar.addClass('bg-success');
        }
    }

    // Auto-save functionality using AJAX
    function autoSave() {
        // Prepare form data
        const formData = $('#formFeedback').serialize();

        // Send data to server
        $.ajax({
            url: '<?= base_url('/api/feedback-asesi/save') ?>',
            type: 'POST',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    // Update feedback ID if it's a new record
                    if (response.result && response.result.id_feedback) {
                        $('#form_id_feedback').val(response.result.id_feedback);
                    }

                    // Update CSRF token
                    $('input[name="<?= csrf_token() ?>"]').val(response.token);

                    // Show subtle success indicator (no popups for auto-save)
                    $('#data-status').html('<i class="fas fa-check text-success"></i> Data tersimpan');
                    setTimeout(function() {
                        updateProgress();
                    }, 2000);
                } else {
                    console.error('Auto-save failed:', response.message);
                }
            },
            error: function(xhr) {
                console.error('Auto-save error:', xhr.responseText);
            }
        });
    }

    // Handle full form submission
    $('#formFeedback').on('submit', function(e) {
        e.preventDefault();

        // Show loading on button
        const btnSave = $('#btnSave');
        const btnText = btnSave.html();
        btnSave.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

        // Submit form data
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                btnSave.html(btnText).prop('disabled', false);

                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message || 'Umpan balik berhasil disimpan',
                        showConfirmButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '<?= base_url('dashboard') ?>';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Gagal menyimpan umpan balik'
                    });
                }
            },
            error: function(xhr) {
                btnSave.html(btnText).prop('disabled', false);

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat menyimpan data'
                });
                console.error(xhr.responseText);
            }
        });
    });

    // Check All button
    $('#checkAll').on('click', function() {
        $('input[value="Y"]').prop('checked', true).parent().addClass('active');
        $('input[value="T"]').prop('checked', false).parent().removeClass('active');
        updateProgress();
        autoSave();
    });

    // Uncheck All button (set all to No)
    $('#uncheckAll').on('click', function() {
        $('input[value="Y"]').prop('checked', false).parent().removeClass('active');
        $('input[value="T"]').prop('checked', true).parent().addClass('active');
        updateProgress();
        autoSave();
    });

    // Helper function to show error message
    function showError(message) {
        $('#loadingData').hide();
        $('#formFeedback').hide();

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });

        $('#data-status').html('<i class="fas fa-exclamation-circle text-danger"></i> Gagal memuat data');
    }
});
</script>
<?= $this->endSection(); ?>
