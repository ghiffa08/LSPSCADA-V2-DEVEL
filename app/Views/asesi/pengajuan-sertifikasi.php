<?= $this->extend("layouts/asesi/layout-app") ?>
<?= $this->section("content") ?>


<!-- Flash Messages -->
<?php if (session()->has('errors')) : ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <div class="alert-body">
            <button class="close" data-dismiss="alert">
                <span>×</span>
            </button>
            <ul class="mb-0">
                <?php foreach (session('errors') as $error) : ?>
                    <li><?= $error ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>
<?php endif ?>

<!-- Main Stepper Container -->
<div class="card">
    <div class="card-header">
        <h4>Form Pengajuan Uji Kompetensi</h4>
    </div>
    <div class="card-body">
        <!-- Stepper Component -->
        <div class="stepper-wrapper">
            <div class="stepper">
                <div class="stepper-item active" data-step="1" onclick="goToStep(1)">
                    <div class="step-counter"><i class="fas fa-file-alt"></i></div>
                    <span class="step-label">Data Pengajuan</span>
                    <span class="step-desc">Informasi skema dan jadwal</span>
                </div>
                <div class="stepper-item" data-step="2" onclick="goToStep(2)">
                    <div class="step-counter"><i class="fas fa-user"></i></div>
                    <span class="step-label">Data Diri</span>
                    <span class="step-desc">Identitas pribadi</span>
                </div>
                <div class="stepper-item" data-step="3" onclick="goToStep(3)">
                    <div class="step-counter"><i class="fas fa-graduation-cap"></i></div>
                    <span class="step-label">Pendidikan</span>
                    <span class="step-desc">Riwayat pendidikan</span>
                </div>
                <div class="stepper-item" data-step="4" onclick="goToStep(4)">
                    <div class="step-counter"><i class="fas fa-map-marker-alt"></i></div>
                    <span class="step-label">Domisili</span>
                    <span class="step-desc">Alamat tempat tinggal</span>
                </div>
                <div class="stepper-item" data-step="5" onclick="goToStep(5)">
                    <div class="step-counter"><i class="fas fa-briefcase"></i></div>
                    <span class="step-label">Pekerjaan</span>
                    <span class="step-desc">Data pekerjaan</span>
                </div>
                <div class="stepper-item" data-step="7" onclick="goToStep(6)">
                    <div class="step-counter"><i class="fas fa-file-upload"></i></div>
                    <span class="step-label">Dokumen</span>
                    <span class="step-desc">Lampiran berkas</span>
                </div>
            </div>

            <form action="<?= site_url('/store-pengajuan'); ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field(); ?>

                <!-- Step Content 1: Data Pengajuan -->
                <div id="step1-content" class="step-content active">

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

                    <div class="form-group mb-5">
                        <label class="form-label" class="form-label">Jadwal Uji Kompetensi<span class="text-danger">*</span></label>
                        <select class="form-control select2 <?php if (session('errors.jadwal_sertifikasi')) : ?>is-invalid<?php endif ?>" id="id_tanggal" name="jadwal_sertifikasi">
                        </select>
                        <input type="hidden" name="id_asesmen" id="id_asesmen">
                        <?php if (session('errors.jadwal_sertifikasi')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.jadwal_sertifikasi') ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="step-navigation">
                        <div></div>
                        <button type="button" class="btn btn-primary" data-stepper-next="2">
                            Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step Content 2: Data Diri -->
                <div id="step2-content" class="step-content">

                    <?= csrf_field(); ?>
                    <div class="form-group mb-3">
                        <label class="form-label">Nama Lengkap<span class="text-danger">*</span></label>
                        <input type="text" class="form-control " name="nama_siswa" value="<?= user()->fullname ?>" readonly>
                        <input type="hidden" name="user_id" value="<?= user()->id ?>" readonly>
                    </div>
                    <div class="row">
                        <div class="form-group mb-3 col-12 col-md-4">
                            <label class="form-label">Email<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="email" value="<?= user()->email ?>" readonly>
                        </div>
                        <div class="form-group mb-3 col-12 col-md-4">
                            <label class="form-label">Nomor Handphone<span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="no_hp" value="<?= user()->no_telp ?>" readonly>
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

                    <div class="step-navigation">
                        <button type="button" class="btn btn-light" data-stepper-prev="1">
                            <i class="fas fa-arrow-left mr-2"></i> Sebelumnya
                        </button>
                        <button type="button" class="btn btn-primary" data-stepper-next="3">
                            Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step Content 3: Pendidikan -->
                <div id="step3-content" class="step-content">

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

                    <div class="step-navigation">
                        <button type="button" class="btn btn-light" data-stepper-next="2">
                            <i class="fas fa-arrow-left mr-2"></i> Sebelumnya
                        </button>
                        <button type="button" class="btn btn-primary" data-stepper-next="4">
                            Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step Content 4: Domisili -->
                <div id="step4-content" class="step-content">

                    <div class="row">
                        <div class="form-group mb-3 col-12 col-md-6">
                            <label class="form-label">Provinsi<span class="text-danger">*</span></label>
                            <select class="form-control select2 <?php if (session('errors.provinsi')) : ?>is-invalid<?php endif ?>" name="provinsi" id="id_provinsi">
                                <option value="">Provinsi</option>
                                <?php
                                foreach ($provinsi as $key => $row) {
                                    $selected = (old('provinsi') == $row['id']) ? 'selected' : '';
                                    echo '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['nama'] . '</option>';
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
                                <!-- Will be populated by JavaScript -->
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
                                <!-- Will be populated by JavaScript -->
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
                                <!-- Will be populated by JavaScript -->
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

                    <div class="step-navigation">
                        <button type="button" class="btn btn-light" data-stepper-prev="3">
                            <i class="fas fa-arrow-left mr-2"></i> Sebelumnya
                        </button>
                        <button type="button" class="btn btn-primary" data-stepper-next="5">
                            Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step Content 5: Pekerjaan -->
                <div id="step5-content" class="step-content">

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

                    <div class="step-navigation">
                        <button type="button" class="btn btn-light" data-stepper-prev="4">
                            <i class="fas fa-arrow-left mr-2"></i> Sebelumnya
                        </button>
                        <button type="button" class="btn btn-primary" data-stepper-next="6">
                            Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step Content 7: Dokumen -->
                <div id="step6-content" class="step-content">

                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="form-group">
                                <label class="form-label" for="">Pas Foto<span class="text-danger">*</span></label>
                                <input type="file" name="pas_foto" class=" <?php if (session('errors.pas_foto')) : ?>is-invalid<?php endif ?>">
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
                                <label class="form-label" for="">Identitas Pribadi (KTP / Kartu Pelajar)<span class="text-danger">*</span></label>
                                <input type="file" name="file_ktp" class=" <?php if (session('errors.file_ktp')) : ?>is-invalid<?php endif ?>">
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
                                <input type="file" name="bukti_pendidikan" class=" <?php if (session('errors.bukti_pendidikan')) : ?>is-invalid<?php endif ?>">
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
                                <input type="file" name="raport" class="<?php if (session('errors.raport')) : ?>is-invalid<?php endif ?>">
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
                                <input type="file" name="sertifikat_pkl" class="<?php if (session('errors.sertifikat_pkl')) : ?>is-invalid<?php endif ?>">
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


                    <div class="step-navigation">
                        <button type="button" class="btn btn-light" data-stepper-next="5">
                            <i class="fas fa-arrow-left mr-2"></i> Sebelumnya
                        </button>
                        <button type="submit" class="btn btn-success" id="submit-button">
                            <span id="button-content"><i class="fas fa-check-circle mr-2"></i> Kirim Pengajuan</span>
                            <span id="spinner" class="spinner-border text-light d-none" role="status" style="width: 1rem; height: 1rem;">
                                <span class="sr-only">Loading...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<?= $this->endSection() ?>

<?= $this->section("js"); ?>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize stepper and expose it globally
        window.stepperInstance = new Stepper();

        // Form submission event
        document.getElementById('multi-step-form')?.addEventListener('submit', function(e) {
            // Show loading spinner
            const buttonContent = document.getElementById('button-content');
            const spinner = document.getElementById('spinner');

            if (buttonContent && spinner) {
                buttonContent.classList.add('d-none');
                spinner.classList.remove('d-none');
            }

            // Form submission continues normally
        });
    });

    /**
     * Global function to navigate directly to a step (for onclick handlers)
     */
    function goToStep(step) {
        if (window.stepperInstance) {
            window.stepperInstance.goToStep(step);
        }
    }

    /**
     * Stepper class for managing multi-step forms
     */
    class Stepper {
        constructor(initialStep = 1) {
            this.currentStep = initialStep;
            this.init();
        }

        /**
         * Initialize the stepper
         */
        init() {
            // Set up initial state
            this.goToStep(this.currentStep, false);

            // Set up navigation buttons
            this.setupEventListeners();
        }

        /**
         * Set up event listeners for next/prev buttons
         */
        setupEventListeners() {
            // Find all next buttons
            document.querySelectorAll('[data-stepper-next]').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const nextStep = parseInt(button.getAttribute('data-stepper-next'));
                    this.nextStep(this.currentStep, nextStep);
                });
            });

            // Find all previous buttons
            document.querySelectorAll('[data-stepper-prev]').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const prevStep = parseInt(button.getAttribute('data-stepper-prev'));
                    this.prevStep(this.currentStep, prevStep);
                });
            });

            // The stepper item click events are already handled by the inline onclick attributes
        }

        /**
         * Navigate to next step with optional validation
         */
        nextStep(currentStep, nextStep) {
            // Can add validation here before proceeding
            const isValid = this.validateStep(currentStep);

            if (isValid) {
                this.goToStep(nextStep);
            }
        }

        /**
         * Navigate to previous step
         */
        prevStep(currentStep, prevStep) {
            this.goToStep(prevStep);
        }

        /**
         * Validate the current step (can be extended)
         */
        validateStep(step) {
            // Add your validation logic here
            // For example:
            // const inputs = document.querySelectorAll(`#step${step}-content .required`);
            // let valid = true;
            // inputs.forEach(input => {
            //     if (!input.value) valid = false;
            // });
            // return valid;

            return true; // Default to true for now
        }

        /**
         * Go to a specific step with animation
         */
        goToStep(step, animate = true) {
            // Store the current step
            this.currentStep = step;

            // Hide all step contents and make all stepper items inactive
            document.querySelectorAll('.step-content').forEach(content => {
                content.classList.remove('active');
                if (animate) {
                    content.style.animation = '';
                }
            });

            // Update stepper items (circles/indicators)
            this.updateStepperItems(step);

            // Show the current step content with animation
            const currentContent = document.getElementById(`step${step}-content`);
            if (currentContent) {
                currentContent.classList.add('active');
                if (animate) {
                    currentContent.style.animation = 'fadeIn 0.5s ease forwards';
                }
            }

            // Scroll to top of the form
            const stepperWrapper = document.querySelector('.stepper-wrapper');
            if (stepperWrapper) {
                stepperWrapper.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            // Trigger a custom event that other code can listen for
            document.dispatchEvent(new CustomEvent('stepper:changed', {
                detail: {
                    step: this.currentStep
                }
            }));
        }

        /**
         * Update the stepper indicator items
         */
        updateStepperItems(step) {
            document.querySelectorAll('.stepper-item').forEach(item => {
                const itemStep = parseInt(item.getAttribute('data-step'));

                // Clear all states
                item.classList.remove('active', 'completed');

                if (itemStep < step) {
                    item.classList.add('completed');
                } else if (itemStep === step) {
                    item.classList.add('active');
                }
            });

            // Also update any potential step title/heading if it exists
            const stepTitle = document.getElementById('current-step-title');
            const activeStepItem = document.querySelector(`.stepper-item[data-step="${step}"]`);

            if (stepTitle && activeStepItem) {
                const stepLabel = activeStepItem.querySelector('.step-label')?.textContent || '';
                stepTitle.textContent = stepLabel;
            }
        }
    }
</script>
<?= $this->endSection() ?>