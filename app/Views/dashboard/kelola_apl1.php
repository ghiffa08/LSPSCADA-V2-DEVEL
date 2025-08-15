<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Data APL 1</h4>
                <div class="card-header-action">
                    <?php if (!empty($listAPL1['data'])): ?>
                        <a class="btn btn-icon btn-warning" href="#" onclick="downloadAllPdf()">
                            <i class="fas fa-download mr-1"></i> Download Semua PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('pesan')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= session()->getFlashdata('pesan') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?= session()->getFlashdata('warning') ?>
                    </div>
                <?php endif; ?>

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
                            <?php if (!empty($listAPL1['data'])): ?>
                                <?php foreach ($listAPL1['data'] as $index => $value): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= esc($value['id_apl1'] ?? '') ?></td>
                                        <td><?= esc($value['nama_siswa'] ?? '') ?></td>
                                        <td><?= esc($value['nama_skema'] ?? '-') ?></td>
                                        <td><?= esc($value['nama_tuk'] ?? '-') ?></td>
                                        <td><?= isset($value['tanggal']) ? date('d/m/Y H:i', strtotime($value['tanggal'])) : '-' ?></td>
                                        <td>
                                            <?php
                                            $status = $value['validasi_apl1'] ?? 'pending';
                                            switch ($status) {
                                                case 'validated':
                                                    echo '<span class="badge badge-success">Valid</span>';
                                                    break;
                                                case 'unvalid':
                                                    echo '<span class="badge badge-danger">Tidak Valid</span>';
                                                    break;
                                                default:
                                                    echo '<span class="badge badge-warning">Pending</span>';
                                                    break;
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-icon btn-warning" href="<?= base_url('kelola_apl1/pdf-' . $value['id_apl1']) ?>" download>
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a class="btn btn-icon btn-info" target="_blank" href="<?= base_url('kelola_apl1/pdf-' . $value['id_apl1']) ?>">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-icon btn-danger btn-delete-apl1"
                                                    data-id="<?= $value['id_apl1']; ?>"
                                                    data-nama="<?= esc($value['nama_siswa']); ?>"
                                                    data-toggle="modal"
                                                    data-target="#deleteAPL1Modal">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i><br>
                                        Tidak ada data APL1
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($listAPL1['data'])): ?>
                    <div class="mt-3">
                        <small class="text-muted">
                            Total: <?= $listAPL1['total'] ?? 0 ?> data APL1
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Email Validation Section untuk Admin -->

<div class="row mt-4">
    <div class="col-12 col-md-4 col-lg-4">
        <div class="pricing">
            <div class="pricing-title">
                <?= date('d/m/Y') ?>
            </div>
            <div class="pricing-padding">
                <div class="pricing-price">
                    <h1><?= count($listEmailAPL1 ?? []); ?></h1>
                    <p>Asesi APL1 yang sudah diverifikasi hari ini</p>
                </div>
            </div>
            <div class="pricing-cta">
                <?php if (!empty($listEmailAPL1)): ?>
                    <a href="#" data-toggle="modal" data-target="#sendEmailAPL1Modal">Kirim Email</a>
                <?php else: ?>
                    <span class="text-muted">Tidak ada email untuk dikirim</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-8">
        <form action="<?= site_url('kelola_apl1/store-email-validasi-by-date') ?>" method="post">
            <?= csrf_field() ?>
            <div class="card">
                <div class="card-header">
                    <h4>Kirim Email Validasi APL 01 pada tanggal:</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dateValidated">Pilih Tanggal</label>
                                <input type="date"
                                    class="form-control"
                                    name="dateValidated"
                                    id="dateValidated"
                                    value="<?= date('Y-m-d') ?>"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Preview Data</label>
                                <div id="previewCount" class="form-control-static">
                                    Pilih tanggal untuk melihat jumlah email
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-sm" id="tableAPL1ByDate">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Asesi</th>
                                            <th>Email</th>
                                            <th>Status Validasi</th>
                                            <th>Tanggal Validasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                Pilih tanggal untuk menampilkan data
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100" id="btnKirimEmail" disabled>
                        <i class="fas fa-envelope mr-1"></i> Kirim Email
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<?= $this->endSection() ?>

<!-- Modals Section -->
<?= $this->section('modals') ?>

<!-- Delete Modal -->
<?= form_open('kelola_apl1/delete', ['method' => 'post']) ?>
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

<!-- Send Email Modal untuk APL1 -->
<?php if (isset($listEmailAPL1) && !empty($listEmailAPL1)): ?>
    <form id="send-email-form" action="<?= site_url('kelola_apl1/store-email-validasi'); ?>" method="POST">
        <?= csrf_field() ?>
        <div class="modal fade" id="sendEmailAPL1Modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="sendEmailAPL1ModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Kirim Email Validasi APL1 | <?= date('d/m/Y') ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Setelah APL1 diverifikasi oleh Admin, kirim email konfirmasi ke asesi berikut:
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Asesi</th>
                                        <th>Email</th>
                                        <th>Skema</th>
                                        <th>Status</th>
                                        <th>Validator</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($listEmailAPL1 as $index => $email): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= esc($email['nama_siswa']) ?></td>
                                            <td><?= esc($email['email']) ?></td>
                                            <td><?= esc($email['nama_skema']) ?></td>
                                            <td>
                                                <?php if ($email['validasi_apl1'] === 'validated'): ?>
                                                    <span class="badge badge-success">Valid</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Tidak Valid</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($email['validator_apl1'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Perhatian:</strong> Email akan dikirim ke <?= count($listEmailAPL1) ?> asesi.
                            Pastikan data sudah benar sebelum mengirim.
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-paper-plane mr-1"></i> Kirim Email ke <?= count($listEmailAPL1) ?> Asesi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section("js") ?>

<script>
    $(document).ready(function() {
        // Initialize DataTable if data exists
        <?php if (!empty($listAPL1['data'])): ?>
            $('#basicTable').DataTable({
                "responsive": true,
                "pageLength": 25,
                "order": [
                    [5, "desc"]
                ], // Sort by date column
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    },
                    "emptyTable": "Tidak ada data APL1",
                    "zeroRecords": "Tidak ada data yang sesuai"
                }
            });
        <?php endif; ?>

        // Delete APL1 handler
        $('.btn-delete-apl1').on('click', function() {
            var id = $(this).data('id');
            var nama = $(this).data('nama');

            $('#apl1Id').val(id);
            $('#apl1Nama').text(nama);
        });

        // Date change handler untuk preview data
        $("#dateValidated").change(function(e) {
            var dateValidated = $(this).val();

            if (dateValidated) {
                // Show loading
                $("#tableAPL1ByDate tbody").html(`
                    <tr>
                        <td colspan="5" class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Memuat data...
                        </td>
                    </tr>
                `);

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('kelola_apl1/getDateValidated') ?>",
                    data: {
                        dateValidated: dateValidated,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        $("#tableAPL1ByDate tbody").html(response);

                        // Count rows (excluding header)
                        var rowCount = $(response).filter('tr').length;
                        if (rowCount > 0) {
                            $("#previewCount").text(`${rowCount} email akan dikirim`);
                            $("#btnKirimEmail").prop('disabled', false);
                        } else {
                            $("#previewCount").text("Tidak ada data untuk tanggal ini");
                            $("#btnKirimEmail").prop('disabled', true);
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#tableAPL1ByDate tbody").html(`
                            <tr>
                                <td colspan="5" class="text-center text-danger">
                                    <i class="fas fa-exclamation-triangle"></i> Error loading data
                                </td>
                            </tr>
                        `);
                        $("#previewCount").text("Error loading data");
                        $("#btnKirimEmail").prop('disabled', true);
                    }
                });
            } else {
                $("#tableAPL1ByDate tbody").html(`
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Pilih tanggal untuk menampilkan data
                        </td>
                    </tr>
                `);
                $("#previewCount").text("Pilih tanggal untuk melihat jumlah email");
                $("#btnKirimEmail").prop('disabled', true);
            }
        });
    });

    // Function to download all PDFs
    function downloadAllPdf() {
        <?php if (!empty($listAPL1['data'])): ?>
            const pdfUrls = <?= json_encode(array_map(function ($value) {
                                return base_url('kelola_apl1/pdf-' . $value['id_apl1']);
                            }, $listAPL1['data'])) ?>;

            if (pdfUrls.length === 0) {
                alert('Tidak ada data untuk didownload');
                return;
            }

            // Use a small delay between downloads to prevent browser issues
            pdfUrls.forEach((url, index) => {
                setTimeout(() => {
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = '';
                    link.click();
                }, index * 500);
            });
        <?php else: ?>
            alert('Tidak ada data untuk didownload');
        <?php endif; ?>
    }
</script>

<?= $this->endSection() ?>