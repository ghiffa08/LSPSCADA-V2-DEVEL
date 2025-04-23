<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<?php
// Array untuk menyimpan URL PDF
$pdfUrls = array();

// Menyimpan URL PDF ke dalam array
foreach ($listAk as $value) {
    $pdfUrls[] = base_url('/kelola_apl1/pdf-' . $value['id_apl1']);
}
?>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Persetujuan Asesmen</h4>
                <div class="card-header-action">
                    <a class="btn btn-icon btn-warning" href="#" onclick="downloadAllPdf()">Download Semua PDF</a>
                </div>

            </div>
            <div class="card-body">


                <div class="table-responsive">

                    <table id="table-2" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID APL 1</th>
                                <th>Nama Asesi</th>
                                <th>Skema</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = null;
                            foreach ($listAk as $value) {
                                $no++;
                            ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><?= $value['id_apl1'] ?></td>
                                    <td><?= $value['nama_siswa'] ?></td>
                                    <td><?= $value['nama_skema'] ?></td>
                                    <td>
                                        <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                            <a class="btn btn-icon btn-warning" href="<?= site_url('/persetujuan-asesmen/pdf-' . $value['id_apl1']) ?>" download=""><i class="fas fa-download"></i></a>
                                            <a class="btn btn-icon btn-info" target="_blank" href="<?= site_url('/persetujuan-asesmen/pdf-' . $value['id_apl1']) ?>"><i class="fas fa-eye"></i></a>
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

<?= $this->endSection() ?>

<?= $this->section("js") ?>
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