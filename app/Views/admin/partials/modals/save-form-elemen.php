<?= form_open('/master/elemen/save', ['id' => 'add-elemen-form']); ?>
<div class="modal fade" id="addElemenModal" data-backdrop="static" tabindex="-1" aria-labelledby="addElemenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Elemen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Masukan ID Skema, ID Unit, Kode Elemen dan Nama Elemen.</p>

                <div class="form-group">
                    <label for="id_skema">Skema<span class="text-danger">*</span></label>
                    <select class="form-control select2 <?= session('errors.id_skema') ? 'is-invalid' : '' ?>" name="id_skema" id="id_skema">
                        <option value="">Skema</option>
                        <?php foreach ($listSkema as $row): ?>
                            <option value="<?= $row['id_skema'] ?>"><?= $row['nama_skema'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (session('errors.id_skema')): ?>
                        <div class="invalid-feedback"><?= session('errors.id_skema') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="id_unit">Unit<span class="text-danger">*</span></label>
                    <select class="form-control select2 <?= session('errors.id_unit') ? 'is-invalid' : '' ?>" name="id_unit" id="id_unit">
                        <option value="">Unit</option>
                    </select>
                    <?php if (session('errors.id_unit')): ?>
                        <div class="invalid-feedback"><?= session('errors.id_unit') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="inputKode">Kode elemen<span class="text-danger">*</span></label>
                    <input type="text" name="kode" class="form-control <?= session('errors.kode') ? 'is-invalid' : '' ?>" id="inputKode">
                    <?php if (session('errors.kode')): ?>
                        <div class="invalid-feedback"><?= session('errors.kode') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="nama">Nama elemen<span class="text-danger">*</span></label>
                    <textarea class="form-control <?= session('errors.nama') ? 'is-invalid' : '' ?>" name="nama" id="nama"></textarea>
                    <?php if (session('errors.nama')): ?>
                        <div class="invalid-feedback"><?= session('errors.nama') ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer bg-whitesmoke br">
                <button type="submit" class="btn btn-primary btn-block">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= form_close(); ?>