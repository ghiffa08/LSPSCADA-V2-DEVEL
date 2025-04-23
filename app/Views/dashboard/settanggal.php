<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Tabel <?= $siteTitle ?></h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addSettanggalModal">
                        Set Tanggal Asesi
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-2" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal & Waktu</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = null;
                            foreach ($listSettanggal as $value) {
                                $no++;
                            ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><?= $value['tanggal'] ?></td>
                                    <td><?= $value['keterangan'] ?></td>
                                    <td>
                                        <div class="badge badge-<?= $value['status'] == "A" ? "success" : "danger" ?>"><?= $value['status']  == "A" ? "Aktif" : "Tidak Aktif" ?></div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editSettanggalModal-<?= $value['id_tanggal']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteSettanggalModal-<?= $value['id_tanggal']; ?>">
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

<!-- Add-Group-Modal -->
<form id="setting-form" action="<?= site_url('/store-settanggal'); ?>" method="POST">
    <div class="modal fade" id="addSettanggalModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Set Tanggal</h5>
                    <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <p class="text-muted">General settings such as, site title, site description, address and so on.</p>
                    <div class="form-group">
                        <label class="form-label">Tanggal Asesi</label>
                        <input type="datetime-local" name="tanggal" class="form-control <?php if (session('errors.tanggal')) : ?>is-invalid<?php endif ?>">
                        <?php if (session('errors.tanggal')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.tanggal') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control <?php if (session('errors.keterangan')) : ?>is-invalid<?php endif ?>" name="keterangan"></textarea>
                        <?php if (session('errors.keterangan')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.keterangan') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="selectgroup w-100">
                            <label class="selectgroup-item">
                                <input type="radio" name="status" class="selectgroup-input" value="A" checked="">
                                <span class="selectgroup-button">Y</span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="status" class="selectgroup-input" value="T">
                                <span class="selectgroup-button">N</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="reset" class="btn btn-secondary">Clear</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>

            </div>
        </div>
    </div>
</form>

<!-- Edit-Group-Modal -->
<?php foreach ($listSettanggal as $key => $value) { ?>
    <form id="setting-form" action="<?= site_url('/update-settanggal'); ?>" method="POST">
        <div class="modal fade" id="editSettanggalModal-<?= $value['id_tanggal'] ?>" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Set Tanggal</h5>
                        <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?= csrf_field(); ?>
                        <p class="text-muted">General settings such as, site title, site description, address and so on.</p>
                        <div class="form-group">
                            <label class="form-label">Tanggal Asesi</label>
                            <input type="hidden" name="edit_id" value="<?= $value['id_tanggal'] ?>">
                            <input type="datetime-local" name="edit_tanggal" class="form-control <?php if (session('errors.edit_tanggal')) : ?>is-invalid<?php endif ?>" value="<?= $value['tanggal'] ?>">
                            <?php if (session('errors.edit_tanggal')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_tanggal') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control <?php if (session('errors.edit_keterangan')) : ?>is-invalid<?php endif ?>" name="edit_keterangan"><?= $value['keterangan'] ?></textarea>
                            <?php if (session('errors.edit_keterangan')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_keterangan') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <div class="selectgroup w-100">
                                <label class="selectgroup-item">
                                    <input type="radio" name="edit_status" class="selectgroup-input" value="A" <?= $value['status'] == "A" ? "checked" : "" ?>>
                                    <span class="selectgroup-button">Y</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="edit_status" class="selectgroup-input" value="T" <?= $value['status'] == "T" ? "checked" : "" ?>>
                                    <span class="selectgroup-button">N</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="reset" class="btn btn-secondary">Clear</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>

                </div>
            </div>
        </div>
    </form>
<?php } ?>

<!-- delete-Group-Modal -->
<?php foreach ($listSettanggal as $row => $value) { ?>
    <form action="<?= site_url('/delete-settanggal'); ?>" method="post">
        <div class="modal fade" id="deleteSettanggalModal-<?= $value['id_tanggal']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Tanggal Asesi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <h5>Apakah anda yakin akan menghapus <span class="text-danger font-weight-bold">"<?= $value['tanggal']; ?>"</span>?</h5>
                        <input type="hidden" name="id" value="<?= $value['id_tanggal']; ?>">

                    </div>
                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php } ?>

<?= $this->endSection() ?>