<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<!-- Welcome Section for Assessor -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="hero bg-primary text-white">
            <div class="hero-inner">
                <h2>Dashboard Asesor - <?= user()->fullname ?></h2>
                <p class="lead">Kelola proses asesmen dan validasi dengan mudah di sini.</p>
                <div class="mt-4">
                    <a href="<?= site_url('/kelola_apl2/validasi') ?>" class="btn btn-outline-white btn-lg btn-icon icon-left">
                        <i class="fas fa-clipboard-check"></i> Validasi FR.APL.02
                    </a>
                    <a href="<?= site_url('/asesor/observasi') ?>" class="btn btn-light btn-lg btn-icon icon-left ml-2">
                        <i class="fas fa-eye"></i> Ceklist Observasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>FR.APL.01 Pending</h4>
                </div>
                <div class="card-body">
                    <?= $totalAPL1Pending ?? '-' ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-danger">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>FR.APL.02 Pending</h4>
                </div>
                <div class="card-body">
                    <?= $totalAPL2Pending ?? '-' ?>
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
                    <h4>APL.02 Divalidasi</h4>
                </div>
                <div class="card-body">
                    <?= $totalAPL2Validated ?? '-' ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-info">
                <i class="fas fa-eye"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Observasi</h4>
                </div>
                <div class="card-body">
                    <?= $totalObservasi ?? '-' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions and Recent Activities -->
<div class="row">
    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header">
                <h4><i class="fas fa-bolt mr-2"></i>Aksi Cepat</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <a href="<?= base_url('/kelola_apl2') ?>" class="btn btn-outline-primary btn-block btn-icon-split">
                            <i class="fas fa-clipboard-list"></i> Kelola FR.APL.02
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="<?= base_url('/kelola_apl2/validasi') ?>" class="btn btn-outline-success btn-block btn-icon-split">
                            <i class="fas fa-clipboard-check"></i> Validasi FR.APL.02
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="<?= base_url('/persetujuan-asesmen') ?>" class="btn btn-outline-warning btn-block btn-icon-split">
                            <i class="fas fa-handshake"></i> Persetujuan Asesmen
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="<?= base_url('/asesor/observasi') ?>" class="btn btn-outline-info btn-block btn-icon-split">
                            <i class="fas fa-eye"></i> Observasi
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="<?= base_url('/monitoring-asesi') ?>" class="btn btn-outline-secondary btn-block btn-icon-split">
                            <i class="fas fa-chart-line"></i> Monitoring
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="<?= base_url('/asesor/feedback') ?>" class="btn btn-outline-primary btn-block btn-icon-split">
                            <i class="fas fa-comment-dots"></i> Feedback
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h4><i class="fas fa-history mr-2"></i>Aktivitas Terbaru</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($recentActivities)) : ?>
                    <div class="activities">
                        <?php foreach ($recentActivities as $activity) : ?>
                            <div class="activity">
                                <div class="activity-icon bg-<?= $activity['color'] ?> text-white shadow-<?= $activity['color'] ?>">
                                    <i class="<?= $activity['icon'] ?>"></i>
                                </div>
                                <div class="activity-detail">
                                    <div class="mb-2">
                                        <span class="text-job font-weight-bold text-<?= $activity['color'] ?>"><?= $activity['title'] ?></span>
                                        <span class="bullet mx-2"></span>
                                        <span class="text-job text-muted"><?= date('d M Y H:i', strtotime($activity['time'])) ?></span>
                                    </div>
                                    <h6 class="text-muted"><?= $activity['description'] ?></h6>
                                    <?php if ($activity['status']) : ?>
                                        <span class="badge badge-<?= $activity['color'] ?>"><?= ucfirst($activity['status']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="empty-state text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada aktivitas</h5>
                        <p class="text-muted">Mulai melakukan validasi atau observasi untuk melihat aktivitas di sini.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center pt-3 pb-3">
                <a href="<?= base_url('/monitoring-asesi') ?>" class="btn btn-primary btn-sm btn-round">Lihat Semua Aktivitas</a>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Statistics Chart -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h4><i class="fas fa-chart-bar mr-2"></i>Statistik Bulanan <?= date('Y') ?></h4>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Upcoming Assessments -->
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header">
                <h4><i class="fas fa-calendar-alt mr-2"></i>Asesmen Mendatang</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($upcomingAssessments)) : ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($upcomingAssessments as $assessment) : ?>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= $assessment['nama_skema'] ?></h6>
                                    <small class="text-muted"><?= date('d M', strtotime($assessment['tanggal'])) ?></small>
                                </div>
                                <p class="mb-1 text-muted"><?= $assessment['waktu'] ?></p>
                                <small class="text-muted"><?= $assessment['tempat'] ?? 'TUK belum ditentukan' ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="empty-state text-center py-3">
                        <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Tidak ada asesmen mendatang</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center pt-3 pb-3">
                <a href="<?= base_url('/asesmen') ?>" class="btn btn-primary btn-sm btn-round">Lihat Semua Jadwal</a>
            </div>
        </div>
    </div>
</div>

<!-- Progress Tracking -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h4><i class="fas fa-tasks mr-2"></i>Progress Kerja Asesor</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h4>Validasi APL.02</h4>
                            </div>
                            <div class="card-body">
                                <div class="progress-wrapper">
                                    <div class="progress-info">
                                        <div class="progress-label">
                                            <span>Progress</span>
                                        </div>
                                        <div class="progress-percentage">
                                            <span><?= $totalAPL2Pending + $totalAPL2Validated > 0 ? round(($totalAPL2Validated / ($totalAPL2Pending + $totalAPL2Validated)) * 100) : 0 ?>%</span>
                                        </div>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: <?= $totalAPL2Pending + $totalAPL2Validated > 0 ? round(($totalAPL2Validated / ($totalAPL2Pending + $totalAPL2Validated)) * 100) : 0 ?>%"></div>
                                    </div>
                                </div>
                                <p class="mb-0 text-muted">
                                    <?= $totalAPL2Validated ?> dari <?= $totalAPL2Pending + $totalAPL2Validated ?> selesai
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-success">
                            <div class="card-header">
                                <h4>Observasi</h4>
                            </div>
                            <div class="card-body">
                                <div class="progress-wrapper">
                                    <div class="progress-info">
                                        <div class="progress-label">
                                            <span>Completed</span>
                                        </div>
                                        <div class="progress-percentage">
                                            <span><?= $totalObservasi ?></span>
                                        </div>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                                    </div>
                                </div>
                                <p class="mb-0 text-muted">Total observasi selesai</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h4>Persetujuan</h4>
                            </div>
                            <div class="card-body">
                                <div class="progress-wrapper">
                                    <div class="progress-info">
                                        <div class="progress-label">
                                            <span>Total</span>
                                        </div>
                                        <div class="progress-percentage">
                                            <span><?= $totalPersetujuanAsesmen ?></span>
                                        </div>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 100%"></div>
                                    </div>
                                </div>
                                <p class="mb-0 text-muted">Persetujuan asesmen</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-info">
                            <div class="card-header">
                                <h4>Bulan Ini</h4>
                            </div>
                            <div class="card-body">
                                <div class="progress-wrapper">
                                    <div class="progress-info">
                                        <div class="progress-label">
                                            <span>Aktivitas</span>
                                        </div>
                                        <div class="progress-percentage">
                                            <span><?= isset($monthlyStats[date('n') - 1]) ? $monthlyStats[date('n') - 1]['total_activities'] : 0 ?></span>
                                        </div>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                                    </div>
                                </div>
                                <p class="mb-0 text-muted">Total aktivitas bulan ini</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Statistics Chart
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyData = <?= json_encode($monthlyStats) ?>;

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => item.month_name),
                datasets: [{
                    label: 'APL.02 Divalidasi',
                    data: monthlyData.map(item => item.apl2_validated),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Observasi Selesai',
                    data: monthlyData.map(item => item.observasi_completed),
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                }
            }
        });
    });
</script>

<?= $this->endSection() ?>