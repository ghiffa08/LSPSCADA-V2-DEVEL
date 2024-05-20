<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>
<?php if (isset($dataAPL1) && $dataAPL1['validasi_apl1'] == "Y") { ?>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="hero bg-primary text-white">
                <div class="hero-inner">
                    <h2>Asesmen Mandiri - <?= $dataAPL1['nama_siswa'] ?></h2>
                    <p class="lead"><?= $dataAPL1['nama_skema'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Panduan Asesmen Mandiri
        </div>
        <div class="card-body">
            <ul>
                <li>Baca Pertanyaan di kolom sebelah kiri.</li>
                <li>Beri tanda centang jika Anda yakin dapat melakukan tugas yang dijelaskan.</li>
                <li>Isi kolom di sebelah kanan dengan mendaftar bukti yang Anda miliki untuk menunjukan bahawa Anda melakukan tugas-tugas ini.</li>
            </ul>
        </div>
    </div>

    <?php foreach ($listUnit as $unit) { ?>
        <form action="<?= site_url('/store-asesmen-mandiri') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <div class="card">
                <div class="card-header">
                    <h4 class="mx-auto"> <?= $unit['nama_unit'] ?></h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <?php
                            $noElemen = 0;
                            foreach ($listElemen as $elemen) {
                                if ($elemen['id_unit'] == $unit['id_unit']) {
                                    $noElemen++;
                            ?>
                                    <thead>
                                        <tr>
                                            <th colspan="12" class="text-center"><?= $noElemen ?>. <?= $elemen['nama_elemen'] ?></th>
                                        </tr>
                                        <tr>
                                        <tr>
                                            <th scope="col">NO</th>
                                            <th scope="col">SUB KOMPETENSI</th>
                                            <th scope="col">K</th>
                                            <th scope="col">BK</th>
                                            <th scope="col">BUKTI PENDUKUNG</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 0;
                                        foreach ($listSubelemen as $subelemen) {
                                            if ($subelemen['id_elemen'] == $elemen['id_elemen'] && $unit['id_unit']) {
                                                $no++;
                                        ?>
                                                <input type="hidden" name="id_skema_<?= $subelemen['id_subelemen'] ?>" value="<?= $subelemen['id_skema'] ?>">
                                                <input type="hidden" name="id_unit_<?= $subelemen['id_subelemen'] ?>" value="<?= $subelemen['id_unit'] ?>">
                                                <input type="hidden" name="id_elemen_<?= $subelemen['id_subelemen'] ?>" value="<?= $subelemen['id_elemen'] ?>">
                                                <input type="hidden" name="id_subelemen_<?= $subelemen['id_subelemen'] ?>" value="<?= $subelemen['id_subelemen'] ?>">
                                                <input type="hidden" name="id_user_<?= $subelemen['id_subelemen'] ?>" value="<?= $dataAPL1['id_siswa'] ?>">
                                                <tr>
                                                    <th scope="row"><?= $noElemen . '.' . $no ?></th>
                                                    <td>
                                                        <?= $subelemen['pertanyaan'] ?>
                                                        <?php if (session('errors.bk_' . $subelemen['id_subelemen'])) { ?>
                                                            <br>
                                                            <small class="text-danger"> <?= session('errors.bk_' . $subelemen['id_subelemen']) ?></small>
                                                        <?php } ?>
                                                    </td>
                                                    <td><input type="radio" class="" name="bk_<?= $subelemen['id_subelemen'] ?>" id="" value="K" <?= (old('bk_' . $subelemen['id_subelemen']) == "K") ? 'checked' : '' ?>></td>
                                                    <td><input type="radio" class="" name="bk_<?= $subelemen['id_subelemen'] ?>" id="" value="BK" <?= (old('bk_' . $subelemen['id_subelemen']) == "BK") ? 'checked' : '' ?>></td>
                                                    <td>
                                                        <div class="">
                                                            <input type="file" class=" <?php if (session('errors.bukti_pendukung_' . $subelemen['id_subelemen'])) : ?>is-invalid<?php endif ?>" id="customFile" name="bukti_pendukung_<?= $subelemen['id_subelemen'] ?>">
                                                            <?php if (session('errors.bukti_pendukung_' . $subelemen['id_subelemen'])) { ?>
                                                                <div class="invalid-feedback">
                                                                    <?= session('errors.bukti_pendukung_' . $subelemen['id_subelemen']) ?>
                                                                </div>
                                                            <?php } ?>
                                                            <!-- <label class="custom-file-label" for="customFile">Choose file</label> -->
                                                        </div>
                                                    </td>
                                                </tr>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </tbody>
                            <?php
                                }
                            }
                            ?>

                        </table>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i><span class="pl-2">Upload </span></button>
            </div>
        </div>
        </form>
    <?php } else { ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state" data-height="400">
                    <div class="empty-state-icon bg-danger">
                        <i class="fas fa-times"></i>
                    </div>
                    <h2>FR. APL 1 Anda belum divalidasi</h2>
                    <p class="lead">
                        Untuk melanjutkan, harap tunggu FR.APL 1 Anda divalidasi oleh Admin.
                    </p>

                    <!-- <a href="<?= site_url('/skema-siswa') ?>" class="btn btn-primary mt-4">Tambah Skema</a> -->
                </div>
            </div>
        </div>
    <?php } ?>
    <?= $this->endSection() ?>