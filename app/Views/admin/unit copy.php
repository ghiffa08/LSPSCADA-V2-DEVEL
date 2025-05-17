<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Unit</h4>
                <div class="card-header-action">
                    <div class="btn-group">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addUnitModal">
                            <i class="fas fa-plus"></i> Tambah Unit
                        </button>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#importExcelModal">
                            <i class="fas fa-upload"></i> Import Excel
                        </button>
                        <button href="<?= base_url('export-unit') ?>" class="btn btn-primary">
                            <i class="fas fa-download"></i> Export Excel
                        </button>
                    </div>

                </div>
            </div>
            <div class="card-body">
                <table id="table-unit" class="table table-bordered table-md">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Skema</th>
                            <th>ID Unit</th>
                            <th>Kode Unit</th>
                            <th>Nama Unit</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<!-- Modals Section -->
<?= $this->section("modals") ?>

<!-- Add Unit Modal -->
<?= form_open('master/unit/save', ['id' => 'add-unit-form']) ?>
<div class="modal fade" id="addUnitModal" data-backdrop="static" tabindex="-1" aria-labelledby="addUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Unit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Masukan ID Skema, Kode Unit, Nama Unit, Keterangan dan Status</p>

                <div class="form-group">
                    <label>Skema<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_skema" id="id_skema">
                        <option value="">Pilih Skema</option>
                        <?php if (isset($listSkema)): ?>
                            <?php foreach ($listSkema as $row): ?>
                                <option value="<?= $row['id_skema'] ?>"><?= $row['nama_skema'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label>Kode Unit<span class="text-danger">*</span></label>
                    <input type="text" name="kode" class="form-control" id="inputKode" placeholder="Masukan Kode Unit">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label>Nama Unit<span class="text-danger">*</span></label>
                    <textarea class="form-control" name="nama" rows="3" placeholder="Masukan Nama Unit"></textarea>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label>Keterangan<span class="text-danger">*</span></label>
                    <textarea class="form-control" name="keterangan" id="inputDescription" rows="3" placeholder="Masukan Keterangan"></textarea>
                    <div class="invalid-feedback"></div>
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
                <button type="submit" class="btn btn-primary btn-lg btn-block">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>
<!-- Import Excel Modal -->
<?= form_open_multipart(site_url('/master/unit/import'), ['id' => 'import-excel-form']); ?>
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
                    <a href="<?= site_url('master/unit/download-template') ?>" class="btn btn-sm btn-primary ml-2">
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

<!-- Delete Unit Modals -->
<?php foreach ($listUnit as $value): ?>
    <?= form_open('master/unit/delete', ['id' => 'delete-unit-form-' . $value['id_unit']]) ?>
    <div class="modal fade" id="deleteUnitModal-<?= $value['id_unit'] ?>" tabindex="-1"
        aria-labelledby="deleteUnitModalLabel-<?= $value['id_unit'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Unit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Apakah anda yakin akan menghapus <span class="text-danger font-weight-bold">"<?= $value['kode_unit'] ?>"</span>?</h5>
                    <input type="hidden" name="id" value="<?= $value['id_unit'] ?>">
                </div>
                <div class="modal-footer bg-whitesmoke">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>
<?php endforeach; ?>

<?= $this->endSection() ?>

<!-- Script Section -->
<?= $this->section('js') ?>

<script>
    /**
     * Unit Form Handler
     * 
     * This script handles the AJAX submission of the unit form
     * and provides functionality for both creation and updates
     */
    $(function() {

        // Initialize select2 component
        $('.select2').select2({
            dropdownParent: $('#addUnitModal')
        });

        // Clear form when modal is hidden
        $('#addUnitModal').on('hidden.bs.modal', function() {
            resetForm('#add-unit-form');
        });

        // Handle form submission
        $('#add-unit-form').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const formData = form.serialize();
            const url = form.attr('action');

            // Disable submit button during AJAX
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            // Clear previous error messages
            form.find('.is-invalid').removeClass('is-invalid');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Hide modal
                        $('#addUnitModal').modal('hide');

                        // Reset form
                        resetForm('#add-unit-form');

                        // Reload DataTable
                        if (typeof table !== 'undefined') {
                            table.ajax.reload();
                        }
                    } else {
                        // Show error messages
                        if (response.errors) {
                            showValidationErrors(form, response.errors);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                        }
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan pada server';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errorMessage
                    });
                },
                complete: function() {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html('Simpan');
                }
            });
        });

        // Helper functions

        /**
         * Reset form to initial state
         */
        function resetForm(formSelector) {
            const form = $(formSelector);

            // Reset form
            form[0].reset();

            // Reset select2
            form.find('.select2').val('').trigger('change');

            // Clear validation errors
            form.find('.is-invalid').removeClass('is-invalid');

            // Reset id if editing
            form.find('input[name="id_unit"]').remove();

            // Reset form action in case it was changed
            form.attr('action', 'master/unit/save');

            // Reset modal title
            $('#addUnitModal .modal-title').text('Tambah Unit');
        }

        /**
         * Show validation errors on form
         */
        function showValidationErrors(form, errors) {
            $.each(errors, function(field, message) {
                // Convert field names to match form input names
                let inputField = field;
                if (field === 'kode_unit') inputField = 'kode';
                if (field === 'nama_unit') inputField = 'nama';

                const input = form.find(`[name="${inputField}"]`);
                input.addClass('is-invalid');

                // Add error message
                const errorDiv = input.next('.invalid-feedback');
                if (errorDiv.length) {
                    errorDiv.text(message);
                } else {
                    input.after(`<div class="invalid-feedback">${message}</div>`);
                }
            });
        }
    });

    /**
     * Open edit modal for unit
     * 
     * @param {number} id Unit ID to edit
     */
    function editUnit(id) {
        // Show modal
        $('#addUnitModal').modal('show');

        // Change modal title
        $('#addUnitModal .modal-title').text('Edit Unit');

        // Show loading in modal
        $('#addUnitModal .modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Loading...</p></div>');

        // Fetch unit data
        $.ajax({
            url: '<?= base_url(); ?>/master/unit/getById/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    // Reset modal content
                    $('#addUnitModal .modal-body').html($('#addUnitModal-original-content').html());

                    // Initialize select2
                    $('.select2').select2({
                        dropdownParent: $('#addUnitModal')
                    });

                    const unit = response.data;
                    const form = $('#add-unit-form');

                    // Add hidden ID field
                    form.append(`<input type="hidden" name="id_unit" value="${unit.id_unit}">`);

                    // Fill form data
                    form.find('[name="id_skema"]').val(unit.id_skema).trigger('change');
                    form.find('[name="kode"]').val(unit.kode_unit);
                    form.find('[name="nama"]').val(unit.nama_unit);
                    form.find('[name="keterangan"]').val(unit.keterangan);
                    form.find(`[name="status"][value="${unit.status}"]`).prop('checked', true);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
                    });

                    $('#addUnitModal').modal('hide');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal mengambil data unit'
                });

                $('#addUnitModal').modal('hide');
            }
        });
    }

    /**
     * Delete unit
     * 
     * @param {number} id Unit ID to delete
     */
    function deleteUnit(id) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: "Apakah Anda yakin ingin menghapus data ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url(); ?>/master/unit/delete/' + id,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // Reload DataTable
                            if (typeof table !== 'undefined') {
                                table.ajax.reload();
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan pada server'
                        });
                    }
                });
            }
        });
    }
</script>
<script>
    const unitModule = (function() {
        'use strict';

        let dataTable;
        const tableId = 'table-unit';
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
                    data: 'id_unit'
                },
                {
                    data: 'kode_unit'
                },
                {
                    data: 'nama_unit'
                },
                {
                    data: 'keterangan'
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
                idField: 'id_unit',
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
                `${baseUrl}/master/unit/get-data-table`,
                columnsWithActions,
                options
            );
        }

        function bindEvents() {
            // Add event handlers here

            // Example: Edit button click handler
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                if (id) {
                    editUnit(id);
                }
            });

            // Example: Delete button click handler
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                if (id) {
                    deleteUnit(id);
                }
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
        unitModule.init();
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
    // For file input label
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Initialize Select2
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: "Pilih Skema"
        });
    });
</script>
<?= $this->endSection() ?>