<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="section-body">
    <h2 class="section-title">All About General Settings</h2>
    <p class="section-lead">
        You can adjust all general settings here
    </p>
    <div id="output-status"></div>
    <div class="row">

        <div class="col-12">
            <div class="card" id="settings-card">
                <div class="card-header">
                    <h4>Data Admin</h4>
                    <div class="card-header-action">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addAdminModal">
                            Tambah Admin
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-2" class="table table-bordered table-md">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Email</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>No HP</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = null;
                                foreach ($listAdmin as $value) {
                                    $no++;
                                ?>
                                    <tr>
                                        <td><?= $no ?></td>
                                        <td><?= $value['email'] ?></td>
                                        <td><?= $value['username'] ?></td>
                                        <td><?= $value['userfullname'] ?></td>
                                        <td><?= $value['no_telp'] ?></td>
                                        <td>
                                            <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                                <button type="button" class="btn btn-icon btn-info" data-toggle="modal" data-target="#editAdminModal-<?= $value['userid']; ?>"><i class="fas fa-edit"></i></button>
                                                <button type="button" class="btn btn-icon btn-danger" data-toggle="modal" data-target="#deleteAdminModal-<?= $value['userid']; ?>"><i class="fas fa-trash"></i></button>
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
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<!-- Add-User-Modal -->
<form action="<?= site_url('/admin/store') ?>" method="POST">
    <div class="modal fade" id="addAdminModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Admin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <?= csrf_field() ?>
                    <p class="text-muted">Masukan Email aktif, Username, Nama Aseor, Nomor Handphone/Whatsapp, Password dan Konfirmasi Password.</p>
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
                        <label class="form-label">Nama Admin</label>
                        <input type="text" name="fullname" class="form-control <?php if (session('errors.fullname')) : ?>is-invalid<?php endif ?>" placeholder="Nama Lengkap Admin">
                        <?php if (session('errors.fullname')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.fullname') ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">No Handphone</label>
                        <input type="text" name="no_telp" class="form-control <?php if (session('errors.no_telp')) : ?>is-invalid<?php endif ?>" placeholder="Nomor Handphone/Whatsapp">
                        <?php if (session('errors.no_telp')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.no_telp') ?>
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

<!-- edit-User-Modal -->
<?php foreach ($listAdmin as $key => $value) { ?>
    <form action="<?= site_url('/admin/update') ?>" method="post" enctype="multipart/form-data">
        <div class="modal fade" id="editAdminModal-<?= $value['userid'] ?>" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Admin</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Perbarui Email aktif, Username, Nama Aseor, Nomor Handphone/Whatsapp, Password dan Konfirmasi Password.</p>
                        <!-- view('Myth\Auth\Views\_message_block') -->
                        <?= csrf_field() ?>
                        <input type="hidden" name="edit_id" value="<?= $value['userid'] ?>">
                        <div class="form-group">
                            <label for="edit_email"><?= lang('Auth.email') ?></label>
                            <input type="edit_email" class="form-control <?php if (session('errors.edit_email')) : ?>is-invalid<?php endif ?>" name="edit_email" aria-describedby="emailHelp" placeholder="<?= lang('Auth.email') ?>" value="<?= $value['email'] ?>" readonly>
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
                                <input type="text" class="form-control <?php if (session('errors.edit_fullname')) : ?>is-invalid<?php endif ?>" name="edit_fullname" placeholder="Nama Lengkap" value="<?= $value['fullname'] ?>">
                                <?php if (session('errors.edit_fullname')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.edit_fullname') ?>
                                    </div>
                                <?php } ?>
                            </div>

                        </div>

                        <div class="form-group">
                            <label for="edit_no_hp">No Handphone</label>
                            <input type="text" name="edit_no_hp" class="form-control <?php if (session('errors.edit_no_hp')) : ?>is-invalid<?php endif ?>" placeholder="Nomor Handphone" value="<?= $value['no_telp'] ?>">
                            <?php if (session('errors.edit_no_hp')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_no_hp') ?>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="">Tanda Tangan</label>
                            <input type="file" name="edit_tanda_tangan" class="tanda_tangan <?php if (session('errors.edit_tanda_tangan')) : ?>is-invalid<?php endif ?>">
                            <?php if (session('errors.edit_tanda_tangan')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_tanda_tangan') ?>
                                </div>
                            <?php } ?>
                            <small>*Tipe Image (jpg/jpeg/png), Ukuran Maksimal 2 MB</small>
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
<?php foreach ($listAdmin as $key => $value) { ?>
    <form action="<?= site_url('admin/delete'); ?>" method="post">
        <div class="modal fade" id="deleteAdminModal-<?= $value['userid']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Admin</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <h5>Apakah anda yakin akan menghapus <span class="text-danger font-weight-bold">"<?= $value['username']; ?>"</span>?</h5>
                        <input type="hidden" name="id" value="<?= $value['userid']; ?>">

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