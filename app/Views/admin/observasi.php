<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Tabel <?= $siteTitle ?></h4>
                <div class="card-header-action">
                    <div class="btn-group">
                        <!-- Primary action button -->
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addKukModal">
                            <i class="fas fa-plus mr-1"></i> Tambah KUK
                        </button>

                        <!-- Import button -->
                        <button class="btn btn-primary" data-toggle="modal" data-target="#importExcelModal">
                            <i class="fas fa-upload"></i> Import Excel
                        </button>

                        <!-- Export button -->
                        <a href="<?= site_url('/export-kuk') ?>" class="btn btn-primary">
                            <i class="fas fa-download mr-1"></i> Export Excel
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="table-observasi" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 5%">No</th>
                            <th>Nama Asesi</th>
                            <th>Nama Asesor</th>
                            <th>Skema Sertifikasi</th>
                            <th>TUK</th>
                            <th>Tanggal Observasi</th>
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
     * Implementation example for Observasi Management
     */
    $(document).ready(function() {
        // Initialize Observasi Manager using the reusable DataEntityManager
        const ObservasiManager = DataEntityManager;

        ObservasiManager.init({
            // Entity information
            entityName: 'Observasi',
            primaryKey: 'id_observasi',

            // Selectors
            selectors: {
                modal: '#saveObservasiModal',
                form: '#add-observasi-form',
                table: '#table-observasi',
                importForm: '#import-excel-form',
                importModal: '#importExcelModal',
                importBtn: '#import-btn',
                filterInput: '#filter-input',
                select2Elements: '.select2',
                modalTitle: '.modal-title'
            },

            // API endpoints
            endpoints: {
                save: 'admin/observasi/save',
                getById: 'admin/observasi/getById/',
                delete: 'admin/observasi/delete/',
                dataTable: 'admin/observasi/get-data-table',
                import: 'admin/observasi/import'
            },

            // Form field selectors for edit handling
            formFields: {
                kode: '[name="kode_skema"]',
                nama: '[name="nama_skema"]',
                jenis: '[name="jenis_skema"]',
                status: '[name="status"]'
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
                    data: null,
                    render: function(data) {
                        const text = `TUK ${data.jenis_tuk} ${data.nama_tuk}`;
                        return `<span>${text.toUpperCase()}</span>`;
                    }
                },
                {
                    data: 'tanggal_observasi',
                    render: function(data) {
                        // Format tanggal jika perlu
                        const date = new Date(data);
                        return date.toLocaleDateString('id-ID', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
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
            },

            // Configure action buttons
            actions: {
                edit: {
                    enabled: true,
                    title: 'Edit',
                    icon: 'fa fa-edit',
                    btnClass: 'btn-info btn-sm mr-1'
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
                        printObservasi(id, rowData);
                    }
                }
            },

            // Custom callbacks
            callbacks: {
                // Optional: Use this if you want to completely customize the action buttons rendering
                // renderActionButtons: function(data, type, row) {
                //     const id = row.id_observasi;
                //     return `
                //         <button type="button" class="btn btn-info btn-sm mr-1" data-id="${id}" data-action="edit" title="Edit">
                //             <i class="fa fa-edit"></i> Edit
                //         </button>
                //         <button type="button" class="btn btn-danger btn-sm mr-1" data-id="${id}" data-action="delete" title="Hapus">
                //             <i class="fa fa-trash"></i> Hapus
                //         </button>
                //         <button type="button" class="btn btn-primary btn-sm" data-id="${id}" data-action="print" title="Print">
                //             <i class="fa fa-print"></i> Print
                //         </button>
                //     `;
                // }
            }
        });

        // Make updateFileLabel globally accessible
        window.updateFileLabel = ObservasiManager.updateFileLabel;

        /**
         * Handle Print Observasi
         * @param {number} id - ID of the observasi to print
         * @param {object} rowData - The data of the selected row
         */
        function printObservasi(id, rowData) {
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
            const printUrl = `${window.location.origin}/pdf/observasi/${id}`;

            // Open print URL in new window
            const printWindow = window.open(printUrl, '_blank');

            if (printWindow) {
                // Close loading notification once window is opened
                Swal.close();

                // Optional: Add event listener to detect if print window is closed
                const checkWindowClosed = setInterval(function() {
                    if (printWindow.closed) {
                        clearInterval(checkWindowClosed);
                        // Optional: Do something after print window is closed
                    }
                }, 1000);
            } else {
                // If popup was blocked
                Swal.fire({
                    icon: 'error',
                    title: 'Popup Diblokir',
                    text: 'Mohon izinkan popup untuk mencetak dokumen observasi.'
                });
            }
        }
    });
</script>
<?= $this->endSection(); ?>