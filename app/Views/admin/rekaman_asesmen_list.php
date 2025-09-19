<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section("content"); ?>
<h2 class="section-title">Manajemen Rekaman Asesmen</h2>
<p class="section-lead">Monitor semua data rekaman asesmen kompetensi yang telah dibuat oleh asesor.</p>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-table"></i> Daftar Rekaman Asesmen</h4>
            </div>
            <div class="card-body">
                <div class="bg-light p-3 mb-4 rounded border">
                    <h6 class="font-weight-bold">Filter Data</h6>
                    <form id="filter-form">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-md-0">
                                    <label>Asesor</label>
                                    <select class="form-control select2" name="id_asesor">
                                        <option value="">Semua Asesor</option>
                                        <?php foreach ($asesor_list as $asesor) : ?>
                                            <option value="<?= $asesor['id_asesor'] ?>" <?= ($filters['id_asesor'] ?? '') == $asesor['id_asesor'] ? 'selected' : '' ?>>
                                                <?= esc($asesor['nama_asesor']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-md-0">
                                    <label>Skema</label>
                                    <select class="form-control select2" name="id_skema">
                                        <option value="">Semua Skema</option>
                                        <?php foreach ($skema_list as $skema) : ?>
                                            <option value="<?= $skema['id_skema'] ?>" <?= ($filters['id_skema'] ?? '') == $skema['id_skema'] ? 'selected' : '' ?>>
                                                <?= esc($skema['nama_skema']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-md-0">
                                    <label>Tanggal Dari</label>
                                    <input type="date" class="form-control" name="tanggal_dari" value="<?= $filters['tanggal_dari'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-md-0">
                                    <label>Tanggal Sampai</label>
                                    <input type="date" class="form-control" name="tanggal_sampai" value="<?= $filters['tanggal_sampai'] ?? '' ?>">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Terapkan Filter</button>
                        <a href="<?= base_url('admin/rekaman-asesmen') ?>" class="btn btn-light"><i class="fas fa-redo"></i> Reset Filter</a>
                    </form>
                </div>

                <div class="d-flex justify-content-end mb-3">
                    <button id="download-batch-btn" class="btn btn-success" disabled>
                        <i class="fas fa-file-archive"></i> Download Batch (.zip)
                    </button>
                </div>

                <form id="batch-download-form" action="<?= base_url('admin/rekaman-asesmen/batch-pdf') ?>" method="post" style="display: none;">
                    <?= csrf_field() ?>
                </form>

                <div class="table-responsive">
                    <table id="table-rekaman-admin" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama Asesi</th>
                                <th class="text-center" style="width: 5%;">
                                    <input type="checkbox" id="select-all-rekaman">
                                </th>
                                <th>Nama Asesor</th>
                                <th>Skema Sertifikasi</th>
                                <th>Tanggal</th>
                                <th>Rekomendasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    // ... (JavaScript Anda yang sudah ada sebelumnya bisa langsung disalin ke sini) ...
    // Pastikan untuk menyesuaikan beberapa hal:
    // 1. Selector tabel: #table-rekaman-admin
    // 2. Checkbox: #select-all-rekaman dan .rekaman-checkbox
    // 3. Form: #batch-download-form dan nama inputnya (rekaman_ids[])
    // 4. URL AJAX dan URL Aksi

    // Helper function
    function showToast(message, type = 'warning') {
        iziToast[type]({
            title: 'Perhatian',
            message: message,
            position: 'topRight'
        });
    }

    $(document).ready(function() {
        $('.select2').select2();

        const baseUrl = '<?= base_url() ?>';
        const csrfName = '<?= csrf_token() ?>';
        let csrfHash = '<?= csrf_hash() ?>';

        const dataTable = $('#table-rekaman-admin').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            order: [
                [5, 'desc']
            ],
            ajax: {
                url: `${baseUrl}/api/rekaman-asesmen/admin/list`, // Sesuaikan URL API
                type: "POST",
                data: function(d) {
                    d.id_asesor = $('select[name="id_asesor"]').val();
                    d.id_skema = $('select[name="id_skema"]').val();
                    d.tanggal_dari = $('input[name="tanggal_dari"]').val();
                    d.tanggal_sampai = $('input[name="tanggal_sampai"]').val();
                    d[csrfName] = csrfHash;
                },
                dataSrc: function(json) {
                    csrfHash = json.csrf_token;
                    return json.data;
                }
            },
            columns: [{
                    data: null,
                    searchable: false,
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'nama_asesi'
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: (data) => `<input type="checkbox" class="rekaman-checkbox" value="${data}">`
                },
                {
                    data: 'nama_asesor'
                },
                {
                    data: 'nama_skema'
                },
                {
                    data: 'tanggal_rekaman'
                },
                {
                    data: 'rekomendasi'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            columnDefs: [{
                    targets: 0,
                    render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                {
                    targets: 5,
                    render: (data) => new Date(data).toLocaleDateString('id-ID', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    })
                },
                {
                    targets: 6,
                    className: 'text-center',
                    render: (data) => data === 'kompeten' ?
                        '<span class="badge badge-success">Kompeten</span>' : '<span class="badge badge-danger">Belum Kompeten</span>'
                },
                {
                    targets: 7,
                    render: (data, type, row) => `
                        <div class="btn-group">
                            <a href="${baseUrl}/admin/rekaman-asesmen/pdf/${row.id}" target="_blank" class="btn btn-sm btn-info" title="Cetak PDF"><i class="fas fa-print"></i></a>
                            <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}" title="Arsipkan"><i class="fas fa-archive"></i></button>
                        </div>`
                }
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
            }
        });

        $('#filter-form').on('submit', e => {
            e.preventDefault();
            dataTable.ajax.reload();
        });

        // --- LOGIKA BATCH DOWNLOAD & DELETE (SAMA PERSIS DENGAN PMO, HANYA GANTI SELECTOR) ---

        $('#table-rekaman-admin tbody').on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            // ... (logika delete sama persis dengan PMO, hanya URL berbeda)
            // Contoh URL: `${baseUrl}/api/rekaman-asesmen/admin/delete/${id}`
        });

        $('#select-all-rekaman').on('click', function() {
            $('.rekaman-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
        });

        $('#table-rekaman-admin tbody').on('change', '.rekaman-checkbox', function() {
            $('#download-batch-btn').prop('disabled', !$('.rekaman-checkbox:checked').length > 0);
            if (!$(this).is(':checked')) {
                $('#select-all-rekaman').prop('checked', false);
            }
        });

        $('#download-batch-btn').on('click', function() {
            const selectedIds = $('.rekaman-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                showToast('Pilih setidaknya satu rekaman untuk diunduh.', 'warning');
                return;
            }

            const button = $(this);
            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Memproses...');

            const form = $('#batch-download-form');
            form.find('input[name="rekaman_ids[]"]').remove();
            selectedIds.forEach(id => {
                form.append(`<input type="hidden" name="rekaman_ids[]" value="${id}">`);
            });
            form.submit();

            setTimeout(() => {
                button.prop('disabled', false).html('<i class="fas fa-file-archive"></i> Download Batch (.zip)');
            }, 5000);
        });

        dataTable.on('draw', function() {
            $('#download-batch-btn').prop('disabled', true);
            $('#select-all-rekaman').prop('checked', false);
        });
    });
</script>
<?= $this->endSection() ?>