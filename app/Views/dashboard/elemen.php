<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Tabel <?= $siteTitle ?></h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addElemenModal">
                        Tambah Elemen
                    </button>
                    <div class="dropdown d-inline">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Import Exel
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item has-icon" href="<?= base_url('Contoh Exel/CONTOH ELEMEN LSP SCADA.xlsx') ?>"><i class="fas fa-file"></i> Contoh Exel</a>
                            <a class="dropdown-item has-icon" type="button" data-toggle="modal" data-target="#importExelModal"><i class="fas fa-upload"></i> Import Exel</a>

                        </div>
                    </div>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addElemenModal">
                        Tambah Elemen
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
                                <th>ID Elemen</th>
                                <th>Kode Elemen</th>
                                <th>Nama Elemen</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = null;
                            foreach ($listElemen as $value) {
                                $no++;
                            ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><?= $value['id_skema'] ?></td>
                                    <td><?= $value['id_unit'] ?></td>
                                    <td><?= $value['id_elemen'] ?></td>
                                    <td><?= $value['kode_elemen'] ?></td>
                                    <td><?= $value['nama_elemen'] ?></td>
                                    <td>
                                        <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                            <button type="button" class="btn btn-icon btn-info" data-toggle="modal" data-target="#editElemenModal-<?= $value['id_elemen']; ?>"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-icon btn-danger" data-toggle="modal" data-target="#deleteElemenModal-<?= $value['id_elemen']; ?>"><i class="fas fa-trash"></i></button>
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
<form id="setting-form" action="<?= site_url('/store-elemen'); ?>" method="POST">
    <div class="modal fade" id="addElemenModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Elemen</h5>
                    <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <p class="text-muted">Masukan ID Skema, ID Unit, Kode Elemen dan Nama Elemen.</p>
                    <div class="form-group">
                        <label>Skema<span class="text-danger">*</span></label>
                        <select class="form-control select2 <?php if (session('errors.id_skema')) : ?>is-invalid<?php endif ?>" name="id_skema" id="id_skema">
                            <option value="">Skema</option>
                            <?php
                            foreach ($listSkema as $key => $row) {
                                echo '<option  value="' . $row['id_skema'] . '">' . $row['nama_skema'] . '</option>';
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
                        <label>Unit<span class="text-danger">*</span></label>
                        <select class="form-control select2 <?php if (session('errors.id_unit')) : ?>is-invalid<?php endif ?>" name="id_unit" id="id_unit">
                            <option value="">Unit</option>

                        </select>
                        <?php if (session('errors.id_unit')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.id_unit') ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kode elemen<span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control <?php if (session('errors.kode')) : ?>is-invalid<?php endif ?>" id="inputKode">
                        <?php if (session('errors.kode')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.kode') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama elemen<span class="text-danger">*</span></label>
                        <textarea class="form-control summernote-simple <?php if (session('errors.nama')) : ?>is-invalid<?php endif ?>" name="nama"></textarea>
                        <?php if (session('errors.nama')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.nama') ?>
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

<!-- Add-Exel-Modal -->
<form id="setting-form" action="<?= site_url('/import-elemen'); ?>" method="POST" enctype="multipart/form-data">
    <div class="modal fade" id="importExelModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Elemen</h5>
                    <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <p class="text-muted">General settings such as, site title, site description, address and so on.</p>
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
<?php foreach ($listElemen as $row => $value) { ?>
    <form id="setting-form" action="<?= site_url('/update-elemen'); ?>" method="POST">
        <div class="modal fade" id="editElemenModal-<?= $value['id_elemen']  ?>" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Elemen</h5>
                        <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?= csrf_field(); ?>
                        <p class="text-muted">General settings such as, site title, site description, address and so on.</p>
                        <div class="form-group">
                            <label>Skema<span class="text-danger">*</span></label>
                            <select class="form-control select2 <?php if (session('errors.edit_id_skema')) : ?>is-invalid<?php endif ?>" name="edit_id_skema">
                                <option value="">Pilih Skema</option>
                                <?php

                                if (isset($listSkema)) {
                                    $no = null;
                                    foreach ($listSkema as $row) {
                                        $no++;

                                        $value['id_skema'] == $row['id_skema'] ? $pilih = 'selected' : $pilih = null;

                                        echo '<option  ' . $pilih . ' value="' . $row['id_skema'] . '">' . $row['nama_skema'] . '</option>';
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
                            <label>Unit<span class="text-danger">*</span></label>
                            <select class="form-control select2 <?php if (session('errors.edit_id_unit')) : ?>is-invalid<?php endif ?>" name="edit_id_unit">
                                <option value="">Pilih Unit</option>
                                <?php

                                if (isset($listUnit)) {
                                    foreach ($listUnit as $row) {

                                        if ($row['status'] == "Y") {
                                            $value['id_unit'] == $row['id_unit'] ? $pilih = 'selected' : $pilih = null;

                                            echo '<option  ' . $pilih . ' value="' . $row['id_unit'] . '">' . $row['nama_unit'] . '</option>';
                                        }
                                    }
                                }

                                ?>
                            </select>
                            <?php if (session('errors.edit_id_unit')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_id_unit') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kode elemen<span class="text-danger">*</span></label>
                            <input type="hidden" name="edit_id" value="<?= $value['id_elemen'] ?>">
                            <input type="text" name="edit_kode" class="form-control <?php if (session('errors.edit_kode')) : ?>is-invalid<?php endif ?>" value="<?= $value['kode_elemen'] ?>">
                            <?php if (session('errors.edit_kode')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_kode') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama elemen<span class="text-danger">*</span></label>
                            <textarea class="form-control summernote-simple <?php if (session('errors.edit_nama')) : ?>is-invalid<?php endif ?>" name="edit_nama"><?= $value['nama_elemen'] ?></textarea>
                            <?php if (session('errors.edit_nama')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_nama') ?>
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
<?php foreach ($listElemen as $row => $value) { ?>
    <form action="<?= site_url('/delete-elemen'); ?>" method="post">
        <div class="modal fade" id="deleteElemenModal-<?= $value['id_elemen']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Elemen</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <h5>Apakah anda yakin akan menghapus <span class="text-danger font-weight-bold">"<?= $value['kode_elemen']; ?>"</span>?</h5>
                        <input type="hidden" name="id" value="<?= $value['id_elemen']; ?>">

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

<?= $this->section('js') ?>
<script>
    $(document).ready(function() {
        $("#id_skema").change(function(e) {
            var id_skema = $("#id_skema").val();
            $.ajax({
                type: "POST",
                url: "<?= base_url('/getUnit') ?>",
                data: {
                    id_skema: id_skema
                },
                success: function(response) {
                    $("#id_unit").html(response);
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>