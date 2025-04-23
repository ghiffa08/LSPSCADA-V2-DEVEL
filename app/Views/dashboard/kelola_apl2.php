<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<?php
// Array untuk menyimpan URL PDF
$pdfUrls = array();

// Menyimpan URL PDF ke dalam array
foreach ($listAPL2 as $value) {
    $pdfUrls[] = base_url('/kelola_apl2/pdf-' . $value['id_apl1']);
}
?>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data APL2</h4>
                <div class="card-header-action">
                    <a class="btn btn-icon btn-warning" href="#" onclick="downloadAllPdf()">Download Semua PDF</a>
                </div>
            </div>
            <div class="card-body">


                <div class="table-responsive">
                    <div class="category-filter">
                        <select id="categoryFilter" class="form-control" style=" display: inline; width: 200px; margin-left: 25px;">
                            <option value="">Show All</option>
                            <option value="pending">Pending</option>
                            <option value="validated">Validated</option>
                            <option value="unvalid">Unvalid</option>
                        </select>
                    </div>

                    <table id="filterTable" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID APL 1</th>
                                <th>ID APL 2</th>
                                <th>Nama Asesi</th>
                                <th>Skema</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = null;
                            foreach ($listAPL2 as $value) {
                                $no++;

                                $badgeColor = '';
                                switch ($value['validasi_apl2']) {
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
                                    <td><?= $value['id_apl2'] ?></td>
                                    <td><?= $value['nama_siswa'] ?></td>
                                    <td><?= $value['nama_skema'] ?></td>
                                    <td>
                                        <div class="badge badge-<?= $badgeColor ?>"><?= $value['validasi_apl2'] ?></div>
                                    </td>
                                    <td>
                                        <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                            <a class="btn btn-icon btn-warning" href="<?= base_url('/kelola_apl1/pdf-' . $value['id_apl1']) ?>" download=""><i class="fas fa-download"></i></a>
                                            <a class="btn btn-icon btn-info" target="_blank" href="<?= base_url('/kelola_apl2/pdf-' . $value['id_apl1']) ?>"><i class="fas fa-eye"></i></a>
                                            <button class="btn btn-icon btn-danger" data-toggle="modal" data-target="#deleteAPL2Modal-<?= $value['id_apl2']; ?>"><i class="fas fa-trash"></i></button>
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

<?php if (in_groups('Admin')) { ?>

    <div class="row">
        <div class="col-12 col-md-4 col-lg-4">
            <div class="pricing">
                <div class="pricing-title">
                    <?= date('d/m/Y') ?>
                </div>
                <div class="pricing-padding">
                    <div class="pricing-price">
                        <h1><?= count($listEmailAPL2); ?></h1>
                        <p>Asesi yang sudah di verifikasi pada hari ini</p>
                    </div>

                </div>
                <div class="pricing-cta">
                    <a data-toggle="modal" data-target="#sendEmailModal">Kirim Email </a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-8">
            <form action="<?= site_url('kelola_apl2/store-email-validasi-by-date') ?>" method="post">
                <div class="card">
                    <div class="card-header">
                        <h4>Kirim Email Validasi APL 01 pada tanggal:</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="table-responsive">
                                    <table id="tableAPL1ByDate" class="table table-bordered table-md">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>ID APL1</th>
                                                <th>ID APL2</th>
                                                <th>Nama Asesi</th>
                                                <th>Skema</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Tanggal Validasi</label>
                                    <input type="date" name="dateValidated" id="dateValidated" class="form-control <?php if (session('errors.dateValidated')) : ?>is-invalid<?php endif ?>" value="<?= old('dateValidated') ?>">
                                    <?php if (session('errors.dateValidated')) { ?>
                                        <div class="invalid-feedback">
                                            <?= session('errors.dateValidated') ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary w-100">Kirim Email</button>
                    </div>
                </div>
            </form>
        </div>

    </div>

<?php } ?>

<?= $this->endSection() ?>

<!-- Modals Section -->
<?= $this->section("modals") ?>

<?php foreach ($listEmailAPL2 as $row) { ?>

    <form id="setting-form" action="<?= site_url('/kelola_apl2/store-email-validasi'); ?>" method="POST">
        <div class="modal fade" id="sendEmailModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Kirim Email Validasi FR.APL.01 | <?= date('d/m/Y', strtotime($row['tanggal_validasi'])) ?></h5>
                        <button type="button" class="close tombol-tutup" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?= csrf_field(); ?>
                        <p class="text-muted">Setelah FR.APL.01 di verifikasi oleh Admin, Kirim email asesmen mandiri ke:</p>
                        <div class="table-responsive">
                            <table id="#tableAPL1ByDate" class="table table-bordered table-md">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>ID APL 1</th>
                                        <th>ID APL 2</th>
                                        <th>Nama Asesi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = null;
                                    foreach ($listEmailAPL2 as $row) {
                                        $no++;

                                        $badgeColor = '';
                                        switch ($row['validasi_apl2']) {
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
                                            <td><?= $row['id_apl1'] ?></td>
                                            <td><?= $row['id_apl2'] ?></td>
                                            <td><?= $row['nama_siswa'] ?></td>
                                            <td>
                                                <div class="badge badge-<?= $badgeColor ?>"><?= $row['validasi_apl2'] ?></div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            Kirim Email Validasi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

<?php } ?>

<?php foreach ($listAPL2 as $row => $value) { ?>
    <form action="<?= site_url('/kelola_apl2/delete'); ?>" method="post">
        <div class="modal fade" id="deleteAPL2Modal-<?= $value['id_apl2']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Skema</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <h5>Apakah anda yakin akan menghapus FR.APL.01 atas nama <span class="text-danger font-weight-bold">"<?= $value['nama_siswa']; ?>"</span>?</h5>
                        <input type="hidden" name="id" value="<?= $value['id_apl2']; ?>">

                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="submit" class="btn btn-danger btn-lg btn-block">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php } ?>

<?= $this->endSection() ?>

<?= $this->section("js") ?>

<script>
    $(document).ready(function() {
        $("#dateValidated").change(function(e) {
            var dateValidated = $("#dateValidated").val();
            $.ajax({
                type: "POST",
                url: "<?= base_url('/getDateValidated2') ?>",
                data: {
                    dateValidated: dateValidated
                },
                success: function(response) {
                    $("#tableAPL1ByDate tbody").html(response);
                }
            });
        });
    });
</script>

<script>
    function downloadAllPdf() {
        <?php foreach ($pdfUrls as $pdfUrl) : ?>
            // Membuat elemen anchor untuk setiap URL PDF dan menginisiasi unduhan
            var link = document.createElement('a');
            link.href = '<?= $pdfUrl ?>';
            link.download = '';
            link.click();
        <?php endforeach; ?>
    }
</script>
<?= $this->endSection() ?>