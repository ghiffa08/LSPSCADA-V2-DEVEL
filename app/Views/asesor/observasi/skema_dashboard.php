<?= $this->extend('template/layout') ?>

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
                            <a href="<?= base_url($breadcrumb['url']) ?>"><?= $breadcrumb['label'] ?></a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section-body">
            <!-- Asesor Info Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-user-check"></i> Informasi Asesor</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Nama:</strong> <?= esc($asesorInfo['nama']) ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Email:</strong> <?= esc($asesorInfo['email']) ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>ID Asesor:</strong> <?= esc($asesorInfo['id']) ?>
                                </div>
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
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Skema</h4>
                            </div>
                            <div class="card-body">
                                <?= $statistics['total_skema'] ?? 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Asesmen</h4>
                            </div>
                            <div class="card-body">
                                <?= $statistics['total_asesmen'] ?? 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Observasi</h4>
                            </div>
                            <div class="card-body">
                                <?= $statistics['total_observasi'] ?? 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-info">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Asesi</h4>
                            </div>
                            <div class="card-body">
                                <?= $statistics['total_asesi'] ?? 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Skema Sertifikasi List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-certificate"></i> Skema Sertifikasi Anda</h4>
                            <div class="card-header-action">
                                <button class="btn btn-primary btn-sm" onclick="refreshSkemaList()">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($skemaList)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    Tidak ada skema sertifikasi yang tersedia untuk Anda saat ini.
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($skemaList as $skema): ?>
                                        <div class="col-lg-6 col-md-12 mb-3">
                                            <div class="card border-left-primary h-100">
                                                <div class="card-header">
                                                    <h5 class="mb-0">
                                                        <span class="badge badge-<?= $skema['status_skema'] === 'active' ? 'success' : 'secondary' ?> mr-2">
                                                            <?= esc($skema['kode_skema']) ?>
                                                        </span>
                                                        <?= esc($skema['nama_skema']) ?>
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row text-center">
                                                        <div class="col-4">
                                                            <div class="h5 mb-0 text-primary"><?= $skema['total_asesmen'] ?></div>
                                                            <small class="text-muted">Asesmen</small>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="h5 mb-0 text-warning"><?= $skema['total_observasi'] ?></div>
                                                            <small class="text-muted">Observasi</small>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="h5 mb-0 text-success"><?= $skema['completed_observasi'] ?></div>
                                                            <small class="text-muted">Selesai</small>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3">
                                                        <div class="progress mb-2">
                                                            <?php
                                                            $progressPercentage = $skema['total_observasi'] > 0
                                                                ? round(($skema['completed_observasi'] / $skema['total_observasi']) * 100, 1)
                                                                : 0;
                                                            ?>
                                                            <div class="progress-bar bg-success" role="progressbar"
                                                                style="width: <?= $progressPercentage ?>%"
                                                                aria-valuenow="<?= $progressPercentage ?>"
                                                                aria-valuemin="0" aria-valuemax="100">
                                                                <?= $progressPercentage ?>%
                                                            </div>
                                                        </div>
                                                        <small class="text-muted">Progress Observasi</small>
                                                    </div>
                                                </div>
                                                <div class="card-footer">
                                                    <div class="btn-group btn-group-sm w-100" role="group">
                                                        <button type="button" class="btn btn-outline-info"
                                                            onclick="viewSkemaDetail(<?= $skema['id_skema'] ?>)">
                                                            <i class="fas fa-eye"></i> Detail
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary"
                                                            onclick="viewAsesmenList(<?= $skema['id_skema'] ?>)">
                                                            <i class="fas fa-list"></i> Asesmen
                                                        </button>
                                                        <button type="button" class="btn btn-outline-success"
                                                            onclick="createObservasi(<?= $skema['id_skema'] ?>)">
                                                            <i class="fas fa-plus"></i> Observasi
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-history"></i> Aktivitas Terbaru</h4>
                        </div>
                        <div class="card-body">
                            <div class="activities" id="recent-activities">
                                <div class="text-center py-3">
                                    <i class="fas fa-spinner fa-spin"></i> Memuat aktivitas...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Skema Detail Modal -->
<div class="modal fade" id="skemaDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Skema Sertifikasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="skemaDetailContent">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Asesmen List Modal -->
<div class="modal fade" id="asesmenListModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Asesmen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="asesmenListContent">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        loadRecentActivities();
    });

    function refreshSkemaList() {
        location.reload();
    }

    function viewSkemaDetail(skemaId) {
        $('#skemaDetailContent').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat detail...</div>');
        $('#skemaDetailModal').modal('show');

        $.ajax({
            url: '<?= base_url('api/skema/detail') ?>/' + skemaId,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    $('#skemaDetailContent').html(response.html);
                } else {
                    $('#skemaDetailContent').html('<div class="alert alert-danger">Gagal memuat detail skema</div>');
                }
            },
            error: function() {
                $('#skemaDetailContent').html('<div class="alert alert-danger">Terjadi kesalahan</div>');
            }
        });
    }

    function viewAsesmenList(skemaId) {
        $('#asesmenListContent').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat daftar asesmen...</div>');
        $('#asesmenListModal').modal('show');

        $.ajax({
            url: '<?= base_url('api/asesmen/by-skema') ?>/' + skemaId,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    $('#asesmenListContent').html(response.html);
                } else {
                    $('#asesmenListContent').html('<div class="alert alert-danger">Gagal memuat daftar asesmen</div>');
                }
            },
            error: function() {
                $('#asesmenListContent').html('<div class="alert alert-danger">Terjadi kesalahan</div>');
            }
        });
    }

    function createObservasi(skemaId) {
        window.location.href = '<?= base_url('asesor/observasi/create') ?>?skema_id=' + skemaId;
    }

    function loadRecentActivities() {
        $.ajax({
            url: '<?= base_url('api/asesor/recent-activities') ?>',
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    $('#recent-activities').html(response.html);
                } else {
                    $('#recent-activities').html('<div class="text-muted text-center">Tidak ada aktivitas terbaru</div>');
                }
            },
            error: function() {
                $('#recent-activities').html('<div class="text-muted text-center">Gagal memuat aktivitas</div>');
            }
        });
    }
</script>
<?= $this->endSection() ?>