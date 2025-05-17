<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Unit</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#saveAsesmenModal">
                        Tambah Asesmen
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-asesmen" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Skema</th>
                                <th>Tempat</th>
                                <th>Tanggal</th>
                                <th>Tujuan</th>
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

<form id="save-asesmen-form" action="<?= site_url('/asesmen/save'); ?>" method="POST">
    <div class="modal fade" id="saveAsesmenModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Asesmen</h5>
                    <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <p class="text-muted">Masukan ID Asesmen, ID Unit, ID ELemen, Kode Subelemen, Pertanyaan.</p>
                    <div class="form-group">
                        <label class="form-label" class="form-label">Asesmen<span class="text-danger">*</span></label>
                        <select class="form-control select2" name="id_skema">
                            <option value="">Pilih Asesmen</option>
                            <?php

                            foreach ($listSkema as $row) {
                                echo '<option value="' . $row['id_skema'] . '">' . $row['nama_skema'] . '</option>';
                            }

                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" class="form-label">Jadwal Uji Kompetensi<span class="text-danger">*</span></label>
                        <select class="form-control select2 <?php if (session('errors.jadwal_sertifikasi')) : ?>is-invalid<?php endif ?>" name="id_tanggal">
                            <option value="">Jadwal Uji Kompetensi</option>
                            <?php

                            foreach ($listSettanggal as $row) {

                                old('tanggal_sertifikasi') == $row['id_tanggal'] ? $pilih = 'selected' : $pilih = null;

                                echo '<option ' . $pilih . ' value="' . $row['id_tanggal'] . '">' . $row['tanggal'] . ' - ' . $row['keterangan'] . '</option>';
                            }

                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" class="form-label">Tempat Uji Kompetensi<span class="text-danger">*</span></label>
                        <select class="form-control select2 <?php if (session('errors.tuk')) : ?>is-invalid<?php endif ?>" name="id_tuk">
                            <option value="">Tempat Uji Kompetensi</option>
                            <?php foreach ($listTUK as $row) : ?>
                                <option value="<?= $row['id_tuk'] ?>">TUK <?= $row['jenis_tuk'] ?> - <?= $row['nama_tuk'] ?></option>;
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" class="form-label">Tujuan Sertifikasi<span class="text-danger">*</span></label>
                        <input type="text" name="tujuan" id="tujuan" class="form-control">
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<!-- Script Section -->
<?= $this->section('js') ?>

<!-- Asesmen JS -->
<?= $this->include('admin/scripts/DataEntityManager') ?>
<script>
    /**
     * Implementation example for Asesmen Management
     */
    $(document).ready(function() {
        // Initialize Asesmen Manager using the reusable DataEntityManager
        const AsesmenManager = DataEntityManager;

        AsesmenManager.init({
            // Entity information
            entityName: 'Asesmen',
            primaryKey: 'id_asesmen',

            // Selectors
            selectors: {
                modal: '#saveAsesmenModal',
                form: '#save-asesmen-form',
                table: '#table-asesmen',
                importForm: '#import-excel-form',
                importModal: '#importExcelModal',
                importBtn: '#import-btn',
                filterInput: '#filter-input',
                select2Elements: '.select2',
                modalTitle: '.modal-title'
            },

            // API endpoints
            endpoints: {
                save: 'asesmen/save',
                getById: 'asesmen/getById/',
                delete: 'asesmen/delete/',
                dataTable: 'asesmen/get-data-table',
                import: 'asesmen/import'
            },

            // Form field selectors for edit handling
            formFields: {
                skema: '[name="id_skema"]',
                tuk: '[name="id_tuk"]',
                tanggal: '[name="id_tanggal]',
                tujuan: '[name="tujuan"]',
                idAsesmen: '[name="id_asesmen"]'
            },

            // Column definitions
            columns: [{
                    data: 'id_asesmen'
                },
                {
                    data: 'nama_skema'
                },
                {
                    data: 'nama_tuk'
                },
                {
                    data: 'tanggal_asesmen'
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
        window.updateFileLabel = AsesmenManager.updateFileLabel;
    });
</script>
<!-- End Asesmen JS -->
<?= $this->endSection() ?>
<!-- End Script Section -->