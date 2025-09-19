<?= $this->extend("layouts/asesi/layout-app") ?>
<?= $this->section("content") ?>

<div class="row">
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-primary">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Pengajuan</h4>
                </div>
                <div class="card-body">
                    <h6 id="stat-total">Memuat...</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Kompeten</h4>
                </div>
                <div class="card-body">
                    <h6 id="stat-kompeten">Memuat...</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-danger">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Belum Kompeten</h4>
                </div>
                <div class="card-body">
                    <h6 id="stat-belum-kompeten">Memuat...</h6>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="pengajuan-list-container">
    <div id="loading-indicator" class="text-center p-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Memuat data...</span>
        </div>
        <p class="mt-3 text-muted">Mengambil data asesmen Anda...</p>
    </div>
</div>


<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header">
                <h4><i class="fas fa-bullhorn mr-2"></i>Pengumuman</h4>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <p class="mb-1">Jadwal Asesmen Periode Berikutnya Akan Segera Diumumkan.</p>
                        <small class="text-muted">Pantau terus halaman ini untuk informasi terbaru.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section("js") ?>
<script>
    $(function() {
        // Fungsi untuk memuat dan menampilkan data dashboard
        function loadDashboardData() {
            $.ajax({
                url: "<?= site_url('asesi/dashboard/data') ?>",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#loading-indicator').hide(); // Sembunyikan spinner

                    if (response.success) {
                        // 1. Update Kartu Statistik
                        // Pastikan service Anda mengembalikan properti ini
                        $('#stat-total').text((response.stats.total || 0) + ' Asesmen');
                        $('#stat-kompeten').text((response.stats.kompeten || 0) + ' Asesmen');
                        $('#stat-belum-kompeten').text((response.stats.belum_kompeten || 0) + ' Asesmen');

                        // 2. Render Daftar Pengajuan Asesmen
                        const container = $('#pengajuan-list-container');
                        container.empty(); // Kosongkan container

                        if (response.pengajuan && response.pengajuan.length > 0) {
                            response.pengajuan.forEach(function(item, index) {
                                // Logika untuk status badge
                                let statusBadge;
                                if (item.status_pengajuan === 'diterima') {
                                    statusBadge = `<span class="badge badge-success mr-2">Diterima</span>`;
                                } else if (item.status_pengajuan === 'ditolak') {
                                    statusBadge = `<span class="badge badge-danger mr-2">Ditolak</span>`;
                                } else {
                                    statusBadge = `<span class="badge badge-warning mr-2">Pending</span>`;
                                }

                                const cardHtml = `
                                <div class="card card-primary mb-3">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-start">
                                            <div class="mr-3">
                                                <div class="avatar avatar-lg bg-primary text-white d-flex align-items-center justify-content-center rounded" style="width: 60px; height: 60px; font-size: 24px; font-weight: bold;">
                                                    ${String(index + 1).padStart(2, '0')}
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    ${statusBadge}
                                                    <small class="text-muted">Diajukan pada: ${new Date(item.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })}</small>
                                                </div>
                                                <h6 class="mb-1 font-weight-semibold">${item.nama_skema || 'Nama Skema Tidak Tersedia'}</h6>
                                                <p class="text-muted mb-3 small">${item.tujuan || 'Tujuan Asesmen'}</p>
                                                <div class="d-flex flex-wrap text-muted small mb-2">
                                                    <div class="mr-3 mb-1">
                                                        <i class="far fa-calendar-alt mr-1"></i>
                                                        ${item.tanggal ? new Date(item.tanggal).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : 'Jadwal belum ditentukan'}
                                                    </div>
                                                    <div class="mr-3 mb-1">
                                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                                        ${item.nama_tuk || 'Lokasi TUK belum ditentukan'}
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-wrap">
                                                     <span class="badge badge-info mr-2 mb-1">
                                                         <i class="fas fa-user-tie mr-1"></i>
                                                        Asesor: ${item.nama_asesor || 'Belum Ditentukan'}
                                                     </span>
                                                </div>
                                            </div>
                                          
                                        </div>
                                    </div>
                                </div>
                                `;
                                container.append(cardHtml);
                            });
                        } else {
                            // Tampilkan pesan jika tidak ada data
                            container.html('<div class="alert alert-info text-center">Anda belum memiliki pengajuan asesmen.</div>');
                        }
                    } else {
                        $('#pengajuan-list-container').html('<div class="alert alert-danger text-center">' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#loading-indicator').hide();
                    $('#pengajuan-list-container').html('<div class="alert alert-danger text-center">Terjadi kesalahan saat memuat data. Silakan coba lagi.</div>');
                }
            });
        }

        // Panggil fungsi saat halaman siap
        loadDashboardData();
    });
</script>
<?= $this->endSection() ?>