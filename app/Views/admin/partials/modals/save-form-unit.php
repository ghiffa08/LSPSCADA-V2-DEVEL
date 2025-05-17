<!-- Save Unit Modal -->
<?= form_open('master/unit/save', ['id' => 'add-unit-form']) ?>
<div class="modal fade" id="addUnitModal" data-backdrop="static" tabindex="-1" aria-labelledby="addUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Unit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Masukan ID Skema, Kode Unit, Nama Unit, Keterangan dan Status</p>

                <div class="form-group">
                    <label>Skema<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_skema" id="id_skema">
                        <option value="">Pilih Skema</option>
                        <?php if (isset($listSkema)): ?>
                            <?php foreach ($listSkema as $row): ?>
                                <option value="<?= $row['id_skema'] ?>"><?= $row['nama_skema'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label>Kode Unit<span class="text-danger">*</span></label>
                    <input type="text" name="kode" class="form-control" id="inputKode" placeholder="Masukan Kode Unit">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label>Nama Unit<span class="text-danger">*</span></label>
                    <textarea class="form-control" name="nama" rows="3" placeholder="Masukan Nama Unit"></textarea>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label>Keterangan<span class="text-danger">*</span></label>
                    <textarea class="form-control" name="keterangan" id="inputDescription" rows="3" placeholder="Masukan Keterangan"></textarea>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label>Status<span class="text-danger">*</span></label>
                    <div class="selectgroup w-100">
                        <label class="selectgroup-item">
                            <input type="radio" name="status" class="selectgroup-input" value="Y" checked>
                            <span class="selectgroup-button">Aktif</span>
                        </label>
                        <label class="selectgroup-item">
                            <input type="radio" name="status" class="selectgroup-input" value="N">
                            <span class="selectgroup-button">Tidak Aktif</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-whitesmoke">
                <button type="submit" class="btn btn-primary btn-lg btn-block">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>