<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section("internal-css") ?>
<?= $this->include('components/signature-pad/css-signature'); ?>
<?= $this->endSection() ?>

<?= $this->section("content"); ?>
<h2 class="section-title">Hi, <?= user()->username ?>!</h2>
<p class="section-lead">
    Change information about yourself on this page.
</p>

<form method="POST" action="<?= site_url('user-update'); ?>" class="needs-validation" novalidate enctype="multipart/form-data">
    <div class="card">
        <div class="card-header">
            <h4>Edit Profile</h4>
        </div>

        <div class="card-body">
            <?php if (session()->has('error')) : ?>
                <div class="alert alert-danger"><?= session('error') ?></div>
            <?php endif; ?>

            <?php if (session()->has('errors')) : ?>
                <ul class="alert alert-danger mb-0">
                    <?php foreach (session('errors') as $error) : ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="hidden" name="id" id="id" class="form-control" value="<?= user()->id ?>" required>
                        <input type="text" name="username" id="username" class="form-control" value="<?= user()->username ?>" required>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="fullname" id="fullname" class="form-control" value="<?= user()->fullname ?>" required>
                        <?php if (session('errors.username')) : ?>
                            <div class="invalid-feedback">
                                <?= session('errors.username') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Nomor Handphone</label>
                        <input type="tel" name="no_telp" id="no_telp" class="form-control" value="<?= user()->no_telp ?>">
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?= user()->email ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Input Tanda Tangan -->
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="">Tanda Tangan</label>
                        <div class="mb-2 text-muted">Silakan tanda tangani pada area di bawah ini.</div>
                        <div class="signature-container">
                            <div class="signature-pad-wrapper">
                                <canvas id="signature-pad"></canvas>
                            </div>
                            <input type="hidden" id="signature-data" name="tanda_tangan">
                            <input type="file" id="signature-upload" accept="image/png, image/jpeg" style="display: none;">
                            <div class="btn-group mt-2">
                                <button type="button" id="clear-signature" class="btn btn-danger">Hapus</button>
                                <button type="button" id="upload-signature" class="btn btn-info">Upload Tanda Tangan</button>
                            </div>

                            <div id="signature-status"></div>
                        </div>
                    </div>
                </div>

                <!-- Preview Tanda Tangan -->
                <div class="col-12 col-md-6">
                    <?php if (!empty(user()->tanda_tangan)) : ?>
                        <div class="form-group">
                            <label for="">Tanda Tangan Saat Ini</label>
                            <div class="mb-2 text-muted">Tanda tangan yang tersimpan:</div>
                            <div class="signature-preview">
                                <img src="<?= get_signature_url(user()->tanda_tangan) ?>" alt="Tanda Tangan" class="img-fluid border rounded">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-footer text-right bg-whitesmoke">
            <button type="submit" class="btn btn-primary btn-lg">Submit</button>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section("js") ?>
<?= $this->include('components/signature-pad/js-signature'); ?>
<?= $this->endSection() ?>