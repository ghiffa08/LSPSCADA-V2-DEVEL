<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('pesan')) : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Berhasil!</strong> <?= session()->getFlashdata('pesan') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('warning')) : ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Peringatan!</strong> <?= session()->getFlashdata('warning') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="section-body">
    <div class="row">
        <div class="col-12">
            <div class="card" id="settings-card">
                <div class="card-header">
                    <h4><i class="fas fa-users"></i> Data Asesi</h4>
                    <div class="card-header-action"> <button class="btn btn-primary" data-toggle="modal" data-target="#addAsesiModal">
                            <i class="fas fa-plus"></i> Tambah Asesi
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-1" class="table table-striped table-md">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Email</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 0;
                                foreach ($listAsesi as $value) {
                                    $no++;
                                ?> <tr>
                                        <td><?= $no ?></td>
                                        <td><?= $value['email'] ?></td>
                                        <td><?= $value['username'] ?></td>
                                        <td><?= $value['userfullname'] ?></td>
                                        <td>
                                            <div class="badge badge-success">Aktif</div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editAsesiModal-<?= $value['userid']; ?>" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteAsesiModal-<?= $value['userid']; ?>" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<!-- Add-Asesi-Modal -->
<form action="<?= site_url('/admin/store-asesi') ?>" method="POST">
    <div class="modal fade" id="addAsesiModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Asesi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <p class="text-muted">Masukan Email aktif, Username, Nama Asesi, Nomor Handphone/Whatsapp, Password dan Konfirmasi Password.</p>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label for="email"><?= lang('Auth.email') ?></label>
                            <input type="email" class="form-control <?php if (session('errors.email')) : ?>is-invalid<?php endif ?>" name="email" aria-describedby="emailHelp" placeholder="<?= lang('Auth.email') ?>" value="<?= old('email') ?>">
                            <?php if (session('errors.email')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.email') ?>
                                </div>
                            <?php } else { ?>
                                <small id="emailHelp" class="form-text text-muted">Kami tidak akan pernah membagikan email Anda kepada orang lain.</small>
                            <?php } ?>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="username"><?= lang('Auth.username') ?></label>
                            <input type="text" class="form-control <?php if (session('errors.username')) : ?>is-invalid<?php endif ?>" name="username" placeholder="<?= lang('Auth.username') ?>" value="<?= old('username') ?>">
                            <?php if (session('errors.username')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.username') ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Asesi</label>
                        <input type="text" name="nama_lengkap" class="form-control <?php if (session('errors.nama_lengkap')) : ?>is-invalid<?php endif ?>" placeholder="Nama Lengkap Asesi">
                        <?php if (session('errors.nama_lengkap')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.nama_lengkap') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label for="password"><?= lang('Auth.password') ?></label>
                            <input type="password" name="password" class="form-control <?php if (session('errors.password')) : ?>is-invalid<?php endif ?>" placeholder="<?= lang('Auth.password') ?>" autocomplete="off">
                            <?php if (session('errors.password')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.password') ?>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="pass_confirm"><?= lang('Auth.repeatPassword') ?></label>
                            <input type="password" name="pass_confirm" class="form-control <?php if (session('errors.pass_confirm')) : ?>is-invalid<?php endif ?>" placeholder="<?= lang('Auth.repeatPassword') ?>" autocomplete="off">
                            <?php if (session('errors.pass_confirm')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.pass_confirm') ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <?= lang('Auth.register') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Edit-Asesi-Modal -->
<?php foreach ($listAsesi as $key => $value) { ?>
    <form action="<?= site_url('/admin/update-asesi') ?>" method="post">
        <div class="modal fade" id="editAsesiModal-<?= $value['userid'] ?>" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Asesi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Perbarui Email aktif, Username, Nama Asesi, dan Nomor Handphone/Whatsapp.</p>
                        <?= csrf_field() ?>
                        <input type="hidden" name="edit_id" value="<?= $value['userid'] ?>">

                        <div class="form-group">
                            <label for="edit_email"><?= lang('Auth.email') ?></label>
                            <input type="email" class="form-control <?php if (session('errors.edit_email')) : ?>is-invalid<?php endif ?>" name="edit_email" aria-describedby="emailHelp" placeholder="<?= lang('Auth.email') ?>" value="<?= $value['email'] ?>">
                            <?php if (session('errors.edit_email')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_email') ?>
                                </div>
                            <?php } else { ?>
                                <small id="emailHelp" class="form-text text-muted">Kami tidak akan pernah membagikan email Anda kepada orang lain.</small>
                            <?php } ?>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-12 col-md-6">
                                <label for="edit_username"><?= lang('Auth.username') ?></label>
                                <input type="text" class="form-control <?php if (session('errors.edit_username')) : ?>is-invalid<?php endif ?>" name="edit_username" placeholder="<?= lang('Auth.username') ?>" value="<?= $value['username'] ?>">
                                <?php if (session('errors.edit_username')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.edit_username') ?>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="form-group col-12 col-md-6">
                                <label for="edit_fullname">Nama Lengkap</label>
                                <input type="text" class="form-control <?php if (session('errors.edit_fullname')) : ?>is-invalid<?php endif ?>" name="edit_fullname" placeholder="Nama Lengkap" value="<?= $value['nama_lengkap'] ?>">
                                <?php if (session('errors.edit_fullname')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.edit_fullname') ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        Update Asesi
                    </button>
                </div>
            </div>
        </div>
        </div>
    </form>
<?php } ?>

<!-- Delete-Asesi-Modal -->
<?php foreach ($listAsesi as $key => $value) { ?>
    <form action="<?= site_url('/admin/delete-asesi') ?>" method="post">
        <div class="modal fade" id="deleteAsesiModal-<?= $value['userid'] ?>" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Asesi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $value['userid'] ?>">
                        <p>Apakah Anda yakin ingin menghapus asesi <strong><?= $value['userfullname'] ?></strong>?</p>
                        <p class="text-warning"><small>Data yang dihapus tidak dapat dikembalikan.</small></p>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php } ?>

<?= $this->endSection() ?>