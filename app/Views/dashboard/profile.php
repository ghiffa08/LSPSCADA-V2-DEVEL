<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row mt-sm-4">
    <div class="col-12 col-md-12">
        <div class="card">
            <form method="POST" action="<?= site_url('user-update'); ?>" class="needs-validation" novalidate="" enctype="multipart/form-data">
                <div class="card-header">
                    <h4>Edit Profile</h4>
                </div>
                <div class="card-body">
                    <?php if (session()->has('error')) : ?>
                        <div class="alert alert-danger">
                            <?= session('error') ?>
                        </div>
                    <?php endif ?>

                    <?php if (session()->has('errors')) : ?>
                        <ul class="alert alert-danger">
                            <?php foreach (session('errors') as $error) : ?>
                                <li><?= $error ?></li>
                            <?php endforeach ?>
                        </ul>
                    <?php endif ?>
                    <div class="row">
                        <div class="form-group col-12">
                            <label>Username</label>
                            <input type="hidden" name="id" id="id" class="form-control" value="<?= user()->id ?>" required="">
                            <input type="text" name="username" id="username" class="form-control" value="<?= user()->username ?>" required="">
                            <div class="invalid-feedback">
                                Please fill in the username
                            </div>
                        </div>
                        <div class="form-group col-12">
                            <label>Nama Lengkap</label>
                            <input type="text" name="fullname" id="fullname" class="form-control" value="<?= user()->fullname ?>" required="">
                            <?php if (session('errors.username')) : ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.username') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="no_telp" id="no_telp" class="form-control" value="<?= user()->no_telp ?>">
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6 col-12">
                            <label>Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?= user()->email ?>" readonly>
                            <div class="invalid-feedback">
                                Please fill in the email
                            </div>
                        </div>
                        <div class="form-group col-md-6 col-12">
                            <label>Role</label>
                            <input type="email" class="form-control" value=" <?= $user->name; ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="">Tanda Tangan</label>
                                <div class="mb-2 text-muted">Click Untuk Melihat Detail!</div>
                                <div class="chocolat-parent">
                                    <a href="<?= base_url('html/upload/tanda tangan/' . user()->tanda_tangan) ?>" class="chocolat-image" title="Just an example">
                                        <div data-crop-image="285">
                                            <img alt="image" src="<?= base_url('html/upload/tanda tangan/' . user()->tanda_tangan) ?>" class="img-fluid">
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="">Foto Tanda Tangan</label>
                                <input type="file" name="tanda_tangan" class="tanda_tangan">
                                <small>*Tipe Image (jpg/jpeg/png), Ukuran Maksimal 2 MB</small>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </div>
        </div>

        </form>
    </div>
</div>
</div>

<?= $this->endSection() ?>