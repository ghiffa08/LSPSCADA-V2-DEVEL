<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>
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
                            <li>Lengkapi informasi asesi dan skema terlebih dahulu.</li>
                            <li>Pilih metode asesmen yang telah dilakukan untuk setiap unit kompetensi.</li>
                            <li>Berikan rekomendasi hasil asesmen berdasarkan penilaian seluruh unit kompetensi.</li>
                            <li>Pastikan semua data terisi dengan benar sebelum menyimpan.</li>
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
                                    <option value="<?= $s['id_skema'] ?>" data-id-asesmen="<?= $s['id_asesmen'] ?>" data-kode-skema="<?= $s['kode_skema'] ?>"><?= $s['nama_skema'] ?></option>
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
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-calendar-alt text-primary mr-1"></i>Tanggal Asesmen</label>
                            <input type="date" class="form-control" name="tanggal_asesmen" id="tanggal_asesmen" value="<?= date('Y-m-d') ?>">
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
                <div id="loadingState" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat data rekaman asesmen...</p>
                </div>

                <!-- Form Rekaman Asesmen -->
                <form action="<?= base_url('asesor/rekaman-asesmen/store') ?>" method="POST" id="formRekamanAsesmen" style="display: none;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_skema" id="form_id_skema" value="">
                    <input type="hidden" name="id_asesmen" id="form_id_asesmen" value="">
                    <input type="hidden" name="id_asesi" id="form_id_asesi" value="">
                    <input type="hidden" name="id_apl1" id="form_id_apl1" value="">
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

                    <!-- Rekaman Asesmen Container -->
                    <div id="rekamanAsesmenContainer">
                        <!-- Dynamic content will be loaded here -->
                    </div>

                    <!-- Rekomendasi dan Catatan -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0 font-weight-bold">
                                <i class="fas fa-clipboard-check text-primary mr-2"></i>
                                Rekomendasi dan Catatan
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Rekomendasi <span class="text-danger">*</span></label>
                                        <select class="form-control" id="rekomendasi" name="rekomendasi" required>
                                            <option value="">-- Pilih Rekomendasi --</option>
                                            <option value="kompeten">Kompeten</option>
                                            <option value="belum_kompeten">Belum Kompeten</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Tindak Lanjut yang Dibutuhkan</label>
                                        <textarea class="form-control" id="tindak_lanjut" name="tindak_lanjut" rows="2" placeholder="Pekerjaan tambahan dan asesmen yang diperlukan untuk mencapai kompetensi"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Komentar/Observasi oleh Asesor</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Komentar tambahan (opsional)"></textarea>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Data Status -->
                <div class="text-center mt-3">
                    <small id="data-status-bottom" class="text-muted">
                        <i class="fas fa-sync text-muted"></i> Menunggu data...
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
<?= $this->section('js'); ?>
<!-- Include JavaScript -->
<?= $this->include("asesor/utility/rekaman-asesmen-js"); ?>

<?= $this->endSection(); ?>