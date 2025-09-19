<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section('content'); ?>

<div class="section-body">
    <!-- Filter Card -->
    <div class="card">
        <div class="card-header">
            <h4>Filter Laporan</h4>
        </div>
        <div class="card-body">
            <form action="<?= base_url('laporan') ?>" method="GET" id="filter-form">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Asesor</label>
                            <select class="form-control select2" name="id_asesor">
                                <option value="">Semua Asesor</option>
                                <?php foreach ($asesor_list as $asesor): ?>
                                    <option value="<?= $asesor['id_asesor'] ?>"
                                        <?= ($filters['id_asesor'] ?? '') == $asesor['id_asesor'] ? 'selected' : '' ?>>
                                        <?= esc($asesor['nama_asesor']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Skema</label>
                            <select class="form-control select2" name="id_skema">
                                <option value="">Semua Skema</option>
                                <?php foreach ($skema_list as $skema): ?>
                                    <option value="<?= $skema['id_skema'] ?>"
                                        <?= ($filters['id_skema'] ?? '') == $skema['id_skema'] ? 'selected' : '' ?>>
                                        <?= esc($skema['nama_skema']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Dari</label>
                            <input type="date" class="form-control" name="tanggal_dari"
                                value="<?= $filters['tanggal_dari'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Sampai</label>
                            <input type="date" class="form-control" name="tanggal_sampai"
                                value="<?= $filters['tanggal_sampai'] ?? '' ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="<?= base_url('laporan') ?>" class="btn btn-light">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4>Daftar Laporan Asesmen</h4>
            <div class="card-header-action">
                <button id="download-batch-btn" class="btn btn-success" disabled>
                    <i class="fas fa-file-archive"></i> Download Batch (.zip)
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="batch-download-form" action="<?= base_url('laporan/asesmen/batch-pdf') ?>" method="post"
                style="display: none;">
                <?= csrf_field() ?>
            </form>
            <div class="table-responsive">
                <table class="table table-striped" id="laporan-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5%;">
                                <input type="checkbox" id="select-all-laporan">
                            </th>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Skema</th>
                            <th>Asesor</th>
                            <th>Jumlah Asesi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporan_list as $index => $laporan): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="laporan-checkbox"
                                        data-id-asesor="<?= $laporan['id_asesor'] ?>"
                                        data-id-skema="<?= $laporan['id_skema'] ?>"
                                        data-tanggal-rekaman="<?= $laporan['tanggal_rekaman'] ?>">
                                </td>
                                <td><?= $index + 1 ?></td>
                                <td><?= format_tanggal_indonesia($laporan['tanggal_rekaman']) ?></td>
                                <td><?= esc($laporan['nama_skema']) ?></td>
                                <td><?= esc($laporan['nama_asesor']) ?></td>
                                <td><?= $laporan['jumlah_asesi'] ?></td>
                                <td>
                                    <div class="buttons">
                                        <?php
                                        $queryParams = http_build_query([
                                            'id_skema' => $laporan['id_skema'],
                                            'tanggal_dari' => $laporan['tanggal_rekaman'],
                                            'tanggal_sampai' => $laporan['tanggal_rekaman']
                                        ]);
                                        ?>
                                        <a href="<?= base_url('laporan/asesmen/pdf/' . $laporan['id_asesor']) . '?' . $queryParams ?>"
                                            class="btn btn-icon btn-primary" target="_blank"
                                            title="Lihat Laporan Lengkap">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('js'); ?>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2();

        // Initialize DataTable
        const dataTable = $('#laporan-table').DataTable({
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            },
            columnDefs: [{
                    orderable: false,
                    targets: [0, 6]
                } // Disable sorting for checkbox and action columns
            ]
        });

        // --- BATCH DOWNLOAD LOGIC ---

        // Helper function to show a toast notification
        function showToast(message, type = 'warning') {
            iziToast[type]({
                title: 'Perhatian',
                message: message,
                position: 'topRight'
            });
        }

        // Handle "Select All" checkbox
        $('#select-all-laporan').on('click', function() {
            const isChecked = $(this).is(':checked');
            $('.laporan-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Handle individual checkbox changes to update button state
        $('#laporan-table').on('change', '.laporan-checkbox', function() {
            const anyChecked = $('.laporan-checkbox:checked').length > 0;
            $('#download-batch-btn').prop('disabled', !anyChecked);

            // Uncheck "Select All" if any individual checkbox is unchecked
            if (!$(this).is(':checked')) {
                $('#select-all-laporan').prop('checked', false);
            }
        });

        // Handle Batch Download button click
        $('#download-batch-btn').on('click', function() {
            const selectedReports = $('.laporan-checkbox:checked').map(function() {
                return {
                    id_asesor: $(this).data('id-asesor'),
                    id_skema: $(this).data('id-skema'),
                    tanggal_rekaman: $(this).data('tanggal-rekaman')
                };
            }).get();

            if (selectedReports.length === 0) {
                showToast('Pilih setidaknya satu laporan untuk diunduh.', 'warning');
                return;
            }

            // Show processing state
            const button = $(this);
            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');

            const form = $('#batch-download-form');
            // Clear previous inputs
            form.find('input[name^="laporan_data"]').remove();

            // Add selected data as hidden inputs
            selectedReports.forEach((report, index) => {
                form.append(`<input type="hidden" name="laporan_data[${index}][id_asesor]" value="${report.id_asesor}">`);
                form.append(`<input type="hidden" name="laporan_data[${index}][id_skema]" value="${report.id_skema}">`);
                form.append(`<input type="hidden" name="laporan_data[${index}][tanggal_rekaman]" value="${report.tanggal_rekaman}">`);
            });

            // Submit the form to trigger download
            form.submit();

            // Reset the button after a delay to allow download to start
            setTimeout(() => {
                button.prop('disabled', false).html('<i class="fas fa-file-archive"></i> Download Batch (.zip)');
            }, 5000); // Reset after 5 seconds
        });

        // Reset checkboxes on DataTable draw event (e.g., pagination, search)
        dataTable.on('draw', function() {
            $('#download-batch-btn').prop('disabled', true);
            $('#select-all-laporan').prop('checked', false);
        });
    });
</script>
<?= $this->endSection(); ?>