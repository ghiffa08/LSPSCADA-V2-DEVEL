<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<?php if (isset($listSkemaSiswa['id_skema'])) { ?>
    <div class="row">
        <div class="col-12">
            <form id="setting-form" action="<?= site_url('/store-apl1'); ?>" method="POST">
                <div class="card">
                    <div class="card-header">
                        <h4>Data Diri Pemohon</h4>
                    </div>
                    <div class="card-body">
                        <?= csrf_field(); ?>
                        <div class="form-group">
                            <label>Nama Lengkap<span class="text-danger">*</span></label>
                            <input type="hidden" name="id_siswa" value="<?= user()->id ?>">
                            <input type="text" class="form-control <?php if (session('errors.nama_siswa')) : ?>is-invalid<?php endif ?>" name="nama_siswa" value="<?= isset($dataAPL1['nama_siswa']) ? $dataAPL1['nama_siswa'] : user()->fullname ?>" readonly>
                            <?php if (session('errors.nama_siswa')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.nama_siswa') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-4">
                                <label>Email<span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php if (session('errors.email')) : ?>is-invalid<?php endif ?>" name="email" value="<?= isset($dataAPL1['email']) ? $dataAPL1['email'] : user()->email ?>" readonly>
                                <?php if (session('errors.email')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.email') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group col-12 col-md-4">
                                <label>Nomor Handphone<span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php if (session('errors.no_hp')) : ?>is-invalid<?php endif ?>" name="no_hp" value="<?= isset($dataAPL1['no_hp']) ? $dataAPL1['no_hp'] : user()->no_telp ?>" readonly>
                                <?php if (session('errors.no_hp')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.no_hp') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class=" form-group col-12 col-md-4">
                                <label>Nomor Telpon</label>
                                <input type="text" class="form-control <?php if (session('errors.telpon_rumah')) : ?>is-invalid<?php endif ?>" name="telpon_rumah" value="<?= isset($dataAPL1['telpon_rumah']) ? $dataAPL1['telpon_rumah'] : old('telpon_rumah') ?>" placeholder="Masukan Nomor Telpon Rumah">
                                <?php if (session('errors.telpon_rumah')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.telpon_rumah') ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nomor KTP/NIK/Paspor<span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php if (session('errors.ktp')) : ?>is-invalid<?php endif ?>" name="ktp" value="<?= isset($dataAPL1['nik']) ? $dataAPL1['nik'] : old('ktp') ?>" placeholder="Masukan Nomor Induk Kependudukan">
                            <?php if (session('errors.ktp')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.ktp') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6">
                                <label>Tempat Lahir<span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php if (session('errors.tempat_lahir')) : ?>is-invalid<?php endif ?>" name="tempat_lahir" value="<?= isset($dataAPL1['tempat_lahir']) ? $dataAPL1['tempat_lahir'] : old('tempat_lahir') ?>" placeholder="Masukan Tempat Lahir">
                                <?php if (session('errors.tempat_lahir')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.tempat_lahir') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group col-12 col-md-6">
                                <label>Tanggal Lahir<span class="text-danger">*</span></label>
                                <input type="date" class="form-control <?php if (session('errors.tanggal_lahir')) : ?>is-invalid<?php endif ?>" name="tanggal_lahir" value="<?= isset($dataAPL1['tanggal_lahir']) ? $dataAPL1['tanggal_lahir'] : old('tanggal_lahir') ?>">
                                <?php if (session('errors.tanggal_lahir')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.tanggal_lahir') ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jenis Kelamin</label>
                            <div class="selectgroup w-100 <?php if (session('errors.jenis_kelamin')) : ?>is-invalid<?php endif ?>">
                                <label class="selectgroup-item">
                                    <input type="radio" name="jenis_kelamin" value="Laki-Laki" class="selectgroup-input" <?php echo (isset($dataAPL1['jenis_kelamin']) && $dataAPL1['jenis_kelamin'] == "Laki-Laki") ? "checked" : (old('jenis_kelamin') == "Laki-Laki" ? "checked" : ""); ?>>
                                    <span class="selectgroup-button">Laki-Laki</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="jenis_kelamin" value="Perempuan" class="selectgroup-input" <?php echo (isset($dataAPL1['jenis_kelamin']) && $dataAPL1['jenis_kelamin'] == "Perempuan") ? "checked" : (old('jenis_kelamin') == "Perempuan" ? "checked" : ""); ?>>
                                    <span class="selectgroup-button">Perempuan</span>
                                </label>
                            </div>
                            <?php if (session('errors.jenis_kelamin')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.jenis_kelamin') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label>Kebangsaan<span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php if (session('errors.kebangsaan')) : ?>is-invalid<?php endif ?>" name="kebangsaan" placeholder="WNI/WNA" value="<?= isset($dataAPL1['kebangsaan']) ? $dataAPL1['kebangsaan'] : old('kebangsaan') ?>">
                            <?php if (session('errors.kebangsaan')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.kebangsaan') ?>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Riwayat Pendidikan Pemohon</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Pendidikan Terakhir<span class="text-danger">*</span></label>
                            <select name="pendidikan_terakhir" class="form-control select2 <?php if (session('errors.pendidikan_terakhir')) : ?>is-invalid<?php endif ?>">
                                <option value="">Pilih Pendidikan Terakhir</option>
                                <option value="SD" <?php echo (isset($dataAPL1['pendidikan_terakhir']) && $dataAPL1['pendidikan_terakhir'] == "SD") || old('pendidikan_terakhir') == "SD" ? "selected" : ""; ?>>SD</option>
                                <option value="SMP" <?php echo (isset($dataAPL1['pendidikan_terakhir']) && $dataAPL1['pendidikan_terakhir'] == "SMP") || old('pendidikan_terakhir') == "SMP" ? "selected" : ""; ?>>SMP</option>
                                <option value="SMA/SMK" <?php echo (isset($dataAPL1['pendidikan_terakhir']) && $dataAPL1['pendidikan_terakhir'] == "SMA/SMK") || old('pendidikan_terakhir') == "SMA/SMK" ? "selected" : ""; ?>>SMA/SMK</option>
                                <option value="Diploma" <?php echo (isset($dataAPL1['pendidikan_terakhir']) && $dataAPL1['pendidikan_terakhir'] == "Diploma") || old('pendidikan_terakhir') == "Diploma" ? "selected" : ""; ?>>Diploma</option>
                                <option value="Sarjana" <?php echo (isset($dataAPL1['pendidikan_terakhir']) && $dataAPL1['pendidikan_terakhir'] == "Sarjana") || old('pendidikan_terakhir') == "Sarjana" ? "selected" : ""; ?>>Sarjana</option>
                                <option value="Magister" <?php echo (isset($dataAPL1['pendidikan_terakhir']) && $dataAPL1['pendidikan_terakhir'] == "Magister") || old('pendidikan_terakhir') == "Magister" ? "selected" : ""; ?>>Magister</option>
                                <option value="Doktor" <?php echo (isset($dataAPL1['pendidikan_terakhir']) && $dataAPL1['pendidikan_terakhir'] == "Doktor") || old('pendidikan_terakhir') == "Doktor" ? "selected" : ""; ?>>Doktor</option>
                            </select>
                            <?php if (session('errors.pendidikan_terakhir')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.pendidikan_terakhir') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label>Nama Sekolah / Perguruan Tinggi<span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php if (session('errors.nama_sekolah')) : ?>is-invalid<?php endif ?>" name="nama_sekolah" placeholder="Masukan Nama Sekolah Atau Perguruan Tinggi" value="<?= isset($dataAPL1['nama_sekolah']) ? $dataAPL1['nama_sekolah'] : old('nama_sekolah') ?>">
                            <?php if (session('errors.nama_sekolah')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.nama_sekolah') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label>Jurusan / Program Studi<span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php if (session('errors.jurusan')) : ?>is-invalid<?php endif ?>" name="jurusan" value="<?= isset($dataAPL1['jurusan']) ? $dataAPL1['jurusan'] : old('jurusan') ?>" placeholder="Masukan Nama Jurusan Atau Program Studi">
                            <?php if (session('errors.jurusan')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.jurusan') ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Alamat Domisili Pemohon</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6">
                                <label>Provinsi<span class="text-danger">*</span></label>
                                <select class="form-control select2 <?php if (session('errors.provinsi')) : ?>is-invalid<?php endif ?>" name="provinsi" id="id_provinsi">
                                    <option value="">Provinsi</option>
                                    <?php
                                    foreach ($provinsi as $key => $row) {

                                        isset($dataAPL1['provinsi']) && $dataAPL1['provinsi'] == $row['id'] ? $pilih = 'selected' : $pilih = null;

                                        echo '<option ' . $pilih . '  value="' . $row['id'] . '">' . $row['nama'] . '</option>';
                                    }
                                    ?>

                                </select>
                                <?php if (session('errors.provinsi')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.provinsi') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group col-12 col-md-6">
                                <label>Kabupaten/Kota<span class="text-danger">*</span></label>
                                <select class="form-control select2 <?php if (session('errors.kabupaten')) : ?>is-invalid<?php endif ?>" name="kabupaten" id="id_kabupaten">
                                    <option value="">Kabupaten/Kota</option>
                                    <option value="<?= isset($dataAPL1['kabupaten']) ? $dataAPL1['kabupaten'] : '' ?>" <?= isset($dataAPL1['kabupaten']) ? 'selected' : '' ?>><?= isset($dataAPL1['nama_kabupaten']) ? $dataAPL1['nama_kabupaten'] : '' ?></option>

                                </select>
                                <?php if (session('errors.kabupaten')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.kabupaten') ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6">
                                <label>Kecamatan<span class="text-danger">*</span></label>
                                <select class="form-control select2 <?php if (session('errors.kecamatan')) : ?>is-invalid<?php endif ?>" name="kecamatan" id="id_kecamatan">
                                    <option value="">Kecamatan</option>
                                    <option value="<?= isset($dataAPL1['kecamatan']) ? $dataAPL1['kecamatan'] : '' ?>" <?= isset($dataAPL1['kecamatan']) ? 'selected' : '' ?>><?= isset($dataAPL1['nama_kecamatan']) ? $dataAPL1['nama_kecamatan'] : '' ?></option>
                                </select>
                                <?php if (session('errors.kecamatan')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.kecamatan') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group col-12 col-md-6">
                                <label>Kelurahan/Desa<span class="text-danger">*</span></label>
                                <select class="form-control select2 <?php if (session('errors.kelurahan')) : ?>is-invalid<?php endif ?>" name="kelurahan" id="id_desa">
                                    <option value="">Kelurahan/Desa</option>
                                    <option value="<?= isset($dataAPL1['kelurahan']) ? $dataAPL1['kelurahan'] : '' ?>" <?= isset($dataAPL1['kelurahan']) ? 'selected' : '' ?>><?= isset($dataAPL1['nama_kelurahan']) ? $dataAPL1['nama_kelurahan'] : '' ?></option>
                                </select>
                                <?php if (session('errors.kelurahan')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.kelurahan') ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-4">
                                <label>RT<span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php if (session('errors.rt')) : ?>is-invalid<?php endif ?>" name="rt" value="<?= isset($dataAPL1['rt']) ? $dataAPL1['rt'] : old('rt') ?>">
                                <?php if (session('errors.rt')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.rt') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group col-12 col-md-4">
                                <label>RW<span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php if (session('errors.rw')) : ?>is-invalid<?php endif ?>" name="rw" value="<?= isset($dataAPL1['rw']) ? $dataAPL1['rw'] : old('rw') ?>">
                                <?php if (session('errors.rw')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.rw') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group col-12 col-md-4">
                                <label>Kode Pos<span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php if (session('errors.kode_pos')) : ?>is-invalid<?php endif ?>" name="kode_pos" value="<?= isset($dataAPL1['kode_pos']) ? $dataAPL1['kode_pos'] : old('kode_pos') ?>">
                                <?php if (session('errors.kode_pos')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.kode_pos') ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4>Data Pekerjaan</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Pekerjaan<span class="text-danger">*</span></label>
                            <select name="pekerjaan" class="form-control select2 <?php if (session('errors.pekerjaan')) : ?>is-invalid<?php endif ?>">
                                <option value="">Pilih Pekerjaan</option>
                                <option value="Pelajar/Mahasiswa" <?php echo (isset($dataAPL1['pekerjaan']) && $dataAPL1['pekerjaan'] == "Pelajar/Mahasiswa") || old('pekerjaan') == "Pelajar/Mahasiswa" ? "selected" : ""; ?>>Pelajar/Mahasiswa</option>
                                <option value="Pegawai" <?php echo (isset($dataAPL1['pekerjaan']) && $dataAPL1['pekerjaan'] == "Pegawai") || old('pekerjaan') == "Pegawai" ? "selected" : ""; ?>>Pegawai</option>
                                <option value="Wiraswasta" <?php echo (isset($dataAPL1['pekerjaan']) && $dataAPL1['pekerjaan'] == "Wiraswasta") || old('pekerjaan') == "Wiraswasta" ? "selected" : ""; ?>>Wiraswasta</option>
                                <option value="Petani" <?php echo (isset($dataAPL1['pekerjaan']) && $dataAPL1['pekerjaan'] == "Petani") || old('pekerjaan') == "Petani" ? "selected" : ""; ?>>Petani</option>
                                <option value="Guru" <?php echo (isset($dataAPL1['pekerjaan']) && $dataAPL1['pekerjaan'] == "Guru") || old('pekerjaan') == "Guru" ? "selected" : ""; ?>>Guru</option>
                                <option value="Dokter" <?php echo (isset($dataAPL1['pekerjaan']) && $dataAPL1['pekerjaan'] == "Dokter") || old('pekerjaan') == "Dokter" ? "selected" : ""; ?>>Dokter</option>
                            </select>
                            <?php if (session('errors.pekerjaan')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.pekerjaan') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label>Nama Instansi</label>
                            <input type="text" class="form-control <?php if (session('errors.nama_lembaga')) : ?>is-invalid<?php endif ?>" name="nama_lembaga" value="<?= isset($dataAPL1['nama_lembaga']) ? $dataAPL1['nama_lembaga'] : old('nama_lembaga') ?>" placeholder="Organisasi / Tempat Kerja / Instansi Terkait / Freelance / - (bila tidak ada)">
                            <?php if (session('errors.nama_lembaga')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.nama_lembaga') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label>Jabatan</label>
                            <input type="text" class="form-control <?php if (session('errors.jabatan')) : ?>is-invalid<?php endif ?>" name="jabatan" value="<?= isset($dataAPL1['jabatan']) ? $dataAPL1['jabatan'] : old('jabatan') ?>" placeholder="Jabatan di Perusahaan">
                            <?php if (session('errors.jabatan')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.jabatan') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label>Alamat Lembaga / Perusahaan</label>
                            <textarea class="form-control <?php if (session('errors.alamat_perusahaan')) : ?>is-invalid<?php endif ?>" name="alamat_perusahaan" id="inputDescription"><?= isset($dataAPL1['alamat_perusahaan']) ? $dataAPL1['alamat_perusahaan'] : old('alamat_perusahaan') ?></textarea>
                            <?php if (session('errors.alamat_perusahaan')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.alamat_perusahaan') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-6">
                                <label>Email Perusahaan</label>
                                <input type="text" class="form-control <?php if (session('errors.email_perusahaan')) : ?>is-invalid<?php endif ?>" name="email_perusahaan" value="<?= isset($dataAPL1['email_perusahaan']) ? $dataAPL1['email_perusahaan'] : old('email_perusahaan') ?>" placeholder="Masukan Nomor Email Perusahaan">
                                <?php if (session('errors.email_perusahaan')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.email_perusahaan') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group col-12 col-md-6">
                                <label>Nomor Telp Perusahaan</label>
                                <input type="text" class="form-control <?php if (session('errors.no_telp_perusahaan')) : ?>is-invalid<?php endif ?>" name="no_telp_perusahaan" value="<?= isset($dataAPL1['no_telp_perusahaan']) ? $dataAPL1['no_telp_perusahaan'] : old('no_telp_perusahaan') ?>" placeholder="Masukan Nomor Telpon Perusahaan">
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
                    <div class="card-header">
                        <h4>Data Sertifikasi</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Skema</label>
                            <select class="form-control selectric" name="id_skema" readonly>
                                <option value="<?= isset($listSkemaSiswa['id_skema']) ? $listSkemaSiswa['id_skema'] : '' ?>" selected>
                                    <?= isset($listSkemaSiswa['nama_skema']) ? $listSkemaSiswa['nama_skema'] : '' ?>
                                </option>
                            </select>
                        </div>

                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i><span class="pl-2">Upload Data Diri</span></button>
                    </div>
                </div>
            </form>

            <form action="<?= site_url('/store-dokumen-apl1') ?>" method="POST" enctype="multipart/form-data">
                <div class="card">
                    <div class="card-header">
                        <h4>Bukti Kelengkapan Pemohon</h4>
                    </div>
                    <div class="card-body">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= isset($dataAPL1['id_apl1']) ? $dataAPL1['id_apl1'] : '' ?>">
                        <!-- <div class="section-title">Bukti Administratif</div> -->
                        <?php if (session()->has('errors')) : ?>
                            <ul class="alert alert-danger">
                                <?php foreach (session('errors') as $error) : ?>
                                    <li><?= $error ?></li>
                                <?php endforeach ?>
                            </ul>
                        <?php endif ?>
                        <div class="form-row">
                            <div class="form-group col-12 col-md-4">
                                <label>Pas Foto<?= (isset($dataAPL1['pas_foto'])) ? '<span class="text-success"><i class="fas fa-check-circle"></i>Pas Foto</span>' : '<span class="text-danger">*</span>'; ?></label>
                                <div id="image-preview" class="image-preview">
                                    <label for="image-upload" id="image-label">Choose File</label>
                                    <input type="file" name="pas_foto" id="image-upload" />
                                </div>
                                <small>Maks ukuran upload 2 MB, jpg,jpeg,png</small>
                            </div>
                            <div class="form-group col-12 col-md-4">
                                <label>Identitas Pribadi (KTP / Kartu Pelajar)<?= (isset($dataAPL1['ktp'])) ? '<span class="text-success"><i class="fas fa-check-circle"></i>Identitas Pribadi</span>' : '<span class="text-danger">*</span>'; ?></label>
                                <div id="ktp-preview" class="image-preview">
                                    <label for="ktp-upload" id="ktp-label">Choose File</label>
                                    <input type="file" name="file_ktp" id="ktp-upload" />
                                </div>
                            </div>
                            <div class="form-group col-12 col-md-4">
                                <label>Bukti Pendidikan <?= (isset($dataAPL1['bukti_pendidikan'])) ? '<span class="text-success"><i class="fas fa-check-circle"></i>Bukti Pendidikan</span>' : '<span class="text-danger">*</span>'; ?></label>
                                <div id="kartu-pelajar-preview" class="image-preview">
                                    <label for="kartu-pelajar-upload" id="kartu-pelajar-label">Choose File</label>
                                    <input type="file" name="bukti_pendidikan" id="kartu-pelajar-upload" />
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i><span class="pl-2">Upload Dokumen</span></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php } else { ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state" data-height="400">
                <div class="empty-state-icon bg-danger">
                    <i class="fas fa-times"></i>
                </div>
                <h2>Skema Belum Dipilih!</h2>
                <p class="lead">
                    Untuk melanjutkan, harap pilih skema terlebih dahulu.
                </p>

                <a href="<?= site_url('/skema-siswa') ?>" class="btn btn-primary mt-4">Tambah Skema</a>
            </div>
        </div>
    </div>
<?php } ?>



<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script>
    $(document).ready(function() {
        $("#id_provinsi").change(function(e) {
            var id_provinsi = $("#id_provinsi").val();
            $.ajax({
                type: "POST",
                url: "<?= base_url('/kabupaten') ?>",
                data: {
                    id_provinsi: id_provinsi
                },
                success: function(response) {
                    $("#id_kabupaten").html(response);
                }
            });
        });
        $("#id_kabupaten").change(function(e) {
            var id_kabupaten = $("#id_kabupaten").val();
            $.ajax({
                type: "POST",
                url: "<?= base_url('/kecamatan') ?>",
                data: {
                    id_kabupaten: id_kabupaten
                },
                success: function(response) {
                    $("#id_kecamatan").html(response);
                }
            });
        });
        $("#id_kecamatan").change(function(e) {
            var id_kecamatan = $("#id_kecamatan").val();
            $.ajax({
                type: "POST",
                url: "<?= base_url('/desa') ?>",
                data: {
                    id_kecamatan: id_kecamatan
                },
                success: function(response) {
                    $("#id_desa").html(response);
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>