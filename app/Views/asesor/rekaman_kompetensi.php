<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<!-- Informasi Asesor & Skema -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info border-left-info">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="font-weight-bold mb-1">
                        <i class="fas fa-user-tie mr-1"></i>Asesor
                    </h6>
                    <p class="mb-0"><?= esc($asesor['nama_lengkap'] ?? 'N/A') ?></p>
                    <?php if (isset($debug_info)): ?>
                        <small class="text-muted">Debug: User ID = <?= $debug_info['user_id'] ?>, Asesor ID = <?= $debug_info['asesor_id'] ?></small>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <h6 class="font-weight-bold mb-1">
                        <i class="fas fa-bookmark mr-1"></i>Skema Sertifikasi
                    </h6>
                    <p class="mb-0">
                        <?php if (isset($skema) && is_array($skema) && isset($skema['nama_skema']) && isset($skema['kode_skema'])): ?>
                            <?= esc($skema['nama_skema']) ?> (<?= esc($skema['kode_skema']) ?>)
                        <?php else: ?>
                            <?php if (isset($error_message)): ?>
                                <span class="text-danger">Error: <?= esc($error_message) ?></span>
                            <?php else: ?>
                                Tidak ada skema
                            <?php endif; ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="font-weight-bold mb-1">
                        <i class="fas fa-id-badge mr-1"></i>Nomor Registrasi
                    </h6>
                    <p class="mb-0"><?= esc($asesor['nomor_registrasi'] ?? 'N/A') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Error Message Display (Tambahkan jika belum ada) -->
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

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-file-alt text-primary mr-2"></i>FR.AK.02. REKAMAN ASESMEN KOMPETENSI</h4>
                <div>
                    <button class="btn btn-light btn-sm" type="button" data-toggle="collapse" data-target="#collapseInfo">
                        <i class="fas fa-info-circle"></i> Info
                    </button>
                </div>
            </div>

            <div class="collapse" id="collapseInfo">
                <div class="card-body bg-light border-top border-bottom">
                    <div class="alert alert-info mb-0">
                        <h6 class="font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Petunjuk Penggunaan</h6>
                        <ul class="mb-0 pl-3">
                            <li>Pilih asesmen yang akan dinilai dari dropdown.</li>
                            <li>Pilih asesi yang akan direkam asesmen kompetensinya.</li>
                            <li>Tandai metode asesmen yang digunakan untuk setiap unit kompetensi.</li>
                            <li>Berikan rekomendasi hasil asesmen dan catatan.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Pilih Asesmen (SAMA SEPERTI OBSERVASI) -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-tasks text-primary mr-1"></i>Pilih Asesmen</label>
                            <select name="id_asesmen" id="id_asesmen" class="form-control select2" required>
                                <option value="">-- Pilih Asesmen --</option>
                                <?php if (isset($asesmen) && is_array($asesmen) && !empty($asesmen)): ?>
                                    <?php foreach ($asesmen as $a): ?>
                                        <?php if (isset($a['id_asesmen']) && !empty($a['id_asesmen'])): ?>
                                            <option value="<?= $a['id_asesmen'] ?>"
                                                data-id-skema="<?= $a['id_skema'] ?? '' ?>"
                                                data-kode-skema="<?= $a['kode_skema'] ?? '' ?>"
                                                data-nama-skema="<?= $a['nama_skema'] ?? '' ?>">
                                                <?= esc($a['tujuan'] ?? 'Asesmen') ?> - <?= esc($a['nama_skema'] ?? 'Unknown') ?>
                                            </option>
                                        <?php else: ?>
                                            <?php log_message('warning', 'Skipping asesmen data without id_asesmen: ' . json_encode($a)); ?>
                                        <?php endif; ?>
                                    <?php endforeach ?>
                                <?php else: ?>
                                    <option value="" disabled>Tidak ada data asesmen</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-calendar-alt text-primary mr-1"></i>Tanggal Asesmen</label>
                            <input type="date" class="form-control" name="tanggal_asesmen" id="tanggal_asesmen" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <!-- Pilih Asesi (SAMA SEPERTI OBSERVASI) -->
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

                <!-- Progress Bar (SAMA SEPERTI OBSERVASI) -->
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
                <div id="loadingState" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat data rekaman asesmen...</p>
                </div>

                <!-- Initial Instructions -->
                <div id="initialInstructions" class="alert alert-info text-center py-4">
                    <i class="fas fa-info-circle fa-2x text-info mb-3"></i>
                    <h5 class="alert-heading">Instruksi Penggunaan</h5>
                    <p class="mb-2">Untuk memulai rekaman asesmen, silakan ikuti langkah berikut:</p>
                    <ol class="list-unstyled mb-0">
                        <li class="mb-2"><strong>1.</strong> Pilih <strong>Asesmen</strong> dari dropdown di atas</li>
                        <li class="mb-2"><strong>2.</strong> Pilih <strong>Asesi</strong> yang akan dinilai</li>
                        <li class="mb-0"><strong>3.</strong> Rekaman unit kompetensi akan dimuat otomatis</li>
                    </ol>
                </div>

                <!-- Empty Data Message -->
                <div id="emptyDataMessage" class="alert alert-warning text-center py-4" style="display: none;">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                    <h5 class="alert-heading">Belum Ada Data Asesi</h5>
                    <p class="mb-2">Belum ada asesi yang terdaftar untuk asesmen yang dipilih.</p>
                    <p class="mb-0">Pastikan asesi sudah mengajukan permohonan dan statusnya telah disetujui.</p>
                </div>

                <!-- Form Rekaman Asesmen -->
                <form action="<?= base_url('asesor/rekaman-asesmen/store') ?>" method="POST" id="formRekamanAsesmen" style="display: none;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_skema" id="form_id_skema" value="">
                    <input type="hidden" name="id_asesmen" id="form_id_asesmen" value="">
                    <input type="hidden" name="id_asesi" id="form_id_asesi" value="">
                    <input type="hidden" name="id_pengajuan" id="form_id_pengajuan" value="">
                    <input type="hidden" name="tanggal_asesmen" id="form_tanggal_asesmen" value="">

                    <!-- Toolbar Buttons -->
                    <div class="d-flex justify-content-between mb-3">
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" id="checkAllMethods">
                                <i class="fas fa-check-double mr-1"></i> Check Semua
                            </button>
                            <button type="button" class="btn btn-warning" id="uncheckAllMethods">
                                <i class="fas fa-times mr-1"></i> Uncheck Semua
                            </button>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-success" id="btnSave">
                                <i class="fas fa-save mr-1"></i> Selesaikan Rekaman
                            </button>
                            <a href="<?= base_url('asesor/rekaman-asesmen') ?>" class="btn btn-light">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <!-- Rekaman Asesmen Container (HANYA UNTUK TABEL UNIT) -->
                    <div id="rekamanAsesmenContainer">
                        <!-- Dynamic content will be loaded here -->
                    </div>

                    <!-- PERBAIKAN: Rekomendasi dan Catatan - TETAP DI TEMPLATE, JANGAN DI-GENERATE JS -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0 font-weight-bold">
                                <i class="fas fa-clipboard-check text-primary mr-2"></i>
                                Rekomendasi dan Catatan
                                <small class="text-muted ml-2">(Auto-save aktif)</small>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Rekomendasi <span class="text-danger">*</span></label>
                                        <select class="form-control" id="rekomendasi" name="rekomendasi" required>
                                            <option value="">-- Pilih Rekomendasi --</option>
                                            <option value="Kompeten">Kompeten</option>
                                            <option value="Belum Kompeten">Belum Kompeten</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Tindak Lanjut yang Dibutuhkan</label>
                                        <textarea class="form-control" id="tindak_lanjut" name="tindak_lanjut" rows="2" 
                                                  placeholder="Pekerjaan tambahan dan asesmen yang diperlukan untuk mencapai kompetensi"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Komentar/Observasi oleh Asesor</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="3" 
                                          placeholder="Komentar tambahan (opsional)"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-footer bg-white">
                <div class="text-muted text-center">
                    <i class="fas fa-shield-alt mr-1"></i> Pastikan semua data rekaman asesmen telah diisi dengan benar sebelum menyimpan.
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('js'); ?>
<?= $this->include("asesor/utility/rekaman-asesmen-js"); ?>
<?= $this->endSection(); ?>