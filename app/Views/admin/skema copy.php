<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Skema</h4>
                <div class="card-header-action">
                    <div class="btn-group">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addSkemaModal">
                            <i class="fas fa-plus"></i> Tambah Skema
                        </button>

                        <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#importExcelModal">
                            <i class="fas fa-upload"></i> Import Excel
                        </button>

                        <button href="<?= base_url('export-skema') ?>" class="btn btn-primary">
                            <i class="fas fa-download"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="table-skema" class="table table-bordered table-md">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Skema</th>
                            <th>Kode Skema</th>
                            <th>Nama Skema</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<!-- Modals Section -->
<?= $this->section("modals") ?>

<!-- Save Skema Modal -->
<?= form_open('master/skema/save', ['id' => 'save-skema-form']) ?>
<div class="modal fade" id="saveSkemaModal" data-backdrop="static" tabindex="-1" aria-labelledby="saveSkemaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span id="modal-title">Tambah Skema</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted" id="modal-description">Masukan Kode Skema, Nama Skema, dan Status Skema</p>

                <input type="hidden" name="id_skema" id="id_skema" value="">

                <div class="form-group">
                    <label>Kode Skema<span class="text-danger">*</span></label>
                    <input type="text" name="kode_skema" id="kode_skema" class="form-control" placeholder="Masukan Kode Skema">
                    <div class="invalid-feedback" id="kode-error"></div>
                </div>

                <div class="form-group">
                    <label>Nama Skema<span class="text-danger">*</span></label>
                    <textarea rows="3" class="form-control" name="nama_skema" id="nama_skema" placeholder="Masukan Nama Skema"></textarea>
                    <div class="invalid-feedback" id="nama-error"></div>
                </div>

                <div class="form-group">
                    <label>Jenis Skema<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="jenis_skema" id="jenis_skema">
                        <option value="">Pilih Jenis Skema</option>
                        <option value="KKNI">KKNI</option>
                        <option value="Okupasi">Okupasi</option>
                        <option value="Klaster">Klaster</option>
                    </select>
                    <div class="invalid-feedback" id="jenis-error"></div>
                </div>

                <div class="form-group">
                    <label>Status<span class="text-danger">*</span></label>
                    <div class="selectgroup w-100">
                        <label class="selectgroup-item">
                            <input type="radio" name="status" class="selectgroup-input" value="Y" checked>
                            <span class="selectgroup-button">Aktif</span>
                        </label>
                        <label class="selectgroup-item">
                            <input type="radio" name="status" class="selectgroup-input" value="N">
                            <span class="selectgroup-button">Tidak Aktif</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-whitesmoke">
                <button type="submit" class="btn btn-primary btn-lg btn-block" id="save-button">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<!-- Import Excel Modal -->
<?= form_open_multipart(site_url('/master/skema/import'), ['id' => 'import-excel-form']); ?>
<div class="modal fade" id="importExcelModal" data-backdrop="static" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importExcelModalLabel">Import KUK</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Pastikan file Excel mengikuti format contoh yang disediakan.
                    <a href="<?= site_url('master/skema/download-template') ?>" class="btn btn-sm btn-primary ml-2">
                        Download Template
                    </a>
                </div>

                <?php if (session('error')): ?>
                    <div class="alert alert-danger">
                        <?= session('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session('success')): ?>
                    <div class="alert alert-success">
                        <?= session('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session('warning')): ?>
                    <div class="alert alert-warning">
                        <?= session('warning') ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="file_excel">File Excel<span class="text-danger">*</span></label>
                    <div class="custom-file">
                        <input type="file"
                            class="custom-file-input <?= session('errors.file_excel') ? 'is-invalid' : '' ?>"
                            id="file_excel"
                            name="file_excel"
                            accept=".xlsx,.xls"
                            required
                            onchange="updateFileLabel(this)">
                        <label class="custom-file-label" for="file_excel">Pilih file Excel</label>
                        <?php if (session('errors.file_excel')): ?>
                            <div class="invalid-feedback"><?= session('errors.file_excel') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-whitesmoke">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Import Data</button>
            </div>
        </div>
    </div>
</div>
<?= form_close(); ?>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteSkemaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Skema</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h5 id="delete-confirmation-text">Apakah anda yakin akan menghapus skema ini?</h5>
                <input type="hidden" name="id" id="delete-id" value="">
            </div>
            <div class="modal-footer bg-whitesmoke">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">Hapus</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<!-- Script Section -->
<?= $this->section('js') ?>

<script>
    const skemaModule = (function() {
        'use strict';

        let dataTable;
        const tableId = 'table-skema';
        const baseUrl = '<?= base_url(); ?>' || '';

        function init() {
            initDataTable();
            bindEvents();
        }

        function initDataTable() {
            // Define columns
            const columns = [{
                    data: 'id_skema'
                },
                {
                    data: 'kode_skema'
                },
                {
                    data: 'nama_skema'
                },
                {
                    data: 'jenis_skema'
                },
                {
                    data: 'status',
                    className: 'text-center',
                    render: function(data) {
                        if (data === 'Y') {
                            return `<span class="badge badge-success">Aktif</span>`;
                        } else if (data === 'N') {
                            return `<span class="badge badge-danger">Nonaktif</span>`;
                        } else {
                            return `<span class="badge badge-secondary">Tidak Diketahui</span>`;
                        }
                    }

                },
            ];

            // Add index and action columns
            const indexedColumns = DataTableHelper.addIndexColumn(columns);
            const columnsWithActions = DataTableHelper.addActionColumn(indexedColumns, {
                idField: 'id_skema',
                edit: {
                    title: 'Edit'
                },
                delete: {
                    title: 'Hapus'
                }
            });

            // Additional options
            const options = {
                order: [
                    [1, 'asc']
                ],
                responsive: true,
                // Optional: Add custom filters
                filters: function() {
                    return {
                        'custom_filter': $('#filter-input').val()
                    };
                }
            };

            // Initialize DataTable
            dataTable = DataTableHelper.initServerSideTable(
                tableId,
                `${baseUrl}/master/skema/get-data-table`,
                columnsWithActions,
                options
            );
        }

        function bindEvents() {
            // Add event handlers here

            // Example: Edit button click handler
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                // Handle edit action
            });

            // Example: Delete button click handler
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                // Handle delete action
            });

            // Example: Filter change handler
            $('#filter-input').on('change', function() {
                DataTableHelper.reloadTable(dataTable);
            });
        }

        // Public API
        return {
            init,
            reloadTable: function() {
                DataTableHelper.reloadTable(dataTable);
            }
        };
    })();

    // Initialize on document ready
    $(document).ready(function() {
        skemaModule.init();
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle form submission with AJAX
        $('#import-excel-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');
            var formData = new FormData(this);

            // Disable submit button and show loading
            $('#import-btn')
                .prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importing...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    // Handle different import statuses
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Impor Berhasil',
                            text: response.message
                        }).then(() => {
                            // Reload datatable or refresh page if needed
                            $('#importExcelModal').modal('hide');
                            // Optionally: table.ajax.reload();
                        });
                    } else if (response.status === 'partial') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Impor Sebagian',
                            html: `
                            <p>${response.message}</p>
                            ${response.failed_rows ? 
                                '<details>' +
                                '<summary>Lihat Baris yang Gagal</summary>' +
                                '<pre>' + JSON.stringify(response.failed_rows, null, 2) + '</pre>' +
                                '</details>' : ''
                            }
                        `
                        }).then(() => {
                            $('#importExcelModal').modal('hide');
                            // Optionally: table.ajax.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Impor Gagal',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan Sistem',
                        text: 'Terjadi kesalahan saat mengimpor data: ' + error
                    });
                },
                complete: function() {
                    // Re-enable submit button
                    $('#import-btn')
                        .prop('disabled', false)
                        .html('Import Data');

                }
            });
        });

        // Update file input label with selected filename
        window.updateFileLabel = function(input) {
            var fileName = input.files[0] ? input.files[0].name : 'Pilih file Excel';
            $(input).next('.custom-file-label').html(fileName);
        };

    });
</script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            width: '100%'
        });

        // File input label
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        // Handle add button click
        $('[data-target="#addSkemaModal"]').click(function() {
            $('#saveSkemaModal').modal('show');
            $('#modal-title').text('Tambah Skema');
            $('#modal-description').text('Masukan Kode Skema, Nama Skema, dan Status Skema');
            $('#save-button').text('Simpan');
            $('#save-skema-form')[0].reset();
            $('#id_skema').val('');
            $('.invalid-feedback').text('');
            $('.is-invalid').removeClass('is-invalid');
            $('#jenis_skema').val('').trigger('change');
            $('input[name="status"][value="Y"]').prop('checked', true);
        });

        // Handle edit button clicks
        $('.edit-skema').click(function() {
            const id = $(this).data('id');

            $.ajax({
                url: '<?= base_url('master/skema/get/') ?>' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#modal-title').text('Edit Skema');
                    $('#modal-description').text('Perbarui Kode Skema, Nama Skema, dan Status Skema');
                    $('#save-button').text('Update');

                    $('#id_skema').val(response.id_skema);
                    $('#kode_skema').val(response.kode_skema);
                    $('#nama_skema').val(response.nama_skema);
                    $('#jenis_skema').val(response.jenis_skema).trigger('change');
                    $('input[name="status"][value="' + response.status + '"]').prop('checked', true);

                    $('.invalid-feedback').text('');
                    $('.is-invalid').removeClass('is-invalid');

                    $('#saveSkemaModal').modal('show');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Gagal memuat data skema');
                }
            });
        });

        // Handle save form submission
        $('#save-skema-form').submit(function(e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = form.find('#save-button');
            const btnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#saveSkemaModal').modal('hide');
                        window.location.href = response.redirect;
                    } else {
                        // Handle validation errors
                        if (response.errors) {
                            for (const field in response.errors) {
                                $(`[name="${field}"]`).addClass('is-invalid');
                                $(`#${field}-error`).text(response.errors[field]);
                            }
                        }
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Terjadi kesalahan saat menyimpan data');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(btnText);
                }
            });
        });

        // Handle delete form submission
        $('[id^="delete-skema-form-"]').submit(function(e) {
            e.preventDefault();

            const form = $(this);
            const deleteBtn = form.find('button[type="submit"]');
            const btnText = deleteBtn.html();

            if (!confirm('Apakah Anda yakin ingin menghapus skema ini?')) {
                return false;
            }

            deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menghapus...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        form.closest('.modal').modal('hide');
                        window.location.href = response.redirect;
                    } else {
                        alert(response.message || 'Gagal menghapus data');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Terjadi kesalahan saat menghapus data');
                },
                complete: function() {
                    deleteBtn.prop('disabled', false).html(btnText);
                }
            });
        });

        // Handle delete button clicks
        $('[data-target^="#deleteSkemaModal-"]').click(function() {
            const id = $(this).data('id');
            const kode = $(this).data('kode');

            $('#delete-confirmation-text').html(
                `Apakah anda yakin akan menghapus <span class="text-danger font-weight-bold">"${kode}"</span>?`
            );
            $('#delete-id').val(id);
            $('#deleteSkemaModal').modal('show');
        });

        // Handle delete confirmation
        $('#confirm-delete').click(function() {
            const id = $('#delete-id').val();
            const btn = $(this);
            const btnText = btn.html();

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menghapus...');

            $.ajax({
                url: '<?= base_url('master/skema/delete') ?>',
                type: 'POST',
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#deleteSkemaModal').modal('hide');
                        window.location.href = response.redirect;
                    } else {
                        alert(response.message || 'Gagal menghapus data');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Terjadi kesalahan saat menghapus data');
                },
                complete: function() {
                    btn.prop('disabled', false).html(btnText);
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>