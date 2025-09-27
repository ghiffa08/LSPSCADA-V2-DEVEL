<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section("content"); ?>
<h2 class="section-title">Manajemen Kop Surat</h2>
<p class="section-lead">Kelola semua data konfigurasi kop surat pada halaman ini.</p>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Konfigurasi Kop Surat</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" id="btn-add-header">
                        <i class="fas fa-plus"></i> Tambah Konfigurasi
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-header" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kop</th>
                                <th>Instansi</th>
                                <th>Logo</th>
                                <th>Judul</th>
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
<?= form_open('api/header-konfigurasi/save', ['id' => 'header-form', 'enctype' => 'multipart/form-data']) ?>
<div class="modal fade" id="saveHeaderModal" data-backdrop="static" tabindex="-1" aria-labelledby="saveHeaderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Konfigurasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Nama Kop <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kop" class="form-control" placeholder="cth: Kop Surat Resmi">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Instansi (Opsional)</label>
                        <select class="form-control select2" name="instansi_id">
                            <option value="">Pilih Instansi (Untuk Kop Surat Default)</option>
                            <?php foreach ($instansiList as $instansi) : ?>
                                <option value="<?= $instansi->id ?>"><?= esc($instansi->nama_instansi) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>File Logo <span class="text-danger">*</span></label>
                        <input type="file" name="logo" class="form-control-file">
                        <small class="form-text text-muted">Max: 1MB. Tipe: png, jpg, gif.</small>
                        <div class="invalid-feedback"></div>
                        <img id="logo-preview" src="" class="mt-2" style="max-width: 200px; display: none;" alt="Logo Preview" />
                    </div>
                    <div class="form-group col-md-4">
                        <label>Lebar Logo (mm) <span class="text-danger">*</span></label>
                        <input type="number" name="logo_width" class="form-control" placeholder="cth: 30">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Judul Utama </label>
                    <input type="text" name="title" class="form-control" placeholder="cth: LEMBAGA SERTIFIKASI PROFESI">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label>Teks Header (Alamat, dll) </label>
                    <textarea rows="3" class="form-control" name="header_string" placeholder="Masukan alamat, kontak, website, dll."></textarea>
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
        const modal = $('#saveHeaderModal');
        const form = $('#header-form');
        const baseUrl = '<?= base_url() ?>';
        let dataTable;

        // =================================================================
        // DataTable Initialization
        // =================================================================
        dataTable = $('#table-header').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "order": [],
            "ajax": {
                "url": `${baseUrl}/api/header-konfigurasi/get-data-table`,
                "type": "POST"
            },
            "columns": [{
                    "data": null,
                    "orderable": false
                }, // No.
                {
                    "data": "nama_kop"
                },
                {
                    "data": "instansi_name"
                },
                {
                    "data": "logo",
                    "orderable": false
                },
                {
                    "data": "title"
                },
                {
                    "data": null,
                    "orderable": false
                } // Aksi
            ],
            "columnDefs": [{
                    "targets": 0, // Render index number
                    "render": (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                {
                    "targets": 2, // Render instansi
                    "render": (data) => data ? data : '<span class="badge badge-light">Default/Global</span>'
                },
                {
                    "targets": 3, // Render logo
                    "className": 'text-center',
                    "render": (data) => `<img src="${baseUrl}/uploads/logos/${data}" alt="logo" width="100">`
                },
                {
                    "targets": -1, // Render action buttons
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
            form.find('select.select2').val('').trigger('change');
            $('#logo-preview').attr('src', '').hide();
            modal.find('.modal-title').text('Tambah Konfigurasi');
        }

        function openEditModal(id) {
            resetForm();
            $.get(`${baseUrl}/api/header-konfigurasi/getById/${id}`, function(response) {
                if (response.status) {
                    const header = response.data;
                    form.find('[name="id"]').val(header.id);
                    form.find('[name="nama_kop"]').val(header.nama_kop);
                    form.find('[name="instansi_id"]').val(header.instansi_id).trigger('change');
                    form.find('[name="logo_width"]').val(header.logo_width);
                    form.find('[name="title"]').val(header.title);
                    form.find('[name="header_string"]').val(header.header_string);

                    if (header.logo) {
                        $('#logo-preview').attr('src', `${baseUrl}/uploads/logos/${header.logo}`).show();
                    }

                    modal.find('.modal-title').text('Edit Konfigurasi');
                    modal.modal('show');
                } else {
                    Swal.fire('Gagal', response.message || 'Data tidak ditemukan', 'error');
                }
            }).fail(() => Swal.fire('Error', 'Gagal mengambil data dari server.', 'error'));
        }

        // =================================================================
        // Event Listeners
        // =================================================================
        $('#btn-add-header').on('click', function() {
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
                data: new FormData(this), // Penting untuk upload file
                processData: false, // Penting untuk upload file
                contentType: false, // Penting untuk upload file
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

        $('#table-header tbody').on('click', '.btn-edit', function() {
            openEditModal($(this).data('id'));
        });

        $('#table-header tbody').on('click', '.btn-delete', function() {
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
                    $.get(`${baseUrl}/api/header-konfigurasi/delete/${id}`, function(response) {
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