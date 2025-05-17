<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Tabel <?= $siteTitle ?></h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#saveTanggalModal">
                        Set Tanggal Asesi
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-tanggal" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal & Waktu</th>
                                <th>Keterangan</th>
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

<!-- Modals Section -->
<?= $this->section("modals") ?>

<!-- Add-Group-Modal -->
<form id="save-tanggal-form" action="<?= site_url('/master/tanggal/save'); ?>" method="POST">
    <div class="modal fade" id="saveTanggalModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Set Tanggal</h5>
                    <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <p class="text-muted">General settings such as, site title, site description, address and so on.</p>
                    <input type="hidden" name="id_tanggal">
                    <div class="form-group">
                        <label class="form-label">Tanggal Asesi</label>
                        <input type="datetime-local" name="tanggal" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control <?php if (session('errors.keterangan')) : ?>is-invalid<?php endif ?>" name="keterangan"></textarea>
                        <?php if (session('errors.keterangan')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.keterangan') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="selectgroup w-100">
                            <label class="selectgroup-item">
                                <input type="radio" name="status" class="selectgroup-input" value="Y" checked="">
                                <span class="selectgroup-button">Y</span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="status" class="selectgroup-input" value="N">
                                <span class="selectgroup-button">N</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="reset" class="btn btn-secondary">Clear</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>

            </div>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<!-- Script Section -->
<?= $this->section('js') ?>

<!-- Tanggal JS -->
<?= $this->include('admin/scripts/DataEntityManager') ?>
<script>
    /**
     * Implementation example for Set Tanggal Management
     */
    $(document).ready(function() {
        // Initialize Set Tanggal Manager using the reusable DataEntityManager
        const SetTanggalManager = DataEntityManager;

        SetTanggalManager.init({
            // Entity information
            entityName: 'SetTanggal',
            primaryKey: 'id_tanggal',

            // Selectors
            selectors: {
                modal: '#saveTanggalModal',
                form: '#save-tanggal-form',
                table: '#table-tanggal',
                importForm: '#import-excel-form',
                importModal: '#importExcelModal',
                importBtn: '#import-btn',
                filterInput: '#filter-input',
                select2Elements: '.select2',
                modalTitle: '.modal-title'
            },

            // API endpoints
            endpoints: {
                save: 'master/tanggal/save',
                getById: 'master/tanggal/getById/',
                delete: 'master/tanggal/delete/',
                dataTable: 'master/tanggal/get-data-table',
                import: 'master/tanggal/import'
            },

            // Form field selectors for edit handling
            formFields: {
                tanggal: '[name="tanggal"]',
                keterangan: '[name="keterangan"]',
                status: '[name="status"]',
                idTanggal: '[name="id_tanggal"]'
            },

            // Column definitions
            columns: [{
                    data: 'tanggal'
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
            renderFormatters: {

            },

            // Default order
            defaultOrder: [1, 'asc'],

            // Additional options for DataTable
            additionalOptions: {
                responsive: true
            }
        });

        // Make updateFileLabel globally accessible
        window.updateFileLabel = SetTanggalManager.updateFileLabel;
    });
</script>
<!-- End Asesmen JS -->
<?= $this->endSection() ?>
<!-- End Script Section -->