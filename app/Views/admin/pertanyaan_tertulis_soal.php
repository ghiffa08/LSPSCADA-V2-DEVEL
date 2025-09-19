<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Tabel <?= $siteTitle ?></h4>
                <div class="card-header-action">
                    <div class="btn-group">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addSoalModal">
                            <i class="fas fa-plus mr-1"></i> Tambah Soal
                        </button>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#importExcelModal">
                            <i class="fas fa-upload"></i> Import Excel
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="table-soal" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 5%">No</th>
                            <th>ID</th>
                            <th>Skema</th>
                            <th>Soal</th>
                            <th>Jenis</th>
                            <th style="width: 10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data tabel akan dimuat secara dinamis -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<!-- Modals Section -->
<?= $this->section("modals") ?>
<?= form_open(site_url('/master/pertanyaan-tertulis-soal/save'), ['id' => 'add-soal-form']); ?>
<div class="modal fade" id="addSoalModal" data-backdrop="static" tabindex="-1" aria-labelledby="soalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="soalModalLabel">Tambah Soal Tertulis</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_soal" id="id_soal" value="">

                <div class="form-group">
                    <label for="id_skema">Skema<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_skema" id="id_skema" required data-placeholder="-- Pilih Skema --">
                        <option value=""></option>
                        <?php foreach ($listSkema as $skema) : ?>
                            <option value="<?= $skema['id_skema'] ?>"><?= esc($skema['nama_skema']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="soal">Teks Soal<span class="text-danger">*</span></label>
                    <!-- Textarea ini akan diubah menjadi Editor Summernote oleh JavaScript -->
                    <textarea name="soal" id="soal" class="summernote"></textarea>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="jenis_soal">Jenis Soal<span class="text-danger">*</span></label>
                            <select class="form-control select2" name="jenis_soal" id="jenis_soal" required>
                                <option value="PILIHAN_GANDA">Pilihan Ganda</option>
                                <option value="ESSAY">Essay</option>
                                <option value="BENAR_SALAH">Benar / Salah</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="urutan">Urutan</label>
                            <input type="number" name="urutan" class="form-control" id="urutan" value="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="aktif">Status</label>
                            <select class="form-control select2" name="aktif" id="aktif">
                                <option value="Y">Aktif</option>
                                <option value="N">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- OPSI DINAMIS: PILIHAN GANDA -->
                <div id="container-pilihan-ganda" style="display: none;">
                    <hr>
                    <h6>Opsi Pilihan Ganda <small>(Pilih satu jawaban yang benar)</small></h6>
                    <div id="pilihan-ganda-inputs">
                        <!-- Input dinamis akan ditambahkan di sini -->
                    </div>
                    <button type="button" class="btn btn-sm btn-success mt-2" id="add-pilihan-btn">
                        <i class="fas fa-plus"></i> Tambah Opsi
                    </button>
                </div>

                <!-- OPSI DINAMIS: BENAR/SALAH -->
                <div id="container-benar-salah" style="display: none;">
                    <hr>
                    <h6>Kunci Jawaban Benar/Salah</h6>
                    <div class="form-group">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="jawaban_benar" name="jawaban_benar_salah" class="custom-control-input" value="Y" checked>
                            <label class="custom-control-label" for="jawaban_benar">Benar</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="jawaban_salah" name="jawaban_benar_salah" class="custom-control-input" value="N">
                            <label class="custom-control-label" for="jawaban_salah">Salah</label>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-whitesmoke">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= form_close(); ?>


<?= $this->endSection() ?>

<!-- Script Section -->
<?= $this->section('js') ?>

<!-- Script JS untuk Bank Soal -->
<?= $this->include('admin/scripts/pertanyaan-tertulis-soal-js') ?>

<?= $this->endSection() ?>