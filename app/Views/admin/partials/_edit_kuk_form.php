<div class="modal-header">
    <h5 class="modal-title">Edit Kriteria Unjuk Kerja (KUK)</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?= form_open(site_url('/master/kuk/update'), ['id' => 'edit-kuk-form']) ?>
<div class="modal-body">
    <input type="hidden" name="id" value="<?= $kuk['id_kuk'] ?>">

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Skema<span class="text-danger">*</span></label>
                <select class="form-control select2" name="id_skema" id="edit_id_skema" required>
                    <option value="">-- Pilih Skema --</option>
                    <?php foreach ($listSkema as $row): ?>
                        <option value="<?= $row['id_skema'] ?>" <?= ($kuk['id_skema'] == $row['id_skema']) ? 'selected' : '' ?>>
                            <?= esc($row['nama_skema']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback error-id_skema"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Unit<span class="text-danger">*</span></label>
                <select class="form-control select2" name="id_unit" id="edit_id_unit" required>
                    <option value="">-- Pilih Unit --</option>
                    <?php foreach ($listUnit as $row): ?>
                        <option value="<?= $row['id_unit'] ?>" <?= ($kuk['id_unit'] == $row['id_unit']) ? 'selected' : '' ?>>
                            <?= esc($row['kode_unit']) ?> - <?= esc($row['nama_unit']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback error-id_unit"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Elemen<span class="text-danger">*</span></label>
                <select class="form-control select2" name="id_elemen" id="edit_id_elemen" required>
                    <option value="">-- Pilih Elemen --</option>
                    <?php foreach ($listElemen as $row): ?>
                        <option value="<?= $row['id_elemen'] ?>" <?= ($kuk['id_elemen'] == $row['id_elemen']) ? 'selected' : '' ?>>
                            <?= esc($row['nama_elemen']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback error-id_elemen"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Kode KUK<span class="text-danger">*</span></label>
                <input type="text" name="kode_kuk" class="form-control" value="<?= esc($kuk['kode_kuk']) ?>" required>
                <div class="invalid-feedback error-kode_kuk"></div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>Nama KUK<span class="text-danger">*</span></label>
        <input type="text" name="nama_kuk" class="form-control" value="<?= esc($kuk['nama_kuk']) ?>" required>
        <div class="invalid-feedback error-nama_kuk"></div>
    </div>

    <div class="form-group">
        <label>Pertanyaan<span class="text-danger">*</span></label>
        <textarea class="form-control" name="pertanyaan" rows="3" required><?= esc($kuk['pertanyaan']) ?></textarea>
        <div class="invalid-feedback error-pertanyaan"></div>
    </div>
</div>
<div class="modal-footer bg-whitesmoke">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
</div>
<?= form_close() ?>