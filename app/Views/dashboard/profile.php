<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row mt-sm-4">
    <div class="col-12 col-md-12 col-lg-5">
        <div class="card profile-widget">
            <div class="profile-widget-header">
                <img alt="image" src="<?= base_url() ?>/stisla/assets/img/avatar/<?= user()->user_image ?>" class="rounded-circle profile-widget-picture">
            </div>
            <div class="profile-widget-description">
                <div class="profile-widget-name"><?= user()->username ?><div class="text-muted d-inline font-weight-normal">
                        <div class="slash"></div>
                        <span class="badge badge-success">
                            <?= $user->name; ?>
                        </span>
                    </div>
                </div>
                <!-- Ujang maman is a superhero name in <b>Indonesia</b>, especially in my family. He is not a fictional character but an original hero in my family, a hero for his children and for his wife. So, I use the name as a user in this template. Not a tribute, I'm just bored with <b>'John Doe'</b>. -->
            </div>
        </div>
    </div>
    <div class="col-12 col-md-12 col-lg-7">
        <div class="card">
            <form method="POST" action="<?= site_url('user-update'); ?>" class="needs-validation" novalidate="">
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
                        <div class="form-group col-md-7 col-12">
                            <label>Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?= user()->email ?>" readonly>
                            <div class="invalid-feedback">
                                Please fill in the email
                            </div>
                        </div>
                        <div class="form-group col-md-5 col-12">
                            <label>Role</label>
                            <input type="email" class="form-control" value=" <?= $user->name; ?>" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Avatar</label>
                        <div class="selectgroup selectgroup-pills">
                            <label class="selectgroup-item">
                                <input type="radio" name="user_image" value="avatar-1.png" class="selectgroup-input" <?= (user()->user_image == 'avatar-1.png') ? 'checked' : '' ?>>
                                <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-user"></i></span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="user_image" value="avatar-2.png" class="selectgroup-input" <?= (user()->user_image == 'avatar-2.png') ? 'checked' : '' ?>>
                                <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-user text-success"></i></span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="user_image" value="avatar-5.png" class="selectgroup-input" <?= (user()->user_image == 'avatar-5.png') ? 'checked' : '' ?>>
                                <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-user text-danger"></i></span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="user_image" value="avatar-3.png" class="selectgroup-input" <?= (user()->user_image == 'avatar-3.png') ? 'checked' : '' ?>>
                                <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-user text-info"></i></span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="user_image" value="avatar-4.png" class="selectgroup-input" <?= (user()->user_image == 'avatar-4.png') ? 'checked' : '' ?>>
                                <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-user text-warning"></i></span>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group mb-0 col-12">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="remember" class="custom-control-input" id="newsletter">
                                <label class="custom-control-label" for="newsletter">Subscribe to newsletter</label>
                                <div class="text-muted form-text">
                                    You will get new information about products, offers and promotions
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>