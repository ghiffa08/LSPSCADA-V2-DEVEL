<!-- Import Excel Modal -->
<?= form_open_multipart(site_url('/master/skema/import'), ['id' => 'import-excel-form']); ?>
<div class="modal fade" id="importExcelModal" data-backdrop="static" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importExcelModalLabel">Import KUK</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Pastikan file Excel mengikuti format contoh yang disediakan.
                    <a href="<?= site_url('master/skema/download-template') ?>" class="btn btn-sm btn-primary ml-2">
                        Download Template
                    </a>
                </div>

                <?php if (session('error')): ?>
                    <div class="alert alert-danger">
                        <?= session('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session('success')): ?>
                    <div class="alert alert-success">
                        <?= session('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session('warning')): ?>
                    <div class="alert alert-warning">
                        <?= session('warning') ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="file_excel">File Excel<span class="text-danger">*</span></label>
                    <div class="custom-file">
                        <input type="file"
                            class="custom-file-input <?= session('errors.file_excel') ? 'is-invalid' : '' ?>"
                            id="file_excel"
                            name="file_excel"
                            accept=".xlsx,.xls"
                            required
                            onchange="updateFileLabel(this)">
                        <label class="custom-file-label" for="file_excel">Pilih file Excel</label>
                        <?php if (session('errors.file_excel')): ?>
                            <div class="invalid-feedback"><?= session('errors.file_excel') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-whitesmoke">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Import Data</button>
            </div>
        </div>
    </div>
</div>
<?= form_close(); ?>