<script>
/**
 * DataEntityManager - Reusable JavaScript module for CRUD operations
 * 
 * This module provides standard functionality for:
 * - DataTable integration
 * - Form handling (create, edit, delete)
 * - AJAX operations with proper error handling
 * - Import/export functionality
 */
const DataEntityManager = (function() {
    'use strict';

    // Private properties
    let config = {
        // Default configuration, will be overridden by init()
        entityName: 'Data',
        primaryKey: 'id',
        baseUrl: '<?= base_url() ?>',
        dependentDropdowns: {},
        selectors: {
            modal: '#entityModal',
            form: '#entityForm',
            table: '#dataTable',
            importForm: '#importForm',
            importModal: '#importModal',
            importBtn: '#importBtn',
            filterInput: '#filterInput',
            select2Elements: '.select2',
            modalTitle: '.modal-title'
        },
        endpoints: {
            save: 'save',
            getById: 'getById/',
            delete: 'delete/',
            dataTable: 'get-data-table',
            import: 'import',
            updateOrder: 'updateOrder'
        },
        formFields: {},
        columns: [],
        defaultOrder: [1, 'asc'],
        additionalOptions: {},
        initCallback: null,
    };

    // Public methods
    return {
        // Initialize with configuration
        init: function(userConfig = {}) {
            // Merge user configuration with defaults
            config = { ...config, ...userConfig };
            
            // Store selectors for public access
            this.selectors = config.selectors;
            this.endpoints = config.endpoints;
            this.baseUrl = config.baseUrl;
            this.entityName = config.entityName;
            
            // Initialize DataTable
            this.initDataTable();
            
            // Initialize form submission handler
            this.initFormHandler();
            
            // Initialize import functionality if available
            if ($(config.selectors.importForm).length) {
                this.initImportHandler();
            }
            
            // Run callback if provided
            if (typeof config.initCallback === 'function') {
                config.initCallback(this);
            }
        },
        
        // Initialize DataTable with the specified columns
        initDataTable: function() {
            const self = this;
            const tableConfig = {
                processing: true,
                serverSide: true,
                ajax: {
                    url: `${config.baseUrl}/${config.endpoints.dataTable}`,
                    type: 'POST'
                },
                columns: [
                    // Add index column
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    // Add user-defined columns
                    ...config.columns,
                    // Add action column
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <button class="btn btn-sm btn-info btn-edit" data-id="${row[config.primaryKey]}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-delete" data-id="${row[config.primaryKey]}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            `;
                        }
                    }
                ],
                order: [config.defaultOrder],
                ...config.additionalOptions
            };
            
            $(config.selectors.table).DataTable(tableConfig);
            
            // Handle edit and delete button clicks
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                self.handleEdit(id);
            });
            
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                self.handleDelete(id);
            });
            
            // Handle filter if available
            if ($(config.selectors.filterInput).length) {
                $(config.selectors.filterInput).on('keyup', function() {
                    $(config.selectors.table).DataTable().search(this.value).draw();
                });
            }
        },
        
        // Initialize form submission handler
        initFormHandler: function() {
            const self = this;
            
            $(config.selectors.form).on('submit', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const submitBtn = form.find('[type="submit"]');
                const originalBtnText = submitBtn.html();
                
                // Disable submit button and show loading
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').attr('disabled', true);
                
                // Clear previous validation errors
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').text('');
                
                // Submit form via AJAX
                $.ajax({
                    url: form.attr('action') || `${config.baseUrl}/${config.endpoints.save}`,
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message || `${config.entityName} berhasil disimpan`,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            
                            // Close modal
                            $(config.selectors.modal).modal('hide');
                            
                            // Reload table
                            self.reloadTable();
                        } else {
                            // Show validation errors
                            if (response.errors) {
                                Object.keys(response.errors).forEach(field => {
                                    const inputField = form.find(`[name="${field}"]`);
                                    const feedbackField = inputField.siblings('.invalid-feedback');
                                    
                                    inputField.addClass('is-invalid');
                                    if (feedbackField.length) {
                                        feedbackField.text(response.errors[field]);
                                    }
                                });
                            } else {
                                // Show general error message
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message || 'Terjadi kesalahan saat menyimpan data'
                                });
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat menyimpan data'
                        });
                        console.error(xhr.responseText);
                    },
                    complete: function() {
                        // Re-enable submit button
                        submitBtn.html(originalBtnText).attr('disabled', false);
                    }
                });
            });
        },
        
        // Initialize import handler
        initImportHandler: function() {
            const self = this;
            
            $(config.selectors.importForm).on('submit', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const submitBtn = $(config.selectors.importBtn);
                const originalBtnText = submitBtn.html();
                
                // Validate file input
                const fileInput = form.find('input[type="file"]');
                if (!fileInput[0].files.length) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Pilih file terlebih dahulu'
                    });
                    return;
                }
                
                // Disable submit button and show loading
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Importing...').attr('disabled', true);
                
                // Submit form via AJAX
                $.ajax({
                    url: `${config.baseUrl}/${config.endpoints.import}`,
                    type: 'POST',
                    data: new FormData(form[0]),
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message || `${config.entityName} berhasil diimport`,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            
                            // Close modal
                            $(config.selectors.importModal).modal('hide');
                            
                            // Reload table
                            self.reloadTable();
                            
                            // Reset form
                            form[0].reset();
                        } else {
                            // Show error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message || 'Terjadi kesalahan saat import data'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat import data'
                        });
                        console.error(xhr.responseText);
                    },
                    complete: function() {
                        // Re-enable submit button
                        submitBtn.html(originalBtnText).attr('disabled', false);
                    }
                });
            });
        },
        
        // Handle delete button click
        handleDelete: function(id) {
            const self = this;
            
            Swal.fire({
                title: `Hapus ${config.entityName}?`,
                text: 'Data yang dihapus tidak dapat dikembalikan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send delete request
                    $.ajax({
                        url: `${config.baseUrl}/${config.endpoints.delete}${id}`,
                        type: 'DELETE',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                // Show success message
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message || `${config.entityName} berhasil dihapus`,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                
                                // Reload table
                                self.reloadTable();
                            } else {
                                // Show error message
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message || 'Terjadi kesalahan saat menghapus data'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            // Show error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan saat menghapus data'
                            });
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        },
        
        // Handle edit button click
        handleEdit: function(id) {
            // This will be overridden by the implementation in the view
            console.log(`Edit ${id}`);
        },
        
        // Reload DataTable
        reloadTable: function() {
            $(config.selectors.table).DataTable().ajax.reload();
        },
        
        // Reset form to initial state
        resetForm: function() {
            // This function is intentionally left empty 
            // Because we're using a custom resetKomponenForm function in the view
            console.log('Using custom form reset implementation');
        },
        
        // Custom method for debugging
        debug: function() {
            console.log('Config:', config);
        }
    };
})();
</script>
