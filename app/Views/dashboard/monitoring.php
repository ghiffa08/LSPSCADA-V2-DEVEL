<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Progress Asesi</h4>
                <div class="card-header-action">
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-2" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th class="align-middle text-center" rowspan="2">
                                    No
                                </th>
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
                            <?php
                            $no = null;
                            foreach ($listMonitoring as $value) {
                                $no++;
                                $badge_class = '';

                                switch ($value['status_apl1']) {
                                    case 'validated':
                                        $badge_class = 'success';
                                        break;
                                    case 'unvalid':
                                        $badge_class = 'danger';
                                        break;
                                    default:
                                        $badge_class = 'warning';
                                        break;
                                }

                                switch ($value['status_apl2']) {
                                    case 'validated':
                                        $badge_class = 'success';
                                        break;
                                    case 'unvalid':
                                        $badge_class = 'danger';
                                        break;
                                    default:
                                        $badge_class = 'warning';
                                        break;
                                }


                            ?>
                                <tr>
                                    <td rowspan="2"><?= $no ?></td>
                                    <td rowspan="2"><?= $value['nama_siswa'] ?></td>
                                    <td>
                                        <a target="_blank" href="<?= base_url('/kelola_apl1/pdf-' . $value['id_apl1']) ?>"><?= $value['id_apl1'] ?></a>
                                    </td>
                                    <td>
                                        <?= $value['validator_apl1'] ?>
                                    </td>
                                    <td>
                                        <div class="badge badge-<?= $badge_class ?>"><?= $value['status_apl1'] ?></div>
                                    </td>
                                    <td>
                                        <div class="badge badge-<?= ($value['email_apl1'] == true) ? "success" : "danger" ?>"><?= ($value['email_apl1'] == true) ? "Terkirim" : "Belum Terkirim" ?></div>
                                    </td>
                                    <td>
                                        <a target="_blank" href="<?= base_url('/kelola_apl2/pdf-' . $value['id_apl1']) ?>"><?= $value['id_apl2'] ?></a>
                                    </td>
                                    <td>
                                        <?= $value['validator_apl2'] ?>
                                    </td>
                                    <td>
                                        <div class="badge badge-<?= $badge_class ?>"><?= $value['status_apl2'] ?></div>
                                    </td>
                                    <td>
                                        <div class="badge badge-<?= ($value['email_apl2'] == true) ? "success" : "danger" ?>"><?= ($value['email_apl2'] == true) ? "Terkirim" : "Belum Terkirim" ?></div>
                                    </td>
                                    <td class="text-center ">
                                        <?php if ($value['status_apl1'] && $value['status_apl2']  == "validated") { ?>
                                            <a class="font-weight-600" target="_blank" href="<?= site_url("/persetujuan-asesmen/pdf-" . $value['id_apl1']) ?>">Persetujuan Asesmen & Kerahasiaan</a>
                                        <?php  } ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-center" colspan="4">
                                        <div class="table-links">
                                            <a target="_blank" href="<?= base_url('/kelola_apl1/pdf-' . $value['id_apl1']) ?>" download="">Download PDF</a>
                                            <div class="bullet"></div>
                                            <a target="_blank" href="<?= base_url('html/upload/pas foto/' . $value['pas_foto']) ?>">Pas Foto</a>
                                            <div class="bullet"></div>
                                            <a target="_blank" href="<?= base_url('html/upload/tanda tangan/' . $value['tanda_tangan_asesi']) ?>">Tanda Tangan</a>
                                            <div class="bullet"></div>
                                            <a target="_blank" href="<?= base_url('html/upload/ktp/' . $value['ktp']) ?>">Identitas</a>
                                            <div class="bullet"></div>
                                            <a target="_blank" href="<?= base_url('html/upload/bukti pendidikan/' . $value['bukti_pendidikan']) ?>">Bukti Pendidikan</a>
                                            <div class="bullet"></div>
                                            <a target="_blank" href="<?= base_url('html/upload/raport/' . $value['raport']) ?>">Raport</a>
                                            <div class="bullet"></div>
                                            <a target="_blank" href="<?= base_url('html/upload/sertifikat pkl/' . $value['sertifikat_pkl']) ?>">Sertifikat PKL</a>
                                        </div>
                                    </td>
                                    <?php if (isset($value['id_apl2'])) { ?>
                                        <td class="text-center" colspan="4">
                                            <div class="table-links">
                                                <a target="_blank" href="<?= base_url('/kelola_apl2/pdf-' . $value['id_apl1']) ?>" download="">Download PDF</a>
                                            </div>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<?= $this->endSection() ?>