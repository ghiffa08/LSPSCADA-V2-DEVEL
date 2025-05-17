<?= $this->extend("layouts/asesi/layout-app"); ?>

<?= $this->section("css") ?>
<?= $this->include('components/signature-pad/css-signature'); ?>
<?= $this->endSection() ?>

<?= $this->section("content"); ?>
<h2 class="section-title">Hi, <?= user()->fullname ?>!</h2>
<p class="section-lead">
    Change information about yourself on this page.
</p>

<?= form_open_multipart('asesi/save') ?>
<?= csrf_field() ?>

<?php if (isset($asesi)) : ?>
    <input type="hidden" name="id_asesi" value="<?= isset($asesi['id_asesi']) ? $asesi['id_asesi'] : $asesi->id_asesi ?>">
<?php endif; ?>

<input type="hidden" name="user_id" value="<?= user_id() ?>">

<?php if (session()->has('error')) : ?>
    <div class="alert alert-danger"><?= session('error') ?></div>
<?php endif; ?>

<?php if (session()->has('success')) : ?>
    <div class="alert alert-success"><?= session('success') ?></div>
<?php endif; ?>

<?php if (session()->has('errors')) : ?>
    <ul class="alert alert-danger mb-0">
        <?php foreach (session('errors') as $error) : ?>
            <li><?= $error ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<div class="bg-primary text-white text-center py-3 rounded mb-3 shadow-sm">
    <h4 class="mb-0">DATA DIRI PEMOHON</h4>
</div>

<div class="card">
    <div class="card-body">


        <div class="form-group mb-3">
            <label class="form-label">Nama Lengkap<span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="fullname" value="<?= user()->fullname ?>">
            <input type="hidden" name="user_id" value="<?= user()->id ?>">
        </div>
        <div class="row">
            <div class="form-group mb-3 col-12 col-md-4">
                <label class="form-label">Email<span class="text-danger">*</span></label>
                <input type="text" class="form-control" value="<?= user()->email ?>" readonly>
                <input type="hidden" name="email" value="<?= user()->email ?>">
            </div>
            <div class="form-group mb-3 col-12 col-md-4">
                <label class="form-label">Nomor Handphone<span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="no_telp" value="<?= user()->no_telp ?>">
            </div>
            <div class="form-group mb-3 col-12 col-md-4">
                <label class="form-label">Nomor Telpon</label>
                <input type="number" class="form-control <?php if (session('errors.telpon_rumah')) : ?>is-invalid<?php endif ?>"
                    name="telpon_rumah"
                    value="<?= setFormValue('telpon_rumah', $asesi ?? null) ?>"
                    placeholder="Masukan Nomor Telpon Rumah">
                <?php if (session('errors.telpon_rumah')) { ?>
                    <div class="invalid-feedback">
                        <?= session('errors.telpon_rumah') ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="form-group mb-3">
            <label class="form-label">Nomor KTP/NIK/Paspor<span class="text-danger">*</span></label>
            <input type="number" class="form-control <?php if (session('errors.nik')) : ?>is-invalid<?php endif ?>"
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
        <div class="form-group mb-3 mb-3">
            <label class="form-label" class="form-label">Jenis Kelamin<span class="text-danger">*</span></label>
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
    <h4 class="mb-0">RIWAYAT PENDIDIKAN PEMOHON</h4>
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
                <option value="Diploma" <?= isSelected("Diploma", "pendidikan_terakhir", $asesi ?? null) ?>>Diploma</option>
                <option value="Sarjana" <?= isSelected("Sarjana", "pendidikan_terakhir", $asesi ?? null) ?>>Sarjana</option>
                <option value="Magister" <?= isSelected("Magister", "pendidikan_terakhir", $asesi ?? null) ?>>Magister</option>
                <option value="Doktor" <?= isSelected("Doktor", "pendidikan_terakhir", $asesi ?? null) ?>>Doktor</option>
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
    <h4 class="mb-0">ALAMAT DOMISILI PEMOHON</h4>
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
                    <!-- Will be filled via Ajax -->
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
                    <!-- Will be filled via Ajax -->
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
                    <!-- Will be filled via Ajax -->
                </select>
                <?php if (session('errors.kelurahan')): ?>
                    <div class="invalid-feedback"><?= session('errors.kelurahan') ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="form-group mb-3 col-12 col-md-4">
                <label class="form-label">RT<span class="text-danger">*</span></label>
                <input type="number" class="form-control <?php if (session('errors.rt')) : ?>is-invalid<?php endif ?>"
                    name="rt"
                    value="<?= setFormValue('rt', $asesi ?? null) ?>"
                    placeholder="Masukan RT">
                <?php if (session('errors.rt')) { ?>
                    <div class="invalid-feedback">
                        <?= session('errors.rt') ?>
                    </div>
                <?php } ?>
            </div>
            <div class="form-group mb-3 col-12 col-md-4">
                <label class="form-label">RW<span class="text-danger">*</span></label>
                <input type="number" class="form-control <?php if (session('errors.rw')) : ?>is-invalid<?php endif ?>"
                    name="rw"
                    value="<?= setFormValue('rw', $asesi ?? null) ?>"
                    placeholder="Masukan RW">
                <?php if (session('errors.rw')) { ?>
                    <div class="invalid-feedback">
                        <?= session('errors.rw') ?>
                    </div>
                <?php } ?>
            </div>
            <div class="form-group mb-3 col-12 col-md-4">
                <label class="form-label">Kode Pos<span class="text-danger">*</span></label>
                <input type="number" class="form-control <?php if (session('errors.kode_pos')) : ?>is-invalid<?php endif ?>"
                    name="kode_pos"
                    value="<?= setFormValue('kode_pos', $asesi ?? null) ?>"
                    placeholder="Masukan Kode Pos">
                <?php if (session('errors.kode_pos')) { ?>
                    <div class="invalid-feedback">
                        <?= session('errors.kode_pos') ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div class="bg-primary text-white text-center py-3 rounded mb-3 shadow-sm">
    <h4 class="mb-0">DATA PEKERJAAN</h4>
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
                <option value="Lainya" <?= isSelected("Lainya", "pekerjaan", $asesi ?? null) ?>>Lainya</option>
            </select>
            <?php if (session('errors.pekerjaan')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.pekerjaan') ?>
                </div>
            <?php } ?>
        </div>
        <div class="form-group mb-3">
            <label class="form-label">Nama Instansi</label>
            <input type="text" class="form-control <?php if (session('errors.nama_lembaga')) : ?>is-invalid<?php endif ?>"
                name="nama_lembaga"
                value="<?= setFormValue('nama_lembaga', $asesi ?? null) ?>"
                placeholder="Organisasi / Tempat Kerja / Instansi Terkait / Freelance / - (bila tidak ada)">
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
                placeholder="Jabatan di Perusahaan">
            <?php if (session('errors.jabatan')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.jabatan') ?>
                </div>
            <?php } ?>
        </div>
        <div class="form-group mb-3">
            <label class="form-label">Alamat Lembaga / Perusahaan</label>
            <textarea class="form-control <?php if (session('errors.alamat_perusahaan')) : ?>is-invalid<?php endif ?>"
                name="alamat_perusahaan"
                id="inputDescription"><?= setFormValue('alamat_perusahaan', $asesi ?? null) ?></textarea>
            <?php if (session('errors.alamat_perusahaan')) { ?>
                <div class="invalid-feedback">
                    <?= session('errors.alamat_perusahaan') ?>
                </div>
            <?php } ?>
        </div>
        <div class="row">
            <div class="form-group mb-3 col-12 col-md-6">
                <label class="form-label">Email Perusahaan</label>
                <input type="text" class="form-control <?php if (session('errors.email_perusahaan')) : ?>is-invalid<?php endif ?>"
                    name="email_perusahaan"
                    value="<?= setFormValue('email_perusahaan', $asesi ?? null) ?>"
                    placeholder="Masukan Nomor Email Perusahaan">
                <?php if (session('errors.email_perusahaan')) { ?>
                    <div class="invalid-feedback">
                        <?= session('errors.email_perusahaan') ?>
                    </div>
                <?php } ?>
            </div>
            <div class="form-group mb-3 col-12 col-md-6">
                <label class="form-label">Nomor Telp Perusahaan</label>
                <input type="text" class="form-control <?php if (session('errors.no_telp_perusahaan')) : ?>is-invalid<?php endif ?>"
                    name="no_telp_perusahaan"
                    value="<?= setFormValue('no_telp_perusahaan', $asesi ?? null) ?>"
                    placeholder="Masukan Nomor Telpon Perusahaan">
                <?php if (session('errors.no_telp_perusahaan')) { ?>
                    <div class="invalid-feedback">
                        <?= session('errors.no_telp_perusahaan') ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div class="bg-primary text-white text-center py-3 rounded mb-3 shadow-sm">
    <h4 class="mb-0">Tanda Tangan Asesi</h4>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <!-- Input Tanda Tangan -->
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="">Tanda Tangan</label>
                    <div class="mb-2 text-muted">Silakan tanda tangani pada area di bawah ini.</div>
                    <div class="signature-container">
                        <div class="signature-pad-wrapper">
                            <canvas id="signature-pad"></canvas>
                        </div>
                        <input type="hidden" id="signature-data" name="tanda_tangan" value="<?= isset($asesi['tanda_tangan']) ? $asesi['tanda_tangan'] : '' ?>">
                        <input type="file" id="signature-upload" accept="image/png, image/jpeg" style="display: none;">
                        <div class="btn-group mt-2">
                            <button type="button" id="clear-signature" class="btn btn-danger">Hapus</button>
                            <button type="button" id="upload-signature" class="btn btn-info">Upload Tanda Tangan</button>
                        </div>

                        <div id="signature-status"></div>
                    </div>
                </div>
            </div>

            <!-- Preview Tanda Tangan -->
            <div class="col-12 col-md-6">
                <?php if (!empty(user()->tanda_tangan) || (isset($asesi['tanda_tangan']) && !empty($asesi['tanda_tangan']))) : ?>
                    <div class="form-group">
                        <label for="">Tanda Tangan Saat Ini</label>
                        <div class="mb-2 text-muted">Tanda tangan yang tersimpan:</div>
                        <div class="signature-preview">
                            <?php if (isset($asesi['tanda_tangan']) && !empty($asesi['tanda_tangan'])) : ?>
                                <img src="<?= get_signature_url($asesi['tanda_tangan']) ?>" alt="Tanda Tangan" class="img-fluid border rounded">
                            <?php else : ?>
                                <img src="<?= get_signature_url(user()->tanda_tangan) ?>" alt="Tanda Tangan" class="img-fluid border rounded">
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-footer text-right bg-whitesmoke">
        <?= form_submit('submit', isset($asesi) ? 'Update' : 'Submit', ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<?= form_close() ?>


<?= $this->endSection() ?>

<?= $this->section("js") ?>
<?= $this->include('components/signature-pad/js-signature'); ?>
<!-- ===== Script Dynamic Dependent ===== -->
<!-- ===== Optimized Script Dynamic Dependent ===== -->
<script>
    $(document).ready(function() {
        // Cache for storing fetched data to avoid redundant requests
        const dataCache = {
            kabupaten: {},
            kecamatan: {},
            desa: {}
        };

        const existingValues = {
            kabupaten: <?= json_encode(getDropdownValue('kabupaten', $asesi)) ?>,
            kecamatan: <?= json_encode(getDropdownValue('kecamatan', $asesi)) ?>,
            kelurahan: <?= json_encode(getDropdownValue('kelurahan', $asesi)) ?>
        };

        const endpoints = {
            kabupaten: "<?= base_url('api/kabupaten') ?>",
            kecamatan: "<?= base_url('api/kecamatan') ?>",
            desa: "<?= base_url('api/desa') ?>"
        };

        const elements = {
            provinsi: "#id_provinsi",
            kabupaten: "#id_kabupaten",
            kecamatan: "#id_kecamatan",
            desa: "#id_desa"
        };

        const placeholders = {
            kabupaten: '-- Pilih Kabupaten/Kota --',
            kecamatan: '-- Pilih Kecamatan --',
            desa: '-- Pilih Kelurahan/Desa --'
        };

        // Preload select2 for faster rendering if you're using it
        $(elements.provinsi).select2({
            placeholder: "-- Pilih Provinsi --",
            allowClear: true
        });
        $(elements.kabupaten).select2({
            placeholder: placeholders.kabupaten,
            allowClear: true
        });
        $(elements.kecamatan).select2({
            placeholder: placeholders.kecamatan,
            allowClear: true
        });
        $(elements.desa).select2({
            placeholder: placeholders.desa,
            allowClear: true
        });

        function resetOptions(targets) {
            targets.forEach(t => {
                const $el = $(elements[t]);
                $el.html(`<option value="">${placeholders[t]}</option>`);
                // Efficiently update select2 if used
                if ($el.data('select2')) {
                    $el.val('').trigger('change.select2');
                } else {
                    $el.val('');
                }
            });
        }

        function loadDropdown(type, parentId, target, nextTrigger = null) {
            // Check cache first
            const cacheKey = type + '_' + parentId;
            if (dataCache[type][cacheKey]) {
                renderOptions(dataCache[type][cacheKey], target, nextTrigger);
                return;
            }

            // Show loading indicator
            const $targetEl = $(elements[target]);
            $targetEl.prop('disabled', true);

            // Prepare data object based on type
            let postData = {};
            switch (type) {
                case 'kabupaten':
                    postData = {
                        id_provinsi: parentId,
                        selected_value: existingValues.kabupaten
                    };
                    break;
                case 'kecamatan':
                    postData = {
                        id_kabupaten: parentId,
                        selected_value: existingValues.kecamatan
                    };
                    break;
                case 'desa':
                    postData = {
                        id_kecamatan: parentId,
                        selected_value: existingValues.kelurahan
                    };
                    break;
            }

            $.ajax({
                type: "POST",
                url: endpoints[type],
                data: postData,
                dataType: "json",
                success: function(response) {
                    // Cache the response
                    dataCache[type][cacheKey] = response;
                    renderOptions(response, target, nextTrigger);
                },
                error: function() {
                    $targetEl.prop('disabled', false);
                    console.error(`Failed to load ${type} data`);
                }
            });
        }

        function renderOptions(response, target, nextTrigger) {
            const $targetEl = $(elements[target]);
            $targetEl.html(response.options);
            $targetEl.prop('disabled', false);

            // Use more efficient trigger method for select2 if used
            if ($targetEl.data('select2')) {
                $targetEl.trigger('change.select2');
            } else {
                $targetEl.trigger('change');
            }

            if (nextTrigger && $(elements[nextTrigger]).val()) {
                $(elements[nextTrigger]).trigger('change');
            }
        }

        // Use delegated event handling for better performance
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

        // Lazy initialization - only trigger the cascade when form is visible
        // or use IntersectionObserver for modern browsers
        if ($(elements.provinsi).is(':visible') && $(elements.provinsi).val()) {
            $(elements.provinsi).trigger('change');
        }
    });
</script>
<?= $this->endSection() ?>