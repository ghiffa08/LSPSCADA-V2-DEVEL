<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-edit text-primary mr-2"></i>Form Rekaman Asesmen Kompetensi</h4>
                <div>
                    <button class="btn btn-light btn-sm" type="button" data-toggle="collapse" data-target="#collapseInfo">
                        <i class="fas fa-info-circle"></i> Petunjuk
                    </button>
                </div>
            </div>

            <div class="collapse" id="collapseInfo">
                <div class="card-body bg-light border-top border-bottom">
                    <div class="alert alert-info mb-0">
                        <h6 class="font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Petunjuk Penggunaan</h6>
                        <ul class="mb-0 pl-3">
                            <li>Pilih asesmen yang akan dinilai dari dropdown.</li>
                            <li>Pilih APL1 (asesi) yang akan direkam asesmen kompetensinya.</li>
                            <li>Perubahan akan disimpan secara otomatis dengan notifikasi di pojok kanan atas.</li>
                            <li>Tombol "Simpan & Finalisasi" digunakan untuk mengunci rekaman.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info border-left-info">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-user-tie mr-1"></i>Asesor</h6>
                                    <p class="mb-0"><?= esc($asesor['nama_lengkap'] ?? 'N/A') ?></p>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-bookmark mr-1"></i>Skema Sertifikasi</h6>
                                    <p class="mb-0" id="info-skema-text">
                                        <?php if (isset($skema) && is_array($skema)) : ?>
                                            <?= esc($skema['nama_skema']) ?> (<?= esc($skema['kode_skema']) ?>)
                                        <?php else : ?>
                                            Pilih Asesmen
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-id-badge mr-1"></i>Nomor Registrasi</h6>
                                    <p class="mb-0"><?= esc($asesor['nomor_registrasi'] ?? 'N/A') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-tasks text-primary mr-1"></i>Pilih Asesmen</label>
                            <select name="id_asesmen" id="id_asesmen" class="form-control select2" required>
                                <option value="">-- Pilih Asesmen --</option>
                                <?php if (isset($asesmen) && is_array($asesmen) && !empty($asesmen)) : ?>
                                    <?php foreach ($asesmen as $a) : ?>
                                        <?php if (isset($a['id_asesmen']) && !empty($a['id_asesmen'])) : ?>
                                            <option value="<?= $a['id_asesmen'] ?>" data-id-skema="<?= $a['id_skema'] ?? '' ?>" data-kode-skema="<?= $a['kode_skema'] ?? '' ?>" data-nama-skema="<?= $a['nama_skema'] ?? '' ?>">
                                                <?= esc($a['tujuan'] ?? 'Asesmen') ?> - <?= esc($a['nama_skema'] ?? 'Unknown') ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach ?>
                                <?php else : ?>
                                    <option value="" disabled>Tidak ada data asesmen</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-user text-primary mr-1"></i>Nama Asesi (APL1)</label>
                            <select name="id_apl1" id="id_apl1" class="form-control select2" required disabled>
                                <option value="">-- Pilih Asesmen Terlebih Dahulu --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="apl1-info" class="row mb-4" style="display: none;">
                    <div class="col-12">
                        <div class="alert alert-light border-left-primary">
                            <div class="row">
                                <div class="col-md-3">
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-user-circle mr-1"></i>Nama</h6>
                                    <p class="mb-0" id="info-nama">-</p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-id-card mr-1"></i>NIK</h6>
                                    <p class="mb-0" id="info-nik">-</p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-envelope mr-1"></i>Email</h6>
                                    <p class="mb-0" id="info-email">-</p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-check-circle mr-1"></i>Status Validasi</h6>
                                    <p class="mb-0" id="info-validasi">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="loadingState" class="text-center py-5" style="display: none;"><div class="spinner-border text-primary" role="status"></div><p class="mt-3 text-muted">Memuat struktur rekaman...</p></div>
                <div id="initialInstructions" class="alert alert-info text-center py-4"><i class="fas fa-info-circle fa-2x text-info mb-3"></i><h5 class="alert-heading">Mulai Merekam Asesmen</h5><p class="mb-0">Silakan pilih asesmen dan asesi untuk memulai.</p></div>
                <div id="emptyDataMessage" class="alert alert-warning text-center py-4" style="display: none;"><i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i><h5 class="alert-heading">Belum Ada Data APL1</h5><p class="mb-0">Belum ada asesi yang divalidasi untuk asesmen yang dipilih.</p></div>

                <form action="<?= base_url('asesor/rekaman-asesmen/store') ?>" method="POST" id="formRekamanAsesmen" style="display: none;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_skema" id="form_id_skema" value="">
                    <input type="hidden" name="id_asesmen" id="form_id_asesmen" value="">
                    <input type="hidden" name="id_apl1" id="form_id_apl1" value="">
                    <input type="hidden" name="tanggal_rekaman" id="tanggal_rekaman" value="<?= date('Y-m-d') ?>">

                    <div class="d-flex justify-content-between mb-3">
                        <div class="btn-group"><button type="button" class="btn btn-primary" id="checkAllMethod"><i class="fas fa-check-double mr-1"></i> Aktifkan Semua</button><button type="button" class="btn btn-warning" id="clearAllMethod"><i class="fas fa-times-circle mr-1"></i> Kosongkan Semua</button></div>
                        <div class="btn-group"><button type="submit" class="btn btn-success" id="btnSave"><i class="fas fa-save mr-1"></i> Simpan & Finalisasi</button></div>
                    </div>

                    <div id="rekamanAsesmenContainer" class="mt-4"></div>

                    <div class="card mt-4 border-top">
                        <div class="card-header"><h5 class="mb-0"><i class="fas fa-clipboard-check mr-2"></i>Rekomendasi dan Tindak Lanjut</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6"><div class="form-group"><label class="font-weight-bold">Rekomendasi <span class="text-danger">*</span></label><div class="selectgroup selectgroup-pills"><label class="selectgroup-item"><input type="radio" name="rekomendasi" value="kompeten" class="selectgroup-input"><span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-check"></i> Kompeten</span></label><label class="selectgroup-item"><input type="radio" name="rekomendasi" value="belum_kompeten" class="selectgroup-input"><span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-times"></i> Belum Kompeten</span></label></div></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="font-weight-bold">Tindak Lanjut</label><textarea class="form-control" name="tindak_lanjut" rows="3" placeholder="Tindak lanjut yang diperlukan..."></textarea></div></div>
                            </div>
                            <div class="row"><div class="col-12"><div class="form-group mb-0"><label class="font-weight-bold">Komentar</label><textarea class="form-control" name="komentar" rows="3" placeholder="Komentar atau catatan tambahan..."></textarea></div></div></div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-footer bg-white"><div class="text-muted text-center"><i class="fas fa-shield-alt mr-1"></i> Pastikan semua data rekaman asesmen telah diisi dengan benar.</div></div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('js'); ?>
<?= $this->include("asesor/utility/rekaman-asesmen-js"); ?>
<?= $this->endSection(); ?>
