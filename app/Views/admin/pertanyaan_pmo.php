<?= $this->extend("layouts/admin/layout-admin"); ?>

<!-- Content Section -->
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Tabel <?= $siteTitle ?></h4>
                <div class="card-header-action">
                    <div class="btn-group">
                        <!-- Tombol Tambah -->
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addPmoPertanyaanModal">
                            <i class="fas fa-plus mr-1"></i> Tambah Pertanyaan
                        </button>

                        <!-- Tombol Import -->
                        <button class="btn btn-primary" data-toggle="modal" data-target="#importExcelModal">
                            <i class="fas fa-upload"></i> Import Excel
                        </button>

                        <!-- Tombol Export (jika ada) -->
                        <!-- <a href="<?= site_url('/export-pmo-pertanyaan') ?>" class="btn btn-primary">
                            <i class="fas fa-download mr-1"></i> Export Excel
                        </a> -->
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="table-pmo-pertanyaan" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 5%">No</th>
                            <th>ID</th>
                            <th>Skema</th>
                            <th>Pertanyaan</th>
                            <th>Jenis Jawaban</th>
                            <th>Aktif</th>
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
<!-- End Content Section -->

<!-- Modals Section -->
<?= $this->section("modals") ?>

<?= form_open(site_url('/master/pmo-pertanyaan/save'), ['id' => 'add-pmo-pertanyaan-form']); ?>
<div class="modal fade" id="addPmoPertanyaanModal" data-backdrop="static" tabindex="-1" aria-labelledby="pmoPertanyaanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pmoPertanyaanModalLabel">Tambah Pertanyaan PMO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Input tersembunyi untuk ID (digunakan saat edit) -->
                <input type="hidden" name="id_pertanyaan" id="id_pertanyaan" value="">

                <div class="form-group">
                    <label for="id_skema">Skema<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_skema" id="id_skema" required data-placeholder="-- Pilih Skema --">
                        <option value=""></option>
                        <?php foreach ($listSkema as $skema): ?>
                            <option value="<?= $skema['id_skema'] ?>"><?= esc($skema['nama_skema']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="id_unit">Unit Kompetensi<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_unit" id="id_unit" required disabled data-placeholder="-- Pilih Unit --">
                        <option value=""></option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="id_elemen">Elemen Kompetensi<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_elemen" id="id_elemen" required disabled data-placeholder="-- Pilih Elemen --">
                        <option value=""></option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="id_kuk">Kriteria Unjuk Kerja (KUK)<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_kuk" id="id_kuk" required disabled data-placeholder="-- Pilih KUK --">
                        <option value=""></option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="pertanyaan">Pertanyaan<span class="text-danger">*</span></label>
                    <textarea class="form-control" name="pertanyaan" id="pertanyaan" rows="3" required></textarea>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="jenis_jawaban">Jenis Jawaban<span class="text-danger">*</span></label>
                            <select class="form-control select2" name="jenis_jawaban" id="jenis_jawaban" required>
                                <option value="YA_TIDAK">Ya / Tidak</option>
                                <option value="PILIHAN_GANDA">Pilihan Ganda</option>
                                <option value="ESSAY">Essay</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="urutan">Urutan</label>
                            <input type="number" name="urutan" class="form-control" id="urutan" value="0">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="aktif">Status</label>
                            <select class="form-control select2" name="aktif" id="aktif">
                                <option value="Y">Aktif</option>
                                <option value="N">Tidak Aktif</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>

                <!-- Area untuk Pilihan Ganda (disembunyikan secara default) -->
                <div id="pilihan-ganda-container" style="display: none;">
                    <hr>
                    <h6>Opsi Pilihan Ganda</h6>
                    <div id="pilihan-ganda-inputs">
                        <!-- Input dinamis akan ditambahkan di sini -->
                    </div>
                    <button type="button" class="btn btn-sm btn-success mt-2" id="add-pilihan-btn">
                        <i class="fas fa-plus"></i> Tambah Opsi
                    </button>
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
<!-- End Modals Section -->

<!-- Script Section -->
<?= $this->section('js') ?>

<!-- PMO Pertanyaan JS -->
<?= $this->include('admin/scripts/pmo-pertanyaan-js') ?>
<!-- End PMO Pertanyaan JS -->

<?= $this->endSection() ?>
<!-- End Script Section -->