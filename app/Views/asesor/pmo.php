<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-tasks text-primary"></i> Manajemen Ceklis PMO</h4>
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
                                                <?= esc($asesor['nama_lengkap']) ?>
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
                        <a href="<?= base_url('asesor/pmo') ?>" class="btn btn-light"><i class="fas fa-redo"></i> Reset Filter</a>
                    </form>
                </div>

                <div class="d-flex justify-content-end mb-3">
                    <button id="download-batch-btn" class="btn btn-success" disabled>
                        <i class="fas fa-file-archive"></i> Download Batch (.zip)
                    </button>
                </div>

                <!-- Hidden form for batch download -->
                <form id="batch-download-form" action="<?= base_url('asesor/pmo/batch-pdf') ?>" method="post" style="display: none;">
                    <?= csrf_field() ?>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped" id="pmo-table" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama Asesi</th>
                                <th class="text-center" style="width: 5%;">
                                    <input type="checkbox" id="select-all-pmo">
                                </th>
                                <th>Skema Sertifikasi</th>
                                <th>Asesor</th>
                                <th>Tanggal Observasi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('js'); ?>
<script>
    // Helper function to show a toast notification
    function showToast(message, type = 'success') {
        const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        iziToast[type]({
            title: type.charAt(0).toUpperCase() + type.slice(1),
            message: message,
            position: 'topRight',
            icon: icon
        });
    }
    $(document).ready(function() {
        $('.select2').select2();

        const pmoTable = $('#pmo-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "<?= base_url('api/pmo/list') ?>",
                type: "POST",
                data: function(d) {
                    // Mengirim data filter ke server
                    d.id_asesor = $('select[name="id_asesor"]').val();
                    d.id_skema = $('select[name="id_skema"]').val();
                    d.tanggal_dari = $('input[name="tanggal_dari"]').val();
                    d.tanggal_sampai = $('input[name="tanggal_sampai"]').val();
                    d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>'; // CSRF Token
                },
                dataSrc: function(json) {
                    // Update CSRF token setelah request
                    $('input[name=<?= csrf_token() ?>]').val(json.<?= csrf_token() ?>);
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
                    data: 'nama_siswa'
                },
                {
                    data: 'id_pmo',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: (data) => `<input type="checkbox" class="pmo-checkbox" value="${data}">`
                },
                {
                    data: 'nama_skema'
                },
                {
                    data: 'nama_asesor'
                },
                {
                    data: 'tanggal_observasi',
                    render: data => data ? new Date(data).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    }) : '-'
                },
                {
                    data: 'id_pmo',
                    searchable: false,
                    orderable: false,
                    className: 'text-center',
                    render: (data, type, row) => `
                        <div class="btn-group">
                            <a href="<?= base_url('pdf/pmo/') ?>${row.id_pmo}" target="__blank" class="btn btn-sm btn-info" title="Lihat/Edit Ceklis"><i class="fas fa-eye"></i></a>
                            <button class="btn btn-sm btn-danger btn-delete" data-id="${data}" title="Hapus Data"><i class="fas fa-trash-alt"></i></button>
                        </div>`
                }
            ],
            fnRowCallback: function(nRow, aData, iDisplayIndex) {
                var index = iDisplayIndex + 1;
                $('td:eq(0)', nRow).html(index);
            },
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
            }
        });

        $('#filter-form').on('submit', e => {
            e.preventDefault();
            pmoTable.ajax.reload();
        });

        $('#pmo-table').on('click', '.btn-delete', function() {
            const idPmo = $(this).data('id');
            Swal.fire({
                title: 'Anda yakin?',
                text: "Data ceklis PMO ini akan dihapus.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `<?= base_url('api/pmo/delete/') ?>${idPmo}`,
                        type: 'DELETE', // Menggunakan metode DELETE
                        dataType: 'json',
                        success: res => {
                            Swal.fire('Berhasil!', res.message || 'Data PMO berhasil dihapus.', 'success');
                            pmoTable.ajax.reload();
                        },
                        error: err => {
                            Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                        }
                    });
                }
            });
        });

        // Handle "Select All" checkbox
        $('#select-all-pmo').on('click', function() {
            const isChecked = $(this).is(':checked');
            $('.pmo-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Handle individual checkbox changes to update button state
        $('#pmo-table tbody').on('change', '.pmo-checkbox', function() {
            const anyChecked = $('.pmo-checkbox:checked').length > 0;
            $('#download-batch-btn').prop('disabled', !anyChecked);

            // Uncheck "Select All" if any individual checkbox is unchecked
            if (!$(this).is(':checked')) {
                $('#select-all-pmo').prop('checked', false);
            }
        });

        // Handle Batch Download button click
        $('#download-batch-btn').on('click', function() {
            const selectedIds = $('.pmo-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                showToast('Pilih setidaknya satu laporan PMO untuk diunduh.', 'warning');
                return;
            }

            // Show processing state
            const button = $(this);
            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');

            const form = $('#batch-download-form');
            // Clear previous inputs
            form.find('input[name="pmo_ids[]"]').remove();

            // Add selected IDs as hidden inputs
            selectedIds.forEach(id => {
                form.append(`<input type="hidden" name="pmo_ids[]" value="${id}">`);
            });

            // Submit the form to trigger download
            form.submit();

            // We can't know for sure when the download is complete,
            // so we'll reset the button after a short delay.
            setTimeout(() => {
                button.prop('disabled', false).html('<i class="fas fa-file-archive"></i> Download Batch (.zip)');
            }, 5000); // Reset after 5 seconds
        });

        pmoTable.on('draw', function() {
            $('#download-batch-btn').prop('disabled', true);
            $('#select-all-pmo').prop('checked', false);
        });
    });
</script>
<?= $this->endSection(); ?>