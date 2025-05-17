<script>
    /**
     * KUK Management Module
     * 
     * A comprehensive module for handling KUK (Kriteria Unjuk Kerja) data management including:
     * - Data table initialization and configuration
     * - CRUD operations (Create, Read, Update, Delete)
     * - Form handling with validation
     * - Excel import functionality
     * - Dynamic dependent dropdowns
     */
    const kukManager = (function() {
        'use strict';

        // Configuration
        const config = {
            baseUrl: '<?= base_url(); ?>' || '',
            selectors: {
                modal: '#addKukModal',
                form: '#add-kuk-form',
                table: '#table-kuk',
                importForm: '#import-excel-form',
                importModal: '#importExcelModal',
                importBtn: '#import-btn',
                filterInput: '#filter-input',
                select2Elements: '.select2',
                modalTitle: '.modal-title',
                skemaSelect: '#id_skema',
                unitSelect: '#id_unit',
                elemenSelect: '#id_elemen'
            },
            endpoints: {
                save: 'master/kuk/save',
                getById: 'master/kuk/getById/',
                delete: 'master/kuk/delete/',
                dataTable: 'master/kuk/get-data-table',
                getUnits: 'api/get-unit',
                getElements: 'api/get-elemen'
            },
            formFields: {
                skema: '[name="id_skema"]',
                unit: '[name="id_unit"]',
                elemen: '[name="id_elemen"]',
                kode: '[name="kode_kuk"]',
                nama: '[name="nama_kuk"]',
                pertanyaan: '[name="pertanyaan"]',
                idKuk: '[name="id_kuk"]'
            }
        };

        // Data table instance
        let dataTable;

        // For storing data during edit operation
        let editData = null;

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
                    data: 'id_kuk'
                },
                {
                    data: 'kode_kuk'
                },
                {
                    data: 'nama_kuk'
                },
                {
                    data: 'pertanyaan'
                }
            ];

            // Add index and action columns
            const indexedColumns = DataTableHelper.addIndexColumn(columns);
            const columnsWithActions = DataTableHelper.addActionColumn(indexedColumns, {
                idField: 'id_kuk',
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
         * Initialize dependent dropdowns (Skema -> Unit -> Elemen)
         */
        function initDependentDropdowns() {
            $(config.selectors.skemaSelect).on('change', function() {
                const skemaId = $(this).val();
                loadUnits(skemaId);
            });

            $(config.selectors.unitSelect).on('change', function() {
                const unitId = $(this).val();
                loadElements(unitId);
            });
        }

        /**
         * Load units based on selected skema
         * @param {string} skemaId - ID of the selected skema
         * @param {function} callback - Optional callback function to execute after units are loaded
         */
        function loadUnits(skemaId, callback) {
            if (!skemaId) {
                // Reset and disable unit dropdown if no skema selected
                $(config.selectors.unitSelect).empty().append('<option value="">Pilih Unit</option>').prop('disabled', true).trigger('change');
                // Also reset elemen dropdown
                $(config.selectors.elemenSelect).empty().append('<option value="">Pilih Elemen</option>').prop('disabled', true).trigger('change');
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
                    $(config.selectors.unitSelect).html(response).prop('disabled', false);

                    // If we're in the middle of an edit operation and have stored data
                    if (editData && editData.id_unit) {
                        $(config.selectors.unitSelect).val(editData.id_unit).trigger('change');
                    } else {
                        $(config.selectors.unitSelect).trigger('change');
                    }

                    // Execute callback if provided
                    if (typeof callback === 'function') {
                        callback();
                    }
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
         * Load elements based on selected unit
         * @param {string} unitId - ID of the selected unit
         * @param {function} callback - Optional callback function to execute after elements are loaded
         */
        function loadElements(unitId, callback) {
            if (!unitId) {
                // Reset and disable elemen dropdown if no unit selected
                $(config.selectors.elemenSelect).empty().append('<option value="">Pilih Elemen</option>').prop('disabled', true).trigger('change');
                return;
            }

            // Enable elemen dropdown and show loading
            $(config.selectors.elemenSelect).prop('disabled', true);
            $(config.selectors.elemenSelect).empty().append('<option value="">Loading...</option>').trigger('change');

            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getElements}`,
                type: 'POST',
                data: {
                    id_unit: unitId
                },
                dataType: 'html',
                success: function(response) {
                    $(config.selectors.elemenSelect).html(response).prop('disabled', false);

                    // If we're in the middle of an edit operation and have stored data
                    if (editData && editData.id_elemen) {
                        $(config.selectors.elemenSelect).val(editData.id_elemen).trigger('change');
                    } else {
                        $(config.selectors.elemenSelect).trigger('change');
                    }

                    // Execute callback if provided
                    if (typeof callback === 'function') {
                        callback();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading elements:', error);
                    $(config.selectors.elemenSelect).empty()
                        .append('<option value="">Error loading elemen</option>')
                        .prop('disabled', true)
                        .trigger('change');

                    showNotification('error', 'Gagal', 'Gagal memuat data elemen');
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
                // Reset edit data when modal is closed
                editData = null;
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
                        if (response.status || response.success) { // Check for both 'status' and 'success' in response
                            showNotification('success', 'Berhasil', response.message || 'Kriteria Unjuk Kerja berhasil ditambahkan!');
                            $(config.selectors.modal).modal('hide');
                            resetForm(config.selectors.form);
                            reloadTable();
                        } else {
                            if (response.errors) {
                                showValidationErrors(form, response.errors);
                            } else {
                                showNotification('error', 'Gagal', response.message || 'Terjadi kesalahan saat memproses data');
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

            // Special placeholder for elemen
            $(config.selectors.elemenSelect).select2({
                width: '100%',
                placeholder: "Pilih Elemen",
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
                    editKuk(id);
                }
            });

            // Delete button click handler
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                if (id) {
                    deleteKuk(id);
                }
            });

            // Filter change handler
            $(config.selectors.filterInput).on('change', function() {
                reloadTable();
            });
        }

        /**
         * Open edit modal for KUK
         */
        function editKuk(id) {
            // First, reset the form to ensure a clean state
            resetForm(config.selectors.form);

            // Show modal
            $(config.selectors.modal).modal('show');

            // Change modal title
            $(config.selectors.modal).find(config.selectors.modalTitle).text('Edit KUK');

            // Show loading in form fields while data loads
            const form = $(config.selectors.form);
            form.find('input, textarea, select').prop('disabled', true);
            form.append('<div id="form-loading" class="text-center py-2"><i class="fas fa-spinner fa-spin"></i> Loading data...</div>');

            // Fetch KUK data
            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getById}${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Remove loading indicator
                    $('#form-loading').remove();
                    form.find('input, textarea, select').prop('disabled', false);

                    if (response.status) {
                        const kuk = response.data;

                        // Store the data for use in the dependent dropdown callbacks
                        editData = kuk;

                        // Add hidden ID field
                        const idField = form.find(config.formFields.idKuk);
                        if (idField.length) {
                            idField.val(kuk.id_kuk);
                        } else {
                            form.append(`<input type="hidden" name="id_kuk" value="${kuk.id_kuk}">`);
                        }

                        // Set other simple fields
                        form.find(config.formFields.kode).val(kuk.kode_kuk);
                        form.find(config.formFields.nama).val(kuk.nama_kuk);
                        form.find(config.formFields.pertanyaan).val(kuk.pertanyaan);

                        // Set skema and trigger cascade of dependent dropdowns
                        form.find(config.formFields.skema).val(kuk.id_skema).trigger('change');

                        // Unit and elemen will be set by the loadUnits and loadElements callbacks
                        // based on the editData we've stored

                    } else {
                        showNotification('error', 'Gagal', response.message || 'Gagal mengambil data KUK');
                        $(config.selectors.modal).modal('hide');
                    }
                },
                error: function(xhr) {
                    // Remove loading indicator
                    $('#form-loading').remove();
                    form.find('input, textarea, select').prop('disabled', false);

                    let errorMessage = 'Gagal mengambil data KUK';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification('error', 'Gagal', errorMessage);
                    $(config.selectors.modal).modal('hide');
                }
            });
        }

        /**
         * Delete KUK with confirmation
         */
        function deleteKuk(id) {
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
            form.find(config.formFields.idKuk).remove();

            // Reset form action in case it was changed
            form.attr('action', `${config.baseUrl}/${config.endpoints.save}`);

            // Reset modal title
            $(config.selectors.modal).find(config.selectors.modalTitle).text('Tambah KUK');

            // Remove any loading indicators if present
            $('#form-loading').remove();
            form.find('input, textarea, select').prop('disabled', false);

            // Disable unit and elemen dropdowns until skema and unit are selected
            $(config.selectors.unitSelect).empty().append('<option value="">Pilih Unit</option>').prop('disabled', true);
            $(config.selectors.elemenSelect).empty().append('<option value="">Pilih Elemen</option>').prop('disabled', true);
        }

        /**
         * Show validation errors on form
         */
        function showValidationErrors(form, errors) {
            // Field name mapping
            const fieldMapping = {
                'kode_kuk': 'kode',
                'nama_kuk': 'nama'
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
        kukManager.init();

        // Make updateFileLabel globally accessible
        window.updateFileLabel = kukManager.updateFileLabel;
    });
</script>