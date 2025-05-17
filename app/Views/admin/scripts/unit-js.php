<script>
    /**
     * Implementation example for Unit Management
     */
    $(document).ready(function() {
        // Initialize Unit Manager using the reusable DataEntityManager
        const UnitManager = DataEntityManager;

        UnitManager.init({
            // Entity information
            entityName: 'Unit',
            primaryKey: 'id_unit',

            // Selectors
            selectors: {
                modal: '#addUnitModal',
                form: '#add-unit-form',
                table: '#table-unit',
                importForm: '#import-excel-form',
                importModal: '#importExcelModal',
                importBtn: '#import-btn',
                filterInput: '#filter-input',
                select2Elements: '.select2',
                modalTitle: '.modal-title'
            },

            // API endpoints
            endpoints: {
                save: 'master/unit/save',
                getById: 'master/unit/getById/',
                delete: 'master/unit/delete/',
                dataTable: 'master/unit/get-data-table',
                import: 'master/unit/import'
            },

            // Form field selectors for edit handling
            formFields: {
                skema: '[name="id_skema"]',
                kode: '[name="kode"]',
                nama: '[name="nama"]',
                keterangan: '[name="keterangan"]',
                status: '[name="status"]',
                idUnit: '[name="id_unit"]'
            },

            // Column definitions
            columns: [{
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
                        const statusMap = {
                            'Y': '<span class="badge badge-success">Aktif</span>',
                            'N': '<span class="badge badge-danger">Nonaktif</span>'
                        };
                        return statusMap[data] || '<span class="badge badge-secondary">Tidak Diketahui</span>';
                    }
                }
            ],

            // Column formatters
            renderFormatters: {},

            // Default order
            defaultOrder: [1, 'asc'],

            // Additional options for DataTable
            additionalOptions: {
                responsive: true
            }
        });

        // Make updateFileLabel globally accessible
        window.updateFileLabel = UnitManager.updateFileLabel;


    });
</script>