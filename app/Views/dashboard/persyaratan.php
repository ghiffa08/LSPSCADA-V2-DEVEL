<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Persyaratan</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addSyaratModal">
                        Tambah Syarat
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-2" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Skema</th>
                                <th>Keterangan Bukti</th>
                                <th>Persyaratan</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = null;
                            foreach ($listPersyaratan as $value) {
                                $no++;
                            ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><?= $value['nama_skema'] ?></td>
                                    <td><?= $value['keterangan_bukti'] ?></td>
                                    <td><?= $value['syarat'] ?></td>
                                    <td>
                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editSyaratModal-<?= $value['id_syarat']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteSyaratModal-<?= $value['id_syarat']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<!-- Modals Section -->
<?= $this->section("modals") ?>
<!-- Add-persyaratan-Modal -->
<form action="<?= site_url('/store-persyaratan'); ?>" method="POST">
    <div class="modal fade" id="addSyaratModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Persyaratan</h5>
                    <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <p class="text-muted">Masukan Skema, Keterangan dan Persyaratan.</p>
                    <div class="form-group">
                        <label>Skema</label>
                        <select class="form-control select2 <?php if (session('errors.id_skema')) : ?>is-invalid<?php endif ?>" name="id_skema">
                            <option value="">Pilih Skema</option>
                            <?php
                            foreach ($listSkema as $row) {
                                echo '<option value="' . $row['id_skema'] . '">' . $row['nama_skema'] . '</option>';
                            }
                            ?>
                        </select>
                        <?php if (session('errors.id_skema')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.id_skema') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label>Keterangan Bukti</label>
                        <select class="form-control select2 <?php if (session('errors.keterangan_bukti')) : ?>is-invalid<?php endif ?>" name="keterangan_bukti">
                            <option value="">Pilih Keterangan bukti</option>
                            <option value="Bukti Kelengkapan Dasar">Bukti Kelengkapan Dasar</option>
                            <option value="Bukti Kelengkapan Kompetensi">Bukti Kelengkapan Kompetensi</option>
                        </select>
                        <?php if (session('errors.keterangan_bukti')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.keterangan_bukti') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Persyaratan</label>
                        <textarea class="form-control summernote-simple <?php if (session('errors.persyaratan')) : ?>is-invalid<?php endif ?>" name="persyaratan"></textarea>
                        <?php if (session('errors.persyaratan')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.persyaratan') ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        Simpan
                    </button>
                </div>

            </div>
        </div>
    </div>
</form>
<!-- edit-persyaratan-Modal -->
<?php foreach ($listPersyaratan as $key => $value) { ?>
    <form action="<?= site_url('/update-persyaratan'); ?>" method="POST">
        <div class="modal fade" id="editSyaratModal-<?= $value['id_syarat'] ?>" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Persyaratan</h5>
                        <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?= csrf_field(); ?>
                        <p class="text-muted">Perbarui Skema, Keterangan dan Persyaratan.</p>
                        <input type="hidden" name="edit_id" value="<?= $value['id_syarat'] ?>">
                        <div class="form-group">
                            <label>Skema</label>
                            <select class="form-control select2 <?php if (session('errors.edit_id_skema')) : ?>is-invalid<?php endif ?>" name="edit_id_skema">
                                <option value="">Pilih Skema</option>
                                <?php
                                foreach ($listSkema as $row) {

                                    $value['id_skema'] == $row['id_skema'] ? $pilih = 'selected' : $pilih = null;

                                    echo '<option  ' . $pilih . ' value="' . $row['id_skema'] . '">' . $row['nama_skema'] . '</option>';
                                }
                                ?>
                            </select>
                            <?php if (session('errors.edit_id_skema')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_id_skema') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label>Keterangan Bukti</label>
                            <select class="form-control select2 <?php if (session('errors.edit_keterangan_bukti')) : ?>is-invalid<?php endif ?>" name="edit_keterangan_bukti">
                                <option value="">Pilih Keterangan bukti</option>
                                <option value="Bukti Kelengkapan Dasar" <?= ($value['keterangan_bukti'] == "Bukti Kelengkapan Dasar") ? "selected" : ""; ?>>Bukti Kelengkapan Dasar</option>
                                <option value="Bukti Kelengkapan Kompetensi" <?= ($value['keterangan_bukti'] == "Bukti Kelengkapan Kompetensi") ? "selected" : ""; ?>>Bukti Kelengkapan Kompetensi</option>
                            </select>
                            <?php if (session('errors.edit_keterangan_bukti')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_keterangan_bukti') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Persyaratan</label>
                            <textarea class="form-control summernote-simple <?php if (session('errors.edit_persyaratan')) : ?>is-invalid<?php endif ?>" name="edit_persyaratan"><?= $value['syarat'] ?></textarea>
                            <?php if (session('errors.edit_persyaratan')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_persyaratan') ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            Update
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>
<?php } ?>

<!-- Delete-Group-Modal -->
<?php foreach ($listPersyaratan as $row => $value) { ?>
    <form action="<?= site_url('/delete-persyaratan'); ?>" method="post">
        <div class="modal fade" id="deleteSyaratModal-<?= $value['id_syarat']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Persyaratan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <h5>Apakah anda yakin akan menghapus <span class="text-danger font-weight-bold">"<?= $value['syarat']; ?>"</span>?</h5>
                        <input type="hidden" name="id" value="<?= $value['id_syarat']; ?>">

                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="submit" class="btn btn-danger btn-lg btn-block">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php } ?>
<?= $this->endSection() ?>