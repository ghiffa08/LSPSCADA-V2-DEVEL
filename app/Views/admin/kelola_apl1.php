<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Data APL 1</h4>
                <div class="card-header-action">
                    <a class="btn btn-icon btn-warning" href="#" onclick="downloadAllPdf()">
                        <i class="fas fa-download mr-1"></i> Download Semua PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="basicTable" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID APL 1</th>
                                <th>Nama Asesi</th>
                                <th>Skema</th>
                                <th>TUK</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = null;
                            foreach ($listAPL1['data'] as $index => $value):
                                $no++;
                            ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= esc($value['id_apl1']) ?></td>
                                    <td><?= esc(format_uppercase($value['nama'])) ?></td>
                                    <td><?= esc($value['nama_skema'] ?? '-') ?></td>
                                    <td><?= esc($value['nama_tuk'] ?? '-') ?></td>
                                    <td><?= format_tanggal_indonesia($value['tanggal']) ?></td>
                                    <td>
                                        <?= getStatusBadge($value['status']) ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a class="btn btn-icon btn-warning" href="<?= base_url('/apl/1/pdf/' . $value['id_apl1']) ?>" download>
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a class="btn btn-icon btn-info" target="_blank" href="<?= base_url('/apl/1/pdf/' . $value['id_apl1']) ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-icon btn-danger btn-delete-apl1"
                                                data-id="<?= $value['id_apl1']; ?>"
                                                data-nama="<?= esc($value['nama']); ?>"
                                                data-toggle="modal"
                                                data-target="#deleteAPL1Modal">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                        </div>
                                    </td>
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

<!-- Modals Section -->
<?= $this->section('modals') ?>

<!-- Delete Modals -->

<?= form_open('/apl/1/delete', ['method' => 'post']) ?>
<div class="modal fade" id="deleteAPL1Modal" tabindex="-1" role="dialog" aria-labelledby="deleteAPL1ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Data APL1</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h5>Apakah anda yakin akan menghapus FR.APL.01 atas nama
                    <span class="text-danger font-weight-bold" id="apl1Nama"></span>?
                </h5>
                <input type="hidden" name="id" id="apl1Id">
            </div>
            <div class="modal-footer bg-whitesmoke br">
                <button type="submit" class="btn btn-danger btn-lg btn-block">Hapus</button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>


<?= $this->endSection() ?>

<?= $this->section("js") ?>

<script>
    $(document).ready(function() {
        $('.btn-delete-apl1').on('click', function() {
            var id = $(this).data('id');
            var nama = $(this).data('nama');

            $('#apl1Id').val(id);
            $('#apl1Nama').text(nama);
        });
    });
</script>


<script>
    // Function to download all PDFs
    function downloadAllPdf() {
        const pdfUrls = <?= json_encode(array_map(function ($value) {
                            return base_url('/kelola_apl1/pdf-');
                        }, $listAPL1)) ?>;

        // Use a small delay between downloads to prevent browser issues
        pdfUrls.forEach((url, index) => {
            setTimeout(() => {
                const link = document.createElement('a');
                link.href = url;
                link.download = '';
                link.click();
            }, index * 500);
        });
    }
</script>

<?= $this->endSection() ?>