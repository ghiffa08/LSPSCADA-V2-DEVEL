<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <form id="setting-form" action="<?= site_url('/update-apl1'); ?>" method="POST">
            <div class="card">
                <div class="card-header">
                    <h4>Rincian Data Pemohon</h4>
                </div>
                <div class="card-body">
                    <?= csrf_field(); ?>
                    <div class="form-group">
                        <label>Nama Lengkap<span class="text-danger">*</span></label>
                        <input type="text" name="id_apl1" value="<?= $dataAPL1['id_apl1'] ?>">
                        <input type="text" name="id_siswa" value="<?= $dataAPL1['id_siswa'] ?>">
                        <input type="text" class="form-control <?php if (session('errors.nama_siswa')) : ?>is-invalid<?php endif ?>" name="nama_siswa" value="<?= $dataAPL1['nama_siswa'] ?>" readonly>
                        <?php if (session('errors.nama_siswa')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.nama_siswa') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label>No. KTP/NIK/Paspor<span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php if (session('errors.ktp')) : ?>is-invalid<?php endif ?>" name="ktp" value="<?= $dataAPL1['nik'] ?>">
                        <?php if (session('errors.ktp')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.ktp') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-4">
                            <label>Email<span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php if (session('errors.email')) : ?>is-invalid<?php endif ?>" name="email" value="<?= $dataAPL1['email'] ?>" readonly>
                            <?php if (session('errors.email')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.email') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label>No Handphone<span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php if (session('errors.no_hp')) : ?>is-invalid<?php endif ?>" name="no_hp" value="<?= $dataAPL1['no_hp'] ?>">
                            <?php if (session('errors.no_hp')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.no_hp') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class=" form-group col-12 col-md-4">
                            <label>No Telpon</label>
                            <input type="text" class="form-control <?php if (session('errors.telpon_rumah')) : ?>is-invalid<?php endif ?>" name="telpon_rumah" value="<?= $dataAPL1['telpon_rumah'] ?>">
                            <?php if (session('errors.telpon_rumah')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.telpon_rumah') ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label>Tempat Lahir<span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php if (session('errors.tempat_lahir')) : ?>is-invalid<?php endif ?>" name="tempat_lahir" value="<?= $dataAPL1['tempat_lahir'] ?>">
                            <?php if (session('errors.tempat_lahir')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.tempat_lahir') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label>Tanggal Lahir<span class="text-danger">*</span></label>
                            <input type="date" class="form-control <?php if (session('errors.tanggal_lahir')) : ?>is-invalid<?php endif ?>" name="tanggal_lahir" value="<?= $dataAPL1['tanggal_lahir'] ?>">
                            <?php if (session('errors.tanggal_lahir')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.tanggal_lahir') ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin<span class="text-danger">*</span></label>
                        <select class="form-control selectric <?php if (session('errors.jenis_kelamin')) : ?>is-invalid<?php endif ?>" name="jenis_kelamin">
                            <option value="Laki-Laki" <?php if ($dataAPL1['jenis_kelamin'] == 'Laki-Laki') echo 'selected'; ?>>Laki-Laki</option>
                            <option value="Perempuan" <?php if ($dataAPL1['jenis_kelamin'] == 'Perempuan') echo 'selected'; ?>>Perempuan</option>
                        </select>
                        <?php if (session('errors.jenis_kelamin')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.jenis_kelamin') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label>Kebangsaan<span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php if (session('errors.kebangsaan')) : ?>is-invalid<?php endif ?>" name="kebangsaan" placeholder="WNI/WNA" value="<?= $dataAPL1['kebangsaan'] ?>">
                        <?php if (session('errors.kebangsaan')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.kebangsaan') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label>Kualifikasi Pendidikan<span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php if (session('errors.pendidikan')) : ?>is-invalid<?php endif ?>" name="pendidikan" value="<?= $dataAPL1['pendidikan'] ?>">
                        <?php if (session('errors.pendidikan')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.pendidikan') ?>
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
                                <option>Provinsi</option>
                                <?php
                                foreach ($provinsi as $key => $row) {
                                    $dataAPL1['provinsi'] == $row['id'] ? $pilih = 'selected' : $pilih = null;

                                    echo '<option  ' . $pilih . ' value="' . $row['id'] . '">' . $row['nama'] . '</option>';
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
                                ?>
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
                            <input type="text" class="form-control <?php if (session('errors.rt')) : ?>is-invalid<?php endif ?>" name="rt" value="<?= $dataAPL1['rt'] ?>">
                            <?php if (session('errors.rt')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.rt') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label>RW<span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php if (session('errors.rw')) : ?>is-invalid<?php endif ?>" name="rw" value="<?= $dataAPL1['rw'] ?>">
                            <?php if (session('errors.rw')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.rw') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label>Kode Pos<span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php if (session('errors.kode_pos')) : ?>is-invalid<?php endif ?>" name="kode_pos" value="<?= $dataAPL1['kode_pos'] ?>">
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
                        <label>Nama Instansi<span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php if (session('errors.nama_lembaga')) : ?>is-invalid<?php endif ?>" name="nama_lembaga" value="<?= $dataAPL1['nama_lembaga'] ?>">
                        <?php if (session('errors.nama_lembaga')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.nama_lembaga') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label>Jurusan<span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php if (session('errors.jurusan')) : ?>is-invalid<?php endif ?>" name="jurusan" value="<?= $dataAPL1['jurusan'] ?>">
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
                    <h4>Data Sertifikasi</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Skema</label>
                        <select class="form-control selectric" name="id_skema">
                            <option value="">Pilih Skema</option>
                            <?php

                            if (isset($listSkema)) {
                                foreach ($listSkema as $row) {
                                    $dataAPL1['id_skema'] == $row['id_skema'] ? $pilih = 'selected' : $pilih = null;
                                    echo '<option ' . $pilih . ' value="' . $row['id_skema'] . '">' . $row['nama_skema'] . '</option>';
                                }
                            }

                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tujuan Asesmen</label>
                        <div class="selectgroup w-100">
                            <label class="selectgroup-item">
                                <input type="radio" name="tujuan" value="Sertifikasi" class="selectgroup-input" <?php if ($dataAPL1['tujuan'] == 'Sertifikasi') echo 'checked'; ?>>
                                <span class="selectgroup-button">Sertifikasi</span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="tujuan" value="PKT" class="selectgroup-input" value="Sertifikasi" class="selectgroup-input" <?php if ($dataAPL1['tujuan'] == 'PKT') echo 'checked'; ?>>
                                <span class="selectgroup-button">Pengakuan Kompetensi Terkini (PKT)</span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="tujuan" value="RPL" class="selectgroup-input" value="Sertifikasi" class="selectgroup-input" <?php if ($dataAPL1['tujuan'] == 'RPL') echo 'checked'; ?>>
                                <span class="selectgroup-button">Rekognisi Pembelajaran Lampau (RPL)</span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="radio" name="tujuan" value="Lainya" class="selectgroup-input" value="Sertifikasi" class="selectgroup-input" <?php if ($dataAPL1['tujuan'] == 'Lainya') echo 'checked'; ?>>
                                <span class="selectgroup-button">Lainya</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
        <form action="<?= site_url('/store-dokumen-apl1') ?>" method="POST" enctype="multipart/form-data">
            <div class="card">
                <div class="card-header">
                    <h4>Dokumen Administratif</h4>
                </div>
                <div class="card-body">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="id" value="<?= $dataAPL1['id_apl1'] ?>">
                    <!-- <div class="section-title">Bukti Administratif</div> -->
                    <div class="form-group">
                        <label>Raport SMA/SMK<span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input <?php if (session('errors.pas_foto')) : ?>is-invalid<?php endif ?>" name="pas_foto">
                            <label class="custom-file-label" for="customFile">Choose file</label>
                        </div>
                        <?php if (session('errors.pas_foto')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.pas_foto') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label>Sertifikat/Surat Keterangan PKL<span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input <?php if (session('errors.pas_foto')) : ?>is-invalid<?php endif ?>" name="pas_foto">
                            <label class="custom-file-label" for="customFile">Choose file</label>
                        </div>
                        <?php if (session('errors.pas_foto')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.pas_foto') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label>Pas Foto 3x4 <?= (isset($dataAPL1['pas_foto']) ? '<span class="text-success"><i class="fas fa-check-circle"></i>Kertu Pelajar</span>' : ''); ?><span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input <?php if (session('errors.pas_foto')) : ?>is-invalid<?php endif ?>" name="pas_foto">
                            <label class="custom-file-label" for="customFile">Choose file</label>
                        </div>
                        <?php if (session('errors.pas_foto')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.pas_foto') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label>KTP/Kartu keluarga <?= (isset($dataAPL1['ktp']) ? '<span class="text-success"><i class="fas fa-check-circle"></i>Kertu Pelajar</span>' : ''); ?><span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input <?php if (session('errors.ktp')) : ?>is-invalid<?php endif ?>" name="file_ktp">
                            <label class="custom-file-label" for="customFile">Choose file</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Kartu Pelajar <?= (isset($dataAPL1['kartu_pelajar']) ? '<span class="text-success"><i class="fas fa-check-circle"></i>Kertu Pelajar</span>' : ''); ?><span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input <?php if (session('errors.kartu_pelajar')) : ?>is-invalid<?php endif ?>" name="kartu_pelajar">
                            <label class="custom-file-label" for="customFile">Choose file</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>


<?= $this->endSection() ?>