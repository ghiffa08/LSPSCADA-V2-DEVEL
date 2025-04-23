<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Unit</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addUnitModal">
                        Tambah Unit
                    </button>

                    <div class="dropdown d-inline">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Import Exel
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item has-icon" href="<?= base_url('Contoh Exel/CONTOH UNIT LSP SCADA.xlsx') ?>"><i class="fas fa-file"></i> Contoh Exel</a>
                            <a class="dropdown-item has-icon" type="button" data-toggle="modal" data-target="#importExelModal"><i class="fas fa-upload"></i> Import Exel</a>

                        </div>
                    </div>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addSkemaModal">
                        Export Exel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-2" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Skema</th>
                                <th>ID Unit</th>
                                <th>Kode Unit</th>
                                <th>Nama Unit</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = null;
                            foreach ($listUnit as $value) {
                                $no++;
                            ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><?= $value['id_skema'] ?></td>
                                    <td><?= $value['id_unit'] ?></td>
                                    <td><?= $value['kode_unit'] ?></td>
                                    <td><?= $value['nama_unit'] ?></td>
                                    <td><?= $value['keterangan'] ?></td>
                                    <td>
                                        <div class="badge badge-<?= $value['status'] == "Y" ? "success" : "danger" ?>"><?= $value['status'] == "Y" ? "Aktif" : "Tidak Aktif" ?></div>
                                    </td>
                                    <td>
                                        <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                            <button type="button" class="btn btn-icon btn-info" data-toggle="modal" data-target="#editUnitModal-<?= $value['id_unit']; ?>"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-icon btn-danger" data-toggle="modal" data-target="#deleteUnitModal-<?= $value['id_unit']; ?>"><i class="fas fa-trash"></i></button>
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
<form id="setting-form" action="<?= site_url('/store-unit'); ?>" method="POST">
    <div class="modal fade" id="addUnitModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Unit</h5>
                    <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <p class="text-muted">Masukan ID Skema, Kode Unit, Nama Unit, Keterangan dan Status</p>
                    <div class="form-group">
                        <label>Skema<span class="text-danger">*</span></label>
                        <select class="form-control select2 <?php if (session('errors.id_skema')) : ?>is-invalid<?php endif ?>" name="id_skema" id="id_skema">
                            <option value="">Pilih Skema</option>
                            <?php

                            if (isset($listSkema)) {
                                foreach ($listSkema as $row) {
                                    echo '<option   value="' . $row['id_skema'] . '">' . $row['nama_skema'] . '</option>';
                                }
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
                        <label class="form-label">Kode Unit<span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control <?php if (session('errors.kode')) : ?>is-invalid<?php endif ?>" id="inputKode">
                        <?php if (session('errors.kode')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.kode') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Unit<span class="text-danger">*</span></label>
                        <textarea class="form-control  <?php if (session('errors.nama')) : ?>is-invalid<?php endif ?>" name="nama"></textarea>
                        <?php if (session('errors.nama')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.nama') ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="form-group">
                        <label>Keterangan<span class="text-danger">*</span></label>
                        <textarea class="form-control  <?php if (session('errors.keterangan')) : ?>is-invalid<?php endif ?>" name="keterangan" id="inputDescription"></textarea>
                        <?php if (session('errors.keterangan')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.keterangan') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status<span class="text-danger">*</span></label>
                        <div class="selectgroup w-100">
                            <label class="selectgroup-item">
                                <input type="radio" name="status" class="selectgroup-input" value="Y" checked="">
                                <span class="selectgroup-button">Aktif</span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="status" class="selectgroup-input" value="N">
                                <span class="selectgroup-button">Tidak Aktif</span>
                            </label>
                        </div>
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

<!-- Add-Exel-Modal -->
<form id="setting-form" action="<?= site_url('/import-unit'); ?>" method="POST" enctype="multipart/form-data">
    <div class="modal fade" id="importExelModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Unit</h5>
                    <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <p class="text-muted">Pilih File Exel dengan tipe Xls/Xlsx dan sesuaikan format seperti pada contoh.</p>
                    <div class="form-group">
                        <label class="form-label">File Exel<span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input <?php if (session('errors.file_exel')) : ?>is-invalid<?php endif ?>" id="customFile" name="file_exel">
                            <label class="custom-file-label" for="customFile">Pilih file</label>
                            <?php if (session('errors.file_exel')) { ?>
                                <div class="invalid-feedback mt-3">
                                    <?= session('errors.file_exel') ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        Import
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Edit-Group-Modal -->
<?php foreach ($listUnit as $row => $value) { ?>
    <form id="setting-form" action="<?= site_url('/update-unit'); ?>" method="POST">
        <div class="modal fade" id="editUnitModal-<?= $value['id_unit'] ?>" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Unit</h5>
                        <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?= csrf_field(); ?>
                        <p class="text-muted">Perbarui ID Skema, Kode Unit, Nama Unit, Keterangan dan Status</p>
                        <div class="form-group">
                            <label>Skema<span class="text-danger">*</span></label>
                            <select class="form-control <?php if (session('errors.edit_id_skema')) : ?>is-invalid<?php endif ?> select2" name="edit_id_skema">
                                <option value="">Pilih Skema</option>
                                <?php

                                if (isset($listSkema)) {
                                    foreach ($listSkema as $row) {
                                        if ($row['status'] == "Y") {
                                            $value['id_skema'] == $row['id_skema'] ? $pilih = 'selected' : $pilih = null;

                                            echo '<option  ' . $pilih . ' value="' . $row['id_skema'] . '">' . $row['nama_skema'] . '</option>';
                                        }
                                    }
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
                            <label class="form-label">Kode Unit<span class="text-danger">*</span></label>
                            <input type="hidden" name="edit_id" value="<?= $value['id_unit'] ?>">
                            <input type="text" name="edit_kode" class="form-control <?php if (session('errors.edit_kode')) : ?>is-invalid<?php endif ?>" value="<?= $value['kode_unit'] ?>">
                            <?php if (session('errors.edit_kode')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_kode') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama Unit<span class="text-danger">*</span></label>
                            <textarea class="form-control  <?php if (session('errors.edit_nama')) : ?>is-invalid<?php endif ?>" name="edit_nama"><?= $value['nama_unit'] ?></textarea>
                            <?php if (session('errors.edit_nama')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_nama') ?>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="form-group">
                            <label>Keterangan<span class="text-danger">*</span></label>
                            <textarea class="form-control  <?php if (session('errors.edit_keterangan')) : ?>is-invalid<?php endif ?>" name="edit_keterangan" id="inputDescription"><?= $value['keterangan'] ?></textarea>
                            <?php if (session('errors.edit_keterangan')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_keterangan') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status<span class="text-danger">*</span></label>
                            <div class="selectgroup w-100">
                                <label class="selectgroup-item">
                                    <input type="radio" name="edit_status" class="selectgroup-input" value="Y" <?= $value['status'] == "Y" ? "checked" : "" ?>>
                                    <span class="selectgroup-button">Aktif</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="edit_status" class="selectgroup-input" value="N" <?= $value['status'] == "N" ? "checked" : "" ?>>
                                    <span class="selectgroup-button">Tidak Aktif</span>
                                </label>
                            </div>
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
<?php foreach ($listUnit as $row => $value) { ?>
    <form action="<?= site_url('/delete-unit'); ?>" method="post">
        <div class="modal fade" id="deleteUnitModal-<?= $value['id_unit']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Unit</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <h5>Apakah anda yakin akan menghapus <span class="text-danger font-weight-bold">"<?= $value['kode_unit']; ?>"</span>?</h5>
                        <input type="hidden" name="id" value="<?= $value['id_unit']; ?>">

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