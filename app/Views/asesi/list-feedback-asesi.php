<?= $this->extend("layouts/asesi/layout-app") ?>
<?= $this->section("content") ?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="mb-0">Umpan Balik Proses Asesmen</h4>
        <p id="feedback-count" class="text-muted mb-0">Memuat data...</p>
    </div>
    <div class="col-md-6 d-flex justify-content-md-end">
        <div class="form-group mb-0" style="width: 200px;">
            <label for="filter-feedback" class="sr-only">Urutkan</label>
            <select id="filter-feedback" class="form-control">
                <option value="terbaru">Terbaru</option>
                <option value="terlama">Terlama</option>
            </select>
        </div>
    </div>
</div>

<div class="row" id="feedback-list-container">
    <div class="col-12 text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section("js") ?>
<script>
    function formatTanggalIndonesia(tanggal) {
        if (!tanggal) return 'Belum diisi';
        const date = new Date(tanggal);
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }

    function getCardDetails(feedback) {
        const isFilled = feedback.id_feedback !== null;
        return {
            footer_icon: isFilled ? 'fa-check-circle' : 'fa-edit',
            footer_class: isFilled ? 'text-success' : 'text-info',
            footer_text: isFilled ? 'Sudah Diisi' : 'Siap untuk Diisi',
            button_text: isFilled ? 'Lihat / Edit Umpan Balik' : 'Isi Umpan Balik',
            button_class: isFilled ? 'btn-primary' : 'btn-success',
            footer_info: formatTanggalIndonesia(feedback.updated_at)
        };
    }

    function loadFeedback(filter = 'terbaru') {
        const container = $('#feedback-list-container');
        const countElement = $('#feedback-count');
        const spinner = `<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>`;

        container.html(spinner);
        countElement.text('Memuat data...');

        $.ajax({
            url: "<?= base_url('/feedback-asesi/filter') ?>", // URL endpoint baru
            type: 'GET',
            data: {
                filter: filter
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.length > 0) {
                    countElement.text(`Ditemukan ${response.length} asesmen yang memerlukan umpan balik`);

                    let allCardsHtml = response.map(item => {
                        const details = getCardDetails(item);

                        return `
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="mr-4">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                    <i class="fas fa-comment-dots fa-2x"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="text-dark font-weight-600">FR.IA.06 - Umpan Balik Asesi</div>
                                                <div class="text-muted">${item.nama_skema}</div>
                                                <div class="font-weight-bold mt-1">Asesor: ${item.nama_asesor}</div>
                                            </div>
                                            <div class="ml-auto pl-3">
                                                <a href="<?= base_url('feedback-asesi') ?>/${item.id_pengajuan}" class="btn ${details.button_class} btn-sm">${details.button_text}</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-whitesmoke d-flex align-items-center ${details.footer_class}">
                                        <i class="fas ${details.footer_icon} mr-2"></i> ${details.footer_text} : ${details.footer_info}
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');

                    container.html(allCardsHtml);

                } else {
                    countElement.text('Tidak ada data ditemukan');
                    container.html('<div class="col-12"><div class="card"><div class="card-body text-center text-muted"><p>Belum ada jadwal asesmen yang memerlukan umpan balik.</p></div></div></div>');
                }
            },
            error: function() {
                countElement.text('Gagal memuat data');
                container.html('<div class="col-12"><div class="card"><div class="card-body text-center text-danger"><p>Terjadi kesalahan saat mengambil data. Silakan coba lagi.</p></div></div></div>');
            }
        });
    }

    $(document).ready(function() {
        loadFeedback();
        $('#filter-feedback').on('change', function() {
            loadFeedback($(this).val());
        });
    });
</script>
<?= $this->endSection() ?>