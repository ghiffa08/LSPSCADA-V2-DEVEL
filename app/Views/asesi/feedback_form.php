<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-comment-dots text-primary mr-2"></i>FR.AK.03. FORMULIR UMPAN BALIK</h4>
                <div>
                    <button class="btn btn-light btn-sm" type="button" data-toggle="collapse" data-target="#collapseInfo">
                        <i class="fas fa-info-circle"></i> Info
                    </button>
                </div>
            </div>

            <div class="collapse" id="collapseInfo">
                <div class="card-body bg-light border-top border-bottom">
                    <div class="alert alert-info mb-0">
                        <h6 class="font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Petunjuk</h6>
                        <ul class="mb-0 pl-3">
                            <li>Pilih asesmen yang sudah diikuti untuk memberikan umpan balik.</li>
                            <li>Umpan balik ini akan digunakan untuk evaluasi dan peningkatan kualitas asesmen.</li>
                            <li>Mohon berikan jawaban dengan jujur dan sesuai pengalaman Anda selama proses asesmen.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <?php if (session()->has('success')) : ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= session('success'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('error')) : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= session('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <h5 class="mb-3">Pilih Asesmen</h5>

                <?php if (empty($asesmenList)) : ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i> Anda belum memiliki asesmen aktif
                    </div>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Skema</th>
                                    <th width="15%">Tanggal Mulai</th>
                                    <th width="15%">Tanggal Selesai</th>
                                    <th width="15%">Status</th>
                                    <th width="15%">Umpan Balik</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($asesmenList as $index => $asesmen) :
                                    // Check if feedback exists for this assessment
                                    $feedbackModel = model('AsesiFeedbackModel');
                                    $existingFeedback = $feedbackModel->getFeedbackByAsesmenId(user()->id_asesi, $asesmen['id_asesmen']);
                                    $feedbackStatus = $existingFeedback ? '<span class="badge badge-success">Sudah Diisi</span>' : '<span class="badge badge-warning">Belum Diisi</span>';
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <td><?= esc($asesmen['nama_skema']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($asesmen['tanggal_mulai'])) ?></td>
                                        <td><?= date('d-m-Y', strtotime($asesmen['tanggal_selesai'])) ?></td>
                                        <td class="text-center">
                                            <?php if ($asesmen['status_asesmen'] == 'selesai') : ?>
                                                <span class="badge badge-success">Selesai</span>
                                            <?php elseif ($asesmen['status_asesmen'] == 'berlangsung') : ?>
                                                <span class="badge badge-primary">Berlangsung</span>
                                            <?php else : ?>
                                                <span class="badge badge-secondary"><?= ucfirst($asesmen['status_asesmen']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= $feedbackStatus ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('asesi/feedback/form/' . $asesmen['id_asesmen']) ?>" class="btn btn-sm btn-primary">
                                                <?= $existingFeedback ? '<i class="fas fa-edit"></i> Edit' : '<i class="fas fa-plus"></i> Isi' ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
