<?= form_open('/master/elemen/save', ['id' => 'add-elemen-form']); ?>
<div class="modal fade" id="addElemenModal" data-backdrop="static" tabindex="-1" aria-labelledby="addElemenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Elemen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Pilih skema dan unit, lalu masukkan detail elemen kompetensi.</p>

                <input type="hidden" name="id_elemen">

                <div class="form-group">
                    <label for="id_skema">Skema Sertifikasi<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_skema" id="id_skema" style="width: 100%;">
                        <option value="">Pilih Skema</option>
                        <?php foreach ($listSkema as $row) : ?>
                            <option value="<?= $row['id_skema'] ?>"><?= $row['nama_skema'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="id_unit">Unit Kompetensi<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="id_unit" id="id_unit" style="width: 100%;" disabled>
                        <option value="">Pilih Skema Terlebih Dahulu</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="kode_elemen">Kode Elemen<span class="text-danger">*</span></label>
                    <input type="text" name="kode_elemen" class="form-control" placeholder="Contoh: 01">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="nama_elemen">Nama Elemen<span class="text-danger">*</span></label>
                    <textarea class="form-control" name="nama_elemen" rows="3"></textarea>
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