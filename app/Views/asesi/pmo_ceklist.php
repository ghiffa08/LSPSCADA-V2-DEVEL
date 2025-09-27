<?= $this->extend("layouts/asesi/layout-app") ?>
<?= $this->section("content"); ?>
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-tasks text-primary mr-2"></i>Ceklis PMO (Portfolio Management Office)</h4>
                <a href="<?= site_url('admin/pmo') ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                </a>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info border-left-info">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-user mr-1"></i>Asesi</h6>
                                    <p class="mb-0"><?= esc($pengajuan_data['nama_asesi'] ?? 'N/A') ?> (NIK: <?= esc($pengajuan_data['nik'] ?? 'N/A') ?>)</p>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-bookmark mr-1"></i>Skema Sertifikasi</h6>
                                    <p class="mb-0"><?= esc($pengajuan_data['nama_skema'] ?? 'N/A') ?> (<?= esc($pengajuan_data['kode_skema'] ?? 'N/A') ?>)</p>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-user-tie mr-1"></i>Asesor</h6>
                                    <p class="mb-0"><?= esc($pengajuan_data['nama_asesor'] ?? 'Belum Ditugaskan') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="progress-container" class="card mb-4 border-left-primary">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="font-weight-bold mb-0"><i class="fas fa-chart-line text-primary mr-1"></i> Kemajuan Pengisian</label>
                            <div>
                                <span id="progress-text" class="badge badge-primary px-2 py-1">0%</span>
                                <span id="data-status" class="ml-2 text-nowrap"><i class="fas fa-sync text-muted"></i> Menunggu data...</span>
                            </div>
                        </div>
                        <div class="progress" style="height: 15px;">
                            <div id="progress-bar" class="progress-bar progress-bar-striped" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div id="loadingData" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat daftar pertanyaan PMO...</p>
                </div>

                <form action="<?= site_url('api/pmo/save') ?>" method="POST" id="formPmo" style="display: none;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_pengajuan" value="<?= esc($pengajuan_data['id_pengajuan']) ?>">
                    <input type="hidden" name="id_skema" value="<?= esc($id_skema) ?>">
                    <input type="hidden" name="id_asesor" value="<?= esc($id_asesor) ?>">
                    <input type="hidden" name="id_pmo" id="id_pmo" value="">

                    <div class="form-group row">
                        <label for="tanggal_observasi" class="col-sm-2 col-form-label font-weight-bold">Tanggal</label>
                        <div class="col-sm-4">
                            <input type="date" class="form-control" name="tanggal_observasi" id="tanggal_observasi" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Daftar Pertanyaan</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary btn-sm" id="checkAllPmo">
                                <i class="fas fa-check-double mr-1"></i> Check Semua (Ya)
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" id="uncheckAllPmo">
                                <i class="fas fa-times mr-1"></i> Uncheck Semua
                            </button>
                        </div>
                    </div>


                    <div id="pmoContainer">
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="catatan" class="font-weight-bold">Catatan / Rekomendasi Tambahan</label>
                        <textarea name="catatan" id="catatan" class="form-control" rows="4" placeholder="Masukkan catatan atau rekomendasi jika ada..."></textarea>
                    </div>

                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-success btn-lg" id="btnSave">
                            <i class="fas fa-save mr-1"></i> Simpan Final Ceklis PMO
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('js') ?>
<?= $this->include('asesi/scripts/pmo-scripts-js') ?>
<?= $this->endSection(); ?>