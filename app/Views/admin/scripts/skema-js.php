<script>
    /**
     * Skema Management Module
     * 
     * A comprehensive module for handling Skema data management including:
     * - Data table initialization and configuration
     * - CRUD operations (Create, Read, Update, Delete)
     * - Form handling with validation
     * - Excel import functionality
     */
    const SkemaManager = (function() {
        'use strict';

        // Configuration
        const config = {
            baseUrl: '<?= base_url(); ?>' || '',
            selectors: {
                modal: '#saveSkemaModal',
                form: '#add-skema-form',
                table: '#table-skema',
                importForm: '#import-excel-form',
                importModal: '#importExcelModal',
                importBtn: '#import-btn',
                filterInput: '#filter-input',
                select2Elements: '.select2',
                modalTitle: '.modal-title'
            },
            endpoints: {
                save: 'master/skema/save',
                getById: 'master/skema/getById/',
                delete: 'master/skema/delete/',
                dataTable: 'master/skema/get-data-table',
                import: 'master/skema/import' // Added missing import endpoint
            },
            formFields: {
                kode: '[name="kode_skema"]',
                nama: '[name="nama_skema"]',
                jenis: '[name="jenis_skema"]',
                status: '[name="status"]',
                idSkema: '[name="id_skema"]'
            }
        };

        // Data table instance
        let dataTable;

        /**
         * Initialize the module
         */
        function init() {
            initDataTable();
            initFormHandling();
            initSelect2();
            initFileInputs();
            initImportHandler();
            bindEvents();
        }

        /**
         * Initialize DataTable with server-side processing
         */
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
                    data: 'jenis_skema',
                    render: function(data) {
                        const jenisMap = {
                            'KKNI': '<span class="badge badge-primary">KKNI</span>',
                            'Okupasi': '<span class="badge badge-info">Okupasi</span>',
                            'Klaster': '<span class="badge badge-warning">Klaster</span>'
                        };
                        return jenisMap[data] || data;
                    }
                },
                {
                    data: 'status',
                    className: 'text-center',
                    render: function(data) {
                        const statusMap = {
                            'Y': '<span class="badge badge-success">Aktif</span>',
                            'N': '<span class="badge badge-danger">Nonaktif</span>'
                        };
                        return statusMap[data] || '<span class="badge badge-secondary">Tidak Diketahui</span>';
                    }
                }
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
                filters: function() {
                    return {
                        'custom_filter': $(config.selectors.filterInput).val()
                    };
                }
            };

            // Initialize DataTable
            dataTable = DataTableHelper.initServerSideTable(
                config.selectors.table.substring(1), // Remove # from selector
                `${config.baseUrl}/${config.endpoints.dataTable}`,
                columnsWithActions,
                options
            );
        }

        /**
         * Initialize form handling for create/edit operations
         */
        function initFormHandling() {
            // Clear form when modal is hidden
            $(config.selectors.modal).on('hidden.bs.modal', function() {
                resetForm(config.selectors.form);
            });

            // Handle form submission
            $(config.selectors.form).on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const formData = form.serialize();
                const url = `${config.baseUrl}/${config.endpoints.save}`;

                // Disable submit button during AJAX
                toggleSubmitButton(submitBtn, true);

                // Clear previous error messages
                form.find('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            showNotification('success', 'Berhasil', response.message);
                            $(config.selectors.modal).modal('hide');
                            resetForm(config.selectors.form);
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

        /**
         * Initialize Select2 components
         */
        function initSelect2() {
            $(config.selectors.select2Elements).select2({
                width: '100%',
                placeholder: "Pilih jenis Skema",
                dropdownParent: $(config.selectors.modal)
            });
        }

        /**
         * Initialize file input styling
         */
        function initFileInputs() {
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        }

        /**
         * Initialize Excel import handler
         */
        function initImportHandler() {
            $(config.selectors.importForm).on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const url = `${config.baseUrl}/${config.endpoints.import}`;
                const formData = new FormData(this);
                const importBtn = $(config.selectors.importBtn);

                // Show loading state
                toggleSubmitButton(importBtn, true, '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importing...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        handleImportResponse(response);
                    },
                    error: function(xhr, status, error) {
                        showNotification('error', 'Kesalahan Sistem', 'Terjadi kesalahan saat mengimpor data: ' + error);
                    },
                    complete: function() {
                        toggleSubmitButton(importBtn, false, 'Import Data');
                    }
                });
            });
        }

        /**
         * Handle import response based on status
         */
        function handleImportResponse(response) {
            if (response.status === 'success') {
                showNotification('success', 'Impor Berhasil', response.message, function() {
                    $(config.selectors.importModal).modal('hide');
                    reloadTable();
                });
            } else if (response.status === 'partial') {
                let message = `<p>${response.message}</p>`;
                if (response.failed_rows) {
                    message += '<details><summary>Lihat Baris yang Gagal</summary><pre>' +
                        JSON.stringify(response.failed_rows, null, 2) + '</pre></details>';
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Impor Sebagian',
                    html: message
                }).then(() => {
                    $(config.selectors.importModal).modal('hide');
                    reloadTable();
                });
            } else {
                showNotification('error', 'Impor Gagal', response.message);
            }
        }

        /**
         * Bind event handlers for buttons and other elements
         */
        function bindEvents() {
            // Edit button click handler
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                if (id) {
                    editSkema(id);
                }
            });

            // Delete button click handler
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                if (id) {
                    deleteSkema(id);
                }
            });

            // Filter change handler
            $(config.selectors.filterInput).on('change', function() {
                reloadTable();
            });
        }

        /**
         * Open edit modal for skema
         */
        function editSkema(id) {
            // First, reset the form to ensure a clean state
            resetForm(config.selectors.form);

            // Show modal
            $(config.selectors.modal).modal('show');

            // Change modal title
            $(config.selectors.modal).find(config.selectors.modalTitle).text('Edit Skema');

            // Show loading in form fields while data loads
            const form = $(config.selectors.form);
            form.find('input, textarea, select').prop('disabled', true);
            form.append('<div id="form-loading" class="text-center py-2"><i class="fas fa-spinner fa-spin"></i> Loading data...</div>');

            // Fetch Skema data
            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getById}${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Remove loading indicator
                    $('#form-loading').remove();
                    form.find('input, textarea, select').prop('disabled', false);

                    if (response.status) {
                        const skema = response.data;

                        // Add hidden ID field
                        const idField = form.find(config.formFields.idSkema);
                        if (idField.length) {
                            idField.val(skema.id_skema);
                        } else {
                            form.append(`<input type="hidden" name="id_skema" value="${skema.id_skema}">`);
                        }

                        // Fill form data
                        form.find(config.formFields.kode).val(skema.kode_skema);
                        form.find(config.formFields.nama).val(skema.nama_skema);
                        form.find(config.formFields.jenis).val(skema.jenis_skema).trigger('change');
                        form.find(`${config.formFields.status}[value="${skema.status}"]`).prop('checked', true);
                    } else {
                        showNotification('error', 'Gagal', response.message || 'Gagal mengambil data skema');
                        $(config.selectors.modal).modal('hide');
                    }
                },
                error: function(xhr) {
                    // Remove loading indicator
                    $('#form-loading').remove();
                    form.find('input, textarea, select').prop('disabled', false);

                    let errorMessage = 'Gagal mengambil data skema';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification('error', 'Gagal', errorMessage);
                    $(config.selectors.modal).modal('hide');
                }
            });
        }

        /**
         * Delete Skema with confirmation
         */
        function deleteSkema(id) {
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

        /**
         * Reset form to initial state
         */
        function resetForm(formSelector) {
            const form = $(formSelector);

            // Reset form
            form[0].reset();

            // Reset select2
            form.find(config.selectors.select2Elements).val('').trigger('change');

            // Clear validation errors
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');

            // Reset id if editing
            form.find(config.formFields.idSkema).remove();

            // Reset form action
            form.attr('action', `${config.baseUrl}/${config.endpoints.save}`);

            // Reset modal title
            $(config.selectors.modal).find(config.selectors.modalTitle).text('Tambah Skema');

            // Remove any loading indicators if present
            $('#form-loading').remove();
            form.find('input, textarea, select').prop('disabled', false);
        }

        /**
         * Show validation errors on form
         */
        function showValidationErrors(form, errors) {
            $.each(errors, function(field, message) {
                const input = form.find(`[name="${field}"]`);
                if (input.length) {
                    input.addClass('is-invalid');
                    const errorDiv = input.next('.invalid-feedback');
                    if (errorDiv.length) {
                        errorDiv.text(message);
                    } else {
                        input.after(`<div class="invalid-feedback">${message}</div>`);
                    }
                }
            });
        }

        /**
         * Toggle submit button state
         */
        function toggleSubmitButton(button, isDisabled, text) {
            button.prop('disabled', isDisabled);
            if (text) {
                button.html(text);
            } else {
                button.html(isDisabled ? '<i class="fas fa-spinner fa-spin"></i> Menyimpan...' : 'Simpan');
            }
        }

        /**
         * Show notification using SweetAlert2
         */
        function showNotification(icon, title, text, callback) {
            const options = {
                icon: icon,
                title: title,
                text: text
            };

            if (icon === 'success') {
                options.timer = 2000;
                options.showConfirmButton = false;
            }

            Swal.fire(options).then(() => {
                if (typeof callback === 'function') {
                    callback();
                }
            });
        }

        /**
         * Reload DataTable
         */
        function reloadTable() {
            if (dataTable) {
                DataTableHelper.reloadTable(dataTable);
            }
        }

        /**
         * Update file input label with selected filename
         */
        function updateFileLabel(input) {
            const fileName = input.files[0] ? input.files[0].name : 'Pilih file Excel';
            $(input).next('.custom-file-label').html(fileName);
        }

        // Public API
        return {
            init,
            reloadTable,
            updateFileLabel
        };
    })();

    // Initialize on document ready
    $(document).ready(function() {
        SkemaManager.init();

        // Make updateFileLabel globally accessible
        window.updateFileLabel = SkemaManager.updateFileLabel;
    });
</script>