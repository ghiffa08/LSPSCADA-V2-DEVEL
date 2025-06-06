<?= $this->extend($config->viewLayout) ?>
<?= $this->section('main'); ?>

<section class="section">
	<div class="container mt-5">
		<div class="row">
			<div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-5 offset-xl-4">
				<!-- <div class="login-brand">
          <img src="../assets/img/stisla-fill.svg" alt="logo" width="100" class="shadow-light rounded-circle">
        </div> -->

				<div class="card card-primary">
					<div class="card-header">
						<h4><?= lang('Auth.loginTitle') ?></h4>
					</div>

					<div class="card-body">
						<?= view('App\Views\Auth\_message_block') ?>
						<form action="<?= url_to('login') ?>" method="post" class="needs-validation" novalidate="">
							<?= csrf_field() ?>

							<?php if ($config->validFields === ['email']) : ?>
								<div class="form-group">
									<label for="login"><?= lang('Auth.email') ?></label>
									<input type="email" class="form-control <?php if (session('errors.login')) : ?>is-invalid<?php endif ?>" name="login" placeholder="<?= lang('Auth.email') ?>">
									<div class="invalid-feedback">
										<?= session('errors.login') ?>
									</div>
								</div>
							<?php else : ?>
								<div class="form-group">
									<label for="login"><?= lang('Auth.emailOrUsername') ?></label>
									<input type="text" class="form-control <?php if (session('errors.login')) : ?>is-invalid<?php endif ?>" name="login" placeholder="<?= lang('Auth.emailOrUsername') ?>">
									<div class="invalid-feedback">
										<?= session('errors.login') ?>
									</div>
								</div>
							<?php endif; ?>

							<div class="form-group">
								<label for="password"><?= lang('Auth.password') ?></label>
								<input type="password" name="password" class="form-control  <?php if (session('errors.password')) : ?>is-invalid<?php endif ?>" placeholder="<?= lang('Auth.password') ?>">
								<div class="invalid-feedback">
									<?= session('errors.password') ?>
								</div>
							</div>

							<?php if ($config->allowRemembering) : ?>
								<div class="form-group">
									<div class="custom-control custom-checkbox">
										<input type="checkbox" name="remember" class="custom-control-input" tabindex="3" id="remember-me" <?php if (old('remember')) : ?> checked <?php endif ?>>
										<label class="custom-control-label" for="remember-me"> <?= lang('Auth.rememberMe') ?></label>
									</div>
								</div>
							<?php endif; ?>

							<div class="form-group">
								<button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
									<?= lang('Auth.loginAction') ?>
								</button>
							</div>
							<div class="text-center mt-2 mb-3">
								<div class="text-job text-muted">OR</div>
							</div>
							<a href="<?= site_url('auth/google') ?>" class="btn btn-block btn-social btn-google">
								<span class="fab fa-google"></span> Google
							</a>
							<!-- $link -->
							<hr>

							<div class="d-flex">
								<?php if ($config->allowRegistration) : ?>
									<p class="mr-auto"><a href="<?= url_to('register') ?>"><?= lang('Auth.needAnAccount') ?></a></p>
								<?php endif; ?>
								<?php if ($config->activeResetter) : ?>
									<p><a href="<?= url_to('forgot') ?>"><?= lang('Auth.forgotYourPassword') ?></a></p>
								<?php endif; ?>
							</div>


						</form>

					</div>
				</div>
				<div class="simple-footer">
					Copyright &copy; LSP SMKN 2 Kuningan.
				</div>
			</div>
		</div>
	</div>
</section>



<?= $this->endSection(); ?>