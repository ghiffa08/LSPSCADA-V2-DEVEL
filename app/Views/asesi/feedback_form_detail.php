<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-comment-dots text-primary mr-2"></i>FR.AK.03. FORMULIR UMPAN BALIK</h4>
                <div>
                    <a href="<?= base_url('asesi/feedback') ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="card-body">
                <?php if (session()->has('success')) : ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= session('success'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('error')) : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= session('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('asesi/feedback/submit') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_asesmen" value="<?= $asesmen['id_asesmen'] ?>">
                    <input type="hidden" name="id_asesor" value="<?= $asesmen['id_asesor'] ?>">

                    <!-- Section 1: Identity Information -->
                    <div class="card mb-4 border-left-primary">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 font-weight-bold text-primary">Informasi Asesmen</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold"><i class="fas fa-user text-primary mr-1"></i>Nama Asesi</label>
                                        <input type="text" class="form-control bg-light" value="<?= esc($asesmen['nama_asesi']) ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold"><i class="fas fa-user-tie text-primary mr-1"></i>Nama Asesor</label>
                                        <input type="text" class="form-control bg-light" value="<?= esc($asesmen['nama_asesor']) ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold"><i class="fas fa-calendar-alt text-primary mr-1"></i>Tanggal Mulai</label>
                                        <input type="date" name="tanggal_mulai" class="form-control bg-light" value="<?= $asesmen['tanggal_mulai'] ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold"><i class="fas fa-calendar-check text-primary mr-1"></i>Tanggal Selesai</label>
                                        <input type="date" name="tanggal_selesai" class="form-control bg-light" value="<?= $asesmen['tanggal_selesai'] ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold"><i class="fas fa-award text-primary mr-1"></i>Skema Sertifikasi</label>
                                        <input type="text" class="form-control bg-light" value="<?= esc($asesmen['nama_skema']) ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Feedback Statements -->
                    <div class="card mb-4 border-left-primary">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 font-weight-bold text-primary">Pernyataan Umpan Balik</h5>
                            <p class="text-muted small mb-0">Mohon berikan jawaban dengan jujur sesuai pengalaman Anda selama proses asesmen</p>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i> Untuk setiap pernyataan, pilih <strong>Ya</strong> atau <strong>Tidak</strong> dan berikan komentar jika diperlukan
                            </div>

                            <!-- Statement 1 -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">1. Saya mendapatkan penjelasan yang cukup tentang proses asesmen</h6>
                                    <div class="form-group mb-2">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_1_ya" name="pernyataan_1" value="Ya" class="custom-control-input" <?= isset($existing['pernyataan_1']) && $existing['pernyataan_1'] == 'Ya' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_1_ya">Ya</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_1_tidak" name="pernyataan_1" value="Tidak" class="custom-control-input" <?= isset($existing['pernyataan_1']) && $existing['pernyataan_1'] == 'Tidak' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_1_tidak">Tidak</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="komentar_1" class="small">Komentar (opsional):</label>
                                        <textarea name="komentar_1" id="komentar_1" class="form-control" rows="2"><?= isset($existing['komentar_1']) ? esc($existing['komentar_1']) : '' ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Statement 2 -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">2. Asesor memberikan kesempatan untuk mendiskusikan/menegosiasikan metode asesmen dan jadwal asesmen</h6>
                                    <div class="form-group mb-2">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_2_ya" name="pernyataan_2" value="Ya" class="custom-control-input" <?= isset($existing['pernyataan_2']) && $existing['pernyataan_2'] == 'Ya' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_2_ya">Ya</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_2_tidak" name="pernyataan_2" value="Tidak" class="custom-control-input" <?= isset($existing['pernyataan_2']) && $existing['pernyataan_2'] == 'Tidak' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_2_tidak">Tidak</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="komentar_2" class="small">Komentar (opsional):</label>
                                        <textarea name="komentar_2" id="komentar_2" class="form-control" rows="2"><?= isset($existing['komentar_2']) ? esc($existing['komentar_2']) : '' ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Statement 3 -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">3. Asesor menggunakan bahasa yang mudah dimengerti</h6>
                                    <div class="form-group mb-2">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_3_ya" name="pernyataan_3" value="Ya" class="custom-control-input" <?= isset($existing['pernyataan_3']) && $existing['pernyataan_3'] == 'Ya' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_3_ya">Ya</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_3_tidak" name="pernyataan_3" value="Tidak" class="custom-control-input" <?= isset($existing['pernyataan_3']) && $existing['pernyataan_3'] == 'Tidak' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_3_tidak">Tidak</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="komentar_3" class="small">Komentar (opsional):</label>
                                        <textarea name="komentar_3" id="komentar_3" class="form-control" rows="2"><?= isset($existing['komentar_3']) ? esc($existing['komentar_3']) : '' ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Statement 4 -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">4. Asesor bersikap profesional, ramah, dan sabar selama proses asesmen</h6>
                                    <div class="form-group mb-2">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_4_ya" name="pernyataan_4" value="Ya" class="custom-control-input" <?= isset($existing['pernyataan_4']) && $existing['pernyataan_4'] == 'Ya' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_4_ya">Ya</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_4_tidak" name="pernyataan_4" value="Tidak" class="custom-control-input" <?= isset($existing['pernyataan_4']) && $existing['pernyataan_4'] == 'Tidak' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_4_tidak">Tidak</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="komentar_4" class="small">Komentar (opsional):</label>
                                        <textarea name="komentar_4" id="komentar_4" class="form-control" rows="2"><?= isset($existing['komentar_4']) ? esc($existing['komentar_4']) : '' ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Statement 5 -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">5. Asesor memberikan pertanyaan yang jelas dan mudah dimengerti</h6>
                                    <div class="form-group mb-2">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_5_ya" name="pernyataan_5" value="Ya" class="custom-control-input" <?= isset($existing['pernyataan_5']) && $existing['pernyataan_5'] == 'Ya' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_5_ya">Ya</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_5_tidak" name="pernyataan_5" value="Tidak" class="custom-control-input" <?= isset($existing['pernyataan_5']) && $existing['pernyataan_5'] == 'Tidak' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_5_tidak">Tidak</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="komentar_5" class="small">Komentar (opsional):</label>
                                        <textarea name="komentar_5" id="komentar_5" class="form-control" rows="2"><?= isset($existing['komentar_5']) ? esc($existing['komentar_5']) : '' ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Statement 6 -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">6. Asesor memberikan umpan balik yang membantu dan informatif</h6>
                                    <div class="form-group mb-2">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_6_ya" name="pernyataan_6" value="Ya" class="custom-control-input" <?= isset($existing['pernyataan_6']) && $existing['pernyataan_6'] == 'Ya' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_6_ya">Ya</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_6_tidak" name="pernyataan_6" value="Tidak" class="custom-control-input" <?= isset($existing['pernyataan_6']) && $existing['pernyataan_6'] == 'Tidak' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_6_tidak">Tidak</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="komentar_6" class="small">Komentar (opsional):</label>
                                        <textarea name="komentar_6" id="komentar_6" class="form-control" rows="2"><?= isset($existing['komentar_6']) ? esc($existing['komentar_6']) : '' ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Statement 7 -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">7. Asesor memberikan kesempatan kepada saya untuk mendemonstrasikan kompetensi yang saya miliki</h6>
                                    <div class="form-group mb-2">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_7_ya" name="pernyataan_7" value="Ya" class="custom-control-input" <?= isset($existing['pernyataan_7']) && $existing['pernyataan_7'] == 'Ya' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_7_ya">Ya</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_7_tidak" name="pernyataan_7" value="Tidak" class="custom-control-input" <?= isset($existing['pernyataan_7']) && $existing['pernyataan_7'] == 'Tidak' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_7_tidak">Tidak</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="komentar_7" class="small">Komentar (opsional):</label>
                                        <textarea name="komentar_7" id="komentar_7" class="form-control" rows="2"><?= isset($existing['komentar_7']) ? esc($existing['komentar_7']) : '' ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Statement 8 -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">8. Asesor menjelaskan dengan jelas tentang keputusan asesmen</h6>
                                    <div class="form-group mb-2">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_8_ya" name="pernyataan_8" value="Ya" class="custom-control-input" <?= isset($existing['pernyataan_8']) && $existing['pernyataan_8'] == 'Ya' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_8_ya">Ya</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_8_tidak" name="pernyataan_8" value="Tidak" class="custom-control-input" <?= isset($existing['pernyataan_8']) && $existing['pernyataan_8'] == 'Tidak' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_8_tidak">Tidak</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="komentar_8" class="small">Komentar (opsional):</label>
                                        <textarea name="komentar_8" id="komentar_8" class="form-control" rows="2"><?= isset($existing['komentar_8']) ? esc($existing['komentar_8']) : '' ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Statement 9 -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">9. Fasilitas dan peralatan yang digunakan selama asesmen memadai</h6>
                                    <div class="form-group mb-2">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_9_ya" name="pernyataan_9" value="Ya" class="custom-control-input" <?= isset($existing['pernyataan_9']) && $existing['pernyataan_9'] == 'Ya' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_9_ya">Ya</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_9_tidak" name="pernyataan_9" value="Tidak" class="custom-control-input" <?= isset($existing['pernyataan_9']) && $existing['pernyataan_9'] == 'Tidak' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_9_tidak">Tidak</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="komentar_9" class="small">Komentar (opsional):</label>
                                        <textarea name="komentar_9" id="komentar_9" class="form-control" rows="2"><?= isset($existing['komentar_9']) ? esc($existing['komentar_9']) : '' ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Statement 10 -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">10. Waktu yang diberikan untuk proses asesmen sudah cukup</h6>
                                    <div class="form-group mb-2">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_10_ya" name="pernyataan_10" value="Ya" class="custom-control-input" <?= isset($existing['pernyataan_10']) && $existing['pernyataan_10'] == 'Ya' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_10_ya">Ya</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="pernyataan_10_tidak" name="pernyataan_10" value="Tidak" class="custom-control-input" <?= isset($existing['pernyataan_10']) && $existing['pernyataan_10'] == 'Tidak' ? 'checked' : '' ?> required>
                                            <label class="custom-control-label" for="pernyataan_10_tidak">Tidak</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="komentar_10" class="small">Komentar (opsional):</label>
                                        <textarea name="komentar_10" id="komentar_10" class="form-control" rows="2"><?= isset($existing['komentar_10']) ? esc($existing['komentar_10']) : '' ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Additional Comments -->
                    <div class="card mb-4 border-left-primary">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 font-weight-bold text-primary">Komentar Tambahan</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label for="komentar_tambahan">Silahkan berikan komentar atau saran tambahan untuk perbaikan proses asesmen (opsional)</label>
                                <textarea name="komentar_tambahan" id="komentar_tambahan" class="form-control" rows="4"><?= isset($existing['komentar_tambahan']) ? esc($existing['komentar_tambahan']) : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-save mr-1"></i> Simpan Umpan Balik
                        </button>
                    </div>
                </form>
            </div>

            <div class="card-footer bg-white">
                <div class="text-muted text-center">
                    <small><i class="fas fa-info-circle mr-1"></i> Terima kasih atas umpan balik yang Anda berikan, kami akan menggunakan masukan ini untuk peningkatan kualitas proses asesmen</small>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
