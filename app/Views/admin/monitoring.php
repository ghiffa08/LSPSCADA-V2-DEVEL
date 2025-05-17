<?= $this->extend("layouts/admin/layout-admin") ?>
<?= $this->section("content") ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Data Progress Asesi</h4>
                <div class="card-header-action">
                    <!-- Action buttons can be placed here if needed -->
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="basicTable" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th class="align-middle text-center" rowspan="2">No</th>
                                <th class="align-middle text-center" rowspan="2">Nama Asesi</th>
                                <th class="text-center" colspan="4">FR APL 01</th>
                                <th class="text-center" colspan="4">FR APL 02</th>
                                <th class="align-middle text-center" rowspan="2">FR AK 01</th>
                            </tr>
                            <tr>
                                <th>ID</th>
                                <th>Validator</th>
                                <th>Status</th>
                                <th>Email</th>
                                <th>ID</th>
                                <th>Validator</th>
                                <th>Status</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listMonitoring as $index => $value): ?>
                                <tr>
                                    <td rowspan="2"><?= $index + 1 ?></td>
                                    <td rowspan="2"><?= esc($value['nama_siswa']) ?></td>
                                    <td>
                                        <a target="_blank" href="<?= base_url('/kelola_apl1/pdf-' . $value['id_apl1']) ?>">
                                            <?= esc($value['id_apl1']) ?>
                                        </a>
                                    </td>
                                    <td><?= esc($value['validator_apl1']) ?></td>
                                    <td>
                                        <?php
                                        $badge_class = getStatusBadge($value['status_apl1']);
                                        ?>
                                        <?= getStatusBadge($value['status_apl1']) ?>
                                    </td>
                                    <td>
                                        <?php $emailStatus = $value['email_apl1'] ? "success" : "danger"; ?>
                                        <?php $emailText = $value['email_apl1'] ? "Terkirim" : "Belum Terkirim"; ?>
                                        <div class="badge badge-<?= $emailStatus ?>">
                                            <?= $emailText ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a target="_blank" href="<?= base_url('/kelola_apl2/pdf-' . $value['id_apl1']) ?>">
                                            <?= esc($value['id_apl2']) ?>
                                        </a>
                                    </td>
                                    <td><?= esc($value['validator_apl2']) ?></td>
                                    <td>
                                        <?= getStatusBadge($value['status_apl2']) ?>
                                    </td>
                                    <td>
                                        <?php $emailStatus = $value['email_apl2'] ? "success" : "danger"; ?>
                                        <?php $emailText = $value['email_apl2'] ? "Terkirim" : "Belum Terkirim"; ?>
                                        <div class="badge badge-<?= $emailStatus ?>">
                                            <?= $emailText ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($value['status_apl1'] === "validated" && $value['status_apl2'] === "validated"): ?>
                                            <a class="font-weight-600" target="_blank"
                                                href="<?= site_url("/persetujuan-asesmen/pdf-" . $value['id_apl1']) ?>">
                                                Persetujuan Asesmen & Kerahasiaan
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center" colspan="4">
                                        <div class="table-links">
                                            <a target="_blank" href="<?= base_url('/kelola_apl1/pdf-' . $value['id_apl1']) ?>" download="">
                                                Download PDF
                                            </a>
                                            <div class="bullet"></div>
                                            <a target="_blank" href="<?= base_url('html/upload/pas foto/' . $value['pas_foto']) ?>">
                                                Pas Foto
                                            </a>
                                            <div class="bullet"></div>
                                            <a target="_blank" href="<?= base_url('html/upload/tanda tangan/' . $value['tanda_tangan_asesi']) ?>">
                                                Tanda Tangan
                                            </a>
                                            <div class="bullet"></div>
                                            <a target="_blank" href="<?= base_url('html/upload/ktp/' . $value['ktp']) ?>">
                                                Identitas
                                            </a>
                                            <div class="bullet"></div>
                                            <a target="_blank" href="<?= base_url('html/upload/bukti pendidikan/' . $value['bukti_pendidikan']) ?>">
                                                Bukti Pendidikan
                                            </a>
                                            <div class="bullet"></div>
                                            <a target="_blank" href="<?= base_url('html/upload/raport/' . $value['raport']) ?>">
                                                Raport
                                            </a>
                                            <div class="bullet"></div>
                                            <a target="_blank" href="<?= base_url('html/upload/sertifikat pkl/' . $value['sertifikat_pkl']) ?>">
                                                Sertifikat PKL
                                            </a>
                                        </div>
                                    </td>
                                    <?php if (isset($value['id_apl2'])): ?>
                                        <td class="text-center" colspan="4">
                                            <div class="table-links">
                                                <a target="_blank" href="<?= base_url('/kelola_apl2/pdf-' . $value['id_apl1']) ?>" download="">
                                                    Download PDF
                                                </a>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>