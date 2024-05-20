<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<!-- Main Content -->

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
                    <h4><?= $siteTitle ?></h4>
                    <div class="card-header-action">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addGroupModal">
                            Tambah Group
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">General settings such as, site title, site description, address and so on.</p>

                    <div class="table-responsive">
                        <table id="" class="table table-bordered table-md">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                            <?php
                            $no = null;
                            foreach ($listGroups as $value) {
                                $no++;
                            ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td>
                                        <div class="badge badge-success"><?= $value['name']; ?></div>
                                    </td>
                                    <td><?= $value['description'] ?></td>
                                    <td>
                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editGroupModal-<?= $value['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteGroupModal-<?= $value['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card" id="settings-card">
                <div class="card-header">
                    <h4>Group Users Setting</h4>
                    <div class="card-header-action">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
                            Tambah User
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">General settings such as, site title, site description, address and so on.</p>

                    <div class="table-responsive">
                        <table id="datatables" class="table table-bordered table-md">
                            <tr>
                                <th>#</th>
                                <th>Group</th>
                                <th>username</th>
                                <th>Nama</th>
                                <th>Action</th>
                            </tr>
                            <?php
                            $no = null;
                            foreach ($listGroupUsers as $value) {
                                $no++;
                            ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td>
                                        <div class="badge badge-success"><?= $value['groupname']; ?></div>
                                    </td>
                                    <td><?= $value['username'] ?></td>
                                    <td><?= $value['userfullname'] ?></td>
                                    <td>
                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editGUserModal-<?= $value['userid']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteUserModal-<?= $value['userid']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
</div>

<?= $this->endSection() ?>

<!-- Modals Section -->
<?= $this->section("modals") ?>

<!-- Add-Group-Modal -->
<form id="setting-form" action="<?= site_url('/store-group'); ?>" method="POST">
    <div class="modal fade" id="addGroupModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Group</h5>
                    <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <p class="text-muted">General settings such as, site title, site description, address and so on.</p>
                    <div class="form-group">
                        <label>Nama Group</label>
                        <input type="text" name="name" class="form-control" id="inputName">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi Group</label>
                        <textarea class="form-control" name="description" id="inputDescription"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-whitesmoke br">
                    <button type="reset" class="btn btn-secondary">Clear</button>
                    <button type="button" class="btn btn-primary" id="tombolSimpan">Submit</button>
                </div>

            </div>
        </div>
    </div>
</form>

<!-- Edit-Group-Modal -->
<?php foreach ($listGroups as $row => $value) { ?>
    <form id="setting-form" action="<?= site_url('/update-group'); ?>" method="POST">
        <div class="modal fade" id="editGroupModal-<?= $value['id'] ?>" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Group - <span class="text-info font-weight-bold"><?= $value['name'] ?></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?= csrf_field(); ?>
                        <p class="text-muted">General settings such as, site title, site description, address and so on.</p>
                        <div class="form-group">
                            <label>Nama Group</label>
                            <input type="hidden" name="id" value="<?= $value['id'] ?>">
                            <input type="text" name="name" class="form-control" value="<?= $value['name'] ?>">
                        </div>
                        <div class="form-group">
                            <label>Deskripsi Group</label>
                            <textarea class="form-control" name="description" id="site-description"><?= $value['description'] ?></textarea>
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

<!-- Delete-Group-Modal -->
<?php foreach ($listGroups as $row => $value) { ?>
    <form action="<?= site_url('/delete-group'); ?>" method="post">
        <div class="modal fade" id="deleteGroupModal-<?= $value['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Group</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <h5>Apakah anda yakin akan menghapus <span class="text-danger font-weight-bold">"<?= $value['name']; ?>"</span>?</h5>
                        <input type="hidden" name="id" value="<?= $value['id']; ?>">

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

<!-- Edit-Groups-Users-Modal -->
<?php foreach ($listGroupUsers as $key => $value) { ?>
    <form id="setting-form" action="<?= site_url('/group-users-update'); ?>" method="POST">
        <div class="modal fade" id="editGUserModal-<?= $value['userid']; ?>" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Group User - <?= $value['userfullname']; ?><span class="text-info font-weight-bold"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?= csrf_field(); ?>
                        <p class="text-muted">General settings such as, site title, site description, address and so on.</p>
                        <div class="form-group">
                            <label>Nama User</label>
                            <input type="hidden" name="id" value="<?= $value['groupuserid']; ?>">
                            <input type="text" name="user" class="form-control" value="<?= $value['userfullname']; ?>" readonly>
                        </div>
                        <div class="form-group ">
                            <label>Group</label>
                            <select class="form-control selectric" name="groupid">
                                <option value="">Pilih Group</option>
                                <?php

                                if (isset($listGroups)) {
                                    foreach ($listGroups as $row) {
                                        isset($value['groupuserid']) && $value['groupuserid'] == $row['id'] ? $pilih = 'selected' : $pilih = null;
                                        echo '<option ' . $pilih . ' value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                    }
                                }

                                ?>
                            </select>
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

<!-- Add-User-Modal -->
<form action="<?= url_to('register') ?>" method="post">
    <div class="modal fade" id="addUserModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Group</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">General settings such as, site title, site description, address and so on.</p>
                    <?= view('Myth\Auth\Views\_message_block') ?>


                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label for="email"><?= lang('Auth.email') ?></label>
                        <input type="email" class="form-control <?php if (session('errors.email')) : ?>is-invalid<?php endif ?>" name="email" aria-describedby="emailHelp" placeholder="<?= lang('Auth.email') ?>" value="<?= old('email') ?>">
                        <small id="emailHelp" class="form-text text-muted"><?= lang('Auth.weNeverShare') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="username"><?= lang('Auth.username') ?></label>
                        <input type="text" class="form-control <?php if (session('errors.username')) : ?>is-invalid<?php endif ?>" name="username" placeholder="<?= lang('Auth.username') ?>" value="<?= old('username') ?>">
                    </div>

                    <div class="form-group">
                        <label for="password"><?= lang('Auth.password') ?></label>
                        <input type="password" name="password" class="form-control <?php if (session('errors.password')) : ?>is-invalid<?php endif ?>" placeholder="<?= lang('Auth.password') ?>" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="pass_confirm"><?= lang('Auth.repeatPassword') ?></label>
                        <input type="password" name="pass_confirm" class="form-control <?php if (session('errors.pass_confirm')) : ?>is-invalid<?php endif ?>" placeholder="<?= lang('Auth.repeatPassword') ?>" autocomplete="off">
                    </div>



                    <div class="form-group">
                        <button type="submit" id="submit-button" class="btn btn-primary btn-lg btn-block">
                            <?= lang('Auth.register') ?>
                        </button>
                    </div>

                    <div class="modal-footer bg-whitesmoke br">
                        <button type="reset" class="btn btn-secondary">Clear</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>

<!-- Delete-Group-Modal -->
<?php foreach ($listGroupUsers as $row => $value) { ?>
    <form action="<?= site_url('/delete-user'); ?>" method="post">
        <div class="modal fade" id="deleteUserModal-<?= $value['userid']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Group</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <h5>Apakah anda yakin akan menghapus <span class="text-danger font-weight-bold">"<?= $value['username']; ?>"</span>?</h5>
                        <input type="hidden" name="id" value="<?= $value['userid']; ?>">

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