<?= $this->extend("layouts/asesor/layout-asesor"); ?>
<?= $this->section("content"); ?>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('pesan')) : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Berhasil!</strong> <?= session()->getFlashdata('pesan') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="section-body">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Daftar Asesi</h4>
                    <div class="card-header-action">
                        <div class="badge badge-info">
                            <i class="fas fa-users"></i> Total: <?= count($listAsesi) ?> Asesi
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">Berikut adalah daftar asesi yang terdaftar dalam sistem. Sebagai asesor, Anda dapat melihat informasi asesi dan mengelola asesmen mereka.</p>

                    <div class="table-responsive">
                        <table id="table-2" class="table table-striped table-md">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Email</th>
                                    <th>Username</th>
                                    <th>Nama Lengkap</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 0;
                                foreach ($listAsesi as $value) {
                                    $no++;
                                ?>
                                    <tr>
                                        <td><?= $no ?></td>
                                        <td><?= $value['email'] ?></td>
                                        <td><?= $value['username'] ?></td>
                                        <td><?= $value['userfullname'] ?></td>
                                        <td>
                                            <div class="badge badge-success">Aktif</div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Actions">
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#viewAsesiModal-<?= $value['userid']; ?>" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary" title="Kelola Asesmen">
                                                    <i class="fas fa-clipboard-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" title="Buat Laporan">
                                                    <i class="fas fa-file-alt"></i>
                                                </button>
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

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Asesi</h4>
                    </div>
                    <div class="card-body">
                        <?= count($listAsesi) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Menunggu Asesmen</h4>
                    </div>
                    <div class="card-body">
                        0
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Asesmen Selesai</h4>
                    </div>
                    <div class="card-body">
                        0
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Perlu Review</h4>
                    </div>
                    <div class="card-body">
                        0
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<!-- View Asesi Details Modal -->
<?php foreach ($listAsesi as $value): ?>
    <div class="modal fade" id="viewAsesiModal-<?= $value['userid'] ?>" tabindex="-1" role="dialog" aria-labelledby="viewAsesiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAsesiModalLabel">Detail Asesi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><?= $value['email'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Username:</strong></td>
                                    <td><?= $value['username'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Lengkap:</strong></td>
                                    <td><?= $value['userfullname'] ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Role:</strong></td>
                                    <td>
                                        <div class="badge badge-warning">Asesi</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <div class="badge badge-success">Aktif</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Assessment Progress Section -->
                    <div class="row">
                        <div class="col-12">
                            <h6><strong>Progress Asesmen</strong></h6>
                            <div class="progress mb-3" style="height: 25px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    0% Selesai
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Status Asesmen:</strong> <span class="badge badge-secondary">Belum Dimulai</span></p>
                                    <p><strong>Tanggal Daftar:</strong> -</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Skema Sertifikasi:</strong> -</p>
                                    <p><strong>Asesor:</strong> <?= $user->nama_lengkap ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary">
                        <i class="fas fa-clipboard-check"></i> Kelola Asesmen
                    </button>
                    <button type="button" class="btn btn-warning">
                        <i class="fas fa-file-alt"></i> Buat Laporan
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?= $this->endSection() ?>