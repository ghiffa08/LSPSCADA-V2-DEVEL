<!-- Save Komponen Modal -->
<div class="modal fade" id="saveKomponenModal" tabindex="-1" role="dialog" aria-labelledby="komponenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="komponenModalLabel">Tambah Komponen Umpan Balik</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="add-komponen-form" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id_komponen" value="">

                    <div class="form-group">
                        <label for="pernyataan">Pernyataan Umpan Balik <span class="text-danger">*</span></label>
                        <textarea name="pernyataan" id="pernyataan" class="form-control" rows="4" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="urutan">Urutan</label>
                        <input type="number" class="form-control" id="urutan" name="urutan" value="0" min="0">
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">Urutan akan digunakan untuk mengurutkan komponen pada form umpan balik</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Komponen Modal -->
