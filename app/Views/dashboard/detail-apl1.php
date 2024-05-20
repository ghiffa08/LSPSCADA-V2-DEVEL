<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="card">
    <div class="card-body">
        <div class="embed-responsive embed-responsive-4by3">
            <iframe class="embed-responsive-item" src="/apl1-pdf-<?= $dataAPL1['id_apl1'] ?>"></iframe>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Pas Foto</h4>
                <div class="card-header-action">
                    <a href="#" class="btn btn-primary">Download</a>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-2 text-muted">Click untuk melihat detail!</div>
                <div class="chocolat-parent">
                    <a href="<?= base_url('upload/pas foto/' . $dataAPL1['pas_foto']) ?>" class="chocolat-image" title="<?= $dataAPL1['pas_foto'] ?>">
                        <div data-crop-image="285">
                            <img alt="image" src="<?= base_url('upload/pas foto/' . $dataAPL1['pas_foto']) ?>" class="img-fluid">
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Foto KTP/KK/Paspor</h4>
                <div class="card-header-action">
                    <a href="#" class="btn btn-primary">Download</a>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-2 text-muted">Click untuk melihat detail!</div>
                <div class="chocolat-parent">
                    <a href="<?= base_url('upload/ktp/' . $dataAPL1['ktp']) ?>" class="chocolat-image" title="<?= $dataAPL1['ktp'] ?>">
                        <div data-crop-image="285">
                            <img alt="image" src="<?= base_url('upload/ktp/' . $dataAPL1['ktp']) ?>" class="img-fluid">
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Foto bukti Pendidikan</h4>
                <div class="card-header-action">
                    <a target="_blank" href="" class="btn btn-primary">Download</a>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-2 text-muted">Click untuk melihat detail!</div>
                <div class="chocolat-parent">
                    <a href="<?= base_url('upload/bukti pendidikan/' . $dataAPL1['bukti_pendidikan']) ?>" class="chocolat-image" title="<?= $dataAPL1['bukti_pendidikan'] ?>">
                        <div data-crop-image="285">
                            <img alt="image" src="<?= base_url('upload/bukti pendidikan/' . $dataAPL1['bukti_pendidikan']) ?>" class="img-fluid">
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="<?= site_url('/validasi-apl1') ?>" method="POST" enctype="multipart/form-data">
    <div class="card">
        <div class="card-body">
            <?= csrf_field(); ?>
            <input type="hidden" name="id" value="<?= $dataAPL1['id_apl1'] ?>">
            <input type="hidden" name="name" value="<?= $dataAPL1['nama_siswa'] ?>">
            <input type="hidden" name="email" value="<?= $dataAPL1['email'] ?>">
            <!-- <div class="section-title">Bukti Administratif</div> -->
            <p class="mb-3 text-muted"> Berdasarkan Ketentuan Persyaratan Dasar, Maka Pemohon:</p>
            <div class="form-group mb-3">
                <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                        <input type="radio" name="validasi_apl1" value="Y" class="selectgroup-input" <?= ($dataAPL1['validasi_apl1'] === "Y") ? 'checked' : "" ?>>
                        <span class="selectgroup-button">Diterima</span>
                    </label>
                    <label class="selectgroup-item">
                        <input type="radio" name="validasi_apl1" value="N" class="selectgroup-input" <?= ($dataAPL1['validasi_apl1'] === "N") ? 'checked' : "" ?>>
                        <span class="selectgroup-button">Tidak Diterima</span>
                    </label>
                </div>
            </div>
            <p class="text-muted">Sebagai Peserta Sertifikasi.</p>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </div>
</form>

<!-- <div class="row">
    <div class="col-12">
        <form id="setting-form" action="<?= site_url('/store-apl1'); ?>" method="POST">
            <div class="card">
                <div class="card-header">
                    <h4>Rincian Data Pemohon</h4>
                </div>
                <div class="card-body">
                    <?= csrf_field(); ?>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>ID APL1</label>
                            <div class="plain-text"><?= $dataAPL1['id_apl1'] ?></div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nama Lengkap</label>
                            <input type="text" readonly class="form-control <?php if (session('errors.nama_siswa')) : ?>is-invalid<?php endif ?>" name="nama_siswa" value="<?= isset($dataAPL1['nama_siswa']) ? $dataAPL1['nama_siswa'] : user()->fullname ?>">
                            <?php if (session('errors.nama_siswa')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.nama_siswa') ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>No. KTP/NIK/Paspor</label>
                        <input type="text" readonly class="form-control <?php if (session('errors.ktp')) : ?>is-invalid<?php endif ?>" name="ktp" value="<?= isset($dataAPL1['nik']) ? $dataAPL1['nik'] : old('ktp') ?>">
                        <?php if (session('errors.ktp')) { ?>
                            <div class="invalid-feedback">
                                <?= session('errors.ktp') ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-4">
                            <label>Email</label>
                            <input type="text" readonly class="form-control <?php if (session('errors.email')) : ?>is-invalid<?php endif ?>" name="email" value="<?= isset($dataAPL1['email']) ? $dataAPL1['email'] : user()->email ?>">
                            <?php if (session('errors.email')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.email') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label>No Handphone</label>
                            <input type="text" readonly class="form-control <?php if (session('errors.no_hp')) : ?>is-invalid<?php endif ?>" name="no_hp" value="<?= isset($dataAPL1['no_hp']) ? $dataAPL1['no_hp'] : user()->no_telp ?>">
                            <?php if (session('errors.no_hp')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.no_hp') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class=" form-group col-12 col-md-4">
                            <label>No Telpon</label>
                            <input type="text" readonly class="form-control <?php if (session('errors.telpon_rumah')) : ?>is-invalid<?php endif ?>" name="telpon_rumah" value="<?= isset($dataAPL1['telpon_rumah']) ? $dataAPL1['telpon_rumah'] : old('telpon_rumah') ?>">
                            <?php if (session('errors.telpon_rumah')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.telpon_rumah') ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label>Tempat Lahir</label>
                            <input type="text" readonly class="form-control <?php if (session('errors.tempat_lahir')) : ?>is-invalid<?php endif ?>" name="tempat_lahir" value="<?= isset($dataAPL1['tempat_lahir']) ? $dataAPL1['tempat_lahir'] : old('tempat_lahir') ?>">
                            <?php if (session('errors.tempat_lahir')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.tempat_lahir') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label>Tanggal Lahir</label>
                            <input type="date" readonly class="form-control <?php if (session('errors.tanggal_lahir')) : ?>is-invalid<?php endif ?>" name="tanggal_lahir" value="<?= isset($dataAPL1['tanggal_lahir']) ? $dataAPL1['tanggal_lahir'] : old('tanggal_lahir') ?>">
                            <?php if (session('errors.tanggal_lahir')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.tanggal_lahir') ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <input type="text" readonly class="form-control" name="tanggal_lahir" value="<?= $dataAPL1['jenis_kelamin'] ?>">
                    </div>
                    <div class="form-group">
                        <label>Kebangsaan</label>
                        <input type="text" readonly class="form-control <?php if (session('errors.kebangsaan')) : ?>is-invalid<?php endif ?>" name="kebangsaan" placeholder="WNI/WNA" value="<?= isset($dataAPL1['kebangsaan']) ? $dataAPL1['kebangsaan'] : old('kebangsaan') ?>">
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
                        <input type="text" readonly class="form-control" value="<?= $dataAPL1['pendidikan_terakhir'] ?>">
                    </div>
                    <div class="form-group">
                        <label>Nama Sekolah / Perguruan Tinggi<span class="text-danger">*</span></label>
                        <input type="text" readonly class="form-control" value="<?= $dataAPL1['nama_sekolah'] ?>">
                    </div>
                    <div class="form-group">
                        <label>Jurusan / Program Studi<span class="text-danger">*</span></label>
                        <input type="text" readonly class="form-control" value="<?= $dataAPL1['jurusan'] ?>">
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
                            <label>Provinsi</label>
                            <input type="text" readonly class="form-control" value="<?= $dataAPL1['nama_provinsi'] ?>">
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label>kabupaten</label>
                            <input type="text" readonly class="form-control" value="<?= $dataAPL1['nama_kabupaten'] ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label>Kecamatan</label>
                            <input type="text" readonly class="form-control" value="<?= $dataAPL1['nama_kecamatan'] ?>">
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label>Kelurahan</label>
                            <input type="text" readonly class="form-control" value="<?= $dataAPL1['nama_kelurahan'] ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-4">
                            <label>RT</label>
                            <input type="text" readonly class="form-control <?php if (session('errors.rt')) : ?>is-invalid<?php endif ?>" name="rt" value="<?= isset($dataAPL1['rt']) ? $dataAPL1['rt'] : old('rt') ?>">
                            <?php if (session('errors.rt')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.rt') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label>RW</label>
                            <input type="text" readonly class="form-control <?php if (session('errors.rw')) : ?>is-invalid<?php endif ?>" name="rw" value="<?= isset($dataAPL1['rw']) ? $dataAPL1['rw'] : old('rw') ?>">
                            <?php if (session('errors.rw')) { ?>
                                <div class="invalid-feedback">
                                    <?= session('errors.rw') ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label>Kode Pos</label>
                            <input type="text" readonly class="form-control <?php if (session('errors.kode_pos')) : ?>is-invalid<?php endif ?>" name="kode_pos" value="<?= isset($dataAPL1['kode_pos']) ? $dataAPL1['kode_pos'] : old('kode_pos') ?>">
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
                        <input type="text" readonly class="form-control" value="<?= $dataAPL1['pekerjaan'] ?>">
                    </div>
                    <div class="form-group">
                        <label>Nama Instansi</label>
                        <input type="text" readonly class="form-control" value="<?= $dataAPL1['nama_lembaga'] ?>">
                    </div>
                    <div class="form-group">
                        <label>Jabatan</label>
                        <input type="text" readonly class="form-control" value="<?= $dataAPL1['jabatan'] ?>">
                    </div>
                    <div class="form-group">
                        <label>Alamat Lembaga / Perusahaan</label>
                        <input type="text" readonly class="form-control" value="<?= $dataAPL1['alamat_perusahaan'] ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label>Email Perusahaan</label>
                            <input type="text" readonly class="form-control" value="<?= $dataAPL1['email_perusahaan'] ?>">
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label>Nomor Telp Perusahaan</label>
                            <input type="text" readonly class="form-control" value="<?= $dataAPL1['no_telp_perusahaan'] ?>">
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
                        <input type="text" readonly class="form-control" value="<?= $dataAPL1['nama_skema'] ?>">
                    </div>

                </div>
            </div>
        </form>
    </div>
</div> -->


<?= $this->endSection() ?>