<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Tempat Uji Kompetensi</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#saveTukModal">
                        Tambah TUK
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-tuk" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama TUK</th>
                                <th>Jenis TUK</th>
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
<?= $this->include('admin/partials/modals/save-form-tuk') ?>
<!-- End Add-Group-Modal -->

<?= $this->endSection() ?>

<!-- Script Section -->
<?= $this->section('js') ?>

<!-- SKEMA JS -->
<?= $this->include('admin/scripts/DataEntityManager') ?>
<?= $this->include('admin/scripts/tuk-js') ?>

<!-- End SKEMA JS -->
<?= $this->endSection() ?>
<!-- End Script Section -->