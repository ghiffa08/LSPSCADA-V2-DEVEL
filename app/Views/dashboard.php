<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>


<?php
if (empty(user()->fullname) && empty(user()->no_telp)) {
?>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="hero bg-primary text-white">
                <div class="hero-inner">
                    <h2>Welcome, <?= user()->username ?>!</h2>
                    <p class="lead">Lengkapi Data Diri anda untuk menyelesaikan registrasi.</p>
                    <div class="mt-4">
                        <a href="<?= site_url('profile/' . user()->id); ?>" class="btn btn-outline-white btn-lg btn-icon icon-left"><i class="far fa-user"></i>Lengkapi Data Diri</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<?php if (in_groups('Peserta')) { ?>
    <div class="card">
        <div class="card-header">
            <h4>Alur Sertifikasi LSP SMKN 2 Kuningan</h4>
        </div>
        <div class="card-body">
            <div class="row mt-4">
                <div class="col-12 col-lg-8 offset-lg-2">
                    <div class="wizard-steps">
                        <div class="wizard-step wizard-step-active">
                            <div class="wizard-step-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="wizard-step-label">
                                Lengkapi Data Diri
                            </div>
                        </div>
                        <div class="wizard-step wizard-step-active">
                            <div class="wizard-step-icon">
                                <i class="fas fa-pen"></i>
                            </div>
                            <div class="wizard-step-label">
                                Pilih Skema Sertifikasi
                            </div>
                        </div>
                        <div class="wizard-step wizard-step-active">
                            <div class="wizard-step-icon">
                                <i class="fas fa-file"></i>
                            </div>
                            <div class="wizard-step-label">
                                Mengisi Form APL 1
                            </div>
                        </div>
                        <div class="wizard-step wizard-step-active">
                            <div class="wizard-step-icon">
                                <i class="fas fa-file"></i>
                            </div>
                            <div class="wizard-step-label">
                                Mengisi Form APL 1
                            </div>
                        </div>
                        <div class="wizard-step wizard-step-success">
                            <div class="wizard-step-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="wizard-step-label">
                                Order Completed
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6 col-lg-6">

            <div class="card">
                <div class="card-header">
                    <h4>Skema Sertifikasi</h4>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($listSkema as $value) { ?>
                            <a href="#" class="list-group-item list-group-item-action flex-column align-items-start">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= $value['kode_skema'] ?></h6>
                                </div>
                                <p class="mb-1"><?= $value['nama_skema'] ?></p>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            <?php
        } else {
            ?>

                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="far fa-user"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Admin</h4>
                                </div>
                                <div class="card-body">
                                    10
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-danger">
                                <i class="far fa-newspaper"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>News</h4>
                                </div>
                                <div class="card-body">
                                    42
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="far fa-file"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Reports</h4>
                                </div>
                                <div class="card-body">
                                    1,201
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-circle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Online Users</h4>
                                </div>
                                <div class="card-body">
                                    47
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class=" col-md-6 col-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>APL 1 success</h4>
                                <div class="card-header-action">
                                    <a href="#" class="btn btn-primary">View All</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                                <th>APL 2</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($listAPL1Sucess as $value) { ?>
                                                <tr>
                                                    <td>
                                                        <a href="#" class="font-weight-600"><img src="<?= base_url('upload/pas foto/' . $value['pas_foto']) ?>" alt="avatar" width="30" class="rounded-circle mr-1"><?= $value['nama_siswa'] ?></a>
                                                    </td>
                                                    <td>
                                                        <div class="badge badge-success"><?= ($value['validasi_apl1'] == "N") ? 'Belum divalidasi' : 'Tervalidasi' ?></div>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-primary btn-action mr-1" target="_blank" data-toggle="tooltip" title="PDF" href="<?= site_url('/apl1-pdf-' . $value['id_apl1']) ?>"><i class="fas fa-file"></i></a>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-primary btn-action mr-1" target="_blank" data-toggle="tooltip" title="APL 2" href="<?= site_url('/asesmen-mandiri/' . $value['id_apl1']) ?>"><i class="fas fa-file"></i></a>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class=" col-md-6 col-12 col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Validasi APL 1</h4>
                                <div class="card-header-action">
                                    <a href="#" class="btn btn-primary">View All</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($listAPL1 as $value) { ?>
                                                <tr>
                                                    <td>
                                                        <a href="#" class="font-weight-600"><img src="<?= base_url(($value['pas_foto']) ? 'upload/pas foto/' . $value['pas_foto'] : 'stisla/assets/img/avatar/avatar-1.png') ?>" alt="avatar" width="30" class="rounded-circle mr-1"><?= $value['nama_siswa'] ?></a>
                                                    </td>
                                                    <td>
                                                        <div class="badge badge-danger"><?= ($value['validasi_apl1'] == "N") ? 'Belum divalidasi' : 'Succes' ?></div>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-primary btn-action mr-1" data-toggle="tooltip" title="Detail" href="<?= site_url('/validasi-apl1/' . $value['id_apl1']) ?>"><i class="fas fa-eye"></i></a>
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
            <?php
        }
            ?>




            <?= $this->endSection() ?>

            <?= $this->section('modals') ?>

            <?= $this->endSection() ?>