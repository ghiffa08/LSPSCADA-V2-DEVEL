<?= $this->extend("layouts/admin/layout-admin"); ?>

<!-- Content Section -->
<?= $this->section("content"); ?>

<h2 class="section-title">Manajemen Skema</h2>
<p class="section-lead">Kelola semua data skema sertifikasi pada halaman ini.</p>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Skema</h4>
                <div class="card-header-action">
                    <div class="btn-group">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#saveSkemaModal">
                            <i class="fas fa-plus"></i> Tambah Skema
                        </button>

                        <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#importExcelModal">
                            <i class="fas fa-upload"></i> Import Excel
                        </button>

                        <button href="<?= base_url('export-skema') ?>" class="btn btn-primary">
                            <i class="fas fa-download"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-skema" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Skema</th>
                                <th>Kode Skema</th>
                                <th>Nama Skema</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<!-- End Content Section -->

<!-- Modals Section -->
<?= $this->section("modals") ?>

<!-- Save Skema Modal -->
<?= $this->include('admin/partials/modals/save-form-skema')  ?>
<!-- End Skema Modal -->

<!-- Import Excel Modal -->
<?= $this->include('admin/partials/modals/import-excel-skema') ?>
<!-- End Import Excel Modal -->

<?= $this->endSection() ?>
<!-- End Modals Section -->

<!-- Script Section -->
<?= $this->section('js') ?>

<!-- SKEMA JS -->
<?= $this->include('admin/scripts/DataEntityManager') ?>
<script>
    /**
     * Implementation example for Skema Management
     */
    $(document).ready(function() {
        // Initialize Skema Manager using the reusable DataEntityManager
        const SkemaManager = DataEntityManager;

        SkemaManager.init({
            // Entity information
            entityName: 'Skema',
            primaryKey: 'id_skema',

            // Selectors
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

            // API endpoints
            endpoints: {
                save: 'master/skema/save',
                getById: 'master/skema/getById/',
                delete: 'master/skema/delete/',
                dataTable: 'master/skema/get-data-table',
                import: 'master/skema/import'
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
                    data: 'id_skema'
                },
                {
                    data: 'kode_skema'
                },
                {
                    data: 'nama_skema'
                },
                {
                    data: 'jenis_skema'
                },
                {
                    data: 'status',
                    className: 'text-center'
                }
            ],

            // Column formatters
            renderFormatters: {
                'jenis_skema': function(data) {
                    const jenisMap = {
                        'KKNI': '<span class="badge badge-primary">KKNI</span>',
                        'Okupasi': '<span class="badge badge-info">Okupasi</span>',
                        'Klaster': '<span class="badge badge-warning">Klaster</span>'
                    };
                    return jenisMap[data] || data;
                },
                'status': function(data) {
                    const statusMap = {
                        'Y': '<span class="badge badge-success">Aktif</span>',
                        'N': '<span class="badge badge-danger">Nonaktif</span>'
                    };
                    return statusMap[data] || '<span class="badge badge-secondary">Tidak Diketahui</span>';
                }
            },

            // Default order
            defaultOrder: [1, 'asc'],

            // Additional options for DataTable
            additionalOptions: {
                responsive: true
            }
        });

        // Make updateFileLabel globally accessible
        window.updateFileLabel = SkemaManager.updateFileLabel;
    });
</script>
<!-- End SKEMA JS -->
<?= $this->endSection() ?>
<!-- End Script Section -->