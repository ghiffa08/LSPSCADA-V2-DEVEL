<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section("content"); ?>

<h2 class="section-title">Manajemen Unit Kompetensi</h2>
<p class="section-lead">Kelola semua data unit kompetensi untuk setiap skema sertifikasi.</p>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Unit</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" id="btn-add-unit">
                        <i class="fas fa-plus"></i> Tambah Unit
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-unit" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Skema</th>
                                <th>Kode Unit</th>
                                <th>Nama Unit</th>
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
    <?= $this->include('admin/partials/modals/save-form-unit') ?>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    $(document).ready(function() {
        const modal = $('#addUnitModal');
        const form = $('#add-unit-form');
        const baseUrl = '<?= base_url() ?>';
        let dataTable;

        dataTable = $('#table-unit').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "order": [],
            "ajax": {
                "url": `${baseUrl}/master/unit/get-data-table`,
                "type": "POST"
            },
            "columns": [
                { "data": null, "orderable": false },
                { "data": "nama_skema" }, // Menampilkan nama skema, bukan ID
                { "data": "kode_unit" },
                { "data": "nama_unit" },
                { "data": "status" },
                { "data": null, "orderable": false }
            ],
            "columnDefs": [{
                "targets": 0,
                "render": (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
            }, {
                "targets": 4,
                "className": 'text-center',
                "render": (data) => data === 'Y' ?
                    '<span class="badge badge-success">Aktif</span>' :
                    '<span class="badge badge-danger">Nonaktif</span>'
            }, {
                "targets": -1,
                "render": (data, type, row) => `
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info btn-edit" data-id="${row.id_unit}" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id_unit}" title="Hapus"><i class="fas fa-trash"></i></button>
                    </div>`
            }],
        });

        function resetForm() {
            form[0].reset();
            form.find('input[name="id_unit"]').val('');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
            form.find('select.select2').val('').trigger('change');
            modal.find('.modal-title').text('Tambah Unit');
        }

        function openEditModal(id) {
            resetForm();
            $.get(`${baseUrl}/master/unit/getById/${id}`, function(response) {
                if (response.status) {
                    const unit = response.data;
                    form.find('[name="id_unit"]').val(unit.id_unit);
                    form.find('[name="id_skema"]').val(unit.id_skema).trigger('change');
                    form.find('[name="kode_unit"]').val(unit.kode_unit);
                    form.find('[name="nama_unit"]').val(unit.nama_unit);
                    form.find('[name="keterangan"]').val(unit.keterangan);
                    form.find(`input[name="status"][value="${unit.status}"]`).prop('checked', true);

                    modal.find('.modal-title').text('Edit Unit');
                    modal.modal('show');
                }
            }).fail(() => Swal.fire('Error', 'Gagal mengambil data dari server.', 'error'));
        }

        $('#btn-add-unit').on('click', function() {
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

        $('#table-unit tbody').on('click', '.btn-edit', function() {
            openEditModal($(this).data('id'));
        });

        $('#table-unit tbody').on('click', '.btn-delete', function() {
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
                    $.get(`${baseUrl}/master/unit/delete/${id}`, function(response) {
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