<!-- Save Skema Modal -->
<?= form_open('master/skema/save', ['id' => 'add-skema-form']) ?>
<div class="modal fade" id="saveSkemaModal" data-backdrop="static" tabindex="-1" aria-labelledby="saveSkemaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span id="modal-title">Tambah Skema</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted" id="modal-description">Masukan Kode Skema, Nama Skema, dan Status Skema</p>

                <input type="hidden" name="id_skema" id="id_skema" value="">

                <div class="form-group">
                    <label>Kode Skema<span class="text-danger">*</span></label>
                    <input type="text" name="kode_skema" id="kode_skema" class="form-control" placeholder="Masukan Kode Skema">
                    <div class="invalid-feedback" id="kode-error"></div>
                </div>

                <div class="form-group">
                    <label>Nama Skema<span class="text-danger">*</span></label>
                    <textarea rows="3" class="form-control" name="nama_skema" id="nama_skema" placeholder="Masukan Nama Skema"></textarea>
                    <div class="invalid-feedback" id="nama-error"></div>
                </div>

                <div class="form-group">
                    <label>Jenis Skema<span class="text-danger">*</span></label>
                    <select class="form-control select2" name="jenis_skema" id="jenis_skema">
                        <option value="">Pilih Jenis Skema</option>
                        <option value="KKNI">KKNI</option>
                        <option value="Okupasi">Okupasi</option>
                        <option value="Klaster">Klaster</option>
                    </select>
                    <div class="invalid-feedback" id="jenis-error"></div>
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