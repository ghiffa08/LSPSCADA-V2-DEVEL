<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section("content"); ?>

<h2 class="section-title">Manajemen Skema</h2>
<p class="section-lead">Kelola semua data skema sertifikasi pada halaman ini.</p>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Skema</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" id="btn-add-skema">
                        <i class="fas fa-plus"></i> Tambah Skema
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-skema" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Skema</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th>Aksi</th>
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
<?= $this->endSection() ?>
<?= $this->section("modals") ?>
<?= $this->include('admin/partials/modals/save-form-skema') ?>
<?= $this->include('admin/partials/modals/import-excel-skema') ?>
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script>
    /**
     * Manages all functionalities for the Skema page, including
     * DataTable initialization, CRUD operations, and modal handling.
     */
    $(document).ready(function() {
        // =================================================================
        // Configuration & Initialization
        // =================================================================

        const modal = $('#saveSkemaModal');
        const form = $('#add-skema-form');
        const baseUrl = '<?= base_url() ?>';
        let dataTable;

        // =================================================================
        // DataTable Initialization
        // =================================================================

        dataTable = $('#table-skema').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "order": [],
            "ajax": {
                "url": `${baseUrl}/master/skema/get-data-table`,
                "type": "POST"
            },
            "columns": [{
                    "data": null,
                    "orderable": false
                }, // No.
                {
                    "data": "kode_skema"
                },
                {
                    "data": "nama_skema"
                },
                {
                    "data": "jenis_skema"
                },
                {
                    "data": "status"
                },
                {
                    "data": null,
                    "orderable": false
                } // Aksi
            ],
            "columnDefs": [{
                "targets": 0, // Render index number
                "render": (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
            }, {
                "targets": 3, // Render 'jenis_skema' as a badge
                "render": function(data) {
                    const jenisMap = {
                        'KKNI': '<span class="badge badge-primary">KKNI</span>',
                        'Okupasi': '<span class="badge badge-info">Okupasi</span>',
                        'Klaster': '<span class="badge badge-warning">Klaster</span>'
                    };
                    return jenisMap[data] || data;
                }
            }, {
                "targets": 4, // Render 'status' as a badge
                "className": 'text-center',
                "render": (data) => data === 'Y' ?
                    '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>'
            }, {
                "targets": -1, // Render action buttons
                "render": (data, type, row) => `
                <div class="btn-group">
                    <button class="btn btn-sm btn-info btn-edit" data-id="${row.id_skema}" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id_skema}" title="Hapus"><i class="fas fa-trash"></i></button>
                </div>
            `
            }],
        });

        // =================================================================
        // Modal & Form Logic
        // =================================================================

        /** Resets the form to its initial state. */
        function resetForm() {
            form[0].reset();
            form.find('input[name="id_skema"]').val('');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
            form.find('select.select2').val('').trigger('change');
            modal.find('.modal-title').text('Tambah Skema');
        }

        /** Fetches skema data and populates the form for editing. */
        function openEditModal(id) {
            resetForm();
            $.get(`${baseUrl}/master/skema/getById/${id}`, function(response) {
                if (response.status) {
                    const skema = response.data;
                    form.find('[name="id_skema"]').val(skema.id_skema);
                    form.find('[name="kode_skema"]').val(skema.kode_skema);
                    form.find('[name="nama_skema"]').val(skema.nama_skema);
                    form.find('[name="jenis_skema"]').val(skema.jenis_skema).trigger('change');
                    form.find(`input[name="status"][value="${skema.status}"]`).prop('checked', true);

                    modal.find('.modal-title').text('Edit Skema');
                    modal.modal('show');
                } else {
                    Swal.fire('Gagal', response.message || 'Data tidak ditemukan', 'error');
                }
            }).fail(() => Swal.fire('Error', 'Gagal mengambil data dari server.', 'error'));
        }

        // =================================================================
        // Event Listeners
        // =================================================================

        // Open modal for adding a new record.
        $('#btn-add-skema').on('click', function() {
            resetForm();
            modal.modal('show');
        });

        // Handle form submission for both create and update.
        form.on('submit', function(e) {
            e.preventDefault();
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    modal.modal('hide');
                    Swal.fire('Berhasil', response.message, 'success');
                    dataTable.ajax.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 400) { // Handle validation errors
                        const errors = xhr.responseJSON.messages;
                        $.each(errors, (field, message) => {
                            const element = form.find(`[name="${field}"]`);
                            element.addClass('is-invalid').next('.invalid-feedback').text(message);
                        });
                        // Baris ini yang kemungkinan besar menyebabkan masalah. Kita nonaktifkan.
                        // toastr.error('Periksa kembali data yang Anda masukkan.', 'Validasi Gagal');
                    } else {
                        Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                    }
                },
                complete: function() {
                    // Fungsi ini akan memastikan tombol kembali normal APAPUN YANG TERJADI.
                    submitBtn.html(originalBtnText).prop('disabled', false);
                }
            });
        });

        // Use event delegation for action buttons in the DataTable.
        $('#table-skema tbody').on('click', '.btn-edit', function() {
            openEditModal($(this).data('id'));
        });

        $('#table-skema tbody').on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Anda yakin ingin menghapus data ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.get(`${baseUrl}/master/skema/delete/${id}`, function(response) {
                        Swal.fire('Dihapus!', response.message, 'success');
                        dataTable.ajax.reload();
                    }).fail((xhr) => {
                        const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus data.';
                        Swal.fire('Gagal', errorMsg, 'error');
                    });
                }
            });
        });

    });
</script>

<?= $this->endSection() ?>