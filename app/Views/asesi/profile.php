<?= $this->extend("layouts/asesi/layout-app"); ?>

<?= $this->section("css") ?>
<?= $this->include('components/signature-pad/css-signature'); ?>
<?= $this->endSection() ?>

<?= $this->section("content"); ?>
<h2 class="section-title">Hi, <?= $user->fullname ?? $user->nama_lengkap ?>!</h2>
<p class="section-lead">
    Kelola informasi profil Anda di halaman ini.F
</p>

<?php if (session()->has('error')) : ?>
    <div class="alert alert-danger"><?= session('error') ?></div>
<?php endif; ?>

<?php if (session()->has('success')) : ?>
    <div class="alert alert-success"><?= session('success') ?></div>
<?php endif; ?>

<?php if (session()->has('pesan')) : ?>
    <div class="alert alert-success"><?= session('pesan') ?></div>
<?php endif; ?>

<?php if (session()->has('errors')) : ?>
    <ul class="alert alert-danger mb-0">
        <?php foreach (session('errors') as $error) : ?>
            <li><?= $error ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- Data User (Always shown) -->
<div class="bg-info text-white text-center py-3 rounded mb-3 shadow-sm">
    <h4 class="mb-0">INFORMASI AKUN</h4>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label"><strong>Nama Lengkap</strong></label>
                    <p class="form-control-plaintext"><?= $user->nama_lengkap ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label"><strong>Email</strong></label>
                    <p class="form-control-plaintext"><?= $user->email ?></p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label"><strong>Username</strong></label>
                    <p class="form-control-plaintext"><?= $user->username ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label"><strong>Nomor Telepon</strong></label>
                    <p class="form-control-plaintext"><?= $user->no_telp ?? '-' ?></p>
                </div>
            </div>
        </div>

        <?php if (!$hasAsesiData): ?>
            <div class="text-center mt-4">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Silakan lengkapi profil detail Anda di bawah ini untuk dapat mendaftar asesmen.</strong>
                </div>
            </div>
        <?php else: ?>
            <div class="mt-4">
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle"></i> <strong>Profil Detail Telah Dilengkapi</strong></h6>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <small><strong>NIK:</strong> <?= $asesi->nik ?? '-' ?></small><br>
                            <small><strong>Tempat, Tanggal Lahir:</strong> <?= ($asesi->tempat_lahir ?? '-') . ', ' . ($asesi->tanggal_lahir ?? '-') ?></small><br>
                            <small><strong>Jenis Kelamin:</strong> <?= $asesi->jenis_kelamin ?? '-' ?></small>
                        </div>
                        <div class="col-md-6">
                            <small><strong>Pendidikan:</strong> <?= $asesi->pendidikan_terakhir ?? '-' ?></small><br>
                            <small><strong>Sekolah/PT:</strong> <?= $asesi->nama_sekolah ?? '-' ?></small><br>
                            <small><strong>Perusahaan:</strong> <?= $asesi->nama_lembaga ?? '-' ?></small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Profile Form - Always Show -->
<?= form_open_multipart('asesi/save') ?>
<?= csrf_field() ?>

<?php if ($hasAsesiData && isset($asesi)) : ?>
    <input type="hidden" name="id_asesi" value="<?= $asesi->id_asesi ?>">
<?php endif; ?>

<input type="hidden" name="id_user" value="<?= $user->id ?>">

<div class="bg-primary text-white text-center py-3 rounded mb-3 shadow-sm">
    <h4 class="mb-0">DETAIL PROFIL ASESI</h4>
</div>

<div class="card">
    <div class="card-body">
        <div class="form-group mb-3">
            <label class="form-label">Nama Lengkap<span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="fullname" value="<?= $user->fullname ?? $user->nama_lengkap ?>" readonly>
            <small class="text-muted">Nama lengkap diambil dari data akun dan tidak dapat diubah di sini.</small>
        </div>

        <div class="row">
            <div class="form-group mb-3 col-12 col-md-6">
                <label class="form-label">Email<span class="text-danger">*</span></label>
                <input type="text" class="form-control" value="<?= $user->email ?>" readonly>
                <input type="hidden" name="email" value="<?= $user->email ?>">
                <small class="text-muted">Email diambil dari data akun dan tidak dapat diubah di sini.</small>
            </div>
            <div class="form-group mb-3 col-12 col-md-6">
                <label class="form-label">Nomor Telepon Rumah</label>
                <input type="text" class="form-control <?php if (session('errors.telpon_rumah')) : ?>is-invalid<?php endif ?>"
                    name="telpon_rumah"
                    value="<?= setFormValue('telpon_rumah', $asesi ?? null) ?>"
                    placeholder="Masukan Nomor Telepon Rumah">
                <?php if (session('errors.telpon_rumah')) { ?>
                    <div class="invalid-feedback">
                        <?= session('errors.telpon_rumah') ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Nomor KTP/NIK/Paspor<span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php if (session('errors.nik')) : ?>is-invalid<?php endif ?>"
                name="nik"
                value="<?= setFormValue('nik', $asesi ?? null) ?>"
                placeholder="Masukan Nomor Induk Kependudukan">
            <?php if (session('errors.nik')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.nik') ?>
                </div>
            <?php } ?>
        </div>

        <div class="row">
            <div class="form-group mb-3 col-12 col-md-6">
                <label class="form-label">Tempat Lahir<span class="text-danger">*</span></label>
                <input type="text" class="form-control <?php if (session('errors.tempat_lahir')) : ?>is-invalid<?php endif ?>"
                    name="tempat_lahir"
                    value="<?= setFormValue('tempat_lahir', $asesi ?? null) ?>"
                    placeholder="Masukan Tempat Lahir">
                <?php if (session('errors.tempat_lahir')) { ?>
                    <div class="invalid-feedback">
                        <?= session('errors.tempat_lahir') ?>
                    </div>
                <?php } ?>
            </div>
            <div class="form-group mb-3 col-12 col-md-6">
                <label class="form-label">Tanggal Lahir<span class="text-danger">*</span></label>
                <input type="date" class="form-control <?php if (session('errors.tanggal_lahir')) : ?>is-invalid<?php endif ?>"
                    name="tanggal_lahir"
                    value="<?= setFormValue('tanggal_lahir', $asesi ?? null) ?>">
                <?php if (session('errors.tanggal_lahir')) { ?>
                    <div class="invalid-feedback">
                        <?= session('errors.tanggal_lahir') ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Jenis Kelamin<span class="text-danger">*</span></label>
            <select class="form-control select2 <?php if (session('errors.jenis_kelamin')) : ?>is-invalid<?php endif ?>" name="jenis_kelamin">
                <option value="">Pilih Jenis Kelamin</option>
                <option value="Laki-Laki" <?= isSelected("Laki-Laki", "jenis_kelamin", $asesi ?? null) ?>>Laki-Laki</option>
                <option value="Perempuan" <?= isSelected("Perempuan", "jenis_kelamin", $asesi ?? null) ?>>Perempuan</option>
            </select>
            <?php if (session('errors.jenis_kelamin')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.jenis_kelamin') ?>
                </div>
            <?php } ?>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Kebangsaan<span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php if (session('errors.kebangsaan')) : ?>is-invalid<?php endif ?>"
                name="kebangsaan"
                value="<?= setFormValue('kebangsaan', $asesi ?? null) ?>"
                placeholder="WNI/WNA">
            <?php if (session('errors.kebangsaan')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.kebangsaan') ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="bg-primary text-white text-center py-3 rounded mb-3 shadow-sm">
    <h4 class="mb-0">RIWAYAT PENDIDIKAN</h4>
</div>

<div class="card">
    <div class="card-body">
        <div class="form-group mb-3">
            <label class="form-label">Pendidikan Terakhir<span class="text-danger">*</span></label>
            <select name="pendidikan_terakhir" class="form-control select2 <?php if (session('errors.pendidikan_terakhir')) : ?>is-invalid<?php endif ?>">
                <option value="">Pilih Pendidikan Terakhir</option>
                <option value="SD" <?= isSelected("SD", "pendidikan_terakhir", $asesi ?? null) ?>>SD</option>
                <option value="SMP" <?= isSelected("SMP", "pendidikan_terakhir", $asesi ?? null) ?>>SMP</option>
                <option value="SMA/SMK" <?= isSelected("SMA/SMK", "pendidikan_terakhir", $asesi ?? null) ?>>SMA/SMK</option>
                <option value="D3" <?= isSelected("D3", "pendidikan_terakhir", $asesi ?? null) ?>>Diploma (D3)</option>
                <option value="S1" <?= isSelected("S1", "pendidikan_terakhir", $asesi ?? null) ?>>Sarjana (S1)</option>
                <option value="S2" <?= isSelected("S2", "pendidikan_terakhir", $asesi ?? null) ?>>Magister (S2)</option>
                <option value="S3" <?= isSelected("S3", "pendidikan_terakhir", $asesi ?? null) ?>>Doktor (S3)</option>
            </select>
            <?php if (session('errors.pendidikan_terakhir')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.pendidikan_terakhir') ?>
                </div>
            <?php } ?>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Nama Sekolah / Perguruan Tinggi<span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php if (session('errors.nama_sekolah')) : ?>is-invalid<?php endif ?>"
                name="nama_sekolah"
                value="<?= setFormValue('nama_sekolah', $asesi ?? null) ?>"
                placeholder="Masukan Nama Sekolah Atau Perguruan Tinggi">
            <?php if (session('errors.nama_sekolah')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.nama_sekolah') ?>
                </div>
            <?php } ?>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Jurusan / Program Studi<span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php if (session('errors.jurusan')) : ?>is-invalid<?php endif ?>"
                name="jurusan"
                value="<?= setFormValue('jurusan', $asesi ?? null) ?>"
                placeholder="Masukan Nama Jurusan Atau Program Studi">
            <?php if (session('errors.jurusan')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.jurusan') ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="bg-primary text-white text-center py-3 rounded mb-3 shadow-sm">
    <h4 class="mb-0">ALAMAT DOMISILI</h4>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="form-group mb-3 col-12 col-md-6">
                <label class="form-label">Provinsi<span class="text-danger">*</span></label>
                <select class="form-control select2 <?= session('errors.provinsi') ? 'is-invalid' : '' ?>"
                    name="provinsi" id="id_provinsi">
                    <option value="">-- Pilih Provinsi --</option>
                    <?php foreach ($provinsi as $row): ?>
                        <option value="<?= $row['id'] ?>" <?= isSelected($row['id'], 'provinsi', $asesi) ?>><?= $row['nama'] ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (session('errors.provinsi')): ?>
                    <div class="invalid-feedback"><?= session('errors.provinsi') ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group mb-3 col-12 col-md-6">
                <label class="form-label">Kabupaten/Kota<span class="text-danger">*</span></label>
                <select class="form-control select2 <?= session('errors.kabupaten') ? 'is-invalid' : '' ?>"
                    name="kabupaten" id="id_kabupaten">
                    <option value="">-- Pilih Kabupaten/Kota --</option>
                </select>
                <?php if (session('errors.kabupaten')): ?>
                    <div class="invalid-feedback"><?= session('errors.kabupaten') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="form-group mb-3 col-12 col-md-6">
                <label class="form-label">Kecamatan<span class="text-danger">*</span></label>
                <select class="form-control select2 <?= session('errors.kecamatan') ? 'is-invalid' : '' ?>"
                    name="kecamatan" id="id_kecamatan">
                    <option value="">-- Pilih Kecamatan --</option>
                </select>
                <?php if (session('errors.kecamatan')): ?>
                    <div class="invalid-feedback"><?= session('errors.kecamatan') ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group mb-3 col-12 col-md-6">
                <label class="form-label">Kelurahan/Desa<span class="text-danger">*</span></label>
                <select class="form-control select2 <?= session('errors.kelurahan') ? 'is-invalid' : '' ?>"
                    name="kelurahan" id="id_desa">
                    <option value="">-- Pilih Kelurahan/Desa --</option>
                </select>
                <?php if (session('errors.kelurahan')): ?>
                    <div class="invalid-feedback"><?= session('errors.kelurahan') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="form-group mb-3 col-12 col-md-4">
                <label class="form-label">RT<span class="text-danger">*</span></label>
                <input type="text" class="form-control <?php if (session('errors.rt')) : ?>is-invalid<?php endif ?>"
                    name="rt"
                    value="<?= setFormValue('rt', $asesi ?? null) ?>"
                    placeholder="RT">
                <?php if (session('errors.rt')) { ?>
                    <div class="invalid-feedback">
                        <?= session('errors.rt') ?>
                    </div>
                <?php } ?>
            </div>
            <div class="form-group mb-3 col-12 col-md-4">
                <label class="form-label">RW<span class="text-danger">*</span></label>
                <input type="text" class="form-control <?php if (session('errors.rw')) : ?>is-invalid<?php endif ?>"
                    name="rw"
                    value="<?= setFormValue('rw', $asesi ?? null) ?>"
                    placeholder="RW">
                <?php if (session('errors.rw')) { ?>
                    <div class="invalid-feedback">
                        <?= session('errors.rw') ?>
                    </div>
                <?php } ?>
            </div>
            <div class="form-group mb-3 col-12 col-md-4">
                <label class="form-label">Kode Pos</label>
                <input type="text" class="form-control <?php if (session('errors.kode_pos')) : ?>is-invalid<?php endif ?>"
                    name="kode_pos"
                    value="<?= setFormValue('kode_pos', $asesi ?? null) ?>"
                    placeholder="Kode Pos">
                <?php if (session('errors.kode_pos')) { ?>
                    <div class="invalid-feedback">
                        <?= session('errors.kode_pos') ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">No. Handphone<span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php if (session('errors.no_hp')) : ?>is-invalid<?php endif ?>"
                name="no_hp"
                value="<?= $user->no_hp ?? '' ?>"
                placeholder="Masukan Nomor Handphone">
            <?php if (session('errors.no_hp')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.no_hp') ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="bg-primary text-white text-center py-3 rounded mb-3 shadow-sm">
    <h4 class="mb-0">INFORMASI PEKERJAAN</h4>
</div>

<div class="card">
    <div class="card-body">
        <div class="form-group mb-3">
            <label class="form-label">Pekerjaan<span class="text-danger">*</span></label>
            <select name="pekerjaan" class="form-control select2 <?php if (session('errors.pekerjaan')) : ?>is-invalid<?php endif ?>">
                <option value="">Pilih Pekerjaan</option>
                <option value="Pelajar/Mahasiswa" <?= isSelected("Pelajar/Mahasiswa", "pekerjaan", $asesi ?? null) ?>>Pelajar/Mahasiswa</option>
                <option value="Pegawai" <?= isSelected("Pegawai", "pekerjaan", $asesi ?? null) ?>>Pegawai</option>
                <option value="Wiraswasta" <?= isSelected("Wiraswasta", "pekerjaan", $asesi ?? null) ?>>Wiraswasta</option>
                <option value="Petani" <?= isSelected("Petani", "pekerjaan", $asesi ?? null) ?>>Petani</option>
                <option value="Guru" <?= isSelected("Guru", "pekerjaan", $asesi ?? null) ?>>Guru</option>
                <option value="Dokter" <?= isSelected("Dokter", "pekerjaan", $asesi ?? null) ?>>Dokter</option>
                <option value="Lainnya" <?= isSelected("Lainnya", "pekerjaan", $asesi ?? null) ?>>Lainnya</option>
            </select>
            <?php if (session('errors.pekerjaan')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.pekerjaan') ?>
                </div>
            <?php } ?>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Nama Lembaga/Perusahaan</label>
            <input type="text" class="form-control <?php if (session('errors.nama_lembaga')) : ?>is-invalid<?php endif ?>"
                name="nama_lembaga"
                value="<?= setFormValue('nama_lembaga', $asesi ?? null) ?>"
                placeholder="Masukan Nama Lembaga/Perusahaan">
            <?php if (session('errors.nama_lembaga')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.nama_lembaga') ?>
                </div>
            <?php } ?>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Jabatan</label>
            <input type="text" class="form-control <?php if (session('errors.jabatan')) : ?>is-invalid<?php endif ?>"
                name="jabatan"
                value="<?= setFormValue('jabatan', $asesi ?? null) ?>"
                placeholder="Masukan Jabatan">
            <?php if (session('errors.jabatan')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.jabatan') ?>
                </div>
            <?php } ?>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Alamat Perusahaan</label>
            <textarea class="form-control <?php if (session('errors.alamat_perusahaan')) : ?>is-invalid<?php endif ?>"
                name="alamat_perusahaan"
                rows="3"
                placeholder="Masukan Alamat Perusahaan"><?= setFormValue('alamat_perusahaan', $asesi ?? null) ?></textarea>
            <?php if (session('errors.alamat_perusahaan')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.alamat_perusahaan') ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Pastikan semua data dengan tanda <span class="text-danger">*</span> terisi dengan benar.
        </div>
        <div class="d-flex justify-content-between">
            <a href="<?= current_url() ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Info Akun
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> <?= $hasAsesiData ? 'Update Profil' : 'Simpan Profil' ?>
            </button>
        </div>
    </div>
</div>

<?= form_close() ?>

<?= $this->endSection() ?>

<?= $this->section("js") ?>
<?= $this->include('components/signature-pad/js-signature'); ?>

<script>
    $(document).ready(function() {
        // Initialize select2 if available
        if ($.fn.select2) {
            $('.select2').select2({
                width: '100%'
            });
        }

        // Dependent dropdown logic for location selection
        const elements = {
            provinsi: '#id_provinsi',
            kabupaten: '#id_kabupaten',
            kecamatan: '#id_kecamatan',
            desa: '#id_desa'
        };

        const endpoints = {
            kabupaten: '<?= base_url('api/kabupaten') ?>',
            kecamatan: '<?= base_url('api/kecamatan') ?>',
            desa: '<?= base_url('api/desa') ?>'
        };

        // Cache for dropdown data
        const dataCache = {
            kabupaten: {},
            kecamatan: {},
            desa: {}
        };

        // Existing values for edit mode
        const existingValues = {
            provinsi: '<?= setFormValue('provinsi', $asesi ?? null) ?>',
            kabupaten: '<?= setFormValue('kabupaten', $asesi ?? null) ?>',
            kecamatan: '<?= setFormValue('kecamatan', $asesi ?? null) ?>',
            kelurahan: '<?= setFormValue('kelurahan', $asesi ?? null) ?>'
        };

        function resetOptions(targets) {
            targets.forEach(target => {
                $(elements[target]).html('<option value="">-- Pilih ' +
                    (target === 'kabupaten' ? 'Kabupaten/Kota' :
                        target === 'kecamatan' ? 'Kecamatan' : 'Kelurahan/Desa') +
                    ' --</option>').prop('disabled', true);
            });
        }

        function loadDropdown(type, parentId, target, nextTrigger = null) {
            const $targetEl = $(elements[target]);
            const cacheKey = parentId;

            if (dataCache[type][cacheKey]) {
                renderOptions(dataCache[type][cacheKey], target, nextTrigger);
                return;
            }

            $targetEl.prop('disabled', true);

            // Prepare POST data according to controller's expected parameters
            let postData = {};

            if (type === 'kabupaten') {
                postData = {
                    id_provinsi: parentId,
                    selected_value: existingValues.kabupaten
                };
            } else if (type === 'kecamatan') {
                postData = {
                    id_kabupaten: parentId,
                    selected_value: existingValues.kecamatan
                };
            } else if (type === 'desa') {
                postData = {
                    id_kecamatan: parentId,
                    selected_value: existingValues.kelurahan
                };
            }

            $.ajax({
                type: "POST",
                url: endpoints[type],
                data: postData,
                dataType: "json",
                success: function(response) {
                    dataCache[type][cacheKey] = response;
                    renderOptions(response, target, nextTrigger);
                },
                error: function(xhr, status, error) {
                    $targetEl.prop('disabled', false);
                    console.error(`Failed to load ${type} data:`, error);
                    console.error('Response:', xhr.responseText);
                }
            });
        }

        function renderOptions(response, target, nextTrigger) {
            const $targetEl = $(elements[target]);
            $targetEl.html(response.options);
            $targetEl.prop('disabled', false);

            if ($targetEl.data('select2')) {
                $targetEl.trigger('change.select2');
            } else {
                $targetEl.trigger('change');
            }

            if (nextTrigger && $(elements[nextTrigger]).val()) {
                $(elements[nextTrigger]).trigger('change');
            }
        }

        // Event handlers
        $(document).on('change', elements.provinsi, function() {
            const id = $(this).val();
            if (!id) return resetOptions(['kabupaten', 'kecamatan', 'desa']);

            loadDropdown('kabupaten', id, 'kabupaten', existingValues.kabupaten ? 'kabupaten' : null);
        });

        $(document).on('change', elements.kabupaten, function() {
            const id = $(this).val();
            if (!id) return resetOptions(['kecamatan', 'desa']);

            loadDropdown('kecamatan', id, 'kecamatan', existingValues.kecamatan ? 'kecamatan' : null);
        });

        $(document).on('change', elements.kecamatan, function() {
            const id = $(this).val();
            if (!id) return resetOptions(['desa']);

            loadDropdown('desa', id, 'desa');
        });

        // Initialize cascade if editing existing data
        if ($(elements.provinsi).is(':visible') && $(elements.provinsi).val()) {
            $(elements.provinsi).trigger('change');
        }
    });
</script>
<?= $this->endSection() ?>