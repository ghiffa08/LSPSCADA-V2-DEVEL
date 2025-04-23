<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>


<?php
if (empty(user()->tanda_tangan)) {
?>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="hero bg-primary text-white">
                <div class="hero-inner">
                    <h2>Selamat Datang di LSP SCADA APP, <?= user()->username ?>!</h2>
                    <p class="lead">Lengkapi Data Diri anda untuk menyelesaikan registrasi.</p>
                    <div class="mt-4">
                        <a href="<?= site_url('profile/' . user()->id); ?>" class="btn btn-outline-white btn-lg btn-icon icon-left"><i class="far fa-user"></i>Mohon Lengkapi Data Diri</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<div class="row">
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-primary">
                <i class="far fa-user"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Admin</h4>
                </div>
                <div class="card-body">
                    <?= $totalAdmin ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-danger">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Asesor</h4>
                </div>
                <div class="card-body">
                    <?= $totalAsesor ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-warning">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Asesi</h4>
                </div>
                <div class="card-body">
                    <?= $totalAsesi ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>