<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Tabel <?= $siteTitle ?? 'page' ?></h4>
                <div class="card-header-action">
                    <div class="btn-group">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addElemenModal">
                            <i class="fas fa-plus"></i> Tambah Elemen
                        </button>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#importExcelModal">
                            <i class="fas fa-upload"></i> Import Excel
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="table-elemen" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Skema</th>
                            <th>ID Unit</th>
                            <th>ID Elemen</th>
                            <th>Kode Elemen</th>
                            <th>Nama Elemen</th>
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

<!-- Modals Section -->
<?= $this->section("modals") ?>

<!-- Add Element Modal -->
<?= $this->include('admin/partials/modals/save-form-elemen') ?>
<!-- End Element Modal -->

<!-- Import Excel Modal -->
<?= $this->include('admin/partials/modals/import-excel-elemen') ?>
<!-- End Import Excel Modal -->

<?= $this->endSection() ?>
<!-- End Modals Section -->

<!-- Script Section -->
<?= $this->section('js') ?>

<!-- ELEMEN JS -->
<?= $this->include('admin/scripts/elemen-js') ?>
<!-- End ELEMEN JS -->

<?= $this->endSection() ?>
<!-- End Script Section -->