<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section("content"); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Konfigurasi Kop Surat</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#saveHeaderModal">
                        <i class="fas fa-plus"></i> Tambah Baru
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-header" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kop</th>
                                <th>Pemilik (Asesor)</th>
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
<div class="modal fade" id="saveHeaderModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="saveHeaderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveHeaderModalLabel">Tambah Konfigurasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="save-header-form" action="#" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="id">

                    <div class="form-group">
                        <label for="nama_kop">Nama Kop Surat <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kop" class="form-control" placeholder="Cth: Kop Surat Asesor A">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="assessor_id">Pemilik (Asesor)</label>
                        <select name="assessor_id" class="form-control select2">
                            <option value="">-- Umum / Default --</option>
                            <?php foreach ($assessors as $assessor) : ?>
                                <option value="<?= $assessor['id_asesor'] ?>"><?= esc($assessor['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Biarkan kosong jika ini adalah kop surat default.</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="logo">Upload Logo (PNG, JPG, max: 1MB)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="logo" id="logo-input" onchange="HeaderManager.updateFileLabel(this)">
                                    <label class="custom-file-label" for="logo-input">Pilih file...</label>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Logo Saat Ini</label>
                            <div>
                                <img src="https://placehold.co/200x80/eef0f2/777777?text=Tidak+Ada+Logo" id="logo-preview" class="img-thumbnail" alt="Logo Preview" style="max-height: 80px;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="logo_width">Lebar Logo (dalam mm) <span class="text-danger">*</span></label>
                        <input type="number" name="logo_width" class="form-control" value="38">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="title">Judul Header <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="LEMBAGA SERTIFIKASI PROFESI...">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="header_string">Detail Header (Alamat, Kontak, dll) <span class="text-danger">*</span></label>
                        <textarea name="header_string" class="form-control" rows="4" placeholder="Jl. Sukamulya No.77..."></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("scripts") ?>
<script>
    /**
     * Header Configuration Management Module
     * Dibuat persis mengikuti struktur dan logika dari SkemaManager
     */
    const HeaderManager = (function() {
        'use strict';

        // Konfigurasi disesuaikan untuk Header
        const config = {
            baseUrl: '<?= base_url(); ?>',
            selectors: {
                modal: '#saveHeaderModal',
                form: '#save-header-form',
                table: '#table-header',
                select2Elements: '.select2',
                modalTitle: '.modal-title',
                logoInput: '#logo-input',
                logoPreview: '#logo-preview'
            },
            endpoints: {
                save: 'api/headerkonfigurasi/save',
                getById: 'api/headerkonfigurasi/getById/',
                delete: 'api/headerkonfigurasi/delete/',
                dataTable: 'api/headerkonfigurasi/getDataTable',
            },
            formFields: {
                id: '[name="id"]',
                nama_kop: '[name="nama_kop"]',
                assessor_id: '[name="assessor_id"]',
                logo_width: '[name="logo_width"]',
                title: '[name="title"]',
                header_string: '[name="header_string"]',
            },
            placeholders: {
                logo: 'https://placehold.co/200x80/eef0f2/777777?text=Tidak+Ada+Logo'
            }
        };

        let dataTable;

        function init() {
            initDataTable();
            initFormHandling();
            initSelect2();
            bindEvents();
        }

        function initDataTable() {
            const columns = [{
                data: 'id' // Untuk penomoran
            }, {
                data: 'nama_kop'
            }, {
                data: 'assessor_name',
                render: data => data ? data : '<span class="badge badge-info">UMUM / DEFAULT</span>'
            }, {
                data: 'logo',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: data => data ? `<img src="${config.baseUrl}/uploads/logos/${data}" height="40" class="img-fluid img-thumbnail">` : 'N/A'
            }, {
                data: 'title'
            }];

            const indexedColumns = DataTableHelper.addIndexColumn(columns);
            const columnsWithActions = DataTableHelper.addActionColumn(indexedColumns, {
                idField: 'id',
                edit: {
                    title: 'Edit'
                },
                delete: {
                    title: 'Hapus'
                }
            });

            dataTable = DataTableHelper.initServerSideTable(
                config.selectors.table.substring(1),
                `${config.baseUrl}/${config.endpoints.dataTable}`,
                columnsWithActions
            );
        }

        function initFormHandling() {
            $(config.selectors.modal).on('hidden.bs.modal', function() {
                resetForm();
            });

            $(config.selectors.form).on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const url = `${config.baseUrl}/${config.endpoints.save}`;

                // PENYESUAIAN WAJIB: Menggunakan FormData karena ada file upload
                const formData = new FormData(this);

                toggleSubmitButton(submitBtn, true);
                clearValidationErrors(form);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false, // Wajib false untuk FormData
                    contentType: false, // Wajib false untuk FormData
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            showNotification('success', 'Berhasil', response.message);
                            $(config.selectors.modal).modal('hide');
                            resetForm();
                            reloadTable();
                        } else {
                            if (response.errors) {
                                showValidationErrors(form, response.errors);
                            } else {
                                showNotification('error', 'Gagal', response.message);
                            }
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan pada server';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showNotification('error', 'Gagal', errorMessage);
                    },
                    complete: function() {
                        toggleSubmitButton(submitBtn, false);
                    }
                });
            });
        }

        function initSelect2() {
            $(config.selectors.select2Elements).select2({
                width: '100%',
                placeholder: "-- Pilih Asesor --",
                dropdownParent: $(config.selectors.modal)
            });
        }

        function bindEvents() {
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                if (id) {
                    editHeader(id);
                }
            });

            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                if (id) {
                    deleteHeader(id);
                }
            });
        }

        function editHeader(id) {
            resetForm();
            const form = $(config.selectors.form);
            const modal = $(config.selectors.modal);

            modal.modal('show');
            modal.find(config.selectors.modalTitle).text('Edit Konfigurasi');

            form.find('.modal-body').append('<div id="form-loading" class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');

            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getById}${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#form-loading').remove();
                    if (response.status) {
                        const data = response.data;

                        form.find(config.formFields.id).val(data.id);
                        form.find(config.formFields.nama_kop).val(data.nama_kop);
                        form.find(config.formFields.assessor_id).val(data.assessor_id).trigger('change');
                        form.find(config.formFields.logo_width).val(data.logo_width);
                        form.find(config.formFields.title).val(data.title);
                        form.find(config.formFields.header_string).val(data.header_string);

                        const logoUrl = data.logo ? `${config.baseUrl}/uploads/logos/${data.logo}` : config.placeholders.logo;
                        $(config.selectors.logoPreview).attr('src', logoUrl);

                    } else {
                        showNotification('error', 'Gagal', response.message || 'Gagal mengambil data');
                        modal.modal('hide');
                    }
                },
                error: function(xhr) {
                    $('#form-loading').remove();
                    let errorMessage = 'Gagal mengambil data';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification('error', 'Gagal', errorMessage);
                    modal.modal('hide');
                }
            });
        }

        function deleteHeader(id) {
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
                        url: `${config.baseUrl}/${config.endpoints.delete}${id}`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                showNotification('success', 'Berhasil', response.message);
                                reloadTable();
                            } else {
                                showNotification('error', 'Gagal', response.message);
                            }
                        },
                        error: function() {
                            showNotification('error', 'Gagal', 'Terjadi kesalahan pada server');
                        }
                    });
                }
            });
        }

        // --- Helper Functions (Struktur sama persis dengan SkemaManager) ---
        function resetForm() {
            const form = $(config.selectors.form);
            form[0].reset();
            form.find(config.selectors.select2Elements).val('').trigger('change');
            clearValidationErrors(form);
            form.find(config.formFields.id).val('');
            $(config.selectors.modal).find(config.selectors.modalTitle).text('Tambah Konfigurasi');
            $('#form-loading').remove();

            // Reset preview logo dan label file
            $(config.selectors.logoPreview).attr('src', config.placeholders.logo);
            $(config.selectors.logoInput).next('.custom-file-label').html('Pilih file...');
        }

        function clearValidationErrors(form) {
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
        }

        function showValidationErrors(form, errors) {
            $.each(errors, function(field, message) {
                const input = form.find(`[name="${field}"]`);
                if (input.length) {
                    input.addClass('is-invalid');
                    const errorDiv = input.closest('.form-group, .custom-file').find('.invalid-feedback');
                    if (errorDiv.length) {
                        errorDiv.text(message);
                    } else {
                        // Fallback jika struktur HTML berbeda
                        input.after(`<div class="invalid-feedback d-block">${message}</div>`);
                    }
                }
            });
        }

        function toggleSubmitButton(button, isDisabled) {
            button.prop('disabled', isDisabled);
            button.html(isDisabled ? '<i class="fas fa-spinner fa-spin"></i> Menyimpan...' : 'Simpan');
        }

        function showNotification(icon, title, text) {
            const options = {
                icon,
                title,
                text
            };
            if (icon === 'success') {
                options.timer = 2000;
                options.showConfirmButton = false;
            }
            Swal.fire(options);
        }

        function reloadTable() {
            if (dataTable) {
                DataTableHelper.reloadTable(dataTable);
            }
        }

        function updateFileLabel(input) {
            const fileName = input.files[0] ? input.files[0].name : 'Pilih file...';
            $(input).next('.custom-file-label').html(fileName);
        }

        // Public API
        return {
            init,
            updateFileLabel // Agar bisa diakses dari onchange
        };
    })();

    // Inisialisasi saat dokumen siap
    $(document).ready(function() {
        if (typeof DataTableHelper !== 'undefined') {
            HeaderManager.init();
        } else {
            console.error('DataTableHelper is not loaded.');
        }
    });
</script>
<?= $this->endSection() ?>