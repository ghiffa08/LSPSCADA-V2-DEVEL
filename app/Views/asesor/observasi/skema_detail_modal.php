<div class="row">
    <div class="col-md-6">
        <h5><?= esc($skema->nama_skema) ?></h5>
        <p class="text-muted mb-2">
            <span class="badge badge-<?= $skema->status_skema === 'active' ? 'success' : 'secondary' ?>">
                <?= esc($skema->kode_skema) ?>
            </span>
        </p>

        <div class="row">
            <div class="col-6">
                <strong>Status:</strong><br>
                <span class="badge badge-<?= $skema->status_skema === 'active' ? 'success' : 'secondary' ?>">
                    <?= ucfirst($skema->status_skema) ?>
                </span>
            </div>
            <div class="col-6">
                <strong>Total Asesmen:</strong><br>
                <span class="h6 text-primary"><?= count($asesmenList) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <?php if (!empty($skema->deskripsi)): ?>
            <strong>Deskripsi:</strong>
            <p class="text-muted"><?= esc($skema->deskripsi) ?></p>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($asesmenList)): ?>
    <hr>
    <h6>Daftar Asesmen Terkait</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Tujuan</th>
                    <th>TUK</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($asesmenList as $asesmen): ?>
                    <tr>
                        <td><?= esc($asesmen['tujuan']) ?></td>
                        <td><?= esc($asesmen['nama_tuk']) ?></td>
                        <td>
                            <small>
                                <?= date('d/m/Y', strtotime($asesmen['tanggal_mulai'])) ?>
                                <?php if ($asesmen['tanggal_selesai']): ?>
                                    - <?= date('d/m/Y', strtotime($asesmen['tanggal_selesai'])) ?>
                                <?php endif; ?>
                            </small>
                        </td>
                        <td>
                            <?php
                            $now = date('Y-m-d');
                            $start = $asesmen['tanggal_mulai'];
                            $end = $asesmen['tanggal_selesai'];

                            if ($now < $start) {
                                $statusClass = 'warning';
                                $statusText = 'Belum Dimulai';
                            } elseif ($now >= $start && $now <= $end) {
                                $statusClass = 'success';
                                $statusText = 'Sedang Berlangsung';
                            } else {
                                $statusClass = 'secondary';
                                $statusText = 'Selesai';
                            }
                            ?>
                            <span class="badge badge-<?= $statusClass ?>"><?= $statusText ?></span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="viewAsesmenDetail(<?= $asesmen['id_asesmen'] ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success"
                                onclick="createObservasiForAsesmen(<?= $asesmen['id_asesmen'] ?>)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info mt-3">
        <i class="fas fa-info-circle"></i>
        Tidak ada asesmen yang tersedia untuk skema ini.
    </div>
<?php endif; ?>

<script>
    function viewAsesmenDetail(asesmenId) {
        window.location.href = '<?= base_url('asesor/observasi/detail-asesmen') ?>/' + asesmenId;
    }

    function createObservasiForAsesmen(asesmenId) {
        window.location.href = '<?= base_url('asesor/observasi/ceklist') ?>?asesmen_id=' + asesmenId;
    }
</script>