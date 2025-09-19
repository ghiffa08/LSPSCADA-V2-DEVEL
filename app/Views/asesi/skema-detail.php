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

    .course-banner h1 {
        font-size: 2.5rem;
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

    .info-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .info-item .icon {
        font-size: 18px;
        color: #4e73df;
        margin-right: 15px;
        width: 20px;
        text-align: center;
    }

    .info-item .text .label {
        font-weight: 600;
        color: #6c757d;
    }

    .info-item .text .value {
        font-size: 14px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section("content") ?>

<div class="course-banner mb-4">
    <h3 id="nama_skema">Memuat...</h3>
    <p>Belajar Mandiri (Micro Skill)</p>
    <hr>
    <div class="header-meta">
        <div class="header-meta-item">
            <span class="label">Kategori</span>
            <span class="value"><i class="fas fa-graduation-cap mr-2"></i><span id="jenis_skema">-</span></span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-body" id="detail-content">
                <div class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Sidebar Column -->
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="alert alert-light">
                    Pelatihan diselenggarakan secara Self-paced learning atau dikerjakan secara mandiri.
                </div>
                <a href="<?= site_url('asesmen-daftar/' . $id_asesmen) ?>" class="btn btn-primary btn-lg btn-block mb-3">DAFTAR SEKARANG</a>
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
        // Ambil ID langsung dari variabel PHP yang dikirim controller
        // json_encode digunakan untuk memastikan format data aman untuk JavaScript
        const asesmenId = <?= json_encode($id_asesmen) ?>;

        // Lakukan request AJAX untuk mengambil detail data
        $.ajax({
            // Gunakan endpoint API yang sudah ada
            url: "<?= site_url('api/getSkemaDetailJson') ?>", // Arahkan ke endpoint yang benar
            type: 'GET',
            dataType: 'json',
            data: {
                id: asesmenId
            },
            success: function(response) {
                const asesmen = response.asesmen;
                const listUnit = response.listUnit;

                // 1. Isi data di Banner
                $('#nama_skema').text(asesmen.nama_skema || '-');
                $('#jenis_skema').text(asesmen.jenis_skema || '-');

                // 2. Isi tombol daftar dan aktifkan
                const daftarUrl = "<?= site_url('asesmen-daftar/') ?>" + asesmen.id_asesmen;
                $('#btn-daftar').attr('href', daftarUrl).removeClass('disabled');

                // 3. Buat HTML untuk tabel unit kompetensi
                let unitHtml = '';
                if (listUnit && listUnit.length > 0) {
                    listUnit.forEach(unit => {
                        unitHtml += `
                        <tr>
                            <td>${unit.kode_unit || '-'}</td>
                            <td>${unit.nama_unit || '-'}</td>
                        </tr>
                    `;
                    });
                } else {
                    unitHtml = '<tr><td colspan="2" class="text-center">Tidak ada unit kompetensi.</td></tr>';
                }

                // 4. Gabungkan semua HTML untuk konten utama dan ganti spinner
                const contentHtml = `
                <div class="card card-primary">
                    <div class="card-header"><h4>Unit Kompetensi</h4></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr><th>Kode Unit</th><th>Nama Unit</th></tr>
                                </thead>
                                <tbody>${unitHtml}</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
                $('#detail-content').html(contentHtml);
            },
            error: function(xhr) {
                console.error("AJAX Error:", xhr.responseText);
                $('#detail-content').html('<div class="alert alert-danger">Gagal memuat detail data. Silakan coba lagi.</div>');
            }
        });
    });
</script>
<?= $this->endSection() ?>