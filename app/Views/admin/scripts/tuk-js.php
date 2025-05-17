<script>
    /**
     * Implementation example for Skema Management
     */
    $(document).ready(function() {
        // Initialize Skema Manager using the reusable DataEntityManager
        const TUKManager = DataEntityManager;

        TUKManager.init({
            // Entity information
            entityName: 'TUK',
            primaryKey: 'id_tuk',

            // Selectors
            selectors: {
                modal: '#saveTukModal',
                form: '#add-tuk-form',
                table: '#table-tuk',
                importForm: '#import-excel-form',
                importModal: '#importExcelModal',
                importBtn: '#import-btn',
                filterInput: '#filter-input',
                select2Elements: '.select2',
                modalTitle: '.modal-title'
            },

            // API endpoints
            endpoints: {
                save: 'master/tuk/save',
                getById: 'master/tuk/getById/',
                delete: 'master/tuk/delete/',
                dataTable: 'master/tuk/get-data-table',
                import: 'master/tuk/import'
            },

            // Form field selectors for edit handling
            formFields: {
                nama: '[name="nama_tuk"]',
                jenis: '[name="jenis_tuk"]',
                idtuk: '[name="id_tuk"]'
            },

            // Column definitions
            columns: [{
                    data: 'nama_tuk'
                },
                {
                    data: 'jenis_tuk'
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
        window.updateFileLabel = TUKManager.updateFileLabel;
    });
</script>