<?= $this->extend("layouts/asesi/layout-app") ?>
<?= $this->section("content") ?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="mb-0">Riwayat Asesmen Mandiri</h4>
        <p id="asesmen-count" class="text-muted mb-0">Memuat data...</p>
    </div>
    <div class="col-md-6 d-flex justify-content-md-end">
        <div class="form-group mb-0" style="width: 200px;">
            <label for="filter-asesmen" class="sr-only">Urutkan</label>
            <select id="filter-asesmen" class="form-control">
                <option value="terbaru">Terbaru</option>
                <option value="terlama">Terlama</option>
                <option value="status">Status</option>
            </select>
        </div>
    </div>
</div>

<div class="row" id="asesmen-list-container">
    <div class="col-12 text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section("js") ?>
<script>
    // Fungsi formatTanggalIndonesia dan getCardDetails tetap sama (tidak perlu diubah)
    function formatTanggalIndonesia(tanggal) {
        if (!tanggal) return '';
        const date = new Date(tanggal);
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function getCardDetails(asesmen) {
        if (!asesmen.id_apl2) {
            return {
                footer_icon: 'fa-edit',
                footer_class: 'text-info',
                footer_text: 'Siap untuk Dikerjakan',
                button_text: 'Mulai Kerjakan',
                button_class: 'btn-success',
                footer_info: 'Silakan klik tombol di atas.'
            };
        }
        const details = {
            button_text: 'Lihat Detail',
            button_class: 'btn-primary',
            footer_info: formatTanggalIndonesia(asesmen.updated_at_apl2)
        };
        switch (asesmen.validasi_apl2) {
            case 'validated':
                details.footer_icon = 'fa-check-circle';
                details.footer_class = 'text-success';
                details.footer_text = 'Sudah Divalidasi';
                break;
            case 'unvalid':
                details.footer_icon = 'fa-times-circle';
                details.footer_class = 'text-danger';
                details.footer_text = 'Ditolak, Perlu Perbaikan';
                break;
            default:
                details.footer_icon = 'fa-history';
                details.footer_class = 'text-primary';
                details.footer_text = 'Menunggu Validasi';
                break;
        }
        return details;
    }

    // --- FUNGSI UTAMA (DENGAN OPTIMASI) ---
    function loadAsesmen(filter = 'terbaru') {
        const container = $('#asesmen-list-container');
        const countElement = $('#asesmen-count');
        const spinner = `<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>`;

        container.html(spinner);
        countElement.text('Memuat data...');

        $.ajax({
            url: "<?= base_url('/asesmen-mandiri/filter') ?>",
            type: 'GET',
            data: {
                filter: filter
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.length > 0) {
                    countElement.text(`Ditemukan ${response.length} Asesmen Mandiri`);

                    // OPTIMASI: Bangun semua HTML dalam satu string
                    let allCardsHtml = response.map(asesmen => {
                        const details = getCardDetails(asesmen);
                        const jenisSkema = asesmen.jenis_skema.charAt(0).toUpperCase() + asesmen.jenis_skema.slice(1).toLowerCase();

                        return `
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="mr-4">
                                                <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                    <i class="fas fa-clipboard-list fa-2x"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="text-dark font-weight-600">Asesmen Mandiri</div>
                                                <div class="text-muted">${asesmen.nama_skema}</div>
                                                <div class="font-weight-bold mt-1">Jenis Skema: ${jenisSkema}</div>
                                            </div>
                                            <div class="ml-auto pl-3">
                                                <a href="<?= base_url('asesmen-mandiri') ?>/${asesmen.id_pengajuan}" class="btn ${details.button_class} btn-sm">${details.button_text}</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-whitesmoke d-flex align-items-center ${details.footer_class}">
                                        <i class="fas ${details.footer_icon} mr-2"></i> ${details.footer_text} : ${details.footer_info}
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join(''); // Gabungkan semua kartu menjadi satu string HTML

                    // OPTIMASI: Masukkan semua HTML ke container dalam satu kali operasi
                    container.html(allCardsHtml);

                } else {
                    countElement.text('Tidak ada data ditemukan');
                    container.html('<div class="col-12"><div class="card"><div class="card-body text-center text-muted"><p>Belum ada data asesmen mandiri yang tersedia.</p></div></div></div>');
                }
            },
            error: function() {
                countElement.text('Gagal memuat data');
                container.html('<div class="col-12"><div class="card"><div class="card-body text-center text-danger"><p>Terjadi kesalahan saat mengambil data. Silakan coba lagi.</p></div></div></div>');
            }
        });
    }

    $(document).ready(function() {
        loadAsesmen();
        $('#filter-asesmen').on('change', function() {
            loadAsesmen($(this).val());
        });
    });
</script>
<?= $this->endSection() ?>