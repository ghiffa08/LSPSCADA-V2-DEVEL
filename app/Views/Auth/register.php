<?= $this->extend($config->viewLayout) ?>
<?= $this->section('main') ?>

<section class="section">
    <div class="container mt-5">
        <div class="row">
            <div class="col-12 col-sm-8 offset-sm-2 col-md-8 offset-md-3 col-lg-8 offset-lg-3 col-xl-5 offset-xl-4">

                <div class="card card-primary">
                    <div class="card-header">
                        <h4><?= lang('Auth.register') ?></h4>
                    </div>

                    <div class="card-body">

                        <?= view('App\Views\Auth\_message_block') ?>

                        <form action="<?= url_to('register') ?>" method="post">
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
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="fullname" class="form-control <?php if (session('errors.fullname')) : ?>is-invalid<?php endif ?>" placeholder="Nama Lengkap Asesor" value="<?= old('fullname') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">No Handphone</label>
                                <input type="text" name="no_telp" class="form-control <?php if (session('errors.no_telp')) : ?>is-invalid<?php endif ?>" placeholder="Nomor Handphone/Whatsapp" value="<?= old('no_telp') ?>">
                            </div>

                            <div class="form-group">
                                <label for="password"><?= lang('Auth.password') ?></label>
                                <input type="password" name="password" class="form-control <?php if (session('errors.password')) : ?>is-invalid<?php endif ?>" placeholder="<?= lang('Auth.password') ?>" autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label for="pass_confirm"><?= lang('Auth.repeatPassword') ?></label>
                                <input type="password" name="pass_confirm" class="form-control <?php if (session('errors.pass_confirm')) : ?>is-invalid<?php endif ?>" placeholder="<?= lang('Auth.repeatPassword') ?>" autocomplete="off">
                            </div>

                            <br>

                            <button type="submit" class="btn btn-primary btn-block"><?= lang('Auth.register') ?></button>
                        </form>


                        <hr>

                        <p><?= lang('Auth.alreadyRegistered') ?> <a href="<?= url_to('login') ?>"><?= lang('Auth.signIn') ?></a></p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>




<?= $this->endSection() ?>