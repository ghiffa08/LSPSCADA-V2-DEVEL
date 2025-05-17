<?= form_open(site_url('/master/kuk/save'), ['id' => 'add-kuk-form']); ?>
<div class="modal fade" id="addKukModal" data-backdrop="static" tabindex="-1" aria-labelledby="kukModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kukModalLabel">Tambah Kriteria Unjuk Kerja</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Hidden input for ID (used during edit) -->
                <input type="hidden" name="id_kuk" id="id_kuk" value="">

                <div class="form-group">
                    <label for="id_skema">Skema<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_skema" id="id_skema" required>
                        <option value="">-- Pilih Skema --</option>
                        <?php foreach ($listSkema as $skema): ?>
                            <option value="<?= $skema['id_skema'] ?>"><?= esc($skema['nama_skema']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback error-id_skema"></div>
                </div>

                <div class="form-group">
                    <label for="id_unit">Unit Kompetensi<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_unit" id="id_unit" required disabled>
                        <option value="">-- Pilih Unit --</option>
                    </select>
                    <div class="invalid-feedback error-id_unit"></div>
                </div>

                <div class="form-group">
                    <label for="id_elemen">Elemen Kompetensi<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_elemen" id="id_elemen" required disabled>
                        <option value="">-- Pilih Elemen --</option>
                    </select>
                    <div class="invalid-feedback error-id_elemen"></div>
                </div>

                <div class="form-group">
                    <label for="kode_kuk">Kode KUK<span class="text-danger">*</span></label>
                    <input type="text" name="kode_kuk" class="form-control" id="kode_kuk" required>
                    <div class="invalid-feedback error-kode_kuk"></div>
                </div>

                <div class="form-group">
                    <label for="nama_kuk">Nama KUK<span class="text-danger">*</span></label>
                    <input type="text" name="nama_kuk" class="form-control" id="nama_kuk" required>
                    <div class="invalid-feedback error-nama_kuk"></div>
                </div>

                <div class="form-group">
                    <label for="pertanyaan">Pertanyaan<span class="text-danger">*</span></label>
                    <textarea class="form-control" name="pertanyaan" id="pertanyaan" rows="3" required></textarea>
                    <div class="invalid-feedback error-pertanyaan"></div>
                </div>
            </div>
            <div class="modal-footer bg-whitesmoke">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= form_close(); ?>