<?= $this->extend("layouts/landingpage/layout-2") ?>

<?= $this->section("styles") ?>
<style>
    /* Custom styles to match the image */
    .course-banner {
        background-color: #3b76e1;
        /* Blue color from image */
        color: white;
        padding: 50px 40px;
        position: relative;
        border-radius: .25rem;
        /* Match Bootstrap card radius */
    }

    .course-banner h3 {
        font-size: 2.2rem;
        font-weight: 700;
    }

    .course-banner p {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    .course-banner hr {
        border-top: 1px solid rgba(255, 255, 255, 0.3);
        margin-top: 2rem;
        margin-bottom: 2rem;
    }

    .like-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        background-color: rgba(255, 255, 255, 0.2);
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        text-decoration: none;
        transition: all 0.3s;
    }

    .like-btn:hover {
        background-color: white;
        color: #e83e8c;
        /* Pink color for heart */
    }

    .header-meta {
        display: flex;
        gap: 40px;
        flex-wrap: wrap;
        /* Allow wrapping on smaller screens */
    }

    .header-meta-item .label {
        font-weight: 600;
        color: white;
        opacity: 0.8;
        margin-bottom: 5px;
        display: block;
        font-size: 0.8rem;
    }

    .header-meta-item .value {
        font-weight: 600;
        color: white;
        font-size: 1rem;
        display: flex;
        align-items: center;
    }

    /* Styles for sidebar */
    .sidebar-card-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Styles for status from new view */
    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        text-align: center;
        margin-top: 10px;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-diterima {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-ditolak {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .status-selesai {
        background-color: #cce7ff;
        color: #004085;
        border: 1px solid #bee5eb;
    }

    .already-registered {
        text-align: center;
        padding: 30px;
    }

    .already-registered i.fa-check-circle {
        font-size: 48px;
        margin-bottom: 20px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section("content") ?>

<div class="course-banner mb-4">
    <a href="#" class="like-btn"><i class="far fa-heart"></i></a>
    <h3 id="nama_skema">Memuat...</h3>
    <p>Belajar Mandiri (Micro Skill)</p>
    <hr>
    <div class="header-meta">
        <div class="header-meta-item">
            <span class="label">Kategori</span>
            <span class="value"><i class="fas fa-graduation-cap mr-2"></i><span id="jenis_skema">-</span></span>
        </div>
        <div class="header-meta-item">
            <span class="label">Jumlah Peserta</span>
            <span class="value"><i class="fas fa-users mr-2"></i><span id="jumlah_peserta">-</span></span>
        </div>
        <div class="header-meta-item">
            <span class="label">Alur Pendaftaran</span>
            <span class="value"><i class="fas fa-user-check mr-2"></i>Self Enrolment</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-body">
                <div id="loading-check" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Memeriksa status pendaftaran...</span>
                    </div>
                    <p class="mt-3 text-muted">Memeriksa status pendaftaran Anda...</p>
                </div>

                <div id="registration-form" class="card card-primary" style="display: none;">
                    <div class="card-body">
                        <h4>Ketentuan & Pernyataan</h4>
                        <p class="mt-3">Dengan ini Menyatakan:</p>
                        <ol style="padding-left: 1.2rem; margin-bottom: 1.5rem;">
                            <li>Berkomitmen untuk menyelesaikan Asesmen dengan batas waktu yang ditentukan penyelenggara.</li>
                            <li>Memahami Asesmen sebagai salah satu syarat untuk bisa mengikuti pelatihan yang telah ditentukan penyelenggara.</li>
                            <li>Mengikuti ketentuan yang berlaku dalam mengikuti Asesmen LSP-P1 SMKN 2 Kuningan.</li>
                        </ol>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="agree" class="custom-control-input" id="agree">
                                <label class="custom-control-label" for="agree">Saya telah membaca dan bersedia mengikuti persyaratan yang ada.</label>
                            </div>
                        </div>
                        <button id="btn-submit" class="btn btn-primary btn-lg btn-block" disabled>SUBMIT PENDAFTARAN</button>
                    </div>
                </div>

                <div id="already-registered" class="card card-info" style="display: none;">
                    <div class="card-body already-registered">
                        <i class="fas fa-check-circle text-success"></i>
                        <h4>Anda Sudah Terdaftar</h4>
                        <p class="mb-3">Anda sudah terdaftar pada skema sertifikasi ini. Silakan pantau status Anda.</p>

                        <div id="status-info" class="w-100">
                            <div class="row justify-content-center">
                                <div class="col-md-6 mb-3">
                                    <h6>Status Pengajuan:</h6>
                                    <div id="status-pengajuan" class="status-badge"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6>Status Asesmen:</h6>
                                    <div id="status-asesmen" class="status-badge"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="<?= site_url('asesi/dashboard') ?>" class="btn btn-primary">
                                <i class="fas fa-tachometer-alt mr-2"></i>Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="sidebar-card-item mb-3">
                    <div>
                        <div class="text-muted">Batas Pendaftaran</div>
                        <h6 class="font-weight-bold mb-0" id="batas_pendaftaran">Memuat...</h6>
                    </div>
                    <div class="badge badge-success p-2">Buka</div>
                </div>
                <hr>
                <div class="text-center">
                    <a href="#"><i class="fa fa-share-alt"></i> Bagikan pelatihan ini</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section("scripts") ?>
<script>
    $(function() {
        // 1. Ambil ID dari URL atau variabel PHP
        const asesmenId = <?= json_encode($id_asesmen) ?>;

        // 2. Ambil detail skema untuk ditampilkan di banner dan sidebar
        function fetchSkemaDetails() {
            $.ajax({
                url: "<?= site_url('api/getSkemaDetailJson') ?>",
                type: 'GET',
                dataType: 'json',
                data: {
                    id: asesmenId
                },
                success: function(response) {
                    // Update Banner
                    $('#nama_skema').text(response.asesmen.nama_skema || '-');
                    $('#jenis_skema').text(response.asesmen.jenis_skema || '-');
                    $('#jumlah_peserta').text(response.asesmen.jumlah_peserta || '3780 Peserta'); // Contoh fallback

                    // Update Sidebar
                    // Pastikan API Anda mengembalikan 'batas_pendaftaran'
                    $('#batas_pendaftaran').text(response.asesmen.batas_pendaftaran || '31 Desember 2025');
                },
                error: function() {
                    $('#nama_skema').text('Gagal Memuat Data Skema');
                    $('#batas_pendaftaran').text('N/A');
                }
            });
        }

        // 3. Fungsi untuk mengecek status pendaftaran
        function checkRegistrationStatus() {
            $.ajax({
                url: "<?= site_url('api/check-registration-status') ?>",
                type: 'GET',
                dataType: 'json',
                data: {
                    id_asesmen: asesmenId
                },
                success: function(response) {
                    $('#loading-check').hide();
                    if (response.already_registered) {
                        showRegistrationStatus(response.data);
                    } else {
                        $('#registration-form').show();
                    }
                },
                error: function() {
                    $('#loading-check').hide();
                    // Jika API gagal, asumsikan belum daftar dan tampilkan form
                    $('#registration-form').show();
                    Swal.fire('Error', 'Gagal memeriksa status pendaftaran. Silakan coba lagi.', 'error');
                }
            });
        }

        // 4. Fungsi untuk menampilkan status pendaftaran jika sudah terdaftar
        function showRegistrationStatus(data) {
            $('#registration-form').hide();
            $('#already-registered').show();

            const statusMap = {
                pengajuan: {
                    el: $('#status-pengajuan'),
                    text: {
                        'pending': 'Menunggu Konfirmasi',
                        'diterima': 'Diterima',
                        'ditolak': 'Ditolak',
                        'selesai': 'Selesai'
                    },
                    classes: {
                        'pending': 'status-pending',
                        'diterima': 'status-diterima',
                        'ditolak': 'status-ditolak',
                        'selesai': 'status-selesai'
                    }
                },
                asesmen: {
                    el: $('#status-asesmen'),
                    text: {
                        'proses': 'Dalam Proses',
                        'kompeten': 'Kompeten',
                        'belum_kompeten': 'Belum Kompeten'
                    },
                    classes: {
                        'proses': 'status-pending',
                        'kompeten': 'status-diterima',
                        'belum_kompeten': 'status-ditolak'
                    }
                }
            };

            const statusPengajuan = data.status_pengajuan;
            const statusAsesmen = data.status_asesmen;

            // Set Status Pengajuan
            statusMap.pengajuan.el.removeClass().addClass('status-badge ' + (statusMap.pengajuan.classes[statusPengajuan] || 'status-pending'));
            statusMap.pengajuan.el.text(statusMap.pengajuan.text[statusPengajuan] || 'N/A');

            // Set Status Asesmen
            statusMap.asesmen.el.removeClass().addClass('status-badge ' + (statusMap.asesmen.classes[statusAsesmen] || 'status-pending'));
            statusMap.asesmen.el.text(statusMap.asesmen.text[statusAsesmen] || 'Belum Dimulai');
        }

        // 5. Logika untuk checkbox dan tombol submit
        $('#agree').on('change', function() {
            $('#btn-submit').prop('disabled', !this.checked);
        });

        // 6. Logika untuk submit pendaftaran via AJAX
        $('#btn-submit').on('click', function() {
            const button = $(this);
            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');

            $.ajax({
                url: "<?= site_url('pengajuan-submit') ?>",
                type: 'POST',
                dataType: 'json',
                data: {
                    id_asesmen: asesmenId,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            allowOutsideClick: false
                        }).then(() => {
                            // Muat ulang halaman untuk menampilkan status terbaru
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal', response.message || 'Terjadi kesalahan.', 'error');
                        button.prop('disabled', false).html('SUBMIT PENDAFTARAN');
                        $('#agree').prop('checked', false);
                    }
                },
                error: function(jqXHR) {
                    const errorMsg = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'Terjadi kesalahan koneksi. Silakan coba lagi.';
                    Swal.fire('Oops...', errorMsg, 'error');
                    button.prop('disabled', false).html('SUBMIT PENDAFTARAN');
                    $('#agree').prop('checked', false);
                }
            });
        });

        // 7. Panggil fungsi saat halaman dimuat
        fetchSkemaDetails();
        checkRegistrationStatus();
    });
</script>
<?= $this->endSection() ?>