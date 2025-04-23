<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>


<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Kirim Email Validasi FR.APL.01</h4>
                <div class="card-header-action">

                </div>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="form-group">
                            <label for="">Tanggal Validasi</label>
                            <div class="input-group">
                                <input type="date" id="dateFilter" class="form-control">
                                <span class="input-group-append">
                                    <button type="button" class="btn btn-primary btn-flat">Kirim<?= count($listAPL1) ?>!</button>
                                </span>
                            </div>
                        </div>
                    </div>


                    <div class="col-12">
                        <div class="table-responsive">

                            <table id="table-2" class="table table-bordered table-md">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>ID APL 1</th>
                                        <th>Nama Asesi</th>
                                        <th>Skema</th>
                                        <th>Status</th>
                                        <th>Tanggal Validasi</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = null;
                                    foreach ($listAPL1 as $value) {
                                        $no++;

                                        $badgeColor = '';
                                        switch ($value['validasi_apl1']) {
                                            case 'validated':
                                                $badgeColor = 'success';
                                                break;
                                            case 'pending':
                                                $badgeColor = 'warning';
                                                break;
                                            default:
                                                $badgeColor = 'danger';
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $no ?></td>
                                            <td><?= $value['id_apl1'] ?></td>
                                            <td><?= $value['nama_siswa'] ?></td>
                                            <td><?= $value['nama_skema'] ?></td>
                                            <td>
                                                <div class="badge badge-<?= $badgeColor ?>"><?= $value['validasi_apl1'] ?></div>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($value['tanggal_validasi'])) ?></td>
                                            <td>
                                                <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                                    <button type="button" class="btn btn-icon btn-info" data-toggle="modal" data-target="#detail-apl1-<?= $value['id_apl1']; ?>"><i class="fas fa-eye"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?= $this->endSection() ?>

<!--  -->