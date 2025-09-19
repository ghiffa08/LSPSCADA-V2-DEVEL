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
                <input type="hidden" name="id_kuk">

                <div class="form-group">
                    <label>Skema Sertifikasi<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_skema" id="id_skema" style="width: 100%;">
                        <option value="">Pilih Skema</option>
                        <?php foreach ($listSkema as $skema) : ?>
                            <option value="<?= $skema['id_skema'] ?>"><?= esc($skema['nama_skema']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label>Unit Kompetensi<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_unit" id="id_unit" style="width: 100%;" disabled>
                        <option value="">Pilih Skema Terlebih Dahulu</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label>Elemen Kompetensi<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_elemen" id="id_elemen" style="width: 100%;" disabled>
                        <option value="">Pilih Unit Terlebih Dahulu</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label>Kode KUK<span class="text-danger">*</span></label>
                    <input type="text" name="kode_kuk" class="form-control" placeholder="Contoh: 1.1">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label>Pertanyaan KUK<span class="text-danger">*</span></label>
                    <textarea class="form-control" name="pertanyaan" rows="3"></textarea>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="modal-footer bg-whitesmoke">
                <button type="submit" class="btn btn-primary btn-block">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= form_close(); ?>