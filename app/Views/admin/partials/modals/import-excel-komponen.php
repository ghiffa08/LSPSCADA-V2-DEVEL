<!-- Import Excel Modal -->
<div class="modal fade" id="importExcelModal" tabindex="-1" role="dialog" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importExcelModalLabel">Import Data Komponen dari Excel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="import-excel-form" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <p class="mb-0"><i class="fas fa-info-circle mr-1"></i> Pastikan format file Excel sesuai dengan template.</p>
                        <p class="mb-0 mt-2">
                            <a href="<?= base_url('download-template-komponen-feedback') ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-download mr-1"></i> Download Template
                            </a>
                        </p>
                    </div>

                    <div class="form-group">
                        <label for="excel_file">File Excel</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="excel_file" name="excel_file" accept=".xls,.xlsx" onchange="updateFileLabel(this)">
                            <label class="custom-file-label" for="excel_file">Pilih file Excel</label>
                        </div>
                        <small class="text-muted">Format file yang didukung: .xls, .xlsx</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" id="import-btn" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Import Excel Modal -->