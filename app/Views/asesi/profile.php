<?= $this->extend("layouts/asesi/layout-app"); ?>

<?= $this->section("styles") ?>
<style>
    /* Custom CSS for Select2 Validation State */
    .is-invalid .select2-selection {
        border-color: #dc3545 !important;
    }

    .is-valid .select2-selection {
        border-color: #28a745 !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section("content"); ?>

<div id="alert-container"></div>

<?php
// Initialize variables to avoid errors if asesi data doesn't exist yet
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
                <h4 class="card-title mb-0">DATA DIRI PEMOHON</h4>
                <button id="edit-profile-btn" class="btn btn-warning">
                    <i class="fas fa-pencil-alt"></i> Edit Profil & Dokumen
                </button>
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
                <div class="form-group mb-3"><label class="form-label">Status Pekerjaan</label>
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

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">BUKTI KELENGKAPAN PEMOHON</h4>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php
                    $dokumen = [
                        'pas_foto' => 'Pas Foto',
                        'tanda_tangan_asesi' => 'Foto Tanda Tangan',
                        'ktp' => 'Identitas (KTP/Kartu Pelajar)',
                        'bukti_pendidikan' => 'Bukti Pendidikan',
                        'raport' => 'Raport',
                        'sertifikat_pkl' => 'Sertifikat PKL'
                    ];

                    foreach ($dokumen as $field => $label) :
                    ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= $label ?>
                            <span>
                                <?php if (!empty($asesi[$field])) : ?>
                                    <a href="<?= base_url('uploads/asesi_dokumen/' . $asesi[$field]) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat File</a>
                                <?php else : ?>
                                    <span class="badge badge-danger">Belum diunggah</span>
                                <?php endif; ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    </div>
<?php endif; ?>

<div id="profile-edit-mode" style="<?= $hasAsesiData ? 'display:none;' : '' ?>">
    <form id="asesi-profile-form" novalidate>
        <?= csrf_field(); ?>
        <?php if ($hasAsesiData) : ?><input type="hidden" name="id_asesi" value="<?= $asesi['id_asesi'] ?>"><?php endif; ?>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">DATA DIRI PEMOHON</h4>
            </div>
            <div class="card-body">
                <div class="form-group mb-3"><label class="form-label">Nama Lengkap <span class="text-danger">*</span></label><input type="text" class="form-control" name="nama_lengkap" value="<?= user()->nama_lengkap ?? '' ?>" readonly style="background-color: #f8f9fa;"></div>
                <div class="row">
                    <div class="form-group mb-3 col-md-4"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" class="form-control" name="email" value="<?= user()->email ?? '' ?>" readonly style="background-color: #f8f9fa;"></div>
                    <div class="form-group mb-3 col-md-4"><label class="form-label">Nomor Handphone <span class="text-danger">*</span></label><input type="text" class="form-control" name="no_hp" id="no_hp" value="<?= user()->no_hp ?? $asesi['no_hp'] ?? '' ?>" placeholder="+62 8xx xxxx xxxx" maxlength="20" data-validate="phone">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 col-md-4"><label class="form-label">Nomor Telepon Rumah</label><input type="text" class="form-control" name="telpon_rumah" id="telpon_rumah" value="<?= $asesi['telpon_rumah'] ?? '' ?>" placeholder="+62 21 xxxx xxxx" maxlength="20" data-validate="landline">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="form-group mb-3"><label class="form-label">NIK <span class="text-danger">*</span></label><input type="text" class="form-control" name="nik" id="nik" value="<?= $asesi['nik'] ?? '' ?>" placeholder="16 digit angka" maxlength="16" data-validate="nik">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="row">
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Tempat Lahir <span class="text-danger">*</span></label><input type="text" class="form-control" name="tempat_lahir" id="tempat_lahir" value="<?= $asesi['tempat_lahir'] ?? '' ?>" data-validate="text">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label><input type="date" class="form-control" name="tanggal_lahir" id="tanggal_lahir" value="<?= $asesi['tanggal_lahir'] ?? '' ?>" max="<?= date('Y-m-d', strtotime('-10 years')) ?>" data-validate="date">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="form-group mb-3"><label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                    <select class="form-control select2-init" name="jenis_kelamin" id="jenis_kelamin" data-validate="select">
                        <option value="">Pilih...</option>
                        <option value="Laki-Laki" <?= ($asesi['jenis_kelamin'] ?? '') == "Laki-Laki" ? "selected" : ""; ?>>Laki-Laki</option>
                        <option value="Perempuan" <?= ($asesi['jenis_kelamin'] ?? '') == "Perempuan" ? "selected" : ""; ?>>Perempuan</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="form-group mb-3"><label class="form-label">Kebangsaan <span class="text-danger">*</span></label>
                    <select class="form-control select2-init" name="kebangsaan" id="kebangsaan" data-validate="select">
                        <option value="">Pilih...</option>
                        <option value="WNI" <?= ($asesi['kebangsaan'] ?? '') == "WNI" ? "selected" : ""; ?>>WNI</option>
                        <option value="WNA" <?= ($asesi['kebangsaan'] ?? '') == "WNA" ? "selected" : ""; ?>>WNA</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">ALAMAT DOMISILI</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Provinsi <span class="text-danger">*</span></label>
                        <select class="form-control select2-init" name="provinsi" id="provinsi" data-validate="select">
                            <option value="">Pilih Provinsi</option>
                            <?php foreach ($provinsi as $prov) : ?>
                                <option value="<?= $prov['id'] ?>" <?= ($asesi['provinsi'] ?? '') == $prov['id'] ? "selected" : ""; ?>><?= $prov['nama'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label>
                        <select class="form-control select2-init" name="kabupaten" id="kabupaten" data-validate="select">
                            <option value="">Pilih...</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Kecamatan <span class="text-danger">*</span></label>
                        <select class="form-control select2-init" name="kecamatan" id="kecamatan" data-validate="select">
                            <option value="">Pilih...</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Kelurahan/Desa <span class="text-danger">*</span></label>
                        <select class="form-control select2-init" name="kelurahan" id="kelurahan" data-validate="select">
                            <option value="">Pilih...</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group mb-3 col-md-4"><label class="form-label">RT <span class="text-danger">*</span></label><input type="text" class="form-control" name="rt" id="rt" value="<?= $asesi['rt'] ?? '' ?>" maxlength="3" data-validate="rt">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 col-md-4"><label class="form-label">RW <span class="text-danger">*</span></label><input type="text" class="form-control" name="rw" id="rw" value="<?= $asesi['rw'] ?? '' ?>" maxlength="3" data-validate="rw">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 col-md-4"><label class="form-label">Kode Pos <span class="text-danger">*</span></label><input type="text" class="form-control" name="kode_pos" id="kode_pos" value="<?= $asesi['kode_pos'] ?? '' ?>" maxlength="5" data-validate="postalCode">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">STATUS PEKERJAAN</h4>
            </div>
            <div class="card-body">
                <div class="form-group mb-3"><label class="form-label">Status Pekerjaan <span class="text-danger">*</span></label>
                    <select class="form-control select2-init" name="status_pekerjaan" id="status_pekerjaan" data-validate="select">
                        <option value="">Pilih Status</option>
                        <option value="Pelajar/Mahasiswa" <?= $statusPekerjaan == "Pelajar/Mahasiswa" ? "selected" : ""; ?>>Pelajar/Mahasiswa</option>
                        <option value="Bekerja" <?= $statusPekerjaan == "Bekerja" ? "selected" : ""; ?>>Bekerja</option>
                        <option value="Tidak Bekerja" <?= $statusPekerjaan == "Tidak Bekerja" ? "selected" : ""; ?>>Tidak Bekerja</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3" id="pendidikan-section" style="display: none;">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">RIWAYAT PENDIDIKAN</h4>
            </div>
            <div class="card-body">
                <div class="form-group mb-3"><label class="form-label">Pendidikan Terakhir</label>
                    <select class="form-control select2-init" name="pendidikan_terakhir" id="pendidikan_terakhir">
                        <option value="">Pilih...</option>
                        <option value="SD" <?= ($asesi['pendidikan_terakhir'] ?? '') == "SD" ? "selected" : ""; ?>>SD</option>
                        <option value="SMP" <?= ($asesi['pendidikan_terakhir'] ?? '') == "SMP" ? "selected" : ""; ?>>SMP</option>
                        <option value="SMA" <?= ($asesi['pendidikan_terakhir'] ?? '') == "SMA" ? "selected" : ""; ?>>SMA</option>
                        <option value="SMK" <?= ($asesi['pendidikan_terakhir'] ?? '') == "SMK" ? "selected" : ""; ?>>SMK</option>
                        <option value="Diploma" <?= ($asesi['pendidikan_terakhir'] ?? '') == "Diploma" ? "selected" : ""; ?>>Diploma</option>
                        <option value="Sarjana" <?= ($asesi['pendidikan_terakhir'] ?? '') == "Sarjana" ? "selected" : ""; ?>>Sarjana</option>
                    </select>
                </div>
                <div class="form-group mb-3"><label class="form-label">Nama Sekolah/Univ <span class="text-danger">*</span></label>
                    <div class="position-relative"><input type="text" class="form-control" name="nama_sekolah" id="nama_sekolah" value="<?= $asesi['nama_sekolah'] ?? '' ?>" placeholder="Pilih jenjang pendidikan terlebih dahulu" autocomplete="off" disabled data-validate="text">
                        <div class="invalid-feedback"></div>
                        <div id="sekolah-dropdown" class="dropdown-menu w-100" style="display: none; max-height: 300px; overflow-y: auto;"></div>
                    </div>
                </div>
                <div class="form-group mb-3"><label class="form-label">Jurusan</label><input type="text" class="form-control" name="jurusan" id="jurusan" value="<?= $asesi['jurusan'] ?? '' ?>" data-validate="text">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3" id="pekerjaan-section" style="display: none;">
            <div class="card-header">
                <h4 class="card-title text-center mb-0">DATA PEKERJAAN</h4>
            </div>
            <div class="card-body">
                <div class="form-group mb-3"><label class="form-label">Detail Pekerjaan/Profesi <span class="text-danger">*</span></label><input type="text" class="form-control" name="detail_pekerjaan" id="detail_pekerjaan" value="<?= ($statusPekerjaan == 'Bekerja') ? ($asesi['pekerjaan'] ?? '') : '' ?>" data-validate="text">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="form-group mb-3"><label class="form-label">Nama Instansi</label><input type="text" class="form-control" name="nama_lembaga" id="nama_lembaga" value="<?= $asesi['nama_lembaga'] ?? '' ?>" data-validate="text">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="form-group mb-3"><label class="form-label">Jabatan</label><input type="text" class="form-control" name="jabatan" id="jabatan" value="<?= $asesi['jabatan'] ?? '' ?>" data-validate="text">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="form-group mb-3"><label class="form-label">Alamat Lembaga</label><textarea class="form-control" name="alamat_perusahaan" id="alamat_perusahaan" rows="2"><?= $asesi['alamat_perusahaan'] ?? '' ?></textarea></div>
                <div class="row">
                    <div class="form-group mb-3 col-md-6"><label class="form-label">Email Perusahaan</label><input type="email" class="form-control" name="email_perusahaan" id="email_perusahaan" value="<?= $asesi['email_perusahaan'] ?? '' ?>" data-validate="email">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group mb-3 col-md-6"><label class="form-label">No Telp Perusahaan</label><input type="text" class="form-control" name="no_telp_perusahaan" id="no_telp_perusahaan" value="<?= $asesi['no_telp_perusahaan'] ?? '' ?>" data-validate="landline" placeholder="+62 21 xxxx xxxx">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body d-flex justify-content-end">
                <button type="button" id="view-profile-btn" class="btn btn-info mr-2" style="<?= !$hasAsesiData ? 'display:none;' : '' ?>"><i class="fas fa-eye"></i> Lihat Profil</button>
                <button type="button" id="cancel-edit-btn" class="btn btn-secondary mr-2" style="<?= !$hasAsesiData ? 'display:none;' : '' ?>">Batal</button>
                <button type="submit" id="submit-profile-btn" class="btn btn-primary">
                    <span class="submit-text"><i class="fas fa-save"></i> <?= $hasAsesiData ? 'Update Profil' : 'Simpan & Lanjutkan' ?></span>
                    <span class="submit-loading d-none"><i class="fas fa-spinner fa-spin"></i> Menyimpan...</span>
                </button>
            </div>
        </div>
    </form>

    <div id="document-section-container" style="display:none;" class="mt-4">
        <form id="document-upload-form">
            <?= csrf_field(); ?>
            <input type="hidden" name="id_asesi" value="<?= $asesi['id_asesi'] ?? '' ?>">
            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="card-title text-center mb-0">BUKTI KELENGKAPAN PEMOHON</h4>
                    <p class="text-center text-muted mb-0">Langkah 2 dari 2: Unggah dokumen yang diperlukan.</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-4 mb-3"><label class="form-label">Pas Foto <span class="text-danger">*</span></label><?php if (!empty($asesi['pas_foto'])) : ?><div class="mb-2"><a href="<?= base_url('uploads/asesi_dokumen/' . $asesi['pas_foto']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat File</a></div><?php endif; ?><input type="file" class="filepond-input" name="pas_foto" data-max-file-size="2MB" data-accepted-file-types="image/png, image/jpeg"></div>
                        <div class="col-12 col-md-4 mb-3"><label class="form-label">Foto Tanda Tangan <span class="text-danger">*</span></label><?php if (!empty($asesi['tanda_tangan_asesi'])) : ?><div class="mb-2"><a href="<?= base_url('uploads/asesi_dokumen/' . $asesi['tanda_tangan_asesi']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat File</a></div><?php endif; ?><input type="file" class="filepond-input" name="tanda_tangan_asesi" data-max-file-size="2MB" data-accepted-file-types="image/png, image/jpeg"></div>
                        <div class="col-12 col-md-4 mb-3"><label class="form-label">Identitas (KTP/Kartu Pelajar) <span class="text-danger">*</span></label><?php if (!empty($asesi['ktp'])) : ?><div class="mb-2"><a href="<?= base_url('uploads/asesi_dokumen/' . $asesi['ktp']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat File</a></div><?php endif; ?><input type="file" class="filepond-input" name="ktp" data-max-file-size="2MB" data-accepted-file-types="image/png, image/jpeg, application/pdf"></div>
                        <div class="col-12 col-md-4 mb-3"><label class="form-label">Bukti Pendidikan</label><?php if (!empty($asesi['bukti_pendidikan'])) : ?><div class="mb-2"><a href="<?= base_url('uploads/asesi_dokumen/' . $asesi['bukti_pendidikan']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat File</a></div><?php endif; ?><input type="file" class="filepond-input" name="bukti_pendidikan" data-max-file-size="2MB" data-accepted-file-types="image/png, image/jpeg, application/pdf"></div>
                        <div class="col-12 col-md-4 mb-3"><label class="form-label">Raport</label><?php if (!empty($asesi['raport'])) : ?><div class="mb-2"><a href="<?= base_url('uploads/asesi_dokumen/' . $asesi['raport']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat File</a></div><?php endif; ?><input type="file" class="filepond-input" name="raport" data-max-file-size="2MB" data-accepted-file-types="application/pdf"></div>
                        <div class="col-12 col-md-4 mb-3"><label class="form-label">Sertifikat PKL</label><?php if (!empty($asesi['sertifikat_pkl'])) : ?><div class="mb-2"><a href="<?= base_url('uploads/asesi_dokumen/' . $asesi['sertifikat_pkl']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat File</a></div><?php endif; ?><input type="file" class="filepond-input" name="sertifikat_pkl" data-max-file-size="2MB" data-accepted-file-types="image/png, image/jpeg, application/pdf"></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-end">
                    <button type="submit" id="submit-docs-btn" class="btn btn-primary">
                        <span class="submit-text"><i class="fas fa-upload"></i> Unggah Dokumen</span>
                        <span class="submit-loading d-none"><i class="fas fa-spinner fa-spin"></i> Mengunggah...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section("js") ?>
<script>
    $(document).ready(function() {
        // --- Initialization & Initial Config ---
        $('.select2-init').select2({
            theme: 'bootstrap-4'
        });

        const filePondInstances = {};
        document.querySelectorAll('.filepond-input').forEach(inputElement => {
            filePondInstances[inputElement.name] = FilePond.create(inputElement, {
                labelIdle: `Seret & Letakkan file Anda atau <span class="filepond--label-action">Jelajahi</span>`,
                labelFileProcessingError: 'Error saat memproses file',
                labelTapToRetry: 'ketuk untuk mencoba lagi',
                labelTapToUndo: 'ketuk untuk batal',
            });
        });


        const CONFIG = {
            urls: {
                saveProfile: '<?= site_url('asesi/save') ?>',
                uploadDocs: '<?= site_url('asesi/upload-documents') ?>',
                kabupaten: '<?= site_url('kabupaten') ?>',
                kecamatan: '<?= site_url('kecamatan') ?>',
                kelurahan: '<?= site_url('kelurahan') ?>',
                sekolah: '<?= site_url('asesi/getSekolah') ?>',
                validateField: '<?= site_url('asesi/validateField') ?>'
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

        // --- Input Formatting Functions ---
        const formatters = {
            phone: value => {
                let digits = value.replace(/[^\d]/g, '');
                if (digits.startsWith('08')) {
                    digits = '628' + digits.substring(2);
                } else if (digits.startsWith('8')) {
                    digits = '62' + digits;
                }
                if (!digits.startsWith('62')) return value;

                let formatted = '+62 ' + (digits.substring(2, 5) || '');
                if (digits.length > 5) formatted += ' ' + (digits.substring(5, 9) || '');
                if (digits.length > 9) formatted += ' ' + (digits.substring(9, 13) || '');
                return formatted.trim();
            },
            landline: value => value.replace(/[^\d\+\s-]/g, ''),
            nik: value => value.replace(/[^\d]/g, '').substring(0, 16),
            text: value => value, // Let backend handle validation
            postalCode: value => value.replace(/[^\d]/g, '').substring(0, 5),
            rt: value => value.replace(/[^\d]/g, '').substring(0, 3),
            rw: value => value.replace(/[^\d]/g, '').substring(0, 3),
            email: value => value.trim(),
        };

        // --- Real-time Validation Logic ---
        let validationTimeout;
        $('[data-validate]').on('input change', function() {
            const $field = $(this);
            const validateType = $field.data('validate');

            if (formatters[validateType]) {
                const originalVal = $field.val();
                const formattedVal = formatters[validateType](originalVal);
                if (originalVal !== formattedVal) {
                    $field.val(formattedVal);
                }
            }

            clearTimeout(validationTimeout);
            validationTimeout = setTimeout(() => validateField($field), 500);
        });

        function validateField($field) {
            const fieldName = $field.attr('name');
            const fieldValue = $field.val();

            if (!fieldValue && $field.prop('tagName') !== 'SELECT') {
                clearFieldError($field);
                return;
            }

            $.ajax({
                url: CONFIG.urls.validateField,
                type: 'POST',
                data: {
                    field: fieldName,
                    value: fieldValue,
                    [csrfTokenName]: csrfHashValue
                },
                dataType: 'json',
                success: function(response) {
                    if (response.valid) {
                        markFieldValid($field);
                    } else {
                        markFieldInvalid($field, response.message);
                    }
                },
                error: function() {
                    console.error(`Validation for field ${fieldName} failed.`);
                }
            });
        }

        // ===================================================================
        // MODIFIED FUNCTIONS FOR SELECT2 VALIDATION
        // ===================================================================

        function markFieldValid($field) {
            if ($field.hasClass('select2-init')) {
                $field.removeClass('is-invalid').addClass('is-valid');
                $field.next('.select2-container').next('.invalid-feedback').text('');
            } else {
                $field.removeClass('is-invalid').addClass('is-valid');
                $field.next('.invalid-feedback').text('');
            }
        }

        function markFieldInvalid($field, msg) {
            if ($field.hasClass('select2-init')) {
                $field.removeClass('is-valid').addClass('is-invalid');
                // Target the feedback div that is after the .select2-container
                $field.next('.select2-container').next('.invalid-feedback').text(msg);
            } else {
                $field.removeClass('is-valid').addClass('is-invalid');
                $field.next('.invalid-feedback').text(msg);
            }
        }

        function clearFieldError($field) {
            if ($field.hasClass('select2-init')) {
                $field.removeClass('is-invalid is-valid');
                $field.next('.select2-container').next('.invalid-feedback').text('');
            } else {
                $field.removeClass('is-invalid is-valid');
                $field.next('.invalid-feedback').text('');
            }
        }

        // ===================================================================
        // END OF MODIFIED FUNCTIONS
        // ===================================================================

        // --- UI Event Handlers (Buttons, Dropdowns, etc.) ---
        $('#edit-profile-btn').on('click', () => {
            $('#profile-view-mode').hide();
            $('#profile-edit-mode').show();
            if (CONFIG.hasData) {
                $('#document-section-container').show();
            }
        });

        $('#cancel-edit-btn').on('click', () => {
            Swal.fire({
                title: 'Anda yakin?',
                text: "Perubahan yang belum disimpan akan hilang.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, batalkan!',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (CONFIG.hasData) {
                        window.location.reload();
                    } else {
                        $('#profile-edit-mode').hide();
                        $('#document-section-container').hide();
                        $('#profile-view-mode').show();
                    }
                }
            });
        });

        $('#view-profile-btn').on('click', function() {
            window.location.reload();
        });

        // Region Dropdowns
        $('#provinsi').on('change', function() {
            const provId = $(this).val();
            if (provId) loadOptions('#kabupaten', CONFIG.urls.kabupaten, {
                id_provinsi: provId
            }, CONFIG.existing.kabupaten, () => {
                if (CONFIG.existing.kabupaten) {
                    $('#kabupaten').trigger('change');
                    CONFIG.existing.kabupaten = '';
                }
            });
            else resetDropdowns(['#kabupaten', '#kecamatan', '#kelurahan']);
        });
        $('#kabupaten').on('change', function() {
            const kabId = $(this).val();
            if (kabId) loadOptions('#kecamatan', CONFIG.urls.kecamatan, {
                id_kabupaten: kabId
            }, CONFIG.existing.kecamatan, () => {
                if (CONFIG.existing.kecamatan) {
                    $('#kecamatan').trigger('change');
                    CONFIG.existing.kecamatan = '';
                }
            });
            else resetDropdowns(['#kecamatan', '#kelurahan']);
        });
        $('#kecamatan').on('change', function() {
            const kecId = $(this).val();
            if (kecId) loadOptions('#kelurahan', CONFIG.urls.kelurahan, {
                id_kecamatan: kecId
            }, CONFIG.existing.kelurahan, () => {
                CONFIG.existing.kelurahan = '';
            });
            else resetDropdowns(['#kelurahan']);
        });


        function loadOptions(selector, url, data, selectedValue = '', callback) {
            const $el = $(selector);
            $el.html('<option value="">Memuat...</option>').prop('disabled', true);
            data[csrfTokenName] = csrfHashValue;
            $.post(url, data, function(response) {
                let options = '<option value="">Pilih...</option>';
                if (response) {
                    response.forEach(item => {
                        options += `<option value="${item.id}" ${item.id == selectedValue ? 'selected' : ''}>${item.nama}</option>`;
                    });
                }
                $el.html(options).prop('disabled', false);
                if (callback) callback();
            }, 'json').fail(() => $el.html('<option value="">Gagal memuat</option>'));
        }

        function resetDropdowns(selectors) {
            selectors.forEach(s => $(s).html('<option value="">Pilih...</option>').prop('disabled', true));
        }

        // Employment / Education Sections
        $('#status_pekerjaan').on('change', function() {
            const status = $(this).val();
            $('#pendidikan-section, #pekerjaan-section').hide();
            if (status === 'Pelajar/Mahasiswa') {
                $('#pendidikan-section').show();
                $('#pendidikan_terakhir').trigger('change');
            } else if (status === 'Bekerja') {
                $('#pekerjaan-section').show();
            }
        });

        // School Search
        let searchTimeoutSekolah;
        $('#pendidikan_terakhir').on('change', function() {
            const $namaSekolah = $('#nama_sekolah');
            if ($(this).val()) {
                $namaSekolah.prop('disabled', false).attr('placeholder', 'Ketik min. 3 huruf untuk mencari...');
            } else {
                $namaSekolah.prop('disabled', true).attr('placeholder', 'Pilih jenjang pendidikan terlebih dahulu.');
                $namaSekolah.val('');
            }
        });

        $('#nama_sekolah').on('keyup', function() {
            clearTimeout(searchTimeoutSekolah);
            const query = $(this).val();
            const jenjang = $('#pendidikan_terakhir').val();
            const $dropdown = $('#sekolah-dropdown');
            if (query.length < 3) {
                $dropdown.hide();
                return;
            }
            searchTimeoutSekolah = setTimeout(() => {
                $.post(CONFIG.urls.sekolah, {
                    jenjang,
                    search: query,
                    [csrfTokenName]: csrfHashValue
                }, function(response) {
                    let html = '';
                    if (response.success && response.results.length > 0) {
                        response.results.forEach(item => {
                            html += `<a href="#" class="dropdown-item sekolah-item">${item.display}</a>`;
                        });
                    } else {
                        html = '<div class="dropdown-item text-muted">Tidak ditemukan.</div>';
                    }
                    $dropdown.html(html).show();
                }, 'json');
            }, 500);
        });

        $(document).on('click', '.sekolah-item', function(e) {
            e.preventDefault();
            $('#nama_sekolah').val($(this).text().split(' - ')[0]);
            $('#sekolah-dropdown').hide();
        });


        // --- Form Submission Logic ---
        $('#asesi-profile-form').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#submit-profile-btn');
            const formData = new FormData(this);
            toggleLoading($btn, true);
            clearAllErrors();

            $.ajax({
                url: CONFIG.urls.saveProfile,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.is_new) {
                        // ** SEAMLESS NEW USER FLOW **
                        Swal.fire({
                            icon: 'success',
                            title: 'Profil Tersimpan!',
                            text: 'Silakan lanjutkan untuk mengunggah dokumen Anda.',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $('#asesi-profile-form').find('[name="id_asesi"]').remove();
                        $('#asesi-profile-form').prepend(`<input type="hidden" name="id_asesi" value="${response.id_asesi}">`);
                        $('#document-upload-form [name="id_asesi"]').val(response.id_asesi);

                        CONFIG.hasData = true;
                        $('#submit-profile-btn .submit-text').html('<i class="fas fa-save"></i> Update Profil');
                        $('#cancel-edit-btn').show();
                        $('#view-profile-btn').show();

                        $('#document-section-container').slideDown(400, function() {
                            $('html, body').animate({
                                scrollTop: $("#document-section-container").offset().top - 20
                            }, 500);
                        });

                    } else {
                        // ** STANDARD UPDATE FLOW **
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#view-profile-btn').show();
                    }
                },
                error: function(jqXHR) {
                    const response = jqXHR.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message || 'Periksa kembali isian Anda.'
                    });
                    if (response.errors) displayAllErrors(response.errors);
                },
                complete: () => toggleLoading($btn, false)
            });
        });

        $('#document-upload-form').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#submit-docs-btn');
            toggleLoading($btn, true);

            const formData = new FormData();
            formData.append(csrfTokenName, csrfHashValue);
            formData.append('id_asesi', $('[name="id_asesi"]', this).val());

            let fileAdded = false;
            for (const fieldName in filePondInstances) {
                const pond = filePondInstances[fieldName];
                const files = pond.getFiles();
                if (files.length > 0) {
                    formData.append(fieldName, files[0].file);
                    fileAdded = true;
                }
            }

            if (!fileAdded) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Ada File',
                    text: 'Silakan pilih setidaknya satu file untuk diunggah.'
                });
                toggleLoading($btn, false);
                return;
            }

            $.ajax({
                url: CONFIG.urls.uploadDocs,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Dokumen Terunggah!',
                        text: response.message,
                    });
                    Object.values(filePondInstances).forEach(pond => {
                        if (pond.getFiles().length > 0) {
                            pond.removeFiles();
                        }
                    });
                },
                error: function(jqXHR) {
                    const response = jqXHR.responseJSON;
                    let errorMsg = response.message || 'Gagal mengunggah. Periksa kembali file Anda.';
                    if (response.errors) {
                        const errorList = Object.values(response.errors).map(err => `<li>${err}</li>`).join('');
                        errorMsg += `<br><br><ul class="text-start">${errorList}</ul>`;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengunggah',
                        html: errorMsg,
                    });
                },
                complete: () => toggleLoading($btn, false)
            });
        });


        // --- HELPER FUNCTIONS ---
        function toggleLoading($btn, isLoading) {
            $btn.prop('disabled', isLoading);
            $btn.find('.submit-text').toggleClass('d-none', isLoading);
            $btn.find('.submit-loading').toggleClass('d-none', !isLoading);
        }

        function displayAllErrors(errors) {
            $.each(errors, function(field, message) {
                markFieldInvalid($(`[name="${field}"]`), message);
            });
        }

        function clearAllErrors() {
            $('[data-validate]').each(function() {
                clearFieldError($(this));
            });
        }

        // --- PAGE INITIALIZATION ---
        if (CONFIG.hasData) {
            $('#status_pekerjaan').trigger('change');
            if (CONFIG.existing.provinsi) {
                $('#provinsi').trigger('change');
            }
        } else {
            $('#profile-edit-mode').show();
        }

    });
</script>
<?= $this->endSection() ?>