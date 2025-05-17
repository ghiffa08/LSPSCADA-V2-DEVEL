<?= $this->extend("layouts/landingpage/layout-2") ?>
<?= $this->section("content") ?>

<section id="features" class="features">
    <form id="upload-form" action="<?= site_url('/store-asesmen-mandiri') ?>" method="POST">
        <?= csrf_field(); ?>
        <input type="hidden" name="id" value="<?= $dataAPL1['id_apl1'] ?>">
        <?php if ($dataAPL2 == null) { ?>
            <?php if (isset($dataAPL1) && $dataAPL1['validasi_apl1'] == "validated") { ?>
                <div class="row">
                    <div class="col-12 col-md-4 order-2">
                        <div class="card card-primary ">
                            <div class="card-body">
                                <ul class="nav nav-tabs d-flex justify-content-around" data-intro='Ini adalah Jumlah Pertanyaan Yang Harus Anda Isi' data-step="1">
                                    <?php
                                    $step = 0;
                                    foreach ($listSubelemen as $subelemen) {
                                        $step++;
                                        $formattedStep = sprintf("%02d", $step);
                                        $endStep = count($listSubelemen) + 1;
                                    ?>
                                        <!-- btn btn-outline-dark -->
                                        <li class="nav-item ">
                                            <button type="button" class=" btn <?= (session('errors.bk_' . $subelemen['id_subelemen']) || session('errors.bukti_pendukung_' . $subelemen['id_subelemen'])) ? 'btn-danger' : 'btn-outline-primary'; ?> mx-auto m-2 w-100 <?= ($step === 1) ? 'active show' : ''; ?> " id="nextCardAsesmen<?= $step ?>" onclick="showCardAsesmen(<?= $step ?>,<?= $endStep ?>)" <?= ($step === 2) ? "data-intro='Klik Angka Untuk Pergi Ke Soal Selanjutnya' data-step='2'" : ''; ?>>
                                                <?= $formattedStep ?>
                                            </button>
                                        </li><!-- End Tab 1 Nav -->
                                    <?php } ?>
                                </ul>
                            </div>
                            <div class="card-footer">
                                <a class="card-link" id="nextCardAsesmen<?= $endStep ?>" onclick="showCardAsesmen(<?= $endStep ?>,<?= $endStep ?>)">Selesai Mengisi</a>
                            </div>
                        </div>

                    </div>
                    <div class="col-12 col-md-8 order-1">
                        <div class="tab-content" style="margin-top: 0;">
                            <!-- <div class="tab-pane active show" id="tab-1">
                            <div class="card card-primary ">
                                <div class="card-body">
                                    <h4 class="card-title text-center">Panduan Asesmen Mandiri</h4>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <ul>
                                        <li>Baca Pertanyaan di kolom sebelah kiri.</li>
                                        <li>Beri tanda centang jika Anda yakin dapat melakukan tugas yang dijelaskan.</li>
                                        <li>Isi kolom di sebelah kanan dengan mendaftar bukti yang Anda miliki untuk menunjukan bahawa Anda melakukan tugas-tugas ini.</li>
                                    </ul>
                                </div>
                                <div class="card-footer text-center">
                                    <button type="button" id="nextCardAsesmen2" onclick="showCardAsesmen(2,<?= $endStep ?>)" class="btn btn-primary"><span class="mr-2">Mulai Asesmen </span><i class="fas fa-arrow-right"></i></button>
                                </div>
                            </div>
                        </div> -->


                            <?php
                            foreach ($listUnit as $unit) {

                                $noElemen = 0;
                                foreach ($listElemen as $elemen) {
                                    if ($elemen['id_unit'] == $unit['id_unit']) {
                                        $noElemen++;
                                        $no = 0;
                                        $step = 0;

                                        foreach ($listSubelemen as $subelemen) {
                                            $step++;
                                            if ($subelemen['id_elemen'] == $elemen['id_elemen'] && $unit['id_unit']) {
                                                $no++;
                                                $nextStep = $step + 1;
                                                $prevStep = $step - 1;

                            ?>


                                                <div class="tab-pane <?= ($step === 1) ? 'active show' : ''; ?> " id="tab-<?= $step ?>">

                                                    <div class="card card-primary mb-3">
                                                        <div class="card-header">
                                                            <h4 class="card-title mx-auto" <?= ($step === 2) ? "data-intro='Ini adalah Unit Sertifikasi' data-step='3'" : ''; ?>><?= $unit['nama_unit'] ?></h4>
                                                        </div>
                                                    </div>

                                                    <div class="card ">
                                                        <div class="card-header">
                                                            <h4 <?= ($step === 2) ? "data-intro='Selanjutnya, Ini Adalah Elemen Sertifikasi' data-step='4'" : ''; ?>><?= $noElemen ?>. <?= $elemen['nama_elemen'] ?>: </h4>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">

                                                                <div class="col-md-12">
                                                                    <p><b>PERTANYAAN NO <?= $noElemen . '.' . $no ?></b></p>
                                                                    <p <?= ($step === 2) ? "data-intro='Selanjutnya, Baca Pertanyaan Dengan Teliti' data-step='5'" : ''; ?>><?= $subelemen['pertanyaan'] ?></p>
                                                                    <hr>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <input type="hidden" name="id_apl1_<?= $subelemen['id_subelemen'] ?>" value="<?= $dataAPL1['id_apl1'] ?>">
                                                                    <input type="hidden" name="id_skema_<?= $subelemen['id_subelemen'] ?>" value="<?= $subelemen['id_skema'] ?>">
                                                                    <input type="hidden" name="id_unit_<?= $subelemen['id_subelemen'] ?>" value="<?= $subelemen['id_unit'] ?>">
                                                                    <input type="hidden" name="id_elemen_<?= $subelemen['id_subelemen'] ?>" value="<?= $subelemen['id_elemen'] ?>">
                                                                    <input type="hidden" name="id_subelemen_<?= $subelemen['id_subelemen'] ?>" value="<?= $subelemen['id_subelemen'] ?>">
                                                                    <ul class="list-group list-group-flush" <?= ($step === 2) ? "data-intro='Selanjutnya, Isi Sesuai Kemampuan Anda Jika Anda Yakin Telah Menguasai Kemampuan Sesuai Yang Di Tanyakan' data-step='6'" : ''; ?>>
                                                                        <li class="list-group-item">
                                                                            <input class="bk-option" type="radio" name="bk_<?= $subelemen['id_subelemen'] ?>" id="bk_kompeten_<?= $subelemen['id_subelemen'] ?>" value="K" <?= (old('bk_' . $subelemen['id_subelemen']) == "K") ? 'checked' : '' ?> data-bukti-kompeten="bukti_kompeten_<?= $subelemen['id_subelemen'] ?>" <?= ($step === 2) ? "data-intro='Selanjutnya, ' data-step='7' " : ''; ?>>
                                                                            <label class="" for="bk_kompeten_<?= $subelemen['id_subelemen'] ?>">Kompeten</label>
                                                                        </li>
                                                                        <li class="list-group-item">
                                                                            <input class="bk-option" type="radio" name="bk_<?= $subelemen['id_subelemen'] ?>" id="bk_belum_kompeten_<?= $subelemen['id_subelemen'] ?>" value="BK" <?= (old('bk_' . $subelemen['id_subelemen']) == "BK") ? 'checked' : '' ?> data-bukti-kompeten="bukti_kompeten_<?= $subelemen['id_subelemen'] ?>">
                                                                            <label class="" for="bk_belum_kompeten_<?= $subelemen['id_subelemen'] ?>">Tidak Kompeten</label>
                                                                        </li>
                                                                    </ul>

                                                                </div>

                                                                <div class="col-12 bukti-kompeten <?= (old('bk_' . $subelemen['id_subelemen']) == "K") ? '' : 'd-none' ?>" id="bukti_kompeten_<?= $subelemen['id_subelemen'] ?>">
                                                                    <select class="form-control " name="bukti_pendukung_<?= $subelemen['id_subelemen'] ?>">
                                                                        <option value="">Pilih Bukti Pendukung</option>
                                                                        <option value="Nilai Raport" <?= (old('bukti_pendukung_' . $subelemen['id_subelemen']) == "Nilai Raport") ? 'selected' : '' ?>>Nilai Raport</option>
                                                                        <option value="Sertifikat Praktek Kerja Lapangan (PKL)" <?= (old('bukti_pendukung_' . $subelemen['id_subelemen']) == "Sertifikat Praktek Kerja Lapangan (PKL)") ? 'selected' : '' ?>>Sertifikat Praktek Kerja Lapangan (PKL)</option>
                                                                    </select>
                                                                    <div class="invalid-feedback">
                                                                        Error message here
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div class="card-footer text-center bg-whitesmoke br">
                                                            <div class="btn-group">
                                                                <?php if ($step > 1) { ?>
                                                                    <button type="button" class="btn btn-primary" id="nextCardAsesmen<?= $prevStep ?>" onclick="showCardAsesmen(<?= $prevStep ?>,<?= $endStep ?>)"><i class="fas fa-arrow-left"></i><span class="ml-2">Back</span></button>
                                                                <?php } ?>
                                                                <button type="button" class="btn btn-primary"><?= $step ?></button>
                                                                <?php if (count($listSubelemen) !== $endStep) { ?>
                                                                    <button type="button" id="nextCardAsesmen<?= $nextStep ?>" onclick="showCardAsesmen(<?= $nextStep ?>,<?= $endStep ?>)" class="btn btn-primary"><span class="mr-2">Next</span><i class="fas fa-arrow-right"></i></button>
                                                                <?php }  ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                            <?php
                                            }
                                        }
                                    }
                                }
                            }
                            ?>

                            <div class="tab-pane" id="tab-<?= $endStep ?>">

                                <div class="card card-primary mb-3">
                                    <div class="card-header">
                                        <h4 class="card-title mx-auto">Selesai Mengisi Asesmen Mandiri</h4>
                                    </div>
                                </div>

                                <div class="card ">
                                    <div class="card-body">
                                        <div class="empty-state" data-height="400">
                                            <div class="empty-state-icon">
                                                <i class="fas fa-rocket"></i>
                                            </div>
                                            <h2>Submit Asesmen Mandiri</h2>
                                            <p class="lead">
                                                Anda telah mencapai akhir dari pertanyaan Asesmen Mandiri, harap periksa kembali sebelum melakukan submit Asesmen.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="card-footer text-center bg-whitesmoke br">
                                        <button type="button" class="btn btn-primary" id="nextCardAsesmen<?= $prevStep + 1 ?>" onclick="showCardAsesmen(<?= $prevStep + 1 ?>,<?= $endStep ?>)"><i class="fas fa-arrow-left"></i><span class="ml-2">Kembali</span></button>
                                        <button type="button" id="submit-button" class="btn btn-primary" onclick="showSpinnerAndSubmit()">
                                            <span id="button-content">
                                                <i class="fas fa-upload"></i><span class="ml-2">Submit</span>
                                            </span>
                                            <span id="spinner" class="spinner-border text-light d-none" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>





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
        <?php } else { ?>
            <div class="row">
                <div class="col-12 col-md-4 order-2">
                    <div class="card card-primary ">
                        <div class="card-body">
                            <ul class="nav nav-tabs d-flex justify-content-around" data-intro='Ini adalah Jumlah Pertanyaan Yang Harus Anda Isi' data-step="1">
                                <?php
                                $step = 0;
                                foreach ($dataAPL2 as $value) {
                                    $step++;
                                    $formattedStep = sprintf("%02d", $step);
                                    $endStep = count($dataAPL2);
                                ?>
                                    <!-- btn btn-outline-dark -->
                                    <li class="nav-item ">
                                        <button type="button" class=" btn <?= (($value['tk'] !== null)) ? 'btn-success' : 'btn-danger'; ?> mx-auto m-2 w-100 <?= ($step === 1) ? 'active show' : ''; ?> " id="nextCardAsesmen<?= $step ?>" onclick="showCardAsesmen(<?= $step ?>,<?= $endStep ?>)" <?= ($step === 2) ? "data-intro='Klik Angka Untuk Pergi Ke Soal Selanjutnya' data-step='2'" : ''; ?>>
                                            <?= $formattedStep ?>
                                        </button>
                                    </li><!-- End Tab 1 Nav -->
                                <?php } ?>
                            </ul>
                        </div>
                        <div class="card-footer">
                            <!-- <a href="#" class="card-link">Card link</a> -->
                            <a class="card-link" id="nextCardAsesmen<?= $endStep ?>" onclick="showCardAsesmen(<?= $endStep ?>,<?= $endStep ?>)">Selesai Mengisi</a>
                        </div>
                    </div>

                </div>
                <div class="col-12 col-md-8 order-1">
                    <div class="tab-content" style="margin-top: 0;">

                        <?php
                        foreach ($listUnit as $unit) {

                            $noElemen = 0;
                            foreach ($listElemen as $elemen) {
                                if ($elemen['id_unit'] == $unit['id_unit']) {
                                    $noElemen++;
                                    $no = 0;
                                    $step = 0;

                                    foreach ($dataAPL2 as $value) {
                                        $step++;
                                        if ($value['id_elemen'] == $elemen['id_elemen'] && $unit['id_unit']) {
                                            $no++;
                                            $nextStep = $step + 1;
                                            $prevStep = $step - 1;
                        ?>


                                            <div class="tab-pane <?= ($step === 1) ? 'active show' : ''; ?> " id="tab-<?= $step ?>">

                                                <div class="card card-primary mb-3">
                                                    <div class="card-header">
                                                        <h4 class="card-title mx-auto" <?= ($step === 2) ? "data-intro='Ini adalah Unit Sertifikasi' data-step='3'" : ''; ?>><?= $unit['nama_unit'] ?></h4>
                                                    </div>
                                                </div>

                                                <div class="card ">
                                                    <div class="card-header">
                                                        <h4 <?= ($step === 2) ? "data-intro='Selanjutnya, Ini Adalah Elemen Sertifikasi' data-step='4'" : ''; ?>><?= $noElemen ?>. <?= $elemen['nama_elemen'] ?>: </h4>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">

                                                            <div class="col-md-12">
                                                                <p><b>PERTANYAAN NO <?= $noElemen . '.' . $no ?></b></p>
                                                                <p <?= ($step === 2) ? "data-intro='Selanjutnya, Baca Pertanyaan Dengan Teliti' data-step='5'" : ''; ?>><?= $value['pertanyaan'] ?></p>
                                                                <hr>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <ul class="list-group list-group-flush" <?= ($step === 2) ? "data-intro='Selanjutnya, Isi Sesuai Kemampuan Anda Jika Anda Yakin Telah Menguasai Kemampuan Sesuai Yang Di Tanyakan' data-step='6'" : ''; ?>>
                                                                    <li class="list-group-item">
                                                                        <input class="bk-option" type="radio" <?= ($value['tk'] == "K") ? 'checked' : '' ?> disabled>
                                                                        <label class="" for="bk_kompeten_<?= $value['id_subelemen'] ?>">Kompeten</label>
                                                                    </li>
                                                                    <li class="list-group-item">
                                                                        <input class="bk-option" type="radio" <?= ($value['tk'] == "BK") ? 'checked' : '' ?> disabled>
                                                                        <label class="" for="bk_belum_kompeten_<?= $value['id_subelemen'] ?>">Tidak Kompeten</label>
                                                                    </li>
                                                                </ul>

                                                            </div>
                                                            <?php if ($value['tk'] == "K") { ?>
                                                                <div class="col-12">
                                                                    <select class=" form-control select2" disabled>
                                                                        <option value="">Tidak Ada</option>
                                                                        <option value="Nilai Raport" <?= ($value['bukti_pendukung'] == "Nilai Raport") ? 'selected' : '' ?>>Nilai Raport</option>
                                                                        <option value="Sertifikat Praktek Kerja Lapangan (PKL)" <?= ($value['bukti_pendukung'] == "Sertifikat Praktek Kerja Lapangan (PKL)") ? 'selected' : '' ?>>Sertifikat Praktek Kerja Lapangan (PKL)</option>
                                                                    </select>
                                                                </div>
                                                            <?php } ?>
                                                        </div>

                                                    </div>
                                                    <div class="card-footer text-center bg-whitesmoke br">
                                                        <?php if ($step > 1) { ?>
                                                            <button type="button" class="btn btn-primary" id="nextCardAsesmen<?= $prevStep ?>" onclick="showCardAsesmen(<?= $prevStep ?>,<?= $endStep ?>)"><i class="fas fa-arrow-left"></i><span class="ml-2">Kembali</span></button>
                                                        <?php } ?>
                                                        <?php if (count($listSubelemen) !== $step) { ?>
                                                            <button type="button" id="nextCardAsesmen<?= $nextStep ?>" onclick="showCardAsesmen(<?= $nextStep ?>,<?= $endStep ?>)" class="btn btn-primary"><span class="mr-2">Selanjutnya (<?= $step ?>)</span><i class="fas fa-arrow-right"></i></button>
                                                        <?php } ?>

                                                    </div>
                                                </div>
                                            </div>


                        <?php
                                        }
                                    }
                                }
                            }
                        }
                        ?>


                    </div>
                </div>

            </div>

        <?php } ?>

    </form>
</section>

<?= $this->endSection() ?>

<?= $this->section("js-reCAPTCHA"); ?>
<script>
    // Menggunakan JavaScript untuk menangani perubahan pada radio button
    const bkOptions = document.querySelectorAll('.bk-option');

    // Tambahkan event listener untuk setiap radio button
    bkOptions.forEach(function(option) {
        option.addEventListener('change', function() {
            const buktiKompetenId = option.dataset.buktiKompeten;
            const buktiKompeten = document.getElementById(buktiKompetenId);

            if (option.value === 'K') {
                buktiKompeten.classList.remove('d-none'); // Tampilkan bukti kompeten
            } else {
                buktiKompeten.classList.add('d-none'); // Sembunyikan bukti kompeten
            }
        });
    });
</script>


<!-- ===== Script Nav-Tab ===== -->
<script>
    function showCardAsesmen(cardNumber, count) {
        for (let i = 1; i <= count; i++) {
            const tab = document.getElementById('tab-' + i);
            tab.style.display = 'none';

            const button = document.getElementById('nextCardAsesmen' + i);
            button.classList.remove('active');
        }

        const activeTab = document.getElementById('tab-' + cardNumber);
        activeTab.style.display = 'block';

        const activeButton = document.getElementById('nextCardAsesmen' + cardNumber);
        activeButton.classList.add('active');
    }
</script>
<!-- ===== End ===== -->


<!-- ===== Config Intro.js ===== -->
<script>
    var intro = introJs();

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('startButton').addEventListener('click', function() {
            showCardAsesmen(1);
            intro.start();
        });
    });

    intro.onbeforechange(function(targetElement) {
        // (indeks dimulai dari 0)
        if (intro._currentStep === 1) {
            // Memanggil fungsi showCard(2) untuk menampilkan "Card 2"
            showCardAsesmen(2);
        }

    });

    intro.oncomplete(function() {
        showCardAsesmen(1);
    });
</script>
<!-- ===== End ===== -->
<?= $this->endSection() ?>