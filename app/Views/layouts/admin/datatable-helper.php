<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const csrfHash = 'X-CSRF-TOKEN';
    /**
     * DataTable Helper
     * A reusable JavaScript module to initialize and manage DataTables
     */
    const DataTableHelper = (function() {
        'use strict';

        /**
         * Initialize a DataTable with server-side processing
         * 
         * @param {string} tableId - The ID of the table element
         * @param {string} ajaxUrl - The server endpoint for data
         * @param {Array} columns - Column definitions
         * @param {Object} options - Additional DataTable options
         * @return {Object} - DataTable instance
         */
        function initServerSideTable(tableId, ajaxUrl, columns, options = {}) {
            // Default configuration
            const defaultConfig = {
                processing: true,
                serverSide: true,
                ajax: {
                    url: ajaxUrl,
                    type: 'POST',
                    data: function(d) {
                        // Add CSRF token
                        d[csrfToken] = csrfHash;

                        // Add custom filters if provided in options
                        if (options.filters && typeof options.filters === 'function') {
                            const customFilters = options.filters();
                            Object.assign(d, customFilters);
                        }

                        return d;
                    }
                },
                columns: columns,
                language: {
                    processing: '<i class="fas fa-spin fa-spinner"></i> Memuat data...',
                    searchPlaceholder: 'Cari...',
                    sSearch: '',
                    zeroRecords: 'Tidak ada data yang ditemukan',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                    infoFiltered: '(difilter dari _MAX_ total data)',
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        previous: '<i class="fas fa-angle-left"></i>'
                    }
                }
            };

            // Merge default config with provided options
            const config = {
                ...defaultConfig,
                ...options
            };

            // Initialize DataTable
            const table = $(`#${tableId}`).DataTable(config);

            // Initialize tooltips if Bootstrap tooltip is available
            if ($.fn.tooltip) {
                $('[data-toggle="tooltip"]').tooltip();
            }

            return table;
        }

        /**
         * Reload DataTable
         * 
         * @param {Object} table - DataTable instance
         * @param {boolean} resetPaging - Whether to reset paging
         */
        function reloadTable(table, resetPaging = false) {
            if (table) {
                table.ajax.reload(null, resetPaging);
            }
        }

        /**
         * Add index column to DataTable columns
         * 
         * @param {Array} columns - Existing columns configuration
         * @return {Array} - Updated columns with index column added at the beginning
         */
        function addIndexColumn(columns) {
            return [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                ...columns
            ];
        }

        /**
         * Add action buttons column to DataTable columns
         * 
         * @param {Array} columns - Existing columns configuration
         * @param {Object} options - Button options (edit, delete, view, custom)
         * @return {Array} - Updated columns with action column added at the end
         */
        function addActionColumn(columns, options = {}) {
            return [
                ...columns,
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        let buttons = '';

                        // View button
                        if (options.view) {
                            buttons += `
                            <button class="btn btn-sm btn-info ${options.view.class || 'btn-view'}" 
                                data-id="${row[options.idField || 'id']}" 
                                data-toggle="tooltip" 
                                title="${options.view.title || 'Lihat'}">
                                <i class="fas fa-eye"></i>
                            </button> `;
                        }

                        // Edit button
                        if (options.edit) {
                            buttons += `
                            <button class="btn btn-sm btn-primary ${options.edit.class || 'btn-edit'}" 
                                data-id="${row[options.idField || 'id']}" 
                                data-toggle="tooltip" 
                                title="${options.edit.title || 'Edit'}">
                                <i class="fas fa-pencil-alt"></i>
                            </button> `;
                        }

                        // Delete button
                        if (options.delete) {
                            buttons += `
                            <button class="btn btn-sm btn-danger ${options.delete.class || 'btn-delete'}" 
                                data-id="${row[options.idField || 'id']}" 
                                data-toggle="tooltip" 
                                title="${options.delete.title || 'Hapus'}">
                                <i class="fas fa-trash"></i>
                            </button> `;
                        }

                        // Custom button(s)
                        if (options.custom) {
                            if (Array.isArray(options.custom)) {
                                options.custom.forEach(btn => {
                                    buttons += btn.renderer(row);
                                });
                            } else if (typeof options.custom === 'function') {
                                buttons += options.custom(row);
                            }
                        }

                        return buttons;
                    }
                }
            ];
        }

        // Public API
        return {
            initServerSideTable,
            reloadTable,
            addIndexColumn,
            addActionColumn
        };
    })();

    // Export for CommonJS/RequireJS environments, if needed
    if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
        module.exports = DataTableHelper;
    }
</script>