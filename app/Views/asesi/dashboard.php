<?= $this->extend("layouts/asesi/layout-app") ?>
<?= $this->section("content") ?>

<!-- Welcome Section - Enhanced with gradients and better visual hierarchy -->
<div class="row">
    <div class="col-12">
        <div class="hero bg-primary text-white">
            <div class="hero-inner">
                <h2>Selamat Datang di Dashboard LSP</h2>
                <p class="lead">Pantau kemajuan sertifikasi dan kelola dokumen Anda dengan mudah di sini.</p>
                <div class="mt-4">
                    <a href="<?= site_url('/asesi/pengajuan') ?>" class="btn btn-outline-white btn-lg btn-icon icon-left"><i class="fas fa-file-signature"></i> Ajukan Sertifikasi</a>
                    <a href="<?= site_url('/asesi/dokumen') ?>" class="btn btn-light btn-lg btn-icon icon-left ml-2"><i class="fas fa-folder-open"></i> Dokumen Saya</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards - Improved with better spacing and visual appeal -->
<div class="row mt-4">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-statistic-1 shadow-sm">
            <div class="card-icon bg-primary">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Status</h4>
                </div>
                <div class="card-body text-primary font-weight-bold">
                    Aktif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-statistic-1 shadow-sm">
            <div class="card-icon bg-success">
                <i class="fas fa-certificate"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Sertifikasi</h4>
                </div>
                <div class="card-body text-success font-weight-bold">
                    2 Proses
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-statistic-1 shadow-sm">
            <div class="card-icon bg-warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Menunggu</h4>
                </div>
                <div class="card-body text-warning font-weight-bold">
                    1 Verifikasi
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-statistic-1 shadow-sm">
            <div class="card-icon bg-info">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Dokumen</h4>
                </div>
                <div class="card-body text-info font-weight-bold">
                    5 Tersimpan
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Certification Progress - Enhanced with modern timeline -->
    <div class="col-lg-8">
        <div class="card card-large-icons shadow">
            <div class="card-header">
                <h4><i class="fas fa-clipboard-check mr-2"></i>Progres Sertifikasi</h4>
                <div class="card-header-action">
                    <a href="#" class="btn btn-primary btn-icon"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="font-weight-bold">Proses Sertifikasi</div>
                        <div class="text-primary font-weight-bold">50%</div>
                    </div>
                    <div class="progress mb-3" data-height="10" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" data-width="50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="width: 50%;"></div>
                    </div>
                </div>
                
                <div class="activities px-4 pb-4">
                    <div class="activity">
                        <div class="activity-icon bg-success text-white shadow-success">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="activity-detail">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-job font-weight-bold text-primary">Langkah 1</span>
                                    <span class="bullet mx-2"></span>
                                    <span class="text-job">Selesai</span>
                                </div>
                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>
                            </div>
                            <h5>Pengajuan Sertifikasi</h5>
                            <p class="text-muted">Anda telah menyelesaikan pendaftaran dan pengajuan sertifikasi.</p>
                        </div>
                    </div>
                    <div class="activity">
                        <div class="activity-icon bg-success text-white shadow-success">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="activity-detail">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-job font-weight-bold text-primary">Langkah 2</span>
                                    <span class="bullet mx-2"></span>
                                    <span class="text-job">Selesai</span>
                                </div>
                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>
                            </div>
                            <h5>Asesmen Mandiri</h5>
                            <p class="text-muted">Anda telah mengisi formulir asesmen mandiri.</p>
                        </div>
                    </div>
                    <div class="activity">
                        <div class="activity-icon bg-primary text-white shadow-primary">
                            <i class="fas fa-sync"></i>
                        </div>
                        <div class="activity-detail">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-job font-weight-bold text-primary">Langkah 3</span>
                                    <span class="bullet mx-2"></span>
                                    <span class="text-job">Dalam Proses</span>
                                </div>
                                <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i> Menunggu</span>
                            </div>
                            <h5>Verifikasi Kelengkapan</h5>
                            <p class="text-muted">Administrator sedang memverifikasi dokumen Anda.</p>
                            <a href="<?= base_url('asesi/dokumen') ?>" class="btn btn-sm btn-primary mt-2">Lihat Dokumen</a>
                        </div>
                    </div>
                    <div class="activity">
                        <div class="activity-icon bg-secondary text-white shadow-secondary">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="activity-detail">
                            <div class="mb-2">
                                <span class="text-job font-weight-bold text-primary">Langkah 4</span>
                                <span class="bullet mx-2"></span>
                                <span class="text-job text-muted">Belum Dimulai</span>
                            </div>
                            <h5 class="text-muted">Penilaian Asesor</h5>
                            <p class="text-muted">Menunggu penilaian dari asesor.</p>
                        </div>
                    </div>
                    <div class="activity">
                        <div class="activity-icon bg-secondary text-white shadow-secondary">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="activity-detail">
                            <div class="mb-2">
                                <span class="text-job font-weight-bold text-primary">Langkah 5</span>
                                <span class="bullet mx-2"></span>
                                <span class="text-job text-muted">Belum Dimulai</span>
                            </div>
                            <h5 class="text-muted">Keputusan Sertifikasi</h5>
                            <p class="text-muted">Menunggu keputusan kompetensi.</p>
                        </div>
                    </div>
                    <div class="activity">
                        <div class="activity-icon bg-secondary text-white shadow-secondary">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="activity-detail">
                            <div class="mb-2">
                                <span class="text-job font-weight-bold text-primary">Langkah 6</span>
                                <span class="bullet mx-2"></span>
                                <span class="text-job text-muted">Belum Dimulai</span>
                            </div>
                            <h5 class="text-muted">Sertifikat Diterbitkan</h5>
                            <p class="text-muted">Penerbitan sertifikat kompetensi.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile & Quick Menu - Enhanced visual appeal -->
    <div class="col-lg-4">
        <!-- Profile Card - Improved layout -->
        <div class="card card-hero shadow">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-user"></i>
                </div>
                <h4>Profil Asesi</h4>
                <div class="card-description">Informasi akun Anda</div>
            </div>
            <div class="card-body p-0">
                <div class="p-4 text-center">
                    <img alt="image" src="<?= base_url('assets/img/avatar/avatar-1.png') ?>" class="rounded-circle profile-widget-picture shadow" width="100">
                    <div class="mt-3 font-weight-bold h4"><?= session()->get('name') ?? 'Asesi' ?></div>
                    <div class="text-muted mb-3"><?= session()->get('email') ?? 'email@example.com' ?></div>
                    <button class="btn btn-primary btn-icon icon-left">
                        <i class="fas fa-id-card-alt"></i> Lihat Profil Lengkap
                    </button>
                </div>
                <div class="px-4 py-3 border-top">
                    <div class="row align-items-center">
                        <div class="col-6 text-center">
                            <div class="text-small font-weight-bold">Skema Sertifikasi</div>
                            <div class="text-small text-muted">Operator SCADA</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="text-small font-weight-bold">Status</div>
                            <span class="badge badge-primary">Asesi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Menu - Enhanced with better icons and layout -->
        <div class="card shadow mt-4">
            <div class="card-header">
                <h4><i class="fas fa-th mr-2"></i>Menu Cepat</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <a href="<?= base_url('asesi/pengajuan') ?>" class="btn btn-outline-primary btn-block btn-icon-split">
                            <i class="fas fa-file-signature"></i> Pengajuan
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="<?= base_url('asesi/asesmen') ?>" class="btn btn-outline-success btn-block btn-icon-split">
                            <i class="fas fa-tasks"></i> Asesmen
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="<?= base_url('asesi/dokumen') ?>" class="btn btn-outline-warning btn-block btn-icon-split">
                            <i class="fas fa-folder-open"></i> Dokumen
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="<?= base_url('asesi/feedback') ?>" class="btn btn-outline-info btn-block btn-icon-split">
                            <i class="fas fa-comment-dots"></i> Feedback
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule & Announcements - Enhanced layout -->
<div class="row mt-4">
    <!-- Schedule - Modernized table design -->
    <div class="col-lg-6">
        <div class="card card-primary shadow">
            <div class="card-header">
                <h4><i class="fas fa-calendar-alt mr-2"></i>Jadwal Asesmen</h4>
                <div class="card-header-action">
                    <div class="btn-group">
                        <a href="#" class="btn active">Mendatang</a>
                        <a href="#" class="btn">Selesai</a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kegiatan</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-weight-medium">30 Mei 2025</td>
                                <td>Verifikasi Portofolio</td>
                                <td class="text-center">
                                    <div class="badge badge-warning">Menunggu</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-medium">5 Juni 2025</td>
                                <td>Uji Kompetensi</td>
                                <td class="text-center">
                                    <div class="badge badge-info">Terjadwal</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-medium">20 Juni 2025</td>
                                <td>Penetapan Keputusan</td>
                                <td class="text-center">
                                    <div class="badge badge-light">Belum Mulai</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-center pt-3 pb-3">
                    <a href="#" class="btn btn-primary btn-sm btn-round">Lihat Semua Jadwal</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcements - Enhanced design -->
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header">
                <h4><i class="fas fa-bullhorn mr-2"></i>Pengumuman</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary btn-icon"><i class="fas fa-bell"></i></button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between mb-2">
                            <h6 class="mb-1 font-weight-bold">Jadwal Asesmen Periode Juni 2025</h6>
                            <small class="text-muted">3 hari yang lalu</small>
                        </div>
                        <p class="mb-2 text-muted">LSP membuka pendaftaran untuk periode sertifikasi Juni 2025. Harap segera melengkapi dokumen.</p>
                        <div class="d-flex w-100 justify-content-between">
                            <small class="text-primary">Admin LSP</small>
                            <a href="#" class="btn btn-sm btn-outline-primary">Detail</a>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between mb-2">
                            <h6 class="mb-1 font-weight-bold">Pembaruan Skema Kompetensi</h6>
                            <small class="text-muted">1 minggu yang lalu</small>
                        </div>
                        <p class="mb-2 text-muted">Terdapat pembaruan pada beberapa skema kompetensi. Silakan periksa detail perubahan.</p>
                        <div class="d-flex w-100 justify-content-between">
                            <small class="text-primary">Admin LSP</small>
                            <a href="#" class="btn btn-sm btn-outline-primary">Detail</a>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center pt-3 pb-3">
                    <a href="#" class="btn btn-primary btn-sm btn-round">Lihat Semua Pengumuman</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Certification Steps - Modern wizard interface -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h4><i class="fas fa-map-signs mr-2"></i>Langkah-langkah Sertifikasi</h4>
            </div>
            <div class="card-body">
                <div class="wizard-steps">
                    <div class="wizard-step wizard-step-active">
                        <div class="wizard-step-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="wizard-step-label">
                            Pendaftaran
                        </div>
                        <div class="mt-2 text-muted text-small">Registrasi akun dan lengkapi data diri</div>
                    </div>
                    <div class="wizard-step wizard-step-active">
                        <div class="wizard-step-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="wizard-step-label">
                            Asesmen Mandiri
                        </div>
                        <div class="mt-2 text-muted text-small">Mengisi formulir penilaian diri</div>
                    </div>
                    <div class="wizard-step wizard-step-active">
                        <div class="wizard-step-icon">
                            <i class="fas fa-file-upload"></i>
                        </div>
                        <div class="wizard-step-label">
                            Upload Dokumen
                        </div>
                        <div class="mt-2 text-muted text-small">Mengunggah bukti pendukung</div>
                    </div>
                    <div class="wizard-step wizard-step-warning">
                        <div class="wizard-step-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="wizard-step-label">
                            Verifikasi
                        </div>
                        <div class="mt-2 text-muted text-small">Pemeriksaan kelengkapan dokumen</div>
                    </div>
                    <div class="wizard-step">
                        <div class="wizard-step-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="wizard-step-label">
                            Pelaksanaan Asesmen
                        </div>
                        <div class="mt-2 text-muted text-small">Uji kompetensi dengan asesor</div>
                    </div>
                    <div class="wizard-step">
                        <div class="wizard-step-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="wizard-step-label">
                            Sertifikasi
                        </div>
                        <div class="mt-2 text-muted text-small">Penerbitan sertifikat kompetensi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Guide & FAQ - Enhanced card design -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h4><i class="fas fa-book mr-2"></i>Panduan & Informasi</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h4>Panduan Persiapan Asesmen</h4>
                            </div>
                            <div class="card-body">
                                <p>Persiapkan diri Anda menghadapi uji kompetensi dengan panduan lengkap ini.</p>
                            </div>
                            <div class="card-footer pt-0">
                                <a href="#" class="btn btn-primary btn-block">Baca Selengkapnya</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h4>Tips Sukses Uji Kompetensi</h4>
                            </div>
                            <div class="card-body">
                                <p>Kiat-kiat praktis untuk memaksimalkan peluang keberhasilan dalam uji kompetensi.</p>
                            </div>
                            <div class="card-footer pt-0">
                                <a href="#" class="btn btn-warning btn-block">Baca Selengkapnya</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header">
                <h4><i class="fas fa-question-circle mr-2"></i>FAQ</h4>
            </div>
            <div class="card-body">
                <div class="accordion" id="accordionFAQ">
                    <div class="accordion">
                        <div class="accordion-header" role="button" data-toggle="collapse" data-target="#panel-body-1" aria-expanded="true">
                            <h4>Bagaimana cara mendaftar sertifikasi?</h4>
                        </div>
                        <div class="accordion-body collapse show" id="panel-body-1">
                            <p class="mb-0">Klik menu "Pengajuan Sertifikasi" dan pilih skema yang ingin diikuti, kemudian lengkapi formulir dan dokumen yang diminta.</p>
                        </div>
                    </div>
                    <div class="accordion">
                        <div class="accordion-header" role="button" data-toggle="collapse" data-target="#panel-body-2">
                            <h4>Berapa lama proses sertifikasi?</h4>
                        </div>
                        <div class="accordion-body collapse" id="panel-body-2">
                            <p class="mb-0">Proses sertifikasi memerlukan waktu sekitar 1-2 bulan, tergantung pada kelengkapan dokumen dan jadwal pelaksanaan uji kompetensi.</p>
                        </div>
                    </div>
                    <div class="accordion">
                        <div class="accordion-header" role="button" data-toggle="collapse" data-target="#panel-body-3">
                            <h4>Dokumen apa saja yang perlu disiapkan?</h4>
                        </div>
                        <div class="accordion-body collapse" id="panel-body-3">
                            <p class="mb-0">Dokumen yang diperlukan antara lain KTP, ijazah pendidikan terakhir, surat keterangan kerja, dan portofolio sesuai skema kompetensi.</p>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="#" class="btn btn-light btn-sm">Lihat Semua FAQ</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics & Support - Enhanced with better cards -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h4><i class="fas fa-chart-line mr-2"></i>Statistik Sertifikasi</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body p-4">
                                <div class="mb-2">
                                    <i class="fas fa-users fa-3x"></i>
                                </div>
                                <div class="stat-label mb-2">Total Peserta</div>
                                <div class="stat-value h2">745</div>
                                <div class="text-small">Seluruh Indonesia</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body p-4">
                                <div class="mb-2">
                                    <i class="fas fa-award fa-3x"></i>
                                </div>
                                <div class="stat-label mb-2">Tersertifikasi</div>
                                <div class="stat-value h2">612</div>
                                <div class="text-small">82% tingkat kelulusan</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card bg-warning text-white text-center">
                            <div class="card-body p-4">
                                <div class="mb-2">
                                    <i class="fas fa-file-signature fa-3x"></i>
                                </div>
                                <div class="stat-label mb-2">Sedang Proses</div>
                                <div class="stat-value h2">93</div>
                                <div class="text-small">Periode Mei-Juni 2025</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-info shadow">
            <div class="card-header">
                <h4><i class="fas fa-headset mr-2"></i>Butuh Bantuan?</h4>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <img src="<?= base_url('assets/img/avatar/avatar-4.png') ?>" alt="support" class="rounded-circle shadow img-fluid" width="100">
                </div>
                <h5 class="mt-3 mb-2">Tim Dukungan LSP</h5>
                <p class="text-muted">Kami siap membantu Anda dengan proses sertifikasi.</p>
                <div class="mt-4">
                    <a href="#" class="btn btn-icon icon-left btn-info mr-2"><i class="far fa-envelope"></i> Email</a>
                    <a href="#" class="btn btn-icon icon-left btn-success"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<!-- JS untuk mengaktifkan background image pada article -->
<?= $this->section("js") ?>
<script>
    $(document).ready(function() {
        $("[data-background]").each(function() {
            var me = $(this);
            me.css({
                backgroundImage: 'url(' + me.data('background') + ')'
            });
        });
    });
</script>
<?= $this->endSection() ?>
