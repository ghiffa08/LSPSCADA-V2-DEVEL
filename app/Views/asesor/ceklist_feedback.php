<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-comments text-primary mr-2"></i>FR.AK.05. UMPAN BALIK DAN CATATAN ASESMEN</h4>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info border-left-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-user-tie mr-1"></i>Asesor</h6>
                                    <p class="mb-0"><?= esc($asesor['nama_lengkap']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-bookmark mr-1"></i>Skema Sertifikasi</h6>
                                    <p class="mb-0"><?= esc($skema['nama_skema']) . ' (' . esc($skema['kode_skema']) . ')' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle mr-2"></i>Error</h6>
                        <p><?= esc($error_message) ?></p>
                    </div>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-tasks text-primary mr-1"></i>Pilih Asesmen</label>
                            <select name="id_asesmen" id="id_asesmen" class="form-control select2" required>
                                <option value="">-- Pilih Asesmen --</option>
                                <?php if (!empty($asesmen)): ?>
                                    <?php foreach ($asesmen as $a): ?>
                                        <option value="<?= $a['id_asesmen'] ?>" data-id-skema="<?= $a['id_skema'] ?>">
                                            <?= esc($a['tujuan']) ?>
                                        </option>
                                    <?php endforeach ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-user text-primary mr-1"></i>Pilih Siswa (APL1)</label>
                            <select name="id_apl1" id="id_apl1" class="form-control select2" required disabled>
                                <option value="">-- Pilih Asesmen Dulu --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-calendar-alt text-primary mr-1"></i>Tanggal Mulai Asesmen</label>
                            <input type="date" class="form-control" name="tanggal_mulai" id="tanggal_mulai" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-calendar-check text-primary mr-1"></i>Tanggal Selesai Asesmen</label>
                            <input type="date" class="form-control" name="tanggal_selesai" id="tanggal_selesai" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>


                <div id="loadingData" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>
                    <p class="mt-2">Memuat komponen feedback...</p>
                </div>
                <div id="initialInstructions" class="alert alert-info text-center py-4">
                    <p class="mb-0">Silakan pilih <strong>Asesmen</strong> dan <strong>Siswa</strong> untuk memulai pengisian form feedback.</p>
                </div>
                <div id="emptyDataMessage" class="alert alert-warning text-center py-4" style="display: none;">
                    <p class="mb-0">Belum ada siswa (APL1) yang terdaftar untuk asesmen yang dipilih.</p>
                </div>

                <form id="formFeedback" style="display: none;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_feedback" id="form_id_feedback">
                    <input type="hidden" name="id_skema" id="form_id_skema">
                    <input type="hidden" name="id_asesi" id="form_id_asesi">
                    <div id="feedbackContainer" class="mb-4">
                    </div>

                    <div class="form-group">
                        <label for="catatan_lain" class="font-weight-bold">Catatan / Komentar Lainnya:</label>
                        <textarea name="catatan_lain" id="catatan_lain" rows="4" class="form-control" placeholder="Tuliskan catatan tambahan jika ada..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success" id="btnSave">
                            <i class="fas fa-save mr-1"></i> Simpan Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('js') ?>
<?= $this->include('asesor/utility/ceklist-feedback-js') ?>
<?= $this->endSection(); ?>