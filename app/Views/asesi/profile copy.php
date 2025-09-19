<?= $this->extend("layouts/asesi/layout-app"); ?>

<?= $this->section("content"); ?>

<div id="alert-container"></div>

<?php
$statusPekerjaan = '';
if ($hasAsesiData) {
    $currentPekerjaan = $asesi['pekerjaan'] ?? '';
    if ($currentPekerjaan == 'Pelajar/Mahasiswa' || $currentPekerjaan == 'Tidak Bekerja') {
        $statusPekerjaan = $currentPekerjaan;
    } elseif (!empty($currentPekerjaan)) {
        $statusPekerjaan = 'Bekerja';
    }
}
?>

<?php if ($hasAsesiData) : ?>
    <div id="profile-view-mode">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">PROFIL SAYA</h4>
                <button id="edit-profile-btn" class="btn btn-warning">
                    <i class="fas fa-pencil-alt"></i> Edit Profil & Dokumen
                </button>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">DATA DIRI PEMOHON</h4>
            </div>
            <div class="card-body">
                <div class="form-group mb-3"><label class="form-label">Kode Asesi</label>
                    <p class="form-control-plaintext ps-1"><?= esc($asesi['kode_asesi']); ?></p>
                </div>
                <div class="form-group mb-3"><label class="form-label">Nama Lengkap</label>
                    <p class="form-control-plaintext ps-1"><?= esc(user()->nama_lengkap ?? ''); ?></p>
                </div>
                <div class="row">
                    <div class="form-group mb-3 col-md-4"><label class="form-label">Email</label>
                        <p class="form-control-plaintext ps-1"><?= esc(user()->email ?? ''); ?></p>
                    </div>
                    <div class="form-group mb-3 col-md-4"><label class="form-label">Nomor Handphone</label>
                        <p class="form-control-plaintext ps-1"><?= esc(user()->no_hp ?? '-'); ?></p>
                    </div>
                    <div class="form-group mb-3 col-md-4"><label class="form-label">Nomor Telepon Rumah</label>
                        <p class="form-control-plaintext ps-1"><?= esc($asesi['telpon_rumah'] ?: '-'); ?></p>
                    </div>
                </div>
                <div class="form-group mb-3"><label class="form-label">NIK</label>
                    <p class="form-control-plaintext ps-1"><?= esc($asesi['nik']); ?></p>
                </div>
                <div class="row">
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Tempat Lahir</label>
                        <p class="form-control-plaintext ps-1"><?= esc($asesi['tempat_lahir']); ?></p>
                    </div>
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Tanggal Lahir</label>
                        <p class="form-control-plaintext ps-1"><?= date('d F Y', strtotime($asesi['tanggal_lahir'])); ?></p>
                    </div>
                </div>
                <div class="form-group mb-3"><label class="form-label">Jenis Kelamin</label>
                    <p class="form-control-plaintext ps-1"><?= esc($asesi['jenis_kelamin']); ?></p>
                </div>
                <div class="form-group mb-3"><label class="form-label">Kebangsaan</label>
                    <p class="form-control-plaintext ps-1"><?= esc($asesi['kebangsaan']); ?></p>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">ALAMAT DOMISILI</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Provinsi</label>
                        <p class="form-control-plaintext ps-1"><?= esc($wilayah['provinsi']); ?></p>
                    </div>
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Kabupaten/Kota</label>
                        <p class="form-control-plaintext ps-1"><?= esc($wilayah['kabupaten']); ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Kecamatan</label>
                        <p class="form-control-plaintext ps-1"><?= esc($wilayah['kecamatan']); ?></p>
                    </div>
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Kelurahan/Desa</label>
                        <p class="form-control-plaintext ps-1"><?= esc($wilayah['kelurahan']); ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group mb-3 col-md-4"><label class="form-label">RT</label>
                        <p class="form-control-plaintext ps-1"><?= esc($asesi['rt']); ?></p>
                    </div>
                    <div class="form-group mb-3 col-md-4"><label class="form-label">RW</label>
                        <p class="form-control-plaintext ps-1"><?= esc($asesi['rw']); ?></p>
                    </div>
                    <div class="form-group mb-3 col-md-4"><label class="form-label">Kode Pos</label>
                        <p class="form-control-plaintext ps-1"><?= esc($asesi['kode_pos']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">STATUS PEKERJAAN</h4>
            </div>
            <div class="card-body">
                <div class="form-group mb-3">
                    <label class="form-label">Status Pekerjaan</label>
                    <p class="form-control-plaintext ps-1"><?= esc($statusPekerjaan); ?></p>
                </div>
            </div>
        </div>

        <?php if ($statusPekerjaan === 'Pelajar/Mahasiswa') : ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="card-title text-center mb-0">RIWAYAT PENDIDIKAN</h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3"><label class="form-label">Pendidikan Terakhir/Sedang Ditempuh</label>
                        <p class="form-control-plaintext ps-1"><?= esc($asesi['pendidikan_terakhir'] ?: '-'); ?></p>
                    </div>
                    <div class="form-group mb-3"><label class="form-label">Nama Sekolah/Perguruan Tinggi</label>
                        <p class="form-control-plaintext ps-1"><?= esc($asesi['nama_sekolah'] ?: '-'); ?></p>
                    </div>
                    <div class="form-group mb-3"><label class="form-label">Jurusan/Program Studi</label>
                        <p class="form-control-plaintext ps-1"><?= esc($asesi['jurusan'] ?: '-'); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($statusPekerjaan === 'Bekerja') : ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="card-title text-center mb-0">DATA PEKERJAAN</h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3"><label class="form-label">Detail Pekerjaan/Profesi</label>
                        <p class="form-control-plaintext ps-1"><?= esc($asesi['pekerjaan'] ?: '-'); ?></p>
                    </div>
                    <div class="form-group mb-3"><label class="form-label">Nama Instansi/Lembaga/Perusahaan</label>
                        <p class="form-control-plaintext ps-1"><?= esc($asesi['nama_lembaga'] ?: '-'); ?></p>
                    </div>
                    <div class="form-group mb-3"><label class="form-label">Jabatan</label>
                        <p class="form-control-plaintext ps-1"><?= esc($asesi['jabatan'] ?: '-'); ?></p>
                    </div>
                    <div class="form-group mb-3"><label class="form-label">Alamat Lembaga/Perusahaan</label>
                        <p class="form-control-plaintext ps-1"><?= esc($asesi['alamat_perusahaan'] ?: '-'); ?></p>
                    </div>
                    <div class="row">
                        <div class="form-group mb-3 col-md-6"><label class="form-label">Email Perusahaan</label>
                            <p class="form-control-plaintext ps-1"><?= esc($asesi['email_perusahaan'] ?: '-'); ?></p>
                        </div>
                        <div class="form-group mb-3 col-md-6"><label class="form-label">Nomor Telepon Perusahaan</label>
                            <p class="form-control-plaintext ps-1"><?= esc($asesi['no_telp_perusahaan'] ?: '-'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>


<div id="profile-edit-mode" style="<?= $hasAsesiData ? 'display:none;' : '' ?>">

    <form id="asesi-profile-form">
        <?= csrf_field(); ?>
        <?php if ($hasAsesiData) : ?><input type="hidden" name="id_asesi" value="<?= $asesi['id_asesi'] ?>"><?php endif; ?>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">DATA DIRI PEMOHON</h4>
            </div>
            <div class="card-body">
                <div class="form-group mb-3"><label class="form-label">Nama Lengkap <span class="text-danger">*</span></label><input type="text" class="form-control" name="nama_lengkap" value="<?= user()->nama_lengkap ?? '' ?>" readonly style="background-color: #f8f9fa;"></div>
                <div class="row">
                    <div class="form-group mb-3 col-md-4"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" class="form-control" value="<?= user()->email ?? '' ?>" readonly style="background-color: #f8f9fa;"></div>
                    <div class="form-group mb-3 col-md-4"><label class="form-label">Nomor Handphone <span class="text-danger">*</span></label><input type="text" class="form-control" name="no_hp" id="no_hp" value="<?= user()->no_hp ?? '' ?>" placeholder="+62 8xx xxxx xxxx" maxlength="17"></div>
                    <div class="form-group mb-3 col-md-4"><label class="form-label">Nomor Telepon Rumah</label><input type="text" class="form-control" name="telpon_rumah" id="telpon_rumah" value="<?= $asesi['telpon_rumah'] ?? '' ?>" placeholder="+62 21 xxxx xxxx" maxlength="17"></div>
                </div>
                <div class="form-group mb-3"><label class="form-label">NIK <span class="text-danger">*</span></label><input type="text" class="form-control" name="nik" id="nik" value="<?= $asesi['nik'] ?? '' ?>" placeholder="16 digit angka" maxlength="16"></div>
                <div class="row">
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Tempat Lahir <span class="text-danger">*</span></label><input type="text" class="form-control" name="tempat_lahir" id="tempat_lahir" value="<?= $asesi['tempat_lahir'] ?? '' ?>"></div>
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label><input type="date" class="form-control" name="tanggal_lahir" id="tanggal_lahir" value="<?= $asesi['tanggal_lahir'] ?? '' ?>"></div>
                </div>
                <div class="form-group mb-3"><label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label><select class="form-control" name="jenis_kelamin" id="jenis_kelamin">
                        <option value="">Pilih...</option>
                        <option value="Laki-Laki" <?= ($asesi['jenis_kelamin'] ?? '') == "Laki-Laki" ? "selected" : ""; ?>>Laki-Laki</option>
                        <option value="Perempuan" <?= ($asesi['jenis_kelamin'] ?? '') == "Perempuan" ? "selected" : ""; ?>>Perempuan</option>
                    </select></div>
                <div class="form-group mb-3"><label class="form-label">Kebangsaan <span class="text-danger">*</span></label><select class="form-control" name="kebangsaan" id="kebangsaan">
                        <option value="">Pilih...</option>
                        <option value="WNI" <?= ($asesi['kebangsaan'] ?? '') == "WNI" ? "selected" : ""; ?>>WNI</option>
                        <option value="WNA" <?= ($asesi['kebangsaan'] ?? '') == "WNA" ? "selected" : ""; ?>>WNA</option>
                    </select></div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">ALAMAT DOMISILI</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Provinsi <span class="text-danger">*</span></label><select class="form-control" name="provinsi" id="provinsi">
                            <option value="">Pilih Provinsi</option><?php foreach ($provinsi as $prov) : ?><option value="<?= $prov['id'] ?>" <?= ($asesi['provinsi'] ?? '') == $prov['id'] ? "selected" : ""; ?>><?= $prov['nama'] ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label><select class="form-control" name="kabupaten" id="kabupaten">
                            <option value="">Pilih...</option>
                        </select></div>
                </div>
                <div class="row">
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Kecamatan <span class="text-danger">*</span></label><select class="form-control" name="kecamatan" id="kecamatan">
                            <option value="">Pilih...</option>
                        </select></div>
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Kelurahan/Desa <span class="text-danger">*</span></label><select class="form-control" name="kelurahan" id="kelurahan">
                            <option value="">Pilih...</option>
                        </select></div>
                </div>
                <div class="row">
                    <div class="form-group mb-3 col-md-4"><label class="form-label">RT <span class="text-danger">*</span></label><input type="text" class="form-control" name="rt" id="rt" value="<?= $asesi['rt'] ?? '' ?>" maxlength="3"></div>
                    <div class="form-group mb-3 col-md-4"><label class="form-label">RW <span class="text-danger">*</span></label><input type="text" class="form-control" name="rw" id="rw" value="<?= $asesi['rw'] ?? '' ?>" maxlength="3"></div>
                    <div class="form-group mb-3 col-md-4"><label class="form-label">Kode Pos <span class="text-danger">*</span></label><input type="text" class="form-control" name="kode_pos" id="kode_pos" value="<?= $asesi['kode_pos'] ?? '' ?>" maxlength="5"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">STATUS PEKERJAAN</h4>
            </div>
            <div class="card-body">
                <div class="form-group mb-3"><label class="form-label">Status Pekerjaan <span class="text-danger">*</span></label><select class="form-control" name="status_pekerjaan" id="status_pekerjaan">
                        <option value="">Pilih Status</option>
                        <option value="Pelajar/Mahasiswa" <?= $statusPekerjaan == "Pelajar/Mahasiswa" ? "selected" : ""; ?>>Pelajar/Mahasiswa</option>
                        <option value="Bekerja" <?= $statusPekerjaan == "Bekerja" ? "selected" : ""; ?>>Bekerja</option>
                        <option value="Tidak Bekerja" <?= $statusPekerjaan == "Tidak Bekerja" ? "selected" : ""; ?>>Tidak Bekerja</option>
                    </select></div>
            </div>
        </div>

        <div class="card mb-3" id="pendidikan-section" style="display: none;">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">RIWAYAT PENDIDIKAN</h4>
            </div>
            <div class="card-body">
                <div class="form-group mb-3"><label class="form-label">Pendidikan Terakhir</label><select class="form-control" name="pendidikan_terakhir" id="pendidikan_terakhir">
                        <option value="">Pilih...</option>
                        <option value="SD" <?= ($asesi['pendidikan_terakhir'] ?? '') == "SD" ? "selected" : ""; ?>>SD</option>
                        <option value="SMP" <?= ($asesi['pendidikan_terakhir'] ?? '') == "SMP" ? "selected" : ""; ?>>SMP</option>
                        <option value="SMA" <?= ($asesi['pendidikan_terakhir'] ?? '') == "SMA" ? "selected" : ""; ?>>SMA</option>
                        <option value="SMK" <?= ($asesi['pendidikan_terakhir'] ?? '') == "SMK" ? "selected" : ""; ?>>SMK</option>
                        <option value="Diploma" <?= ($asesi['pendidikan_terakhir'] ?? '') == "Diploma" ? "selected" : ""; ?>>Diploma</option>
                        <option value="Sarjana" <?= ($asesi['pendidikan_terakhir'] ?? '') == "Sarjana" ? "selected" : ""; ?>>Sarjana</option>
                    </select></div>
                <div class="form-group mb-3">
                    <label class="form-label">Nama Sekolah/Univ <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="text" class="form-control" name="nama_sekolah" id="nama_sekolah" value="<?= $asesi['nama_sekolah'] ?? '' ?>" placeholder="Ketik min. 3 huruf untuk mencari..." autocomplete="off" disabled>
                        <div id="sekolah-dropdown" class="dropdown-menu w-100" style="display: none; max-height: 300px; overflow-y: auto;"></div>
                    </div>
                    <small class="text-muted">Pilih jenjang pendidikan terlebih dahulu.</small>
                </div>
                <div class="form-group mb-3"><label class="form-label">Jurusan</label><input type="text" class="form-control" name="jurusan" id="jurusan" value="<?= $asesi['jurusan'] ?? '' ?>"></div>
            </div>
        </div>

        <div class="card mb-3" id="pekerjaan-section" style="display: none;">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">DATA PEKERJAAN</h4>
            </div>
            <div class="card-body">
                <div class="form-group mb-3"><label class="form-label">Detail Pekerjaan/Profesi <span class="text-danger">*</span></label><input type="text" class="form-control" name="detail_pekerjaan" id="detail_pekerjaan" value="<?= ($statusPekerjaan == 'Bekerja') ? ($asesi['pekerjaan'] ?? '') : '' ?>"></div>
                <div class="form-group mb-3"><label class="form-label">Nama Instansi</label><input type="text" class="form-control" name="nama_lembaga" id="nama_lembaga" value="<?= $asesi['nama_lembaga'] ?? '' ?>"></div>
                <div class="form-group mb-3"><label class="form-label">Jabatan</label><input type="text" class="form-control" name="jabatan" id="jabatan" value="<?= $asesi['jabatan'] ?? '' ?>"></div>
                <div class="form-group mb-3"><label class="form-label">Alamat Lembaga</label><textarea class="form-control" name="alamat_perusahaan" id="alamat_perusahaan" rows="2"><?= $asesi['alamat_perusahaan'] ?? '' ?></textarea></div>
                <div class="row">
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Email Perusahaan</label><input type="email" class="form-control" name="email_perusahaan" id="email_perusahaan" value="<?= $asesi['email_perusahaan'] ?? '' ?>"></div>
                    <div class="form-group mb-3 col-md-6"><label class="form-label">No Telp Perusahaan</label><input type="text" class="form-control" name="no_telp_perusahaan" id="no_telp_perusahaan" value="<?= $asesi['no_telp_perusahaan'] ?? '' ?>"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body d-flex justify-content-end">
                <?php if ($hasAsesiData) : ?><button type="button" id="cancel-edit-btn" class="btn btn-secondary me-2">Batal</button><?php endif; ?>
                <button type="submit" id="submit-profile-btn" class="btn btn-primary">
                    <span class="submit-text"><i class="fas fa-save"></i> <?= $hasAsesiData ? 'Update Profil' : 'Simpan Profil' ?></span>
                    <span class="submit-loading d-none"><i class="fas fa-spinner fa-spin"></i> Menyimpan...</span>
                </button>
            </div>
        </div>
    </form>

    <?php if ($hasAsesiData) : ?>
        <div id="document-section" class="mt-4">
            <form id="document-upload-form">
                <?= csrf_field(); ?>
                <input type="hidden" name="id_asesi" value="<?= $asesi['id_asesi'] ?>">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4 class="card-title text-center mb-0">BUKTI KELENGKAPAN PEMOHON</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-4 mb-3"><label class="form-label">Pas Foto <span class="text-danger">*</span></label><?php if (!empty($asesi['pas_foto'])) : ?><div class="mb-2"><a href="<?= base_url('uploads/asesi_dokumen/' . $asesi['pas_foto']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat File</a></div><?php endif; ?><input type="file" class="filepond-input" name="pas_foto" data-max-file-size="2MB" data-accepted-file-types="image/png, image/jpeg"></div>
                            <div class="col-12 col-md-4 mb-3"><label class="form-label">Foto Tanda Tangan <span class="text-danger">*</span></label><?php if (!empty($asesi['tanda_tangan_asesi'])) : ?><div class="mb-2"><a href="<?= base_url('uploads/asesi_dokumen/' . $asesi['tanda_tangan_asesi']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat File</a></div><?php endif; ?><input type="file" class="filepond-input" name="tanda_tangan_asesi" data-max-file-size="2MB" data-accepted-file-types="image/png, image/jpeg"></div>
                            <div class="col-12 col-md-4 mb-3"><label class="form-label">Identitas (KTP/Kartu Pelajar) <span class="text-danger">*</span></label><?php if (!empty($asesi['ktp'])) : ?><div class="mb-2"><a href="<?= base_url('uploads/asesi_dokumen/' . $asesi['ktp']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat File</a></div><?php endif; ?><input type="file" class="filepond-input" name="ktp" data-max-file-size="2MB" data-accepted-file-types="image/png, image/jpeg, application/pdf"></div>
                            <div class="col-12 col-md-4 mb-3"><label class="form-label">Bukti Pendidikan <span class="text-danger">*</span></label><?php if (!empty($asesi['bukti_pendidikan'])) : ?><div class="mb-2"><a href="<?= base_url('uploads/asesi_dokumen/' . $asesi['bukti_pendidikan']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat File</a></div><?php endif; ?><input type="file" class="filepond-input" name="bukti_pendidikan" data-max-file-size="2MB" data-accepted-file-types="image/png, image/jpeg, application/pdf"></div>
                            <div class="col-12 col-md-4 mb-3"><label class="form-label">Raport <span class="text-danger">*</span></label><?php if (!empty($asesi['raport'])) : ?><div class="mb-2"><a href="<?= base_url('uploads/asesi_dokumen/' . $asesi['raport']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat File</a></div><?php endif; ?><input type="file" class="filepond-input" name="raport" data-max-file-size="2MB" data-accepted-file-types="application/pdf"></div>
                            <div class="col-12 col-md-4 mb-3"><label class="form-label">Sertifikat PKL <span class="text-danger">*</span></label><?php if (!empty($asesi['sertifikat_pkl'])) : ?><div class="mb-2"><a href="<?= base_url('uploads/asesi_dokumen/' . $asesi['sertifikat_pkl']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat File</a></div><?php endif; ?><input type="file" class="filepond-input" name="sertifikat_pkl" data-max-file-size="2MB" data-accepted-file-types="image/png, image/jpeg, application/pdf"></div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body text-end">
                        <button type="submit" id="submit-docs-btn" class="btn btn-success">
                            <span class="submit-text"><i class="fas fa-upload"></i> Unggah Dokumen Terpilih</span>
                            <span class="submit-loading d-none"><i class="fas fa-spinner fa-spin"></i> Mengunggah...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section("js") ?>
<script>
    $(document).ready(function() {
        // Inisialisasi FilePond
        $('.filepond-input').each(function() {
            const inputElement = this;
            FilePond.create(inputElement, {
                name: $(inputElement).attr('name'),
                allowMultiple: false,
                maxFileSize: $(inputElement).data('max-file-size'),
                acceptedFileTypes: $(inputElement).data('accepted-file-types'),
                labelIdle: `Seret & Lepas file atau <span class="filepond--label-action">Jelajahi</span>`,
                labelMaxFileSizeExceeded: 'File terlalu besar',
                labelMaxFileSize: 'Ukuran maksimal adalah {filesize}',
            });
        });

        // Konfigurasi URL dan data
        const CONFIG = {
            urls: {
                saveProfile: '<?= site_url('asesi/save') ?>',
                uploadDocs: '<?= site_url('asesi/upload-documents') ?>',
                kabupaten: '<?= site_url('kabupaten') ?>',
                kecamatan: '<?= site_url('kecamatan') ?>',
                kelurahan: '<?= site_url('kelurahan') ?>',
                sekolah: '<?= site_url('asesi/getSekolah') ?>'
            },
            hasData: <?= $hasAsesiData ? 'true' : 'false' ?>,
            existing: {
                provinsi: '<?= $asesi['provinsi'] ?? '' ?>',
                kabupaten: '<?= $asesi['kabupaten'] ?? '' ?>',
                kecamatan: '<?= $asesi['kecamatan'] ?? '' ?>',
                kelurahan: '<?= $asesi['kelurahan'] ?? '' ?>',
            }
        };

        const csrfTokenName = '<?= csrf_token() ?>';
        let csrfHashValue = '<?= csrf_hash() ?>';

        // Handler tombol edit dan batal
        $('#edit-profile-btn').on('click', function() {
            $('#profile-view-mode').hide();
            $('#profile-edit-mode').show();
        });
        $('#cancel-edit-btn').on('click', function() {
            $('#profile-edit-mode').hide();
            $('#profile-view-mode').show();
        });

        // Fungsi untuk memuat data dropdown wilayah
        function loadOptions(elementId, url, data, selectedValue = '') {
            const $el = $(`#${elementId}`);
            $el.html('<option value="">Memuat...</option>');
            data[csrfTokenName] = csrfHashValue;
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    let options = '<option value="">Pilih...</option>';
                    if (response) {
                        response.forEach(item => {
                            options += `<option value="${item.id}" ${item.id == selectedValue ? 'selected' : ''}>${item.nama}</option>`;
                        });
                    }
                    $el.html(options).prop('disabled', false);
                    if (selectedValue) {
                        $el.trigger('change');
                    }
                },
                error: function() {
                    $el.html('<option value="">Gagal memuat</option>');
                }
            });
        }

        // Event handler untuk dropdown wilayah
        $('#provinsi').on('change', function() {
            const provId = $(this).val();
            if (provId) {
                loadOptions('kabupaten', CONFIG.urls.kabupaten, {
                    id_provinsi: provId
                }, CONFIG.existing.kabupaten);
            }
        });
        $('#kabupaten').on('change', function() {
            const kabId = $(this).val();
            if (kabId) {
                loadOptions('kecamatan', CONFIG.urls.kecamatan, {
                    id_kabupaten: kabId
                }, CONFIG.existing.kecamatan);
            }
        });
        $('#kecamatan').on('change', function() {
            const kecId = $(this).val();
            if (kecId) {
                loadOptions('kelurahan', CONFIG.urls.kelurahan, {
                    id_kecamatan: kecId
                }, CONFIG.existing.kelurahan);
            }
        });

        // Trigger change pada provinsi jika data sudah ada
        if (CONFIG.hasData && CONFIG.existing.provinsi) {
            $('#provinsi').trigger('change');
        }

        // Handler untuk menampilkan/menyembunyikan bagian pekerjaan/pendidikan
        $('#status_pekerjaan').on('change', function() {
            const status = $(this).val();
            $('#pendidikan-section, #pekerjaan-section').hide();
            if (status === 'Pelajar/Mahasiswa') {
                $('#pendidikan-section').show();
            } else if (status === 'Bekerja') {
                $('#pekerjaan-section').show();
            }
        }).trigger('change');

        // Handler untuk pencarian sekolah
        let searchTimeout;
        $('#pendidikan_terakhir').on('change', function() {
            const $namaSekolah = $('#nama_sekolah');
            $namaSekolah.val('');
            if ($(this).val()) {
                $namaSekolah.prop('disabled', false).attr('placeholder', 'Ketik min. 3 huruf untuk mencari...');
            } else {
                $namaSekolah.prop('disabled', true).attr('placeholder', 'Pilih jenjang pendidikan terlebih dahulu.');
            }
        });
        $('#nama_sekolah').on('keyup', function() {
            clearTimeout(searchTimeout);
            const query = $(this).val();
            const jenjang = $('#pendidikan_terakhir').val();
            const $dropdown = $('#sekolah-dropdown');

            if (query.length < 3) {
                $dropdown.hide();
                return;
            }
            searchTimeout = setTimeout(() => {
                $.ajax({
                    url: CONFIG.urls.sekolah,
                    type: 'POST',
                    data: {
                        jenjang: jenjang,
                        search: query,
                        [csrfTokenName]: csrfHashValue
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $dropdown.html('<div class="dropdown-item text-muted">Mencari...</div>').show();
                    },
                    success: function(response) {
                        let html = '';
                        if (response.success && response.results.length > 0) {
                            response.results.forEach(item => {
                                html += `<a href="#" class="dropdown-item sekolah-item">${item.display}</a>`;
                            });
                        } else {
                            html = '<div class="dropdown-item text-muted">Tidak ditemukan.</div>';
                        }
                        $dropdown.html(html);
                    }
                });
            }, 500);
        });
        $(document).on('click', '.sekolah-item', function(e) {
            e.preventDefault();
            $('#nama_sekolah').val($(this).text().split(' (')[0]);
            $('#sekolah-dropdown').hide();
        });

        /**
         * HANDLER UNTUK FORM PROFIL (#asesi-profile-form)
         */
        $('#asesi-profile-form').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#submit-profile-btn');
            const formData = new FormData(this);
            toggleLoading($btn, true);
            clearErrors();
            $.ajax({
                url: CONFIG.urls.saveProfile,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showAlert('error', 'Periksa kembali isian Anda.');
                        if (response.errors) {
                            displayErrors(response.errors);
                        }
                    }
                },
                error: function() {
                    showAlert('error', 'Terjadi kesalahan server.');
                },
                complete: function() {
                    toggleLoading($btn, false);
                }
            });
        });

        /**
         * HANDLER UNTUK FORM DOKUMEN (#document-upload-form)
         */
        $('#document-upload-form').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#submit-docs-btn');
            const formData = new FormData(this);
            toggleLoading($btn, true);
            clearErrors();
            $.ajax({
                url: CONFIG.urls.uploadDocs,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert('error', 'Gagal mengunggah. Periksa kembali file Anda.');
                        if (response.errors) {
                            displayErrors(response.errors, true);
                        }
                    }
                },
                error: function() {
                    showAlert('error', 'Terjadi kesalahan server saat mengunggah.');
                },
                complete: function() {
                    toggleLoading($btn, false);
                }
            });
        });

        // --- FUNGSI-FUNGSI HELPER ---
        function toggleLoading($btn, isLoading) {
            if (isLoading) {
                $btn.prop('disabled', true);
                $btn.find('.submit-text').addClass('d-none');
                $btn.find('.submit-loading').removeClass('d-none');
            } else {
                $btn.prop('disabled', false);
                $btn.find('.submit-loading').addClass('d-none');
                $btn.find('.submit-text').removeClass('d-none');
            }
        }

        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
            $('#alert-container').html(`<div class="alert ${alertClass} alert-dismissible fade show" role="alert"><i class="fas fa-${icon}"></i> ${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`);
            window.scrollTo(0, 0);
        }

        function displayErrors(errors, isFile = false) {
            $.each(errors, function(field, message) {
                const $input = $(`[name="${field}"]`);
                if (isFile) {
                    const pondInstance = FilePond.find($input[0]);
                    if (pondInstance) {
                        $input.closest('div').append(`<div class="invalid-feedback d-block">${message}</div>`);
                    }
                } else {
                    $input.addClass('is-invalid');
                    $input.closest('.form-group').find('.invalid-feedback').text(message);
                }
            });
        }

        function clearErrors() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove(); // Lebih baik hapus elemen error agar tidak menumpuk
        }
    });
</script>
<?= $this->endSection() ?>