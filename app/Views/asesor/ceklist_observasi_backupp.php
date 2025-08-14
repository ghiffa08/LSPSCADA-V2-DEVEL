<?= $this->extend('layouts/admin/layout-admin'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clipboard-check text-primary mr-2"></i>Ceklist Observasi
        </h1>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= esc($error) ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="fas fa-clipboard-check text-primary mr-2"></i>
                        FR.IA.01. CEKLIS OBSERVASI AKTIVITAS DI TEMPAT KERJA
                    </h4>
                </div>

                <div class="card-body">
                    <!-- Asesor Information -->
                    <div class="alert alert-info border-left-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="font-weight-bold mb-1">
                                    <i class="fas fa-certificate mr-1"></i>Skema Sertifikasi
                                </h6>
                                <p class="mb-0">
                                    <?= isset($skema['nama_skema']) ? esc($skema['nama_skema']) : 'Unknown' ?> 
                                    (<?= isset($skema['kode_skema']) ? esc($skema['kode_skema']) : 'Unknown' ?>)
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="font-weight-bold mb-1">
                                    <i class="fas fa-id-badge mr-1"></i>Nomor Registrasi
                                </h6>
                                <p class="mb-0"><?= isset($asesor['nomor_registrasi']) ? esc($asesor['nomor_registrasi']) : 'Tidak ada' ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Observasi Form -->
                    <form id="form-observasi" action="<?= base_url('ceklist-observasi/store') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="row mb-4">
                            <!-- Asesmen Selection -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold" for="id_asesmen">
                                        <i class="fas fa-tasks text-primary mr-1"></i>Pilih Asesmen
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="id_asesmen" id="id_asesmen" class="form-control select2" required>
                                        <option value="">-- Pilih Asesmen --</option>
                                        <?php if (!empty($asesmen) && is_array($asesmen)): ?>
                                            <?php foreach ($asesmen as $a): ?>
                                                <option value="<?= esc($a['id_asesmen']) ?>"
                                                    data-id-skema="<?= esc($a['id_skema']) ?>"
                                                    data-kode-skema="<?= esc($a['kode_skema'] ?? '') ?>"
                                                    data-nama-skema="<?= esc($a['nama_skema'] ?? '') ?>">
                                                    <?= esc($a['tujuan'] ?? 'Unknown') ?> - <?= esc($a['nama_skema'] ?? 'Unknown') ?>
                                                </option>
                                            <?php endforeach ?>
                                        <?php else: ?>
                                            <option value="" disabled>Tidak ada asesmen tersedia</option>
                                        <?php endif; ?>
                                    </select>
                                    
                                    <?php if (empty($asesmen)): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Belum ada asesmen untuk skema <?= isset($skema['nama_skema']) ? esc($skema['nama_skema']) : 'ini' ?>. 
                                            Silakan hubungi administrator.
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Tanggal Observasi -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold" for="tanggal_observasi">
                                        <i class="fas fa-calendar text-primary mr-1"></i>Tanggal Observasi
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tanggal_observasi" id="tanggal_observasi" 
                                           class="form-control" value="<?= date('Y-m-d') ?>" 
                                           min="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Fields (Optional) -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold" for="id_asesi">
                                        <i class="fas fa-user text-primary mr-1"></i>ID Asesi
                                    </label>
                                    <input type="text" name="id_asesi" id="id_asesi" 
                                           class="form-control" placeholder="Masukkan ID Asesi (opsional)">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold" for="id_pengajuan">
                                        <i class="fas fa-file-alt text-primary mr-1"></i>ID Pengajuan
                                    </label>
                                    <input type="number" name="id_pengajuan" id="id_pengajuan" 
                                           class="form-control" placeholder="Masukkan ID Pengajuan (opsional)">
                                </div>
                            </div>
                        </div>

                        <!-- TUK and Set Tanggal (if available) -->
                        <?php if (!empty($tuk) || !empty($set_tanggal)): ?>
                        <div class="row mb-4">
                            <?php if (!empty($tuk)): ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold" for="id_tuk">
                                        <i class="fas fa-building text-primary mr-1"></i>TUK
                                    </label>
                                    <select name="id_tuk" id="id_tuk" class="form-control select2">
                                        <option value="">-- Pilih TUK --</option>
                                        <?php foreach ($tuk as $t): ?>
                                            <option value="<?= esc($t['id_tuk']) ?>">
                                                <?= esc($t['nama_tuk']) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($set_tanggal)): ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold" for="id_set_tanggal">
                                        <i class="fas fa-calendar-alt text-primary mr-1"></i>Periode Asesmen
                                    </label>
                                    <select name="id_set_tanggal" id="id_set_tanggal" class="form-control select2">
                                        <option value="">-- Pilih Periode --</option>
                                        <?php foreach ($set_tanggal as $st): ?>
                                            <option value="<?= esc($st['id_set_tanggal']) ?>">
                                                <?= esc(date('d/m/Y', strtotime($st['tanggal_mulai']))) ?> - 
                                                <?= esc(date('d/m/Y', strtotime($st['tanggal_selesai']))) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Form Actions -->
                        <div class="text-right">
                            <button type="button" class="btn btn-secondary mr-2" onclick="resetForm()">
                                <i class="fas fa-undo mr-2"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary" id="btn-submit">
                                <i class="fas fa-save mr-2"></i>Mulai Observasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    // Initialize Select2 with Bootstrap theme
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder');
        }
    });

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    $('#tanggal_observasi').attr('min', today);

    // Form validation and submission
    $('#form-observasi').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $('#btn-submit');
        const originalText = submitBtn.html();
        
        // Validate required fields
        if (!validateForm()) {
            return false;
        }
        
        // Show loading state
        submitBtn.prop('disabled', true)
                 .html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        if (response.data && response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    showError(response.message || 'Terjadi kesalahan');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'Terjadi kesalahan sistem';
                
                if (response) {
                    if (response.errors) {
                        // Show validation errors
                        const errors = Object.values(response.errors).join('<br>');
                        errorMessage = errors;
                    } else if (response.message) {
                        errorMessage = response.message;
                    }
                }
                
                showError(errorMessage);
            },
            complete: function() {
                // Restore button state
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Real-time validation
    $('select[required], input[required]').on('change blur', function() {
        validateField($(this));
    });
});

/**
 * Validate entire form
 */
function validateForm() {
    let isValid = true;
    
    // Check required fields
    $('select[required], input[required]').each(function() {
        if (!validateField($(this))) {
            isValid = false;
        }
    });
    
    // Custom validation for date
    const tanggalObservasi = $('#tanggal_observasi').val();
    if (tanggalObservasi && new Date(tanggalObservasi) < new Date()) {
        showFieldError($('#tanggal_observasi'), 'Tanggal observasi tidak boleh di masa lalu');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Validate individual field
 */
function validateField($field) {
    const value = $field.val();
    const fieldName = $field.attr('name');
    
    // Remove previous error state
    $field.removeClass('is-invalid');
    $field.siblings('.invalid-feedback').remove();
    
    if ($field.prop('required') && (!value || value.trim() === '')) {
        showFieldError($field, `${getFieldLabel($field)} harus diisi`);
        return false;
    }
    
    // Add success state
    $field.removeClass('is-invalid').addClass('is-valid');
    return true;
}

/**
 * Show field error
 */
function showFieldError($field, message) {
    $field.addClass('is-invalid');
    
    if (!$field.siblings('.invalid-feedback').length) {
        $field.after(`<div class="invalid-feedback">${message}</div>`);
    }
}

/**
 * Get field label
 */
function getFieldLabel($field) {
    const label = $(`label[for="${$field.attr('id')}"], label[for="${$field.attr('name')}"]`).first();
    return label.length ? label.text().replace('*', '').trim() : $field.attr('name');
}

/**
 * Show error message
 */
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        html: message,
        confirmButtonText: 'OK'
    });
}

/**
 * Reset form
 */
function resetForm() {
    Swal.fire({
        title: 'Reset Form?',
        text: 'Semua data yang telah diisi akan hilang',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Reset!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#form-observasi')[0].reset();
            $('.select2').val(null).trigger('change');
            $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
            $('.invalid-feedback').remove();
            
            // Set tanggal to today
            $('#tanggal_observasi').val('<?= date('Y-m-d') ?>');
            
            Swal.fire({
                icon: 'success',
                title: 'Form direset!',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}
</script>

<?= $this->endSection(); ?>
