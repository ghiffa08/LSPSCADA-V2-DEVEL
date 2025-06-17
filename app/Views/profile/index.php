<?= $this->extend('layouts/admin/layout-admin') ?>

<?= $this->section('content') ?>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><?= $pageTitle ?></h1>
            <div class="section-header-breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb): ?>
                    <?php if (isset($breadcrumb['active']) && $breadcrumb['active']): ?>
                        <div class="breadcrumb-item active"><?= $breadcrumb['label'] ?></div>
                    <?php else: ?>
                        <div class="breadcrumb-item">
                            <a href="<?= $breadcrumb['url'] ?>"><?= $breadcrumb['label'] ?></a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Pengaturan Profile</h2>
            <p class="section-lead">
                Ubah informasi tentang diri Anda di halaman ini.
            </p>

            <div class="row mt-sm-4">
                <div class="col-12 col-md-12 col-lg-5">
                    <div class="card profile-widget">
                        <div class="profile-widget-header">
                            <img alt="image" src="<?= $user->avatar ? base_url('writable/uploads/avatars/' . $user->avatar) : base_url('assets/img/avatar/avatar-1.png') ?>"
                                class="rounded-circle profile-widget-picture" id="profile-picture">
                            <div class="profile-widget-items">
                                <div class="profile-widget-item">
                                    <div class="profile-widget-item-label">Role</div>
                                    <div class="profile-widget-item-value"><?= $user->getRole() ?? 'No Role' ?></div>
                                </div>
                                <div class="profile-widget-item">
                                    <div class="profile-widget-item-label">Status</div>
                                    <div class="profile-widget-item-value">
                                        <span class="badge <?= $user->active ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $user->active ? 'Aktif' : 'Nonaktif' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="profile-widget-item">
                                    <div class="profile-widget-item-label">Bergabung</div>
                                    <div class="profile-widget-item-value"><?= date('d M Y', strtotime($user->created_at)) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="profile-widget-description">
                            <div class="profile-widget-name"><?= $user->nama_lengkap ?: $user->username ?>
                                <div class="text-muted d-inline font-weight-normal">
                                    <div class="slash"></div> <?= $user->username ?>
                                </div>
                            </div>
                            <p><?= $user->email ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-12 col-lg-7">
                    <div class="card">
                        <form method="post" class="needs-validation" novalidate="" id="profile-form">
                            <div class="card-header">
                                <h4>Edit Profile</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6 col-12">
                                        <label>Username</label>
                                        <input type="text" class="form-control" name="username" value="<?= $user->username ?>" required="">
                                        <div class="invalid-feedback">
                                            Username harus diisi
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6 col-12">
                                        <label>Email</label>
                                        <input type="email" class="form-control" name="email" value="<?= $user->email ?>" required="">
                                        <div class="invalid-feedback">
                                            Email harus diisi dengan format yang benar
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-12">
                                        <label>Nama Lengkap</label>
                                        <input type="text" class="form-control" name="nama_lengkap" value="<?= $user->nama_lengkap ?>" required="">
                                        <div class="invalid-feedback">
                                            Nama lengkap harus diisi
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Change Password Card -->
                    <div class="card">
                        <form method="post" class="needs-validation" novalidate="" id="password-form">
                            <div class="card-header">
                                <h4>Ubah Password</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Password Saat Ini</label>
                                    <input type="password" class="form-control" name="current_password" required="">
                                    <div class="invalid-feedback">
                                        Password saat ini harus diisi
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Password Baru</label>
                                    <input type="password" class="form-control" name="new_password" required="">
                                    <div class="invalid-feedback">
                                        Password baru harus diisi
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" name="confirm_password" required="">
                                    <div class="invalid-feedback">
                                        Konfirmasi password harus diisi
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-key"></i> Ubah Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Upload Avatar Card -->
                    <div class="card">
                        <form method="post" enctype="multipart/form-data" id="avatar-form">
                            <div class="card-header">
                                <h4>Upload Avatar</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Pilih Foto</label>
                                    <input type="file" class="form-control" name="avatar" accept="image/*" required="">
                                    <small class="form-text text-muted">Maksimal 2MB, format JPG/PNG</small>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-upload"></i> Upload Avatar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Profile form submission
        $('#profile-form').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            let submitBtn = $(this).find('button[type="submit"]');
            let originalText = submitBtn.html();

            // Show loading state
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

            $.ajax({
                url: '<?= site_url('profile/update') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        iziToast.success({
                            title: 'Berhasil!',
                            message: response.message,
                            position: 'topRight'
                        });

                        // Reload page after delay to show updated data
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        iziToast.error({
                            title: 'Error!',
                            message: response.message,
                            position: 'topRight'
                        });

                        // Show validation errors
                        if (response.errors) {
                            Object.keys(response.errors).forEach(function(field) {
                                let input = $(`[name="${field}"]`);
                                input.addClass('is-invalid');
                                input.siblings('.invalid-feedback').text(response.errors[field]);
                            });
                        }
                    }
                },
                error: function(xhr) {
                    iziToast.error({
                        title: 'Error!',
                        message: 'Terjadi kesalahan sistem',
                        position: 'topRight'
                    });
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Password form submission
        $('#password-form').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            let submitBtn = $(this).find('button[type="submit"]');
            let originalText = submitBtn.html();

            // Show loading state
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Mengubah...').prop('disabled', true);

            $.ajax({
                url: '<?= site_url('profile/change-password') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        iziToast.success({
                            title: 'Berhasil!',
                            message: response.message,
                            position: 'topRight'
                        });

                        // Clear form
                        $('#password-form')[0].reset();
                        $('#password-form').find('.is-invalid').removeClass('is-invalid');
                    } else {
                        iziToast.error({
                            title: 'Error!',
                            message: response.message,
                            position: 'topRight'
                        });

                        // Show validation errors
                        if (response.errors) {
                            Object.keys(response.errors).forEach(function(field) {
                                let input = $(`[name="${field}"]`);
                                input.addClass('is-invalid');
                                input.siblings('.invalid-feedback').text(response.errors[field]);
                            });
                        }
                    }
                },
                error: function(xhr) {
                    iziToast.error({
                        title: 'Error!',
                        message: 'Terjadi kesalahan sistem',
                        position: 'topRight'
                    });
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Avatar form submission
        $('#avatar-form').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            let submitBtn = $(this).find('button[type="submit"]');
            let originalText = submitBtn.html();

            // Show loading state
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Mengupload...').prop('disabled', true);

            $.ajax({
                url: '<?= site_url('profile/upload-avatar') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        iziToast.success({
                            title: 'Berhasil!',
                            message: response.message,
                            position: 'topRight'
                        });

                        // Update profile picture
                        if (response.avatar_url) {
                            $('#profile-picture').attr('src', response.avatar_url);
                        }

                        // Clear form
                        $('#avatar-form')[0].reset();
                    } else {
                        iziToast.error({
                            title: 'Error!',
                            message: response.message,
                            position: 'topRight'
                        });

                        // Show validation errors
                        if (response.errors) {
                            Object.keys(response.errors).forEach(function(field) {
                                let input = $(`[name="${field}"]`);
                                input.addClass('is-invalid');
                                input.siblings('.invalid-feedback').text(response.errors[field]);
                            });
                        }
                    }
                },
                error: function(xhr) {
                    iziToast.error({
                        title: 'Error!',
                        message: 'Terjadi kesalahan sistem',
                        position: 'topRight'
                    });
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Clear validation on input change
        $('input').on('input', function() {
            $(this).removeClass('is-invalid');
        });
    });
</script>
<?= $this->endSection() ?>