<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('pesan')) : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('pesan'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php elseif (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="section-body">
    <!-- Header Card dengan Info Asesor -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4><i class="fas fa-question-circle mr-2"></i>FR.IA.03. PERTANYAAN UNTUK MENDUKUNG OBSERVASI</h4>
                    <div class="card-header-action">
                        <span class="badge badge-info">
                            <i class="fas fa-user-tie mr-1"></i>
                            <?= esc($asesor['nama_lengkap'] ?? 'Asesor tidak ditemukan') ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Mobile-friendly info display -->
                    <div class="row">
                        <div class="col-md-6 col-12 mb-3">
                            <h6 class="font-weight-bold mb-1">
                                <i class="fas fa-certificate mr-1 text-primary"></i>Skema Sertifikasi
                            </h6>
                            <p class="mb-0 text-muted">
                                <?php if (isset($skema) && is_array($skema)): ?>
                                    <span class="d-block"><?= esc($skema['nama_skema'] ?? 'N/A') ?></span>
                                    <small class="text-info">(<?= esc($skema['kode_skema'] ?? 'N/A') ?>)</small>
                                <?php else: ?>
                                    <span class="text-muted">Skema belum ditetapkan</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <h6 class="font-weight-bold mb-1">
                                <i class="fas fa-id-badge mr-1 text-success"></i>Nomor Registrasi
                            </h6>
                            <p class="mb-0 text-muted">
                                <?= esc($asesor['nomor_registrasi'] ?? 'Belum terdaftar') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Selection Form Card - PERBAIKAN: SAMA SEPERTI OBSERVASI -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-cog mr-2"></i>Pengaturan PMO</h4>
                </div>
                <div class="card-body">
                    <!-- Error Message Display -->
                    <?php if (isset($error_message)): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-danger">
                                    <h6><i class="fas fa-exclamation-triangle mr-2"></i>Error</h6>
                                    <p><?= esc($error_message) ?></p>
                                    <small>Silakan hubungi administrator atau coba refresh halaman.</small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Pilih Asesmen - SAMA SEPERTI OBSERVASI -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold"><i class="fas fa-tasks text-primary mr-1"></i>Pilih Asesmen</label>
                                <select name="id_asesmen" id="id_asesmen" class="form-control select2" required>
                                    <option value="">-- Pilih Asesmen --</option>
                                    <?php if (isset($asesmen) && is_array($asesmen) && !empty($asesmen)): ?>
                                        <?php foreach ($asesmen as $a): ?>
                                            <option value="<?= esc($a['id_asesmen']) ?>"
                                                    data-id-skema="<?= esc($a['id_skema']) ?>"
                                                    data-nama-skema="<?= esc($a['nama_skema']) ?>"
                                                    data-kode-skema="<?= esc($a['kode_skema']) ?>">
                                                <?= esc($a['tujuan']) ?>
                                            </option>
                                        <?php endforeach ?>
                                    <?php else: ?>
                                        <option value="" disabled>Tidak ada data asesmen</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold"><i class="fas fa-calendar-alt text-primary mr-1"></i>Tanggal PMO</label>
                                <input type="date" class="form-control" name="tanggal_pmo" id="tanggal_pmo" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Pilih Asesi - SAMA SEPERTI OBSERVASI -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold"><i class="fas fa-user text-primary mr-1"></i>Nama Asesi</label>
                                <select name="id_asesi" id="id_asesi" class="form-control select2" required disabled>
                                    <option value="">-- Pilih Asesmen Terlebih Dahulu --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold"><i class="fas fa-hashtag text-primary mr-1"></i>Kode Skema</label>
                                <input type="text" class="form-control bg-light" id="kode_skema" value="" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons - Mobile friendly -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="mb-2 mb-sm-0 w-100 w-sm-auto">
                                    <button type="button" id="btnMuatPMO" class="btn btn-primary btn-lg w-100 w-sm-auto" disabled>
                                        <i class="fas fa-download mr-1"></i>
                                        <span class="d-none d-sm-inline">Muat </span>PMO
                                    </button>
                                </div>
                                <div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Pilih asesmen dan asesi untuk memulai
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Initial Instructions - SAMA SEPERTI OBSERVASI -->
    <div id="initialInstructions" class="alert alert-info text-center py-4">
        <i class="fas fa-info-circle fa-2x text-info mb-3"></i>
        <h5 class="alert-heading">Instruksi Penggunaan</h5>
        <p class="mb-2">Untuk memulai PMO, silakan ikuti langkah berikut:</p>
        <ol class="list-unstyled mb-0">
            <li class="mb-2"><strong>1.</strong> Pilih <strong>Asesmen</strong> dari dropdown di atas</li>
            <li class="mb-2"><strong>2.</strong> Pilih <strong>Asesi</strong> yang akan dinilai</li>
            <li class="mb-0"><strong>3.</strong> PMO akan dimuat otomatis</li>
        </ol>
    </div>

    <!-- Empty Data Message - SAMA SEPERTI OBSERVASI -->
    <div id="emptyDataMessage" class="alert alert-warning text-center py-4" style="display: none;">
        <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
        <h5 class="alert-heading">Belum Ada Data Asesi</h5>
        <p class="mb-2">Belum ada asesi yang terdaftar untuk asesmen yang dipilih.</p>
        <p class="mb-0">Pastikan asesi sudah mengajukan permohonan dan statusnya telah disetujui.</p>
    </div>

    <!-- Progress Card -->
    <div class="row" id="progressCard" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
                        <div class="mb-2 mb-sm-0">
                            <h6 class="mb-1"><i class="fas fa-chart-pie mr-1"></i>Progress PMO</h6>
                            <small class="text-muted">Total pertanyaan yang telah dijawab</small>
                        </div>
                        <div class="text-center">
                            <div class="progress-circle" style="position: relative; display: inline-block;">
                                <canvas id="progressChart" width="60" height="60"></canvas>
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                    <span id="progressPercent" class="font-weight-bold text-primary">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form PMO - Mobile Optimized -->
    <div class="row" id="pmoForm" style="display: none;">
        <div class="col-12">
            <form id="formPMO">
                <?= csrf_field() ?>
                <input type="hidden" name="id_skema" id="form_id_skema" value="">
                <input type="hidden" name="id_asesmen" id="form_id_asesmen" value="">
                <input type="hidden" name="id_asesi" id="form_id_asesi" value="">
                <input type="hidden" name="id_pengajuan" id="form_id_pengajuan" value="">
                <input type="hidden" name="tanggal_pmo" id="form_tanggal_pmo" value="">

                <!-- Pertanyaan PMO Card -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-question-circle mr-2"></i>Pertanyaan Mendukung Observasi</h4>
                        <div class="card-header-action">
                            <!-- Mobile-friendly bulk actions -->
                            <div class="btn-group" role="group">
                                <button type="button" id="markAllYa" class="btn btn-sm btn-success" title="Tandai Semua YA">
                                    <i class="fas fa-check-double"></i>
                                    <span class="d-none d-md-inline ml-1">Semua YA</span>
                                </button>
                                <button type="button" id="markAllTidak" class="btn btn-sm btn-warning" title="Tandai Semua TIDAK">
                                    <i class="fas fa-times"></i>
                                    <span class="d-none d-md-inline ml-1">Semua TIDAK</span>
                                </button>
                                <button type="button" id="clearAll" class="btn btn-sm btn-secondary" title="Reset Semua">
                                    <i class="fas fa-undo"></i>
                                    <span class="d-none d-md-inline ml-1">Reset</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Petunjuk:</strong> Jawab pertanyaan berikut dengan memilih "YA" atau "TIDAK" berdasarkan observasi yang dilakukan.
                        </div>

                        <!-- Mobile-optimized questions container -->
                        <div id="questionContainer">
                            <!-- Dynamic questions will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Catatan Asesor Card -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-clipboard-list mr-2"></i>Catatan Asesor</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="catatan_asesor" class="form-label font-weight-bold">
                                    <i class="fas fa-comment-alt mr-1"></i>Catatan dan Observasi Asesor
                                </label>
                                <textarea class="form-control" id="catatan_asesor" name="catatan_asesor" rows="4"
                                    placeholder="Masukkan catatan tambahan atau observasi khusus dari asesor"></textarea>
                            </div>
                        </div>

                        <!-- Action Buttons - Mobile Friendly -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                    <div class="mb-2 mb-sm-0 w-100 w-sm-auto">
                                        <button type="submit" class="btn btn-success btn-lg w-100 w-sm-auto" id="btnSimpan">
                                            <i class="fas fa-save mr-1"></i>Simpan PMO
                                        </button>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-warning" id="btnGeneratePDF" style="display: none;">
                                            <i class="fas fa-file-pdf mr-1"></i>
                                            <span class="d-none d-sm-inline">Generate </span>PDF
                                        </button>
                                        <a href="<?= base_url('asesor/pmo') ?>" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left mr-1"></i>
                                            <span class="d-none d-sm-inline">Kembali</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none;">
    <div class="loading-content">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <p class="mt-2">Memuat data...</p>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- Mobile-friendly styles using Bootstrap 4 -->
<style>
    /* Loading Overlay */
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .loading-content {
        text-align: center;
        color: white;
    }

    /* Question cards mobile-friendly */
    .question-card {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }

    .question-card:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .question-header {
        background-color: #f8f9fa;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #dee2e6;
        border-radius: 0.375rem 0.375rem 0 0;
    }

    .question-body {
        padding: 1rem;
    }

    /* Mobile-friendly radio buttons */
    .answer-options {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 0.75rem;
    }

    .answer-option {
        flex: 1;
        min-width: 120px;
    }

    .custom-radio .custom-control-label {
        padding-left: 0.5rem;
        cursor: pointer;
        font-weight: 500;
    }

    .custom-radio .custom-control-input:checked~.custom-control-label::before {
        background-color: #007bff;
        border-color: #007bff;
    }

    /* Unit group styling */
    .unit-group {
        margin-bottom: 2rem;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .unit-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem;
        font-weight: 600;
    }

    .unit-content {
        padding: 1rem;
        background-color: #f8f9fa;
    }

    /* Progress indicator */
    .progress-circle {
        position: relative;
        display: inline-block;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .card-header h4 {
            font-size: 1rem;
        }

        .card-header-action .btn {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .form-control {
            font-size: 14px;
        }

        .btn {
            font-size: 14px;
        }

        .question-header {
            padding: 0.5rem;
        }

        .question-body {
            padding: 0.75rem;
        }

        .answer-options {
            flex-direction: column;
            gap: 0.5rem;
        }

        .answer-option {
            min-width: auto;
        }
    }

    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }

        .card {
            margin-bottom: 1rem;
        }

        .card-body {
            padding: 1rem;
        }

        .btn-group {
            flex-direction: column;
            width: 100%;
        }

        .btn-group .btn {
            border-radius: 0.25rem !important;
            margin-bottom: 0.25rem;
        }
    }

    /* Touch-friendly interactive elements */
    .btn,
    .form-control,
    select,
    .custom-control-label {
        min-height: 44px;
        /* iOS recommended touch target size */
    }

    /* Alert responsiveness */
    .alert {
        font-size: 14px;
    }

    @media (max-width: 576px) {
        .alert {
            font-size: 13px;
            padding: 0.5rem;
        }
    }

    /* Empty state styling */
    .empty-state img {
        max-width: 120px;
        opacity: 0.6;
    }

    /* Gap utility for older browsers */
    .gap-2>*+* {
        margin-left: 0.5rem;
    }

    @media (max-width: 576px) {
        .gap-2 {
            flex-direction: column;
        }

        .gap-2>*+* {
            margin-left: 0;
            margin-top: 0.5rem;
        }
    }

    /* Auto-save indicator */
    .auto-save-indicator {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #28a745;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .auto-save-indicator.show {
        opacity: 1;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Include the JavaScript -->
<?= $this->include('asesor/utility/pmo-js') ?>
<?= $this->endSection() ?>