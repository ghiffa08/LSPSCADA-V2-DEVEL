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
        <form id="uploadForm" action="<?= site_url('/store-pengajuan'); ?>" method="POST" enctype="multipart/form-data">
            <ul class="nav nav-tabs row gy-4 d-flex">
                <li class="nav-item col-12 col-md-4 col-lg-4">
                    <button type="button" class="nav-link active show w-100" id="nextToCard1" onclick="showCard(1)">
                        <i class="bi bi-info-square color-cyan"></i>
                        <h4>Data Pengajuan</h4>
                    </button>
                </li><!-- End Tab 1 Nav -->

                <li class="nav-item col-12 col-md-4 col-lg-4">
                    <button type="button" class="nav-link w-100" id="nextToCard2" onclick="showCard(2)">
                        <i class="bi bi-card-heading color-indigo"></i>
                        <h4>Profil Peserta</h4>
                    </button>
                </li><!-- End Tab 2 Nav -->

                <li class="nav-item col-12 col-md-4 col-lg-4">
                    <button type="button" class="nav-link w-100 " id="nextToCard3" onclick="showCard(3)">
                        <i class="bi bi-upload color-teal"></i>
                        <h4>Dokumen Portofolio</h4>
                    </button>
                </li><!-- End Tab 3 Nav -->
            </ul>

            <div class="tab-content">

                <div class="tab-pane active show" id="tab-1">

                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title text-center">DATA PENGAJUAN UJI KOMPETENSI</h4>
                        </div>
                    </div>

                    <div class="card mb-3">


                        <div class="card-body">
                            <div class="form-group mb-3 mb-3">
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

                            <div class="form-group mb-3 mb-3">
                                <label class="form-label" class="form-label">Jadwal Uji Kompetensi<span class="text-danger">*</span></label>
                                <select class="form-control select2 <?php if (session('errors.jadwal_sertifikasi')) : ?>is-invalid<?php endif ?>" name="jadwal_sertifikasi">
                                    <option value="">Pilih Jadwal Uji Kompetensi</option>
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

                            <div class="form-group mb-3 mb-3">
                                <label class="form-label" class="form-label">Tempat Uji Kompetensi<span class="text-danger">*</span></label>
                                <select class="form-control select2 <?php if (session('errors.tuk')) : ?>is-invalid<?php endif ?>" name="tuk">
                                    <option value="">Pilih Tempat Uji Kompetensi</option>
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
                    </div>


                    <div class="card">
                        <div class="card-body text-center">
                            <button type="button" id="nextToCard2" onclick="showCard(2)" class="btn btn-primary">Selanjutnya</button>
                        </div>
                    </div>

                </div><!-- End Tab Content 1 -->

                <div class="tab-pane" id="tab-2">

                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title text-center">DATA DIRI PEMOHON</h4>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <?= csrf_field(); ?>
                            <div class="form-group mb-3">
                                <label class="form-label">Nama Lengkap<span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php if (session('errors.nama_siswa')) : ?>is-invalid<?php endif ?>" name="nama_siswa" value="<?= old('nama_siswa') ?>" placeholder="Masukan Nama Lengkap">
                                <?php if (session('errors.nama_siswa')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.nama_siswa') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="row">
                                <div class="form-group mb-3 col-12 col-md-4">
                                    <label class="form-label">Email<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php if (session('errors.email')) : ?>is-invalid<?php endif ?>" name="email" value="<?= old('email') ?>" placeholder="Masukan Alamat Email">
                                    <?php if (session('errors.email')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.email') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="form-group mb-3 col-12 col-md-4">
                                    <label class="form-label">Nomor Handphone<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control <?php if (session('errors.no_hp')) : ?>is-invalid<?php endif ?>" name="no_hp" value="<?= old('no_hp') ?>" placeholder="Masukan Nomor Handphone/Whatsapp">
                                    <?php if (session('errors.no_hp')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.no_hp') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class=" form-group mb-3 col-12 col-md-4">
                                    <label class="form-label">Nomor Telpon</label>
                                    <input type="number" class="form-control <?php if (session('errors.telpon_rumah')) : ?>is-invalid<?php endif ?>" name="telpon_rumah" value="<?= old('telpon_rumah') ?>" placeholder="Masukan Nomor Telpon Rumah">
                                    <?php if (session('errors.telpon_rumah')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.telpon_rumah') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Nomor KTP/NIK/Paspor<span class="text-danger">*</span></label>
                                <input type="number" class="form-control <?php if (session('errors.nik')) : ?>is-invalid<?php endif ?>" name="nik" value="<?= old('nik') ?>" placeholder="Masukan Nomor Induk Kependudukan">
                                <?php if (session('errors.nik')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.nik') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="row">
                                <div class="form-group mb-3 col-12 col-md-6">
                                    <label class="form-label">Tempat Lahir<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php if (session('errors.tempat_lahir')) : ?>is-invalid<?php endif ?>" name="tempat_lahir" value="<?= old('tempat_lahir') ?>" placeholder="Masukan Tempat Lahir">
                                    <?php if (session('errors.tempat_lahir')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.tempat_lahir') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="form-group mb-3 col-12 col-md-6">
                                    <label class="form-label">Tanggal Lahir<span class="text-danger">*</span></label>
                                    <input type="date" class="form-control <?php if (session('errors.tanggal_lahir')) : ?>is-invalid<?php endif ?>" name="tanggal_lahir" value="<?= old('tanggal_lahir') ?>">
                                    <?php if (session('errors.tanggal_lahir')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.tanggal_lahir') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group mb-3 mb-3">
                                <label class="form-label" class="form-label">Jenis Kelamin<span class="text-danger">*</span></label>
                                <select class="form-control select2 <?php if (session('errors.jenis_kelamin')) : ?>is-invalid<?php endif ?>" name="jenis_kelamin">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-Laki" <?= (old('jenis_kelamin') == "Laki-Laki" ? "selected" : ""); ?>>Laki-Laki</option>
                                    <option value="Perempuan" <?= (old('jenis_kelamin') == "Perempuan" ? "selected" : ""); ?>>Perempuan</option>
                                </select>
                                <?php if (session('errors.jenis_kelamin')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.jenis_kelamin') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Kebangsaan<span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php if (session('errors.kebangsaan')) : ?>is-invalid<?php endif ?>" name="kebangsaan" value="<?= old('kebangsaan') ?>" placeholder="WNI/WNA">
                                <?php if (session('errors.kebangsaan')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.kebangsaan') ?>
                                    </div>
                                <?php } ?>
                            </div>

                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title text-center">RIWAYAT PENDIDIKAN PEMOHON</h4>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="form-label">Pendidikan Terakhir<span class="text-danger">*</span></label>
                                <select name="pendidikan_terakhir" class="form-control select2 <?php if (session('errors.pendidikan_terakhir')) : ?>is-invalid<?php endif ?>">
                                    <option value="">Pilih Pendidikan Terakhir</option>
                                    <option value="SD">SD</option>
                                    <option value="SMP">SMP</option>
                                    <option value="SMA/SMK">SMA/SMK</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="Sarjana">Sarjana</option>
                                    <option value="Magister">Magister</option>
                                    <option value="Doktor">Doktor</option>
                                </select>
                                <?php if (session('errors.pendidikan_terakhir')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.pendidikan_terakhir') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Nama Sekolah / Perguruan Tinggi<span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php if (session('errors.nama_sekolah')) : ?>is-invalid<?php endif ?>" name="nama_sekolah" value="<?= old('nama_sekolah') ?>" placeholder="Masukan Nama Sekolah Atau Perguruan Tinggi">
                                <?php if (session('errors.nama_sekolah')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.nama_sekolah') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Jurusan / Program Studi<span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php if (session('errors.jurusan')) : ?>is-invalid<?php endif ?>" name="jurusan" value="<?= old('jurusan') ?>" placeholder="Masukan Nama Jurusan Atau Program Studi">
                                <?php if (session('errors.jurusan')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.jurusan') ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title text-center">ALAMAT DOMISILI PEMOHON</h4>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group mb-3 col-12 col-md-6">
                                    <label class="form-label">Provinsi<span class="text-danger">*</span></label>
                                    <select class="form-control select2 <?php if (session('errors.provinsi')) : ?>is-invalid<?php endif ?>" name="provinsi" id="id_provinsi">
                                        <option value="">Provinsi</option>
                                        <?php
                                        foreach ($provinsi as $key => $row) {
                                            old('provinsi') == $row['id'] ? $pilih = 'selected' : $pilih = null;

                                            echo '<option ' . $pilih . ' value="' . $row['id'] . '">' . $row['nama'] . '</option>';
                                        }
                                        ?>

                                    </select>
                                    <?php if (session('errors.provinsi')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.provinsi') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="form-group mb-3 col-12 col-md-6">
                                    <label class="form-label">Kabupaten/Kota<span class="text-danger">*</span></label>
                                    <select class="form-control select2 <?php if (session('errors.kabupaten')) : ?>is-invalid<?php endif ?>" name="kabupaten" id="id_kabupaten">
                                        <option value="">Kabupaten/Kota</option>


                                    </select>
                                    <?php if (session('errors.kabupaten')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.kabupaten') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group mb-3 col-12 col-md-6">
                                    <label class="form-label">Kecamatan<span class="text-danger">*</span></label>
                                    <select class="form-control select2 <?php if (session('errors.kecamatan')) : ?>is-invalid<?php endif ?>" name="kecamatan" id="id_kecamatan">
                                        <option value="">Kecamatan</option>
                                    </select>
                                    <?php if (session('errors.kecamatan')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.kecamatan') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="form-group mb-3 col-12 col-md-6">
                                    <label class="form-label">Kelurahan/Desa<span class="text-danger">*</span></label>
                                    <select class="form-control select2 <?php if (session('errors.kelurahan')) : ?>is-invalid<?php endif ?>" name="kelurahan" id="id_desa">
                                        <option value="">Kelurahan/Desa</option>
                                    </select>
                                    <?php if (session('errors.kelurahan')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.kelurahan') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group mb-3 col-12 col-md-4">
                                    <label class="form-label">RT<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control <?php if (session('errors.rt')) : ?>is-invalid<?php endif ?>" name="rt" value="<?= old('rt') ?>" placeholder="Masukan RT">
                                    <?php if (session('errors.rt')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.rt') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="form-group mb-3 col-12 col-md-4">
                                    <label class="form-label">RW<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control <?php if (session('errors.rw')) : ?>is-invalid<?php endif ?>" name="rw" value="<?= old('rw') ?>" placeholder="Masukan RW">
                                    <?php if (session('errors.rw')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.rw') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="form-group mb-3 col-12 col-md-4">
                                    <label class="form-label">Kode Pos<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control <?php if (session('errors.kode_pos')) : ?>is-invalid<?php endif ?>" name="kode_pos" value="<?= old('kode_pos') ?>" placeholder="Masukan Kode Pos">
                                    <?php if (session('errors.kode_pos')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.kode_pos') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title text-center">DATA PEKERJAAN</h4>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="form-label">Pekerjaan<span class="text-danger">*</span></label>
                                <select name="pekerjaan" class="form-control select2 <?php if (session('errors.pekerjaan')) : ?>is-invalid<?php endif ?>">
                                    <option value="">Pilih Pekerjaan</option>
                                    <option value="Pelajar/Mahasiswa" <?= (old('pekerjaan') == "Pelajar/Mahasiswa" ? "selected" : ""); ?>>Pelajar/Mahasiswa</option>
                                    <option value="Pegawai" <?= (old('pekerjaan') == "Pegawai" ? "selected" : ""); ?>>Pegawai</option>
                                    <option value="Wiraswasta" <?= (old('pekerjaan') == "Wiraswasta" ? "selected" : ""); ?>>Wiraswasta</option>
                                    <option value="Petani" <?= (old('pekerjaan') == "Petani" ? "selected" : ""); ?>>Petani</option>
                                    <option value="Guru" <?= (old('pekerjaan') == "Guru" ? "selected" : ""); ?>>Guru</option>
                                    <option value="Dokter" <?= (old('pekerjaan') == "Dokter" ? "selected" : ""); ?>>Dokter</option>
                                </select>
                                <?php if (session('errors.pekerjaan')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.pekerjaan') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Nama Instansi</label>
                                <input type="text" class="form-control <?php if (session('errors.nama_lembaga')) : ?>is-invalid<?php endif ?>" name="nama_lembaga" value="<?= old('nama_lembaga') ?>" placeholder="Organisasi / Tempat Kerja / Instansi Terkait / Freelance / - (bila tidak ada)">
                                <?php if (session('errors.nama_lembaga')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.nama_lembaga') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Jabatan</label>
                                <input type="text" class="form-control <?php if (session('errors.jabatan')) : ?>is-invalid<?php endif ?>" name="jabatan" value="<?= old('jabatan') ?>" placeholder="Jabatan di Perusahaan">
                                <?php if (session('errors.jabatan')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.jabatan') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Alamat Lembaga / Perusahaan</label>
                                <textarea class="form-control <?php if (session('errors.alamat_perusahaan')) : ?>is-invalid<?php endif ?>" name="alamat_perusahaan" id="inputDescription"><?= old('alamat_perusahaan') ?></textarea>
                                <?php if (session('errors.alamat_perusahaan')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.alamat_perusahaan') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="row">
                                <div class="form-group mb-3 col-12 col-md-6">
                                    <label class="form-label">Email Perusahaan</label>
                                    <input type="text" class="form-control <?php if (session('errors.email_perusahaan')) : ?>is-invalid<?php endif ?>" name="email_perusahaan" value="<?= old('email_perusahaan') ?>" placeholder="Masukan Nomor Email Perusahaan">
                                    <?php if (session('errors.email_perusahaan')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.email_perusahaan') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="form-group mb-3 col-12 col-md-6">
                                    <label class="form-label">Nomor Telp Perusahaan</label>
                                    <input type="text" class="form-control <?php if (session('errors.no_telp_perusahaan')) : ?>is-invalid<?php endif ?>" name="no_telp_perusahaan" value="<?= old('no_telp_perusahaan') ?>" placeholder="Masukan Nomor Telpon Perusahaan">
                                    <?php if (session('errors.no_telp_perusahaan')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.no_telp_perusahaan') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body text-center">
                            <button type="button" id="nextToCard1" onclick="showCard(1)" class="btn btn-primary">Kembali</button>
                            <button type="button" id="nextToCard2" onclick="showCard(3)" class="btn btn-primary">Selanjutnya</button>
                        </div>
                    </div>

                </div><!-- End Tab Content 2 -->

                <div class="tab-pane" id="tab-3">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title text-center">BUKTI KELENGKAPAN PEMOHON</h4>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class=" card-body">

                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label class="form-label" for="">Pas Foto<span class="text-danger">*</span></label>
                                        <input type="file" name="pas_foto" class="image-preview-filepond">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label class="form-label" for="">Identitas Pribadi (KTP / Kartu Pelajar)<span class="text-danger">*</span></label>
                                        <input type="file" name="file_ktp" class="filepond-ktp">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label class="form-label" for="">Bukti Pendidikan<span class="text-danger">*</span></label>
                                        <input type="file" name="bukti_pendidikan" class="filepond-bukti-pendidikan">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <?= $widgetTag ?>
                            </div>
                        </div>
                    </div>


                    <div class="card">
                        <div class="card-body text-center">
                            <button type="button" id="nextToCard2" onclick="showCard(2)" class="btn btn-primary">Kembali</button>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </div>



                </div><!-- End Tab Content 3 -->

            </div>
        </form>


</section><!-- End Features Section -->

<?= $this->endSection() ?>

<?= $this->section("js-reCAPTCHA"); ?>
<?= $scriptTag ?>

<?= $this->endSection() ?>