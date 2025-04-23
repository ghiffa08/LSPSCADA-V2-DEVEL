<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Tempat Uji Kompetensi</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addTUKModal">
                        Tambah TUK
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-2" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama TUK</th>
                                <th>Jenis TUK</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = null;
                            foreach ($listTUK as $value) {
                                $no++;
                            ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><?= $value['nama_tuk'] ?></td>
                                    <td><?= $value['jenis_tuk'] ?></td>
                                    <td>
                                        <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                            <button type="button" class="btn btn-icon btn-info" data-toggle="modal" data-target="#editTUKModal-<?= $value['id_tuk']; ?>"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-icon btn-danger" data-toggle="modal" data-target="#deleteTUKModal-<?= $value['id_tuk']; ?>"><i class="fas fa-trash"></i></button>
                                        </div>
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

<!-- Add-Group-Modal -->
<form id="setting-form" action="<?= site_url('/store-tuk'); ?>" method="POST">
    <div class="modal fade" id="addTUKModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tempat Uji Kompetensi</h5>
                    <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <p class="text-muted">Masukan Nama Tempat Uji Kompetensi(TUK).</p>
                    <div class="form-group">
                        <label class="form-label">Nama Tempat Uji Kompetensi</label>
                        <input type="text" name="nama" class="form-control <?php if (session('errors.nama')) : ?>is-invalid<?php endif ?>" placeholder="Nama Tempat TUK">
                        <?php if (session('errors.nama')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.nama') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label>Jenis TUK</label>
                        <select class="form-control <?php if (session('errors.jenis_tuk')) : ?>is-invalid<?php endif ?> select2" name="jenis_tuk">
                            <option value="">Pilih Jenis TUK</option>
                            <option value="Sewaktu">Sewaktu</option>
                            <option value="Tempat Kerja">Tempat Kerja</option>
                            <option value="Mandiri">Mandiri</option>
                        </select>
                        <?php if (session('errors.jenis_tuk')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.jenis_tuk') ?>
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

<!-- Edit-Group-Modal -->
<?php foreach ($listTUK as $row => $value) { ?>
    <form id="setting-form" action="<?= site_url('/update-tuk'); ?>" method="POST">
        <div class="modal fade" id="editTUKModal-<?= $value['id_tuk']; ?>" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Tempat Uji Kompetensi</h5>
                        <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?= csrf_field(); ?>
                        <p class="text-muted">Perbarui Tempat Uji Kompetensi.</p>
                        <div class="form-group">
                            <label class="form-label">Nama Tempat Uji Kompetensi</label>
                            <input type="hidden" name="edit_id" value="<?= $value['id_tuk']; ?>">
                            <input type="text" name="edit_nama" class="form-control <?php if (session('errors.edit_nama')) : ?>is-invalid<?php endif ?>" value="<?= $value['nama_tuk']; ?>">
                            <?php if (session('errors.edit_nama')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_nama') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label>Jenis TUK</label>
                            <select class="form-control <?php if (session('errors.edit_jenis_tuk')) : ?>is-invalid<?php endif ?> select2" name="edit_jenis_tuk">
                                <option value="">Pilih Jenis TUK</option>
                                <option value="Sewaktu" <?= ($value['jenis_tuk'] == "Sewaktu") ? 'selected' : '' ?>>Sewaktu</option>
                                <option value="Tempat Kerja" <?= ($value['jenis_tuk'] == "Tempat Kerja") ? 'selected' : '' ?>>Tempat Kerja</option>
                                <option value="Mandiri" <?= ($value['jenis_tuk'] == "Mandiri") ? 'selected' : '' ?>>Mandiri</option>
                            </select>
                            <?php if (session('errors.edit_jenis_tuk')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_jenis_tuk') ?>
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

<!-- delete-Group-Modal -->
<?php foreach ($listTUK as $row => $value) { ?>
    <form action="<?= site_url('/delete-tuk'); ?>" method="post">
        <div class="modal fade" id="deleteTUKModal-<?= $value['id_tuk']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Tempat Uji Kompetensi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <h5>Apakah anda yakin akan menghapus <span class="text-danger font-weight-bold">"<?= $value['nama_tuk']; ?>"</span>?</h5>
                        <input type="hidden" name="id" value="<?= $value['id_tuk']; ?>">

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