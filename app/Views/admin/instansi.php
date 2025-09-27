<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section("content"); ?>
<h2 class="section-title">Manajemen Instansi</h2>
<p class="section-lead">Kelola semua data instansi yang akan digunakan pada kop surat.</p>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Instansi</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" id="btn-add-instansi">
                        <i class="fas fa-plus"></i> Tambah Instansi
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-instansi" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Instansi</th>
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
<?= form_open('api/instansi/save', ['id' => 'instansi-form']) ?>
<div class="modal fade" id="saveInstansiModal" data-backdrop="static" tabindex="-1" aria-labelledby="saveInstansiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Instansi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id">

                <div class="form-group">
                    <label>Nama Instansi <span class="text-danger">*</span></label>
                    <input type="text" name="nama_instansi" class="form-control" placeholder="cth: SMKN 1 Kedawung">
                    <div class="invalid-feedback"></div>
                </div>

            </div>
            <div class="modal-footer bg-whitesmoke">
                <button type="submit" class="btn btn-primary btn-lg btn-block">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    $(document).ready(function() {
        // =================================================================
        // Configuration & Initialization
        // =================================================================
        const modal = $('#saveInstansiModal');
        const form = $('#instansi-form');
        const baseUrl = '<?= base_url() ?>';
        let dataTable;

        // =================================================================
        // DataTable Initialization
        // =================================================================
        dataTable = $('#table-instansi').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "order": [],
            "ajax": {
                "url": `${baseUrl}/api/instansi/get-data-table`,
                "type": "POST"
            },
            "columns": [{
                    "data": null,
                    "orderable": false
                },
                {
                    "data": "nama_instansi"
                },
                {
                    "data": null,
                    "orderable": false
                }
            ],
            "columnDefs": [{
                    "targets": 0,
                    "render": (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                {
                    "targets": -1,
                    "render": (data, type, row) => `
                <div class="btn-group">
                    <button class="btn btn-sm btn-info btn-edit" data-id="${row.id}" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}" title="Hapus"><i class="fas fa-trash"></i></button>
                </div>`
                }
            ],
        });

        // =================================================================
        // Modal & Form Logic
        // =================================================================
        function resetForm() {
            form[0].reset();
            form.find('input[name="id"]').val('');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
            modal.find('.modal-title').text('Tambah Instansi');
        }

        function openEditModal(id) {
            resetForm();
            $.get(`${baseUrl}/api/instansi/getById/${id}`, function(response) {
                if (response.status) {
                    const instansi = response.data;
                    form.find('[name="id"]').val(instansi.id);
                    form.find('[name="nama_instansi"]').val(instansi.nama_instansi);
                    modal.find('.modal-title').text('Edit Instansi');
                    modal.modal('show');
                } else {
                    Swal.fire('Gagal', response.message || 'Data tidak ditemukan', 'error');
                }
            }).fail(() => Swal.fire('Error', 'Gagal mengambil data dari server.', 'error'));
        }

        // =================================================================
        // Event Listeners
        // =================================================================
        $('#btn-add-instansi').on('click', function() {
            resetForm();
            modal.modal('show');
        });

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
                    if (xhr.status === 400) {
                        const errors = xhr.responseJSON.messages;
                        $.each(errors, (field, message) => {
                            const element = form.find(`[name="${field}"]`);
                            element.addClass('is-invalid').next('.invalid-feedback').text(message);
                        });
                    } else {
                        Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                    }
                },
                complete: function() {
                    submitBtn.html(originalBtnText).prop('disabled', false);
                }
            });
        });

        $('#table-instansi tbody').on('click', '.btn-edit', function() {
            openEditModal($(this).data('id'));
        });

        $('#table-instansi tbody').on('click', '.btn-delete', function() {
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
                    $.get(`${baseUrl}/api/instansi/delete/${id}`, function(response) {
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