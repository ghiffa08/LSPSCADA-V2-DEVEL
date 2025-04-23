<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data FR.APL.01. Yang Menunggu Untuk di Validasi</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-2" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID APL 2</th>
                                <th>ID APL 1</th>
                                <th>Kode Jawaban</th>
                                <th>Nama Asesi</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = null;
                            foreach ($listAPL2Pending as $value) {
                                $no++;
                            ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><?= $value['id_apl2'] ?></td>
                                    <td><?= $value['id_apl1'] ?></td>
                                    <td><?= $value['kode_jawaban_apl2'] ?></td>
                                    <td><?= $value['nama_siswa'] ?></td>
                                    <td>
                                        <div class="badge badge-<?= $value['validasi_apl2'] == "pending" ? "warning" : "danger" ?>"><?= $value['validasi_apl2'] ?></div>
                                    </td>
                                    <td>
                                        <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                            <button type="button" class="btn btn-icon btn-info" data-toggle="modal" data-target="#detail-apl2-<?= $value['id_apl2']; ?>"><i class="fas fa-eye"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
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
<?php foreach ($listAPL2Pending as $key => $value) { ?>
    <form id="setting-form" action="<?= site_url('/kelola_apl2/validasi-store') ?>" method="POST">
        <div class="modal fade" id="detail-apl2-<?= $value['id_apl2'] ?>" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Validasi FR.APL.01 | <?= $value['nama_siswa'] ?></h5>
                        <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= $value['id_apl2'] ?>">
                        <input type="hidden" name="id_apl1" value="<?= $value['id_apl1'] ?>">
                        <input type="hidden" name="id_asesor" value="<?= user()->id ?>">
                        <input type="hidden" name="name" value="<?= $value['nama_siswa'] ?>">
                        <input type="hidden" name="email" value="<?= $value['email'] ?>">
                        <div class="row">
                            <div class="col-12">
                                <div class="embed-responsive embed-responsive-4by3 border border-secondary">
                                    <iframe class="embed-responsive-item" src="<?= base_url('/kelola_apl2/pdf-' . $value['id_apl1']) ?>"></iframe>
                                </div>
                            </div>
                        </div>
                        <div class="container mt-3">
                            <div class="selectgroup w-100">
                                <label class="selectgroup-item">
                                    <input type="radio" name="validasi_apl2" value="validated" class="selectgroup-input" <?= ($value['validasi_apl2'] === "validated") ? 'checked' : "" ?>>
                                    <span class="selectgroup-button">Valid</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="validasi_apl2" value="unvalid" class="selectgroup-input" <?= ($value['validasi_apl2'] === "unvalid") ? 'checked' : "" ?>>
                                    <span class="selectgroup-button">Tidak Valid</span>
                                </label>
                            </div>
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
<?php } ?>
<?= $this->endSection() ?>