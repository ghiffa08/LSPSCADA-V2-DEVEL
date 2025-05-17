<?= $this->extend("layouts/landingpage/layout-2") ?>
<?= $this->section("content") ?>

<?php if (session()->has('errors')) : ?>
    <ul class="alert alert-danger">
        <?php foreach (session('errors') as $error) : ?>
            <li><?= $error ?></li>
        <?php endforeach ?>
    </ul>
<?php endif ?>

<form id="upload-form" action="<?= site_url('/store-pengajuan'); ?>" method="POST" enctype="multipart/form-data">
    <ul class="nav nav-tabs row gy-4 d-flex">
        <li class="nav-item col-12 col-md-4 col-lg-4" data-intro='Pertama, Pilih Data pengajuan' data-step="1">
            <button type="button" class="nav-link active show w-100" id="nextToCard1" onclick="showCard(1)">
                <i class="bi bi-info-square color-cyan"></i>
                <h4>Data Pengajuan</h4>
            </button>
        </li><!-- End Tab 1 Nav -->

        <li class="nav-item col-12 col-md-4 col-lg-4">
            <button type="button" class="nav-link w-100" id="nextToCard2" onclick="showCard(2)" data-intro='Selanjutnya, Pilih Profil Peserta' data-step="3">
                <i class="bi bi-card-heading color-indigo"></i>
                <h4>Profil Peserta</h4>
            </button>
        </li><!-- End Tab 2 Nav -->

        <li class="nav-item col-12 col-md-4 col-lg-4">
            <button type="button" class="nav-link w-100 " id="nextToCard3" onclick="showCard(3)" data-intro='Selanjutnya, Pilih Dokumen Portofolio' data-step="8">
                <i class="bi bi-upload color-teal"></i>
                <h4>Dokumen Portofolio</h4>
            </button>
        </li><!-- End Tab 3 Nav -->
    </ul>

    <div class="tab-content">

        <div class="tab-pane active show" id="tab-1">
            <div data-intro='Selanjutnya, Pilih Skema Sertifikasi' data-step="2">
                <div class="card mb-3">
                    <div class="card-body">
                        <h4 class="card-title text-center">DATA PENGAJUAN UJI KOMPETENSI</h4>
                    </div>
                </div>

                <div class="card mb-3">


                    <div class="card-body">
                        <div class="form-group mb-3 mb-3">
                            <label class="form-label" class="form-label">Skema<span class="text-danger">*</span></label>
                            <select class="form-control select2 <?php if (session('errors.skema_sertifikasi')) : ?>is-invalid<?php endif ?>" id="id_skema" name="skema_sertifikasi">
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
                            <select class="form-control select2 <?php if (session('errors.jadwal_sertifikasi')) : ?>is-invalid<?php endif ?>" id="id_tanggal" name="jadwal_sertifikasi" disabled>

                            </select>
                            <?php if (session('errors.jadwal_sertifikasi')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.jadwal_sertifikasi') ?>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="form-group mb-3 mb-3">
                            <label class="form-label" class="form-label">Tempat Uji Kompetensi<span class="text-danger">*</span></label>
                            <select class="form-control select2 <?php if (session('errors.tuk')) : ?>is-invalid<?php endif ?>" name="tuk" id=id_tuk disabled>

                            </select>
                            <?php if (session('errors.tuk')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.tuk') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <input type="hidden" name="id_asesmen" id="id_asesmen">
                    </div>
                </div>
            </div>



            <div class="card">
                <div class="card-body text-center">
                    <button type="button" id="nextToCard2" onclick="showCard(2)" class="btn btn-primary"><span class="mr-2">Selanjutnya</span><i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

        </div><!-- End Tab Content 1 -->

        <div class="tab-pane" id="tab-2">
            <div data-intro='Selanjutnya, Lengkapi Data Diri Anda' data-step="4">
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
                                <?php } else { ?>
                                    <small>Email Harus Mengunakan Email aktif!</small>
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
            </div>

            <div data-intro='Selanjutnya, Lengkapi Data Riwayat Pendidikan' data-step="5">
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
                                <option value="SD" <?php echo old('pendidikan_terakhir') == "SD" ? "selected" : ""; ?>>SD</option>
                                <option value="SMP" <?php echo old('pendidikan_terakhir') == "SMP" ? "selected" : ""; ?>>SMP</option>
                                <option value="SMA/SMK" <?php echo old('pendidikan_terakhir') == "SMA/SMK" ? "selected" : ""; ?>>SMA/SMK</option>
                                <option value="Diploma" <?php echo old('pendidikan_terakhir') == "Diploma" ? "selected" : ""; ?>>Diploma</option>
                                <option value="Sarjana" <?php echo old('pendidikan_terakhir') == "Sarjana" ? "selected" : ""; ?>>Sarjana</option>
                                <option value="Magister" <?php echo old('pendidikan_terakhir') == "Magister" ? "selected" : ""; ?>>Magister</option>
                                <option value="Doktor" <?php echo old('pendidikan_terakhir') == "Doktor" ? "selected" : ""; ?>>Doktor</option>
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
            </div>

            <div data-intro='Selanjutnya, Lengkapi Data Alamat Domisili' data-step="6">
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


                                        echo '<option value="' . $row['id'] . '">' . $row['nama'] . '</option>';
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
            </div>

            <div data-intro='Selanjutnya, Lengkapi Data Pekerjaan' data-step="7">
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
                                <option value="Lainya" <?= (old('pekerjaan') == "Lainya" ? "selected" : ""); ?>>Lainya</option>
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
            </div>


            <div class="card">
                <div class="card-body text-center">
                    <button type="button" id="nextToCard1" onclick="showCard(1)" class="btn btn-primary"><i class="fas fa-arrow-left"></i><span class="ml-2">Kembali</span></button>
                    <button type="button" id="nextToCard2" onclick="showCard(3)" class="btn btn-primary"><span class="mr-2">Selanjutnya</span><i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

        </div><!-- End Tab Content 2 -->

        <div class="tab-pane" id="tab-3">

            <div data-intro='Selanjutnya, Upload Document' data-step="9">
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
                                    <input type="file" name="pas_foto" class="image-preview-filepond <?php if (session('errors.pas_foto')) : ?>is-invalid<?php endif ?>">
                                    <?php if (session('errors.pas_foto')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.pas_foto') ?>
                                        </div>
                                    <?php } else { ?>
                                        <small>*Tipe Image (jpg/jpeg/png), Ukuran Maksimal 2 MB</small>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="">Foto Tanda Tangan<span class="text-danger">*</span></label>
                                    <input type="file" name="tanda_tangan_asesi" class="filepond-tanda-tangan  <?php if (session('errors.tanda_tangan_asesi')) : ?>is-invalid<?php endif ?>">
                                    <?php if (session('errors.tanda_tangan_asesi')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.tanda_tangan_asesi') ?>
                                        </div>
                                    <?php } else { ?>
                                        <small>*Tipe Image (jpg/jpeg/png), Ukuran Maksimal 2 MB</small>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="">Identitas Pribadi (KTP / Kartu Pelajar)<span class="text-danger">*</span></label>
                                    <input type="file" name="file_ktp" class="filepond-ktp <?php if (session('errors.file_ktp')) : ?>is-invalid<?php endif ?>">
                                    <?php if (session('errors.file_ktp')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.file_ktp') ?>
                                        </div>
                                    <?php } else { ?>
                                        <small>*Tipe Image (jpg/jpeg/png) / file pdf, Ukuran Maksimal 2 MB</small>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="">Bukti Pendidikan<span class="text-danger">*</span></label>
                                    <input type="file" name="bukti_pendidikan" class="filepond-bukti-pendidikan <?php if (session('errors.bukti_pendidikan')) : ?>is-invalid<?php endif ?>">
                                    <?php if (session('errors.bukti_pendidikan')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.bukti_pendidikan') ?>
                                        </div>
                                    <?php } else { ?>
                                        <small>*Tipe Image (jpg/jpeg/png) / file pdf, Ukuran Maksimal 2 MB</small>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="">Raport<span class="text-danger">*</span></label>
                                    <input type="file" name="raport" class="filepond-raport <?php if (session('errors.raport')) : ?>is-invalid<?php endif ?>">
                                    <?php if (session('errors.raport')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.raport') ?>
                                        </div>
                                    <?php } else { ?>
                                        <small>*Tipe file pdf, Ukuran Maksimal 2 MB</small>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="">Sertifikat PKL<span class="text-danger">*</span></label>
                                    <input type="file" name="sertifikat_pkl" class="filepond-sertifikat-pkl <?php if (session('errors.sertifikat_pkl')) : ?>is-invalid<?php endif ?>">
                                    <?php if (session('errors.sertifikat_pkl')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.sertifikat_pkl') ?>
                                        </div>
                                    <?php } else { ?>
                                        <small>*Tipe Image (jpg/jpeg/png) / file pdf, Ukuran Maksimal 2 MB</small>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>




            <div class="card" data-intro='Selanjutnya, Check reCHAPTCHA' data-step="10">
                <div class="card-body">
                    <div class="form-group">
                        <?= $widgetTag ?>
                    </div>
                </div>
            </div>


            <div class="card">
                <div class="card-body text-center">
                    <button type="button" id="nextToCard2" onclick="showCard(2)" class="btn btn-primary"><i class="fas fa-arrow-left"></i><span class="ml-2">Kembali</span></button>
                    <button type="button" id="submit-button" class="btn btn-primary" data-intro='Terakhir, Upload Data Diri Anda dan Tunggu Info Lebih Lanjut Melalui Email.' data-step="11" onclick="showSpinnerAndSubmit()">
                        <span id="button-content">
                            <i class="fas fa-upload"></i><span class="ml-2">Upload</span>
                        </span>
                        <span id="spinner" class="spinner-border text-light d-none" role="status">
                            <span class="sr-only">Loading...</span>
                        </span>
                    </button>
                </div>
            </div>



        </div><!-- End Tab Content 3 -->

    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section("js-reCAPTCHA"); ?>
<?= $scriptTag ?>

<!-- <script>
    // Function to show SweetAlert2 loader
    function showLoader() {
        Swal.fire({
            title: 'Loading...',
            html: 'Data sedang diproses',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => {
                Swal.showLoading();
            }
        });
    }

    // Function to handle click event on submit button
    document.getElementById('submit-button').addEventListener('click', function() {
        // Show loader
        showLoader();

        // Simulate saving process (Replace this with actual saving logic)
        setTimeout(function() {
            // Close the loader
            Swal.close();
            // You can add additional logic here after data is saved
            // For example, display a success message
            Swal.fire({
                icon: 'success',
                title: 'Data berhasil disimpan',
                showConfirmButton: false,
                timer: 1500
            });
        }, 2000); // Simulate 2 seconds delay for saving process
    });
</script> -->

<!-- ===== Config Intro.js ===== -->
<script>
    var intro = introJs();

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('startButton').addEventListener('click', function() {
            showCard(1);
            intro.start();
        });
    });

    intro.onbeforechange(function(targetElement) {
        // (indeks dimulai dari 0)
        if (intro._currentStep === 2) {
            // Memanggil fungsi showCard(2) untuk menampilkan "Card 2"
            showCard(2);
        }
        if (intro._currentStep === 7) {
            // Memanggil fungsi showCard(2) untuk menampilkan "Card 2"
            showCard(3);
        }
    });

    intro.oncomplete(function() {
        showCard(1);
    });
</script>
<!-- ===== End ===== -->
<?= $this->endSection() ?>