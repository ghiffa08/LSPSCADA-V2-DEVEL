<?= $this->extend("layouts/admin/layout-admin"); ?>

<!-- Content Section -->
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
                <table id="table-kuk" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 5%">No</th>
                            <th>ID KUK</th>
                            <th>Kode KUK</th>
                            <th>Nama KUK</th>
                            <th>Pertanyaan</th>
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

<?= $this->endSection() ?>
<!-- End Content Section -->

<!-- Modals Section -->
<?= $this->section("modals") ?>

<!-- Save Unit Modal -->
<?= $this->include('admin/partials/modals/save-form-kuk') ?>
<!-- End Save Unit Modal -->

<!-- Import Excel Modal -->
<?= $this->include('admin/partials/modals/import-excel-kuk') ?>
<!-- End Import Excel Modal -->

<?= $this->endSection() ?>
<!-- End Modals Section -->

<!-- Script Section -->
<?= $this->section('js') ?>

<!-- KUK JS -->
<?= $this->include('admin/scripts/kuk-js') ?>
<!-- End KUK JS -->

<?= $this->endSection() ?>
<!-- End Script Section -->