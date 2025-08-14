<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('pesan')) : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('pesan'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php elseif (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="section-body">
    <!-- Header Card -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h4><i class="fas fa-question-circle mr-2"></i>FR.IA.03. PERTANYAAN UNTUK MENDUKUNG OBSERVASI</h4>
                    <div class="card-header-action">
                        <a href="<?= base_url('asesor/pmo/create') ?>" class="btn btn-success">
                            <i class="fas fa-plus mr-1"></i>Buat PMO Baru
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Info Asesor -->
                    <div class="row">
                        <div class="col-md-6 col-12 mb-3">
                            <h6 class="font-weight-bold mb-1">
                                <i class="fas fa-certificate mr-1 text-primary"></i>Skema Sertifikasi
                            </h6>
                            <p class="mb-0 text-muted">
                                <?php if (isset($skema) && is_array($skema)): ?>
                                    <span class="d-block"><?= esc($skema['nama_skema'] ?? 'N/A') ?></span>
                                    <small class="text-info">(<?= esc($skema['kode_skema'] ?? 'N/A') ?>)</small>
                                <?php else: ?>
                                    <span class="text-muted">Skema belum ditetapkan</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <h6 class="font-weight-bold mb-1">
                                <i class="fas fa-user-tie mr-1 text-success"></i>Asesor
                            </h6>
                            <p class="mb-0 text-muted">
                                <?= esc($asesor['nama_lengkap'] ?? 'Asesor tidak ditemukan') ?>
                                <br><small><?= esc($asesor['nomor_registrasi'] ?? 'Belum terdaftar') ?></small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PMO List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-list mr-2"></i>Daftar PMO</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($asesmen) && is_array($asesmen)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablePMO">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Asesmen</th>
                                        <th>Skema</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Tanggal Dibuat</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($asesmen as $index => $item): ?>
                                        <tr>
                                            <td class="text-center"><?= $index + 1 ?></td>
                                            <td>
                                                <strong><?= esc($item['tujuan']) ?></strong>
                                                <br><small class="text-muted">ID: <?= esc($item['id_asesmen']) ?></small>
                                            </td>
                                            <td>
                                                <?= esc($item['nama_skema']) ?>
                                                <br><small class="text-info"><?= esc($item['kode_skema']) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info">
                                                    <i class="fas fa-clock mr-1"></i>Tersedia
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?= date('d/m/Y', strtotime($item['created_at'])) ?>
                                                <br><small class="text-muted"><?= date('H:i', strtotime($item['created_at'])) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('asesor/pmo/create?asesmen=' . $item['id_asesmen']) ?>"
                                                        class="btn btn-sm btn-primary" title="Kelola PMO">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state text-center py-5">
                            <img src="<?= base_url('assets/img/empty-data.svg') ?>" alt="No Data" style="width: 120px; opacity: 0.6;">
                            <h5 class="text-muted mt-3">Belum Ada Asesmen</h5>
                            <p class="text-muted">Belum ada asesmen yang tersedia untuk membuat PMO</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#tablePMO').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
            },
            "order": [
                [4, "desc"]
            ]
        });
    });
</script>
<?= $this->endSection() ?>