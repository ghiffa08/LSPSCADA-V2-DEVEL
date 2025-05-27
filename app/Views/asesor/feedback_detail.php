<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-comment-dots text-primary mr-2"></i>FR.AK.03. FORMULIR UMPAN BALIK</h4>
                <div>
                    <a href="<?= base_url('asesor/feedback/pdf/' . $feedback['id_feedback']) ?>" class="btn btn-danger btn-sm" target="_blank">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                    <a href="<?= base_url('asesor/dashboard') ?>" class="btn btn-light btn-sm ml-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Section 1: Identity Information -->
                <div class="card mb-4 border-left-primary">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-weight-bold text-primary">Informasi Asesmen</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-user text-primary mr-1"></i>Nama Asesi</label>
                                    <p class="form-control-plaintext"><?= esc($feedback['nama_asesi']) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-user-tie text-primary mr-1"></i>Nama Asesor</label>
                                    <p class="form-control-plaintext"><?= esc($feedback['nama_asesor']) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-calendar-alt text-primary mr-1"></i>Tanggal Mulai</label>
                                    <p class="form-control-plaintext"><?= date('d-m-Y', strtotime($feedback['tanggal_mulai'])) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-calendar-check text-primary mr-1"></i>Tanggal Selesai</label>
                                    <p class="form-control-plaintext"><?= date('d-m-Y', strtotime($feedback['tanggal_selesai'])) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold"><i class="fas fa-award text-primary mr-1"></i>Skema Sertifikasi</label>
                                    <p class="form-control-plaintext"><?= esc($feedback['kode_skema']) ?> - <?= esc($feedback['nama_skema']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Feedback Results -->
                <div class="card mb-4 border-left-primary">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-weight-bold text-primary">Hasil Umpan Balik</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">No.</th>
                                        <th>Pernyataan</th>
                                        <th width="10%">Jawaban</th>
                                        <th width="30%">Komentar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">1</td>
                                        <td>Asesi mendapatkan penjelasan yang cukup tentang proses asesmen</td>
                                        <td class="text-center">
                                            <?php if ($feedback['pernyataan_1'] === 'Ya'): ?>
                                                <span class="badge badge-success">Ya</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Tidak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($feedback['komentar_1']) ?: '<em class="text-muted">Tidak ada komentar</em>' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">2</td>
                                        <td>Asesor memberikan kesempatan untuk mendiskusikan/menegosiasikan metode asesmen dan jadwal asesmen</td>
                                        <td class="text-center">
                                            <?php if ($feedback['pernyataan_2'] === 'Ya'): ?>
                                                <span class="badge badge-success">Ya</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Tidak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($feedback['komentar_2']) ?: '<em class="text-muted">Tidak ada komentar</em>' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">3</td>
                                        <td>Asesor menggunakan bahasa yang mudah dimengerti</td>
                                        <td class="text-center">
                                            <?php if ($feedback['pernyataan_3'] === 'Ya'): ?>
                                                <span class="badge badge-success">Ya</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Tidak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($feedback['komentar_3']) ?: '<em class="text-muted">Tidak ada komentar</em>' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">4</td>
                                        <td>Asesor bersikap profesional, ramah, dan sabar selama proses asesmen</td>
                                        <td class="text-center">
                                            <?php if ($feedback['pernyataan_4'] === 'Ya'): ?>
                                                <span class="badge badge-success">Ya</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Tidak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($feedback['komentar_4']) ?: '<em class="text-muted">Tidak ada komentar</em>' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">5</td>
                                        <td>Asesor memberikan pertanyaan yang jelas dan mudah dimengerti</td>
                                        <td class="text-center">
                                            <?php if ($feedback['pernyataan_5'] === 'Ya'): ?>
                                                <span class="badge badge-success">Ya</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Tidak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($feedback['komentar_5']) ?: '<em class="text-muted">Tidak ada komentar</em>' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">6</td>
                                        <td>Asesor memberikan umpan balik yang membantu dan informatif</td>
                                        <td class="text-center">
                                            <?php if ($feedback['pernyataan_6'] === 'Ya'): ?>
                                                <span class="badge badge-success">Ya</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Tidak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($feedback['komentar_6']) ?: '<em class="text-muted">Tidak ada komentar</em>' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">7</td>
                                        <td>Asesor memberikan kesempatan kepada asesi untuk mendemonstrasikan kompetensi yang dimiliki</td>
                                        <td class="text-center">
                                            <?php if ($feedback['pernyataan_7'] === 'Ya'): ?>
                                                <span class="badge badge-success">Ya</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Tidak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($feedback['komentar_7']) ?: '<em class="text-muted">Tidak ada komentar</em>' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">8</td>
                                        <td>Asesor menjelaskan dengan jelas tentang keputusan asesmen</td>
                                        <td class="text-center">
                                            <?php if ($feedback['pernyataan_8'] === 'Ya'): ?>
                                                <span class="badge badge-success">Ya</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Tidak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($feedback['komentar_8']) ?: '<em class="text-muted">Tidak ada komentar</em>' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">9</td>
                                        <td>Fasilitas dan peralatan yang digunakan selama asesmen memadai</td>
                                        <td class="text-center">
                                            <?php if ($feedback['pernyataan_9'] === 'Ya'): ?>
                                                <span class="badge badge-success">Ya</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Tidak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($feedback['komentar_9']) ?: '<em class="text-muted">Tidak ada komentar</em>' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">10</td>
                                        <td>Waktu yang diberikan untuk proses asesmen sudah cukup</td>
                                        <td class="text-center">
                                            <?php if ($feedback['pernyataan_10'] === 'Ya'): ?>
                                                <span class="badge badge-success">Ya</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Tidak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($feedback['komentar_10']) ?: '<em class="text-muted">Tidak ada komentar</em>' ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Additional Comments -->
                <div class="card mb-4 border-left-primary">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 font-weight-bold text-primary">Komentar Tambahan</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($feedback['komentar_tambahan'])): ?>
                            <div class="p-3 bg-light border rounded">
                                <?= nl2br(esc($feedback['komentar_tambahan'])) ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><em>Tidak ada komentar tambahan</em></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="card border-left-secondary">
                    <div class="card-body py-2">
                        <div class="small text-muted">
                            <div class="d-flex justify-content-between">
                                <span><strong>Disubmit pada:</strong> <?= date('d-m-Y H:i:s', strtotime($feedback['created_at'])) ?></span>
                                <?php if ($feedback['updated_at'] !== $feedback['created_at']): ?>
                                    <span><strong>Terakhir diperbarui:</strong> <?= date('d-m-Y H:i:s', strtotime($feedback['updated_at'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
