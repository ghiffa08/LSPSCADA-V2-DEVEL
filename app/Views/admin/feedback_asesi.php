<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Tabel <?= $siteTitle ?></h4>
                <div class="card-header-action">
                    <div class="btn-group">
                        <a href="<?= site_url('/asesor/feedback/create') ?>" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i> Tambah Umpan Balik
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="table-feedback-asesi" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 5%">No</th>
                            <th>Nama Asesi</th>
                            <th>Nama Asesor</th>
                            <th>Skema Sertifikasi</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th style="width: 10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Table data will be loaded dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>


<?= $this->section('js') ?>
<?= $this->include('admin/scripts/DataEntityManager') ?>
<script>
    /**
     * Implementation for Feedback Asesi Management
     */
    $(document).ready(function() {
        // Initialize Feedback Manager using the reusable DataEntityManager
        const FeedbackManager = DataEntityManager;

        FeedbackManager.init({
            // Entity information
            entityName: 'Feedback Asesi',
            primaryKey: 'id_feedback',

            // Selectors
            selectors: {
                modal: '#saveFeedbackModal',
                form: '#add-feedback-form',
                table: '#table-feedback-asesi',
                importForm: '#import-excel-form',
                importModal: '#importExcelModal',
                importBtn: '#import-btn',
                filterInput: '#filter-input',
                select2Elements: '.select2',
                modalTitle: '.modal-title'
            },

            // API endpoints
            endpoints: {
                save: 'api/feedback-asesi/save',
                getById: 'api/feedback-asesi/getById/',
                delete: 'api/feedback-asesi/delete/',
                dataTable: 'api/feedback-asesi/get-data-table',
                import: 'api/feedback-asesi/import'
            },

            // Column definitions
            columns: [{
                    data: 'nama_asesi',
                    render: function(data) {
                        return `<span>${data.toUpperCase()}</span>`;
                    }
                },
                {
                    data: 'nama_asesor',
                    render: function(data) {
                        return `<span>${data.toUpperCase()}</span>`;
                    }
                },
                {
                    data: 'nama_skema'
                },
                {
                    data: 'tanggal_mulai',
                    render: function(data) {
                        // Format tanggal
                        const date = new Date(data);
                        return date.toLocaleDateString('id-ID', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                    }
                },
                {
                    data: 'tanggal_selesai',
                    render: function(data) {
                        // Format tanggal
                        const date = new Date(data);
                        return date.toLocaleDateString('id-ID', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                    }
                }
            ],

            // Default order
            defaultOrder: [1, 'asc'],

            // Additional options for DataTable
            additionalOptions: {
                responsive: true
            },

            // Configure action buttons
            actions: {
                edit: {
                    enabled: true,
                    title: 'Edit',
                    icon: 'fa fa-edit',
                    btnClass: 'btn-info btn-sm mr-1',
                    callback: function(id, rowData) {
                        window.location.href = `${window.location.origin}/asesor/feedback/edit/${id}`;
                    }
                },
                delete: {
                    enabled: true,
                    title: 'Hapus',
                    icon: 'fa fa-trash',
                    btnClass: 'btn-danger btn-sm mr-1'
                },
                // Add custom print button
                print: {
                    enabled: true,
                    title: 'Print',
                    icon: 'fa fa-print',
                    btnClass: 'btn-primary btn-sm',
                    callback: function(id, rowData) {
                        // Print action implementation
                        printFeedback(id, rowData);
                    }
                }
            }
        });

        /**
         * Handle Print Feedback
         * @param {number} id - ID of the feedback to print
         * @param {object} rowData - The data of the selected row
         */
        function printFeedback(id, rowData) {
            // Show loading notification
            Swal.fire({
                title: 'Mempersiapkan Dokumen',
                text: 'Mohon tunggu...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Generate print URL
            const printUrl = `${window.location.origin}/pdf/feedback/${id}`;

            // Open print URL in new window
            const printWindow = window.open(printUrl, '_blank');

            if (printWindow) {
                // Close loading notification once window is opened
                Swal.close();
            } else {
                // If popup was blocked
                Swal.fire({
                    icon: 'error',
                    title: 'Popup Diblokir',
                    text: 'Mohon izinkan popup untuk mencetak dokumen umpan balik.'
                });
            }
        }
    });
</script>
<?= $this->endSection(); ?>
