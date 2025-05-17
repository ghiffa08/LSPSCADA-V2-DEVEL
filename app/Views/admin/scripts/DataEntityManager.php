<script>
    /**
     * Data Entity Manager Module with Dependent Dropdown Support
     * 
     * A reusable and configurable module for handling various entity data management including:
     * - Data table initialization and configuration
     * - CRUD operations (Create, Read, Update, Delete)
     * - Form handling with validation
     * - Excel import functionality
     * - Dynamic dependent dropdowns
     * 
     * @author Claude
     * @version 1.1.0
     */
    const DataEntityManager = (function() {
        'use strict';

        // Module configuration - will be overridden by init params
        let config = {
            baseUrl: '',
            entityName: '',
            primaryKey: '',
            selectors: {
                modal: '',
                form: '',
                table: '',
                importForm: '',
                importModal: '',
                importBtn: '',
                filterInput: '',
                select2Elements: '',
                modalTitle: ''
            },
            endpoints: {
                save: '',
                getById: '',
                delete: '',
                dataTable: '',
                import: ''
            },
            formFields: {},
            columns: [],
            renderFormatters: {},
            customFilters: null,
            additionalOptions: {},
            defaultOrder: [1, 'asc'],
            dependentDropdowns: {}, // New configuration for dependent dropdowns
            callbacks: {
                beforeSave: null,
                afterSave: null,
                beforeDelete: null,
                afterDelete: null,
                beforeEdit: null,
                afterEdit: null,
                onImportSuccess: null,
                onDropdownChange: null // New callback for dropdown changes
            }
        };

        // Data table instance
        let dataTable;
        // Current edit data (used for dependent dropdowns during edit)
        let editData = null;

        /**
         * Initialize the module with custom configuration
         * @param {Object} customConfig - Custom configuration to override defaults
         */
        function init(customConfig) {
            // Merge custom config with default config
            config = mergeConfig(config, customConfig);

            // Ensure baseUrl is set
            config.baseUrl = config.baseUrl || '<?= base_url(); ?>' || '';

            initDataTable();
            initFormHandling();

            // Initialize Select2 if selector is provided
            if (config.selectors.select2Elements) {
                initSelect2();
            }

            // Initialize dependent dropdowns if configured
            if (Object.keys(config.dependentDropdowns).length > 0) {
                initDependentDropdowns();
            }

            // Initialize file inputs if import functionality is enabled
            if (config.selectors.importForm) {
                initFileInputs();
                initImportHandler();
            }

            bindEvents();
        }

        /**
         * Initialize dependent dropdowns based on configuration
         */
        function initDependentDropdowns() {
            // Iterate through each dependent dropdown configuration
            Object.keys(config.dependentDropdowns).forEach(parentKey => {
                const dropdownConfig = config.dependentDropdowns[parentKey];

                // Add change event listener to parent dropdown
                $(dropdownConfig.parentSelector).on('change', function() {
                    const parentValue = $(this).val();
                    const childSelector = dropdownConfig.childSelector;

                    // Reset child dropdowns in the chain
                    resetChildDropdowns(dropdownConfig);

                    if (parentValue) {
                        // Show loading state
                        $(childSelector).prop('disabled', true);
                        $(childSelector).html(`<option value="">Loading...</option>`).trigger('change');

                        // Make AJAX call to get dependent data
                        $.ajax({
                            url: `${config.baseUrl}/${dropdownConfig.endpoint}`,
                            type: 'POST',
                            data: {
                                [dropdownConfig.paramName]: parentValue
                            },
                            dataType: 'html',
                            success: function(response) {
                                $(childSelector).html(response).prop('disabled', false);

                                // If editing and we have stored data for this child
                                if (editData && editData[dropdownConfig.valueField]) {
                                    $(childSelector).val(editData[dropdownConfig.valueField]).trigger('change');
                                }

                                // Execute callback if provided
                                if (typeof config.callbacks.onDropdownChange === 'function') {
                                    config.callbacks.onDropdownChange(parentKey, parentValue, childSelector);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error(`Error loading ${childSelector}:`, error);
                                $(childSelector).html(`<option value="">Error loading data</option>`)
                                    .prop('disabled', true)
                                    .trigger('change');

                                showNotification('error', 'Gagal', `Gagal memuat data dependen`);
                            }
                        });
                    }
                });
            });
        }

        /**
         * Reset child dropdowns and their dependents in the chain
         */
        function resetChildDropdowns(dropdownConfig) {
            const childSelector = dropdownConfig.childSelector;

            // Reset current child
            $(childSelector).html(`<option value="">${dropdownConfig.placeholder || 'Pilih...'}</option>`)
                .prop('disabled', true)
                .trigger('change');

            // If this child has its own dependents, reset them too
            if (dropdownConfig.childConfig) {
                resetChildDropdowns(dropdownConfig.childConfig);
            }
        }

        /**
         * Load data for a dependent dropdown
         * @param {string} parentKey - Key of the parent dropdown in config
         * @param {string} parentValue - Selected value of the parent dropdown
         * @param {function} callback - Optional callback to execute after loading
         */
        function loadDependentDropdown(parentKey, parentValue, callback) {
            if (!config.dependentDropdowns[parentKey]) return;

            const dropdownConfig = config.dependentDropdowns[parentKey];
            const childSelector = dropdownConfig.childSelector;

            if (!parentValue) {
                resetChildDropdowns(dropdownConfig);
                return;
            }

            // Show loading state
            $(childSelector).prop('disabled', true);
            $(childSelector).html(`<option value="">Loading...</option>`).trigger('change');

            $.ajax({
                url: `${config.baseUrl}/${dropdownConfig.endpoint}`,
                type: 'POST',
                data: {
                    [dropdownConfig.paramName]: parentValue
                },
                dataType: 'html',
                success: function(response) {
                    $(childSelector).html(response).prop('disabled', false);

                    // If editing and we have stored data for this child
                    if (editData && editData[dropdownConfig.valueField]) {
                        $(childSelector).val(editData[dropdownConfig.valueField]).trigger('change');
                    }

                    // Execute callback if provided
                    if (typeof callback === 'function') {
                        callback();
                    }

                    // Execute global dropdown change callback if provided
                    if (typeof config.callbacks.onDropdownChange === 'function') {
                        config.callbacks.onDropdownChange(parentKey, parentValue, childSelector);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(`Error loading ${childSelector}:`, error);
                    $(childSelector).html(`<option value="">Error loading data</option>`)
                        .prop('disabled', true)
                        .trigger('change');

                    showNotification('error', 'Gagal', `Gagal memuat data dependen`);
                }
            });
        }

        /**
         * Deep merge two objects
         * @param {Object} target - Target object
         * @param {Object} source - Source object to merge
         * @returns {Object} - Merged object
         */
        function mergeConfig(target, source) {
            const output = Object.assign({}, target);

            if (isObject(target) && isObject(source)) {
                Object.keys(source).forEach(key => {
                    if (isObject(source[key])) {
                        if (!(key in target)) {
                            Object.assign(output, {
                                [key]: source[key]
                            });
                        } else {
                            output[key] = mergeConfig(target[key], source[key]);
                        }
                    } else {
                        Object.assign(output, {
                            [key]: source[key]
                        });
                    }
                });
            }

            return output;
        }

        /**
         * Check if value is an object
         * @param {*} item - Item to check
         * @returns {boolean} - True if item is an object
         */
        function isObject(item) {
            return (item && typeof item === 'object' && !Array.isArray(item));
        }

        /**
         * Initialize DataTable with server-side processing
         */
        function initDataTable() {
            // Process columns with formatters
            const processedColumns = config.columns.map(column => {
                // If there's a formatter for this column, apply it
                if (column.data in config.renderFormatters) {
                    column.render = config.renderFormatters[column.data];
                }
                return column;
            });

            // Add index and action columns
            const indexedColumns = DataTableHelper.addIndexColumn(processedColumns);
            const columnsWithActions = DataTableHelper.addActionColumn(indexedColumns, {
                idField: config.primaryKey,
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
                    config.defaultOrder
                ],
                responsive: true,
                filters: function() {
                    const filters = {};

                    // Add custom filter if filterInput selector is provided
                    if (config.selectors.filterInput) {
                        filters.custom_filter = $(config.selectors.filterInput).val();
                    }

                    // Apply additional custom filters if provided
                    if (typeof config.customFilters === 'function') {
                        Object.assign(filters, config.customFilters());
                    }

                    return filters;
                },
                ...config.additionalOptions
            };

            // Initialize DataTable
            const tableSelector = config.selectors.table.substring(1); // Remove # from selector
            dataTable = DataTableHelper.initServerSideTable(
                tableSelector,
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
                editData = null; // Clear edit data
            });

            // Handle form submission
            $(config.selectors.form).on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                let formData = form.serialize();
                const url = `${config.baseUrl}/${config.endpoints.save}`;

                // Run before save callback if provided
                if (typeof config.callbacks.beforeSave === 'function') {
                    const processedData = config.callbacks.beforeSave(formData, form);
                    // If callback returns false, stop submission
                    if (processedData === false) return;

                    // If callback returns modified data, use it
                    if (processedData) {
                        formData = processedData;
                    }
                }

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

                            // Run after save callback if provided
                            if (typeof config.callbacks.afterSave === 'function') {
                                config.callbacks.afterSave(response, form);
                            }
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
                placeholder: `Pilih ${config.entityName.toLowerCase()}`,
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

                    // Run import success callback if provided
                    if (typeof config.callbacks.onImportSuccess === 'function') {
                        config.callbacks.onImportSuccess(response);
                    }
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
                    editEntity(id);
                }
            });

            // Delete button click handler
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                if (id) {
                    deleteEntity(id);
                }
            });

            // Filter change handler
            if (config.selectors.filterInput) {
                $(config.selectors.filterInput).on('change', function() {
                    reloadTable();
                });
            }
        }

        /**
         * Open edit modal for entity
         */
        function editEntity(id) {
            // Run before edit callback if provided
            if (typeof config.callbacks.beforeEdit === 'function') {
                const result = config.callbacks.beforeEdit(id);
                // If callback returns false, stop edit process
                if (result === false) return;
            }

            // First, reset the form to ensure a clean state
            resetForm(config.selectors.form);

            // Show modal
            $(config.selectors.modal).modal('show');

            // Change modal title
            $(config.selectors.modal).find(config.selectors.modalTitle).text(`Edit ${config.entityName}`);

            // Show loading in form fields while data loads
            const form = $(config.selectors.form);
            form.find('input, textarea, select').prop('disabled', true);
            form.append('<div id="form-loading" class="text-center py-2"><i class="fas fa-spinner fa-spin"></i> Loading data...</div>');

            // Store current edit data (will be used for dependent dropdowns)
            editData = null;

            // Fetch entity data
            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getById}${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Remove loading indicator
                    $('#form-loading').remove();
                    form.find('input, textarea, select').prop('disabled', false);

                    if (response.status) {
                        const entityData = response.data;
                        editData = entityData; // Store for dependent dropdowns

                        // Add hidden ID field
                        const idFieldSelector = `[name="${config.primaryKey}"]`;
                        const idField = form.find(idFieldSelector);
                        if (idField.length) {
                            idField.val(entityData[config.primaryKey]);
                        } else {
                            form.append(`<input type="hidden" name="${config.primaryKey}" value="${entityData[config.primaryKey]}">`);
                        }

                        // Fill form data dynamically based on formFields configuration
                        Object.keys(config.formFields).forEach(key => {
                            const fieldSelector = config.formFields[key];
                            const fieldName = fieldSelector.match(/\[name="([^"]+)"\]/)[1];
                            const field = form.find(fieldSelector);

                            if (field.length) {
                                const value = entityData[fieldName];

                                // Handle different field types
                                if (field.is(':radio')) {
                                    // For radio buttons
                                    form.find(`${fieldSelector}[value="${value}"]`).prop('checked', true);
                                } else if (field.is('select')) {
                                    // For select elements, possibly with Select2
                                    field.val(value).trigger('change');

                                    // If this is a parent dropdown in a dependent relationship,
                                    // we need to load its children with the current value
                                    Object.keys(config.dependentDropdowns).forEach(parentKey => {
                                        const dropdownConfig = config.dependentDropdowns[parentKey];
                                        if (fieldSelector === dropdownConfig.parentSelector) {
                                            loadDependentDropdown(parentKey, value, function() {
                                                // After loading child, check if we need to load further dependents
                                                if (dropdownConfig.childConfig && entityData[dropdownConfig.childConfig.valueField]) {
                                                    const childValue = entityData[dropdownConfig.childConfig.valueField];
                                                    const childElement = $(dropdownConfig.childSelector).val(childValue).trigger('change');

                                                    // If this child has its own dependents, load them
                                                    if (dropdownConfig.childConfig.childConfig) {
                                                        loadDependentDropdown(
                                                            Object.keys(config.dependentDropdowns).find(
                                                                k => config.dependentDropdowns[k].parentSelector === dropdownConfig.childSelector
                                                            ),
                                                            childValue
                                                        );
                                                    }
                                                }
                                            });
                                        }
                                    });
                                } else {
                                    // For normal inputs
                                    field.val(value);
                                }
                            }
                        });

                        // Run after edit callback if provided
                        if (typeof config.callbacks.afterEdit === 'function') {
                            config.callbacks.afterEdit(entityData, form);
                        }
                    } else {
                        showNotification('error', 'Gagal', response.message || `Gagal mengambil data ${config.entityName.toLowerCase()}`);
                        $(config.selectors.modal).modal('hide');
                    }
                },
                error: function(xhr) {
                    // Remove loading indicator
                    $('#form-loading').remove();
                    form.find('input, textarea, select').prop('disabled', false);

                    let errorMessage = `Gagal mengambil data ${config.entityName.toLowerCase()}`;
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification('error', 'Gagal', errorMessage);
                    $(config.selectors.modal).modal('hide');
                }
            });
        }

        /**
         * Delete entity with confirmation
         */
        function deleteEntity(id) {
            // Run before delete callback if provided
            if (typeof config.callbacks.beforeDelete === 'function') {
                const result = config.callbacks.beforeDelete(id);
                // If callback returns false, stop delete process
                if (result === false) return;
            }

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

                                // Run after delete callback if provided
                                if (typeof config.callbacks.afterDelete === 'function') {
                                    config.callbacks.afterDelete(id, response);
                                }
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

            // Reset select2 if selector is available
            if (config.selectors.select2Elements) {
                form.find(config.selectors.select2Elements).val('').trigger('change');
            }

            // Reset dependent dropdowns
            if (Object.keys(config.dependentDropdowns).length > 0) {
                Object.keys(config.dependentDropdowns).forEach(parentKey => {
                    const dropdownConfig = config.dependentDropdowns[parentKey];
                    resetChildDropdowns(dropdownConfig);

                    // Reset parent dropdown
                    $(dropdownConfig.parentSelector).val('').trigger('change');
                });
            }

            // Clear validation errors
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');

            // Reset primary key if editing
            form.find(`[name="${config.primaryKey}"]`).remove();

            // Reset form action
            form.attr('action', `${config.baseUrl}/${config.endpoints.save}`);

            // Reset modal title
            $(config.selectors.modal).find(config.selectors.modalTitle).text(`Tambah ${config.entityName}`);

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
            updateFileLabel,
            editEntity,
            deleteEntity,
            resetForm,
            loadDependentDropdown
        };
    })();
</script>