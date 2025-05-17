<!-- Add-Group-Modal -->
<form id="add-tuk-form" action="<?= site_url('/master/tuk/save'); ?>" method="POST">
    <div class="modal fade" id="saveTukModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tempat Uji Kompetensi</h5>
                    <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field(); ?>
                    <p class="text-muted">Masukan Nama Tempat Uji Kompetensi(TUK).</p>
                    <div class="form-group">
                        <label class="form-label">Nama Tempat Uji Kompetensi</label>
                        <input type="text" name="nama_tuk" class="form-control" placeholder="Nama Tempat Uji Kompetensi">
                    </div>
                    <div class="form-group">
                        <label>Jenis TUK</label>
                        <select class="form-control select2" name="jenis_tuk">
                            <option value="">Pilih Jenis TUK</option>
                            <option value="Sewaktu">Sewaktu</option>
                            <option value="Tempat Kerja">Tempat Kerja</option>
                            <option value="Mandiri">Mandiri</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        Simpan
                    </button>
                </div>

            </div>
        </div>
    </div>
</form>