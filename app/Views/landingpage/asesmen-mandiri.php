<?= $this->extend("layouts/landingpage/layout-2"); ?>

<?= $this->section("hero"); ?>

<section id="hero-static" class="hero-static d-flex align-items-center">
    <div class="container d-flex flex-column justify-content-center align-items-center text-center position-relative" data-aos="zoom-out">
        <h2><?= $siteTitle ?></h2>
        <p><?= $siteSubtitle ?></p>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section("content"); ?>

<section id="features" class="features">
    <div class="container" data-aos="fade-up">
        <?php if (isset($dataAPL1) && $dataAPL1['validasi_apl1'] == "Y") { ?>

            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title text-center">Panduan Asesmen Mandiri</h4>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <ul>
                        <li>Baca Pertanyaan di kolom sebelah kiri.</li>
                        <li>Beri tanda centang jika Anda yakin dapat melakukan tugas yang dijelaskan.</li>
                        <li>Isi kolom di sebelah kanan dengan mendaftar bukti yang Anda miliki untuk menunjukan bahawa Anda melakukan tugas-tugas ini.</li>
                    </ul>
                </div>
            </div>

            <?php foreach ($listUnit as $unit) { ?>
                <form action="<?= site_url('/store-asesmen-mandiri') ?>" method="POST">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="id" value="<?= $dataAPL1['id_apl1'] ?>">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title text-center"><?= $unit['nama_unit'] ?></h4>
                        </div>
                    </div>

                    <?php
                    $noElemen = 0;
                    foreach ($listElemen as $elemen) {
                        if ($elemen['id_unit'] == $unit['id_unit']) {
                            $noElemen++;
                    ?>

                            <div class="card mb-3">
                                <div class="card-header">
                                    <?= $noElemen ?>. <?= $elemen['nama_elemen'] ?>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped my-datatables">

                                            <thead>
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

                                                        <tr>
                                                            <td scope="row"><?= $noElemen . '.' . $no ?></td>
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
                                                                <input type="hidden" name="id_apl1_<?= $subelemen['id_subelemen'] ?>" value="<?= $dataAPL1['id_apl1'] ?>">
                                                                <input type="hidden" name="id_skema_<?= $subelemen['id_subelemen'] ?>" value="<?= $subelemen['id_skema'] ?>">
                                                                <input type="hidden" name="id_unit_<?= $subelemen['id_subelemen'] ?>" value="<?= $subelemen['id_unit'] ?>">
                                                                <input type="hidden" name="id_elemen_<?= $subelemen['id_subelemen'] ?>" value="<?= $subelemen['id_elemen'] ?>">
                                                                <input type="hidden" name="id_subelemen_<?= $subelemen['id_subelemen'] ?>" value="<?= $subelemen['id_subelemen'] ?>">
                                                                <select class="form-control select2 <?php if (session('errors.bukti_pendukung_' . $subelemen['id_subelemen'])) : ?>is-invalid<?php endif ?>" name="bukti_pendukung_<?= $subelemen['id_subelemen'] ?>">
                                                                    <option value="">Pilih Bukti Pendukung</option>
                                                                    <option value="Nilai Raport" <?= (old('bukti_pendukung_' . $subelemen['id_subelemen']) == "Nilai Raport" ? "selected" : ""); ?>>Nilai Raport</option>
                                                                    <option value="Sertifikat Praktek Kerja Lapangan (PKL)" <?= (old('bukti_pendukung_' . $subelemen['id_subelemen']) == "Sertifikat Praktek Kerja Lapangan (PKL)" ? "selected" : ""); ?>>Sertifikat Praktek Kerja Lapangan (PKL)</option>
                                                                </select>
                                                                <?php if (session('errors.bukti_pendukung_' . $subelemen['id_subelemen'])) { ?>
                                                                    <div class="invalid-feedback">
                                                                        <?= session('errors.bukti_pendukung_' . $subelemen['id_subelemen']) ?>
                                                                    </div>
                                                                <?php } ?>

                                                            </td>
                                                        </tr>
                                                <?php
                                                    }
                                                }
                                                ?>

                                            </tbody>


                                        </table>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    }
                    ?>
                <?php } ?>

                <div class="card mb-3">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i><span class="pl-2">Upload </span></button>
                    </div>
                </div>
                </form>
            <?php } else { ?>
                <div class="card mb-3">
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
    </div>
</section>

<?= $this->endSection() ?>