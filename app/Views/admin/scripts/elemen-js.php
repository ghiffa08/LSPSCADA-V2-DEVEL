<script>
    /**
     * Unit Management Module
     * 
     * A comprehensive module for handling unit data management including:
     * - Data table initialization and configuration
     * - CRUD operations (Create, Read, Update, Delete)
     * - Form handling with validation
     * - Excel import functionality
     * - Dynamic dependent dropdowns
     */
    const ElemenManager = (function() {
        'use strict';

        // Configuration
        const config = {
            baseUrl: '<?= base_url(); ?>' || '',
            selectors: {
                modal: '#addElemenModal',
                form: '#add-elemen-form',
                table: '#table-elemen',
                importForm: '#import-excel-form',
                importModal: '#importExcelModal',
                importBtn: '#import-btn',
                filterInput: '#filter-input',
                select2Elements: '.select2',
                modalTitle: '.modal-title',
                skemaSelect: '#id_skema',
                unitSelect: '#id_unit'
            },
            endpoints: {
                save: 'master/elemen/save',
                getById: 'master/elemen/getById/',
                delete: 'master/elemen/delete/',
                dataTable: 'master/elemen/get-data-table',
                getUnits: 'api/get-unit'
            },
            formFields: {
                skema: '[name="id_skema"]',
                unit: '[name="id_unit"]',
                kode: '[name="kode"]',
                nama: '[name="nama"]',
                idElemen: '[name="id_elemen"]'
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
            initDependentDropdowns();
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
                    data: 'id_unit'
                },
                {
                    data: 'id_elemen'
                },
                {
                    data: 'kode_elemen'
                },
                {
                    data: 'nama_elemen'
                }
            ];


            // Add index and action columns
            const indexedColumns = DataTableHelper.addIndexColumn(columns);
            const columnsWithActions = DataTableHelper.addActionColumn(indexedColumns, {
                idField: 'id_elemen',
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
         * Initialize dependent dropdowns (Skema -> Unit)
         */
        function initDependentDropdowns() {
            $(config.selectors.skemaSelect).on('change', function() {
                const skemaId = $(this).val();
                loadUnits(skemaId);
            });
        }

        /**
         * Load units based on selected skema
         */
        function loadUnits(skemaId) {
            if (!skemaId) {
                // Reset and disable unit dropdown if no skema selected
                $(config.selectors.unitSelect).empty().append('<option value="">Pilih Unit</option>').prop('disabled', true).trigger('change');
                return;
            }

            // Enable unit dropdown and show loading
            $(config.selectors.unitSelect).prop('disabled', true);
            $(config.selectors.unitSelect).empty().append('<option value="">Loading...</option>').trigger('change');

            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getUnits}`,
                type: 'POST',
                data: {
                    id_skema: skemaId
                },
                dataType: 'html',
                success: function(response) {
                    $(config.selectors.unitSelect).html(response).prop('disabled', false).trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error('Error loading units:', error);
                    $(config.selectors.unitSelect).empty()
                        .append('<option value="">Error loading units</option>')
                        .prop('disabled', true)
                        .trigger('change');

                    showNotification('error', 'Gagal', 'Gagal memuat data unit');
                }
            });
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
                const url = form.attr('action');

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
                placeholder: "Pilih",
                dropdownParent: $(config.selectors.modal)
            });

            // Special placeholder for skema
            $(config.selectors.skemaSelect).select2({
                width: '100%',
                placeholder: "Pilih Skema",
                dropdownParent: $(config.selectors.modal)
            });

            // Special placeholder for unit
            $(config.selectors.unitSelect).select2({
                width: '100%',
                placeholder: "Pilih Unit",
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
                const url = form.attr('action');
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
                    editElemen(id);
                }
            });

            // Delete button click handler
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                if (id) {
                    deleteElemen(id);
                }
            });

            // Filter change handler
            $(config.selectors.filterInput).on('change', function() {
                reloadTable();
            });
        }

        /**
         * Open edit modal for elemen
         */
        function editElemen(id) {
            // First, reset the form to ensure a clean state
            resetForm(config.selectors.form);

            // Show modal
            $(config.selectors.modal).modal('show');

            // Change modal title
            $(config.selectors.modal).find(config.selectors.modalTitle).text('Edit Elemen');

            // Show loading in form fields while data loads
            const form = $(config.selectors.form);
            form.find('input, textarea, select').prop('disabled', true);
            form.append('<div id="form-loading" class="text-center py-2"><i class="fas fa-spinner fa-spin"></i> Loading data...</div>');

            // Fetch Elemen data
            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getById}${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Remove loading indicator
                    $('#form-loading').remove();
                    form.find('input, textarea, select').prop('disabled', false);

                    if (response.status) {
                        const elemen = response.data;

                        // Add hidden ID field
                        // First check if it exists, if so update it, otherwise create it
                        const idField = form.find(config.formFields.idElemen);
                        if (idField.length) {
                            idField.val(elemen.id_elemen);
                        } else {
                            form.append(`<input type="hidden" name="id_elemen" value="${elemen.id_elemen}">`);
                        }

                        // Fill skema dropdown first
                        form.find(config.formFields.skema).val(elemen.id_skema).trigger('change');

                        // Wait for units to load before setting unit value
                        setTimeout(function() {
                            form.find(config.formFields.unit).val(elemen.id_unit).trigger('change');
                            form.find(config.formFields.kode).val(elemen.kode_elemen);
                            form.find(config.formFields.nama).val(elemen.nama_elemen);
                        }, 500);
                    } else {
                        showNotification('error', 'Gagal', response.message || 'Gagal mengambil data elemen');
                        $(config.selectors.modal).modal('hide');
                    }
                },
                error: function(xhr) {
                    // Remove loading indicator
                    $('#form-loading').remove();
                    form.find('input, textarea, select').prop('disabled', false);

                    let errorMessage = 'Gagal mengambil data elemen';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification('error', 'Gagal', errorMessage);
                    $(config.selectors.modal).modal('hide');
                }
            });
        }

        /**
         * Delete Elemen with confirmation
         */
        function deleteElemen(id) {
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
            form.find('.invalid-feedback').text(''); // Clear error messages but keep the divs

            // Reset id if editing
            form.find(config.formFields.idElemen).remove();

            // Reset form action in case it was changed
            form.attr('action', `${config.baseUrl}/${config.endpoints.save}`);

            // Reset modal title
            $(config.selectors.modal).find(config.selectors.modalTitle).text('Tambah Elemen');

            // Remove any loading indicators if present
            $('#form-loading').remove();
            form.find('input, textarea, select').prop('disabled', false);

            // Disable unit dropdown until skema is selected
            $(config.selectors.unitSelect).empty().append('<option value="">Pilih Unit</option>').prop('disabled', true);
        }

        /**
         * Show validation errors on form
         */
        function showValidationErrors(form, errors) {
            // Field name mapping
            const fieldMapping = {
                'kode_elemen': 'kode',
                'nama_elemen': 'nama'
            };

            $.each(errors, function(field, message) {
                // Convert field names to match form input names
                const inputField = fieldMapping[field] || field;
                const input = form.find(`[name="${inputField}"]`);

                if (input.length) {
                    input.addClass('is-invalid');

                    // Add error message
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
         * Create loading spinner element
         */
        function createLoadingElement() {
            return '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Loading...</p></div>';
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
        ElemenManager.init();

        // Make updateFileLabel globally accessible
        window.updateFileLabel = ElemenManager.updateFileLabel;
    });
</script>