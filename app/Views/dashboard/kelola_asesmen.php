<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Unit</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addAsesmenModal">
                        Tambah Asesmen
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-2" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Asesmen</th>
                                <th>Skema</th>
                                <th>Jenis Sertifikasi</th>
                                <th>Tempat</th>
                                <th>Tanggal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = null;
                            foreach ($listAsesmen as $value) {
                                $no++;
                            ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><?= $value['id_asesmen'] ?></td>
                                    <td><?= $value['nama_skema'] ?></td>
                                    <td><?= $value['jenis_skema'] ?></td>
                                    <td><?= $value['nama_tuk'] ?></td>
                                    <td><?= $value['tanggal'] ?></td>
                                    <td>
                                        <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                            <button type="button" class="btn btn-icon btn-info" data-toggle="modal" data-target="#editAsesmenModal-<?= $value['id_asesmen']; ?>"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-icon btn-danger" data-toggle="modal" data-target="#deleteAsesmenModal-<?= $value['id_asesmen']; ?>"><i class="fas fa-trash"></i></button>
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

<form id="setting-form" action="<?= site_url('/asesmen/store'); ?>" method="POST">
    <div class="modal fade" id="addAsesmenModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
                    <p class="text-muted">Masukan ID Skema, ID Unit, ID ELemen, Kode Subelemen, Pertanyaan.</p>
                    <div class="form-group">
                        <label class="form-label" class="form-label">Skema<span class="text-danger">*</span></label>
                        <select class="form-control select2 <?php if (session('errors.skema_sertifikasi')) : ?>is-invalid<?php endif ?>" name="skema_sertifikasi">
                            <option value="">Pilih Skema</option>
                            <?php

                            foreach ($listSkema as $row) {

                                old('skema_sertifikasi') == $row['id_skema'] ? $pilih = 'selected' : $pilih = null;

                                echo '<option ' . $pilih . ' value="' . $row['id_skema'] . '">' . $row['nama_skema'] . '</option>';
                            }

                            ?>
                        </select>
                        <?php if (session('errors.skema_sertifikasi')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.skema_sertifikasi') ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label" class="form-label">Jadwal Uji Kompetensi<span class="text-danger">*</span></label>
                        <select class="form-control select2 <?php if (session('errors.jadwal_sertifikasi')) : ?>is-invalid<?php endif ?>" name="jadwal_sertifikasi">
                            <option value="">Jadwal Uji Kompetensi</option>
                            <?php

                            foreach ($listSettanggal as $row) {

                                old('tanggal_sertifikasi') == $row['id_tanggal'] ? $pilih = 'selected' : $pilih = null;

                                echo '<option ' . $pilih . ' value="' . $row['id_tanggal'] . '">' . $row['tanggal'] . ' - ' . $row['keterangan'] . '</option>';
                            }

                            ?>
                        </select>
                        <?php if (session('errors.jadwal_sertifikasi')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.jadwal_sertifikasi') ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label" class="form-label">Tempat Uji Kompetensi<span class="text-danger">*</span></label>
                        <select class="form-control select2 <?php if (session('errors.tuk')) : ?>is-invalid<?php endif ?>" name="tuk">
                            <option value="">Tempat Uji Kompetensi</option>
                            <?php

                            foreach ($listTUK as $row) {

                                old('id_tanggal') == $row['id_tuk'] ? $pilih = 'selected' : $pilih = null;

                                echo '<option ' . $pilih . ' value="' . $row['id_tuk'] . '">' . $row['nama_tuk'] . '</option>';
                            }

                            ?>
                        </select>
                        <?php if (session('errors.tuk')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.tuk') ?>
                            </div>
                        <?php } ?>
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

<?php foreach ($listAsesmen as $key => $value) { ?>
    <form id="setting-form" action="<?= site_url('/asesmen/update'); ?>" method="POST">
        <div class="modal fade" id="editAsesmenModal-<?= $value['id_asesmen'] ?>" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Asesmen</h5>
                        <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?= csrf_field(); ?>
                        <p class="text-muted">Masukan ID Skema, ID Unit, ID ELemen, Kode Subelemen, Pertanyaan.</p>
                        <input type="hidden" name="edit_id" value="<?= $value['id_asesmen'] ?>">
                        <div class="form-group">
                            <label class="form-label" class="form-label">Skema<span class="text-danger">*</span></label>
                            <select class="form-control select2 <?php if (session('errors.edit_skema_sertifikasi')) : ?>is-invalid<?php endif ?>" name="edit_skema_sertifikasi">
                                <option value="">Pilih Skema</option>
                                <?php

                                foreach ($listSkema as $row) {

                                    $value['id_skema'] == $row['id_skema'] ? $pilih = 'selected' : $pilih = null;

                                    echo '<option ' . $pilih . ' value="' . $row['id_skema'] . '">' . $row['nama_skema'] . '</option>';
                                }

                                ?>
                            </select>
                            <?php if (session('errors.edit_skema_sertifikasi')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_skema_sertifikasi') ?>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" class="form-label">Jadwal Uji Kompetensi<span class="text-danger">*</span></label>
                            <select class="form-control select2 <?php if (session('errors.edit_jadwal_sertifikasi')) : ?>is-invalid<?php endif ?>" name="edit_jadwal_sertifikasi">
                                <option value="">Jadwal Uji Kompetensi</option>
                                <?php

                                foreach ($listSettanggal as $row) {

                                    $value['id_tanggal'] == $row['id_tanggal'] ? $pilih = 'selected' : $pilih = null;

                                    echo '<option ' . $pilih . ' value="' . $row['id_tanggal'] . '">' . $row['tanggal'] . ' - ' . $row['keterangan'] . '</option>';
                                }

                                ?>
                            </select>
                            <?php if (session('errors.edit_jadwal_sertifikasi')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_jadwal_sertifikasi') ?>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" class="form-label">Tempat Uji Kompetensi<span class="text-danger">*</span></label>
                            <select class="form-control select2 <?php if (session('errors.edit_tuk')) : ?>is-invalid<?php endif ?>" name="edit_tuk">
                                <option value="">Tempat Uji Kompetensi</option>
                                <?php

                                foreach ($listTUK as $row) {

                                    $value['id_tuk'] == $row['id_tuk'] ? $pilih = 'selected' : $pilih = null;

                                    echo '<option ' . $pilih . ' value="' . $row['id_tuk'] . '">' . $row['nama_tuk'] . '</option>';
                                }

                                ?>
                            </select>
                            <?php if (session('errors.edit_tuk')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.edit_tuk') ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php } ?>

<?php foreach ($listAsesmen as $row => $value) { ?>
    <form action="<?= site_url('/asesmen/delete'); ?>" method="post">
        <div class="modal fade" id="deleteAsesmenModal-<?= $value['id_asesmen']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Skema</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <h5>Apakah anda yakin akan menghapus <span class="text-danger font-weight-bold">"<?= $value['id_asesmen']; ?>"</span>?</h5>
                        <input type="hidden" name="id" value="<?= $value['id_asesmen']; ?>">

                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="submit" class="btn btn-danger btn-lg btn-block">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php } ?>
<?= $this->endSection() ?>