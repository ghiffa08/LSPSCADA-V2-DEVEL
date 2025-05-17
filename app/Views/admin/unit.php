<?= $this->extend("layouts/admin/layout-admin"); ?>

<!-- Content Section -->
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Tabel <?= $siteTitle ?></h4>
                <div class="card-header-action">
                    <div class="btn-group">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addUnitModal">
                            <i class="fas fa-plus"></i> Tambah Unit
                        </button>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#importExcelModal">
                            <i class="fas fa-upload"></i> Import Excel
                        </button>
                        <button href="<?= base_url('export-unit') ?>" class="btn btn-primary">
                            <i class="fas fa-download"></i> Export Excel
                        </button>
                    </div>

                </div>
            </div>
            <div class="card-body">
                <table id="table-unit" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Skema</th>
                            <th>ID Unit</th>
                            <th>Kode Unit</th>
                            <th>Nama Unit</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<!-- End Content Section -->

<!-- Modals Section -->
<?= $this->section("modals") ?>

<!-- Save Unit Modal -->
<?= $this->include('admin/partials/modals/save-form-unit') ?>
<!-- End Save Unit Modal -->

<!-- Import Excel Modal -->
<?= $this->include('admin/partials/modals/import-excel-unit') ?>
<!-- End Import Excel Modal -->

<?= $this->endSection() ?>
<!-- End Modals Section -->

<!-- Script Section -->
<?= $this->section('js') ?>

<!-- UNIT JS -->
<?= $this->include('admin/scripts/DataEntityManager') ?>
<?= $this->include('admin/scripts/unit-js') ?>
<!-- End UNIT JS -->

<?= $this->endSection() ?>
<!-- End Script Section -->