<div class="modal fade" id="validatePengajuanModal" tabindex="-1" role="dialog" aria-labelledby="validatePengajuanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="validatePengajuanModalLabel">Validasi Pengajuan Asesmen (FR.APL.01)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="validate-pengajuan-form">
                <?= csrf_field() ?>
                <input type="hidden" name="id_pengajuan" id="validate_id_pengajuan">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-5">

                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Detail Pengajuan</h4>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-5">Nama Asesi</dt>
                                        <dd class="col-sm-7" id="validate_nama_asesi">: Memuat...</dd>

                                        <dt class="col-sm-5">Skema Sertifikasi</dt>
                                        <dd class="col-sm-7" id="validate_nama_skema">: Memuat...</dd>

                                        <dt class="col-sm-5">Tujuan Asesmen</dt>
                                        <dd class="col-sm-7" id="validate_tujuan">: Memuat...</dd>

                                        <dt class="col-sm-5">Tanggal Pengajuan</dt>
                                        <dd class="col-sm-7" id="validate_tanggal">: Memuat...</dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-header">
                                    <h4 class="card-title">Dokumen Pendukung</h4>
                                </div>
                                <div class="card-body text-center">
                                    <div class="btn-group w-100">
                                        <a href="#" id="doc_pas_foto" target="_blank" class="btn btn-info disabled w-100"><i class="fas fa-id-badge"></i> Pas Foto</a>
                                        <a href="#" id="doc_ktp" target="_blank" class="btn btn-info disabled w-100"><i class="fas fa-id-card"></i> KTP</a>
                                        <a href="#" id="doc_pendidikan" target="_blank" class="btn btn-info disabled w-100"><i class="fas fa-graduation-cap"></i> Ijazah</a>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-header">
                                    <h4 class="card-title">Tindakan Validasi</h4>
                                </div>
                                <div class="card-body">
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status_pengajuan" value="diterima" class="selectgroup-input" checked>
                                            <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-check-circle"></i> Diterima</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="status_pengajuan" value="ditolak" class="selectgroup-input">
                                            <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-times-circle"></i> Ditolak</span>
                                        </label>
                                    </div>
                                    <div class="form-group mt-3" id="catatan-penolakan-section" style="display: none;">
                                        <label for="catatan_penolakan">Alasan Penolakan (Wajib diisi jika ditolak)</label>
                                        <textarea name="catatan" id="catatan_penolakan" class="form-control" rows="3" placeholder="Contoh: Dokumen KTP tidak terbaca dengan jelas."></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-lg-7">
                            <h6 class="text-secondary text-center mb-3">Preview Dokumen FR.APL.01</h6>
                            <div class="embed-responsive embed-responsive-4by3 border rounded">
                                <iframe id="pdf_viewer" class="embed-responsive-item" src="about:blank" title="Preview Dokumen Pengajuan"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Hasil Validasi</button>
                </div>
            </form>
        </div>
    </div>
</div>