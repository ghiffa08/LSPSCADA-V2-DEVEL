<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Tabel <?= $siteTitle ?></h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addSubelemenModal">
                        Tambah Subelemen
                    </button>

                    <div class="dropdown d-inline">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Import Exel
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item has-icon" href="<?= base_url('Contoh Exel/CONTOH SUBELEMEN LSP SCADA.xlsx') ?>"><i class="fas fa-file"></i> Contoh Exel</a>
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
                                <th>ID Elemen</th>
                                <th>ID Subelemen</th>
                                <th>Kode Subelemen</th>
                                <th>Nama Subelemen</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = null;
                            foreach ($listSubelemen as $value) {
                                $no++;
                            ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><?= $value['id_skema'] ?></td>
                                    <td><?= $value['id_unit'] ?></td>
                                    <td><?= $value['id_elemen'] ?></td>
                                    <td><?= $value['id_subelemen'] ?></td>
                                    <td><?= $value['kode_subelemen'] ?></td>
                                    <td><?= $value['pertanyaan'] ?></td>
                                    <td>
                                        <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                            <button type="button" class="btn btn-icon btn-info" data-toggle="modal" data-target="#editSubelemenModal-<?= $value['id_subelemen']; ?>"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-icon btn-danger" data-toggle="modal" data-target="#deleteSubelemenModal-<?= $value['id_subelemen']; ?>"><i class="fas fa-trash"></i></button>
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
<form id="setting-form" action="<?= site_url('/store-subelemen'); ?>" method="POST">
    <div class="modal fade" id="addSubelemenModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Subelemen</h5>
                    <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <p class="text-muted">Masukan ID Skema, ID Unit, ID ELemen, Kode Subelemen, Pertanyaan.</p>
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
                        <label>Elemen<span class="text-danger">*</span></label>
                        <select class="form-control select2 <?php if (session('errors.id_elemen')) : ?>is-invalid<?php endif ?>" name="id_elemen" id="id_elemen">
                            <option value="">Pilih Unit</option>

                        </select>
                        <?php if (session('errors.id_elemen')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.id_elemen') ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kode Subelemen<span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control <?php if (session('errors.kode')) : ?>is-invalid<?php endif ?>" id="inputKode">
                        <?php if (session('errors.kode')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.kode') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group  mb-0">
                        <label class="form-label">Pertanyaan<span class="text-danger">*</span></label>
                        <textarea class="form-control  <?php if (session('errors.pertanyaan')) : ?>is-invalid<?php endif ?>" name="pertanyaan"></textarea>
                        <?php if (session('errors.pertanyaan')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.pertanyaan') ?>
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
<form id="setting-form" action="<?= site_url('/import-subelemen'); ?>" method="POST" enctype="multipart/form-data">
    <div class="modal fade" id="importExelModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Subelemen</h5>
                    <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <p class="text-muted">Pilih File Exel dengan tipe Xls/Xlsx dan sesuaikan format seperti pada contoh.</p>
                    <div class="form-group">
                        <label class="form-label">File Exel</label>
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
<?php foreach ($listSubelemen as $row => $value) { ?>
    <form id="setting-form" action="<?= site_url('/update-subelemen'); ?>" method="POST">
        <div class="modal fade" id="editSubelemenModal-<?= $value['id_subelemen']  ?>" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Subelemen</h5>
                        <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?= csrf_field(); ?>
                        <p class="text-muted">Perbarui ID Skema, ID Unit, ID ELemen, Kode Subelemen, Pertanyaan.</p>
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
                            <label>Elemen<span class="text-danger">*</span></label>
                            <select class="form-control select2 <?php if (session('errors.edit_id_elemen')) : ?>is-invalid<?php endif ?>" name="edit_id_elemen">
                                <option value="">Pilih Elemen</option>
                                <?php

                                if (isset($listElemen)) {
                                    foreach ($listElemen as $row) {

                                        $value['id_elemen'] == $row['id_elemen'] ? $pilih = 'selected' : $pilih = null;

                                        echo '<option  ' . $pilih . ' value="' . $row['id_elemen'] . '">' . $row['nama_elemen'] . '</option>';
                                    }
                                }

                                ?>
                            </select>
                            <?php if (session('errors.edit_id_elemen')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_id_elemen') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kode Subelemen<span class="text-danger">*</span></label>
                            <input type="hidden" name="edit_id" value="<?= $value['id_subelemen'] ?>">
                            <input type="text" name="edit_kode" class="form-control <?php if (session('errors.edit_kode')) : ?>is-invalid<?php endif ?>" value="<?= $value['kode_subelemen'] ?>">
                            <?php if (session('errors.edit_kode')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_kode') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pertanyaan<span class="text-danger">*</span></label>
                            <textarea class="form-control  <?php if (session('errors.edit_pertanyaan')) : ?>is-invalid<?php endif ?>" name="edit_pertanyaan"><?= $value['pertanyaan'] ?></textarea>
                            <?php if (session('errors.edit_pertanyaan')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_pertanyaan') ?>
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
<?php foreach ($listSubelemen as $row => $value) { ?>
    <form action="<?= site_url('/delete-subelemen'); ?>" method="post">
        <div class="modal fade" id="deleteSubelemenModal-<?= $value['id_subelemen']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Subelemen</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <h5>Apakah anda yakin akan menghapus <span class="text-danger font-weight-bold">"<?= $value['kode_subelemen']; ?>"</span>?</h5>
                        <input type="hidden" name="id" value="<?= $value['id_subelemen']; ?>">

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
        $("#id_unit").change(function(e) {
            var id_unit = $("#id_unit").val();
            $.ajax({
                type: "POST",
                url: "<?= base_url('/getElemen') ?>",
                data: {
                    id_unit: id_unit
                },
                success: function(response) {
                    $("#id_elemen").html(response);
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>