<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-comment-dots text-primary mr-2"></i>FR.IA.06. UMPAN BALIK DARI ASESI</h4>
                <div>
                    <button class="btn btn-info" type="button" data-toggle="collapse" data-target="#collapseInfo">
                        <i class="fas fa-info-circle"></i> Info
                    </button>
                </div>
            </div>

            <div class="collapse" id="collapseInfo">
                <div class="card-body bg-light border-top border-bottom">
                    <div class="alert alert-info mb-0">
                        <h6 class="font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Petunjuk Penggunaan</h6>
                        <ul class="mb-0 pl-3">
                            <li>Pengisian umpan balik ini akan disimpan secara otomatis saat Anda melakukan perubahan.</li>
                            <li>Tombol <strong>Simpan</strong> hanya diperlukan untuk finalisasi semua perubahan.</li>
                            <li>Pilih jawaban "Ya" atau "Tidak" untuk setiap pernyataan umpan balik.</li>
                            <li>Tambahkan komentar jika diperlukan.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Informasi Asesi & Skema -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-bookmark text-primary mr-1"></i>Nama Skema</label>
                            <select name="id_skema" id="id_skema" class="form-control select2" required>
                                <option value="">-- Pilih Skema --</option>
                                <?php foreach ($skema as $s): ?>
                                    <option value="<?= $s['id_skema'] ?>" data-id-asesmen="<?= $s['id_asesmen'] ?>"><?= $s['nama_skema'] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-hashtag text-primary mr-1"></i>Nomor Skema</label>
                            <input type="text" class="form-control bg-light" id="kode_skema" value="" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-user text-primary mr-1"></i>Nama Asesi</label>
                            <select name="id_asesi" id="id_asesi" class="form-control select2" required disabled>
                                <option value="">-- Pilih Skema Terlebih Dahulu --</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-calendar-alt text-primary mr-1"></i>Tanggal Mulai</label>
                            <input type="date" class="form-control" name="tanggal_mulai" id="tanggal_mulai" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-calendar-check text-primary mr-1"></i>Tanggal Selesai</label>
                            <input type="date" class="form-control" name="tanggal_selesai" id="tanggal_selesai" value="<?= date('Y-m-d') ?>">
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
                <form action="<?= base_url('/asesor/feedback/save') ?>" method="POST" id="formFeedback" style="display: none;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_feedback" id="form_id_feedback" value="">
                    <input type="hidden" name="id_asesmen" id="form_id_asesmen" value="">
                    <input type="hidden" name="id_skema" id="form_id_skema" value="">
                    <input type="hidden" name="id_asesi" id="form_id_asesi" value="">
                    <input type="hidden" name="tanggal_mulai" id="form_tanggal_mulai" value="<?= date('Y-m-d') ?>">
                    <input type="hidden" name="tanggal_selesai" id="form_tanggal_selesai" value="<?= date('Y-m-d') ?>">

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
                            <a href="<?= base_url('asesmen') ?>" class="btn btn-light">
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
                            <textarea name="catatan_lain" id="catatan_lain" class="form-control" rows="4" placeholder="Masukkan catatan tambahan jika ada..."></textarea>
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
    // Initialize select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Variables
    let feedbackData = {};
    let selectedSkema = '';
    let selectedAsesi = '';
    let selectedFeedback = '';
    let totalKomponen = 0;
    let komponen = [];

    // Date input change handler
    $('#tanggal_mulai, #tanggal_selesai').on('change', function() {
        const id = $(this).attr('id');
        const formId = 'form_' + id;
        const value = $(this).val();
        $('#' + formId).val(value);

        // If both dates are filled and we have skema and asesi, try to find existing feedback
        if ($('#tanggal_mulai').val() && $('#tanggal_selesai').val() && selectedSkema && selectedAsesi) {
            loadFeedbackData();
        }
    });

    // Skema change handler
    $('#id_skema').on('change', function() {
        selectedSkema = $(this).val();
        const idAsesmen = $(this).find(':selected').data('id-asesmen');

        // Update form values
        $('#form_id_skema').val(selectedSkema);
        $('#form_id_asesmen').val(idAsesmen);

        if (selectedSkema) {
            // Show kode skema
            $.ajax({
                url: '<?= base_url('api/feedback-asesi/get-skema-details') ?>',
                type: 'GET',
                data: {
                    id_skema: selectedSkema
                },
                success: function(response) {
                    if (response.success) {
                        // Update kode skema
                        $('#kode_skema').val(response.skema.kode_skema);

                        // Enable and populate asesi dropdown
                        $('#id_asesi').prop('disabled', false).empty().append('<option value="">-- Pilih Asesi --</option>');

                        if (response.asesi && response.asesi.length > 0) {
                            response.asesi.forEach(function(asesi) {
                                $('#id_asesi').append(`<option value="${asesi.id_asesi}">${asesi.nama_lengkap}</option>`);
                            });
                        } else {
                            $('#id_asesi').append('<option value="" disabled>Tidak ada asesi tersedia untuk skema ini</option>');
                        }

                        // Refresh select2
                        $('#id_asesi').trigger('change').select2();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Gagal memuat data skema'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat memuat data skema'
                    });
                    console.error(xhr.responseText);
                }
            });
        } else {
            // Reset form
            $('#kode_skema').val('');
            $('#id_asesi').prop('disabled', true).empty().append('<option value="">-- Pilih Skema Terlebih Dahulu --</option>');
            $('#id_asesi').select2();
        }
    });

    // Asesi change handler
    $('#id_asesi').on('change', function() {
        selectedAsesi = $(this).val();
        $('#form_id_asesi').val(selectedAsesi);

        if (selectedAsesi && selectedSkema) {
            loadFeedbackData();
        }
    });

    // Load feedback data
    function loadFeedbackData() {
        // Show loading indicator
        $('#loadingData').show();
        $('#formFeedback').hide();
        $('#progress-container').show();
        $('#data-status').html('<i class="fas fa-sync fa-spin text-primary"></i> Memuat data...');

        // Get komponen list first
        $.ajax({
            url: '<?= base_url('api/feedback-asesi/get-komponen') ?>',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    komponen = response.komponen;
                    totalKomponen = komponen.length;

                    // Check for existing feedback data
                    checkExistingFeedback();
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
            data: {
                id_asesi: selectedAsesi,
                id_skema: selectedSkema,
                tanggal_mulai: $('#tanggal_mulai').val(),
                tanggal_selesai: $('#tanggal_selesai').val()
            },
            success: function(response) {
                if (response.success) {
                    if (response.exists) {
                        selectedFeedback = response.id_feedback;
                        $('#form_id_feedback').val(selectedFeedback);

                        // Load existing feedback data
                        loadExistingFeedback(selectedFeedback);
                    } else {
                        // No existing feedback, create new form
                        renderFeedbackForm({}, {});
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

    // Load existing feedback if available
    function loadExistingFeedback(id_feedback) {
        $.ajax({
            url: '<?= base_url('api/feedback-asesi/load-feedback') ?>',
            type: 'GET',
            data: {
                id_feedback: id_feedback
            },
            success: function(response) {
                if (response.success) {
                    // Update form with existing data
                    const feedback = response.feedback;
                    $('#catatan_lain').val(feedback.catatan_lain || '');

                    // Render feedback form with existing data
                    renderFeedbackForm(komponen, response.existing_data);
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

    // Render feedback form
    function renderFeedbackForm(komponenList, existingData) {
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
                <tr>
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
                        <textarea class="form-control feedback-komentar" name="komentar[${itemId}]" rows="2" placeholder="Komentar (opsional)">${existingKomentar}</textarea>
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

    // Auto-save functionality
    function autoSave() {
        const formData = $('#formFeedback').serialize();

        // Don't save if no skema or asesi selected
        if (!selectedSkema || !selectedAsesi) {
            return;
        }

        $.ajax({
            url: '<?= base_url('api/feedback-asesi/save') ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Update feedback ID if it's a new record
                    if (response.result && response.result.id_feedback) {
                        selectedFeedback = response.result.id_feedback;
                        $('#form_id_feedback').val(selectedFeedback);
                    }
                } else {
                    console.error('Auto-save failed:', response.message);
                }
            },
            error: function(xhr) {
                console.error('Auto-save error:', xhr.responseText);
            }
        });
    }

    // Handle form submission
    $('#formFeedback').on('submit', function(e) {
        e.preventDefault();

        // Show loading
        const btnSave = $('#btnSave');
        const btnText = btnSave.html();
        btnSave.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

        // Submit form data
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                btnSave.html(btnText).prop('disabled', false);

                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        showConfirmButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '<?= base_url('asesmen') ?>';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
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
