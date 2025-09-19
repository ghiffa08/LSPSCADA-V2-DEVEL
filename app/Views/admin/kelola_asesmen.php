<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section("content"); ?>
<h2 class="section-title">Pendaftaran Asesmen</h2>
<p class="section-lead">Kelola semua data pendaftaran asesmen oleh asesi.</p>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Pendaftaran Asesmen</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" id="btn-add-asesmen">
                        <i class="fas fa-plus"></i> Tambah Pendaftaran
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-asesmen" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Skema</th>
                                <th>Tempat Uji Kompetensi (TUK)</th>
                                <th>Tanggal Asesmen</th>
                                <th>Tujuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section("modals") ?>
<form id="save-asesmen-form" action="<?= site_url('/master/asesmen/save'); ?>" method="POST">
    <div class="modal fade" id="saveAsesmenModal" data-backdrop="static" tabindex="-1" aria-labelledby="saveAsesmenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Formulir Pendaftaran Asesmen</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_asesmen">

                    <div class="form-group">
                        <label>Skema Sertifikasi<span class="text-danger">*</span></label>
                        <select class="form-control select2" name="id_skema" id="id_skema" style="width: 100%;">
                            <option value="">Pilih Skema</option>
                            <?php foreach ($listSkema as $row) : ?>
                                <option value="<?= $row['id_skema'] ?>"><?= $row['nama_skema'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label>Tempat Uji Kompetensi (TUK)<span class="text-danger">*</span></label>
                        <select class="form-control select2" name="id_tuk" id="id_tuk" style="width: 100%;">
                            <option value="">Pilih TUK</option>
                            <?php foreach ($listTUK as $row) : ?>
                                <option value="<?= $row['id_tuk'] ?>">TUK <?= $row['jenis_tuk'] ?> - <?= $row['nama_tuk'] ?></option>
                            <?php endforeach ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label>Jadwal Uji Kompetensi<span class="text-danger">*</span></label>
                        <select class="form-control select2" name="id_tanggal" id="id_tanggal" style="width: 100%;">
                            <option value="">Pilih Jadwal</option>
                            <?php foreach ($listSettanggal as $row) : ?>
                                <option value="<?= $row['id_tanggal'] ?>"><?= $row['tanggal'] ?> - <?= $row['keterangan'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label>Tujuan Sertifikasi<span class="text-danger">*</span></label>
                        <select class="form-control select2" name="tujuan" id="tujuan" style="width: 100%;">
                            <option value="">Pilih Tujuan Sertifikasi</option>
                            <option value="Sertifikasi">Sertifikasi</option>
                            <option value="PKT">PKT</option>
                            <option value="RPL">RPL</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer bg-whitespoke">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>


<?= $this->section('js') ?>
<script>
    $(document).ready(function() {
        // Konfigurasi
        const modal = $('#saveAsesmenModal');
        const form = $('#save-asesmen-form');
        const baseUrl = '<?= base_url() ?>';
        let dataTable;

        // Inisialisasi DataTable
        dataTable = $('#table-asesmen').DataTable({
            "processing": true, "serverSide": true, "responsive": true, "order": [],
            "ajax": { "url": `${baseUrl}/master/asesmen/get-data-table`, "type": "POST" },
            "columns": [
                { "data": null, "orderable": false }, { "data": "nama_skema" },
                { "data": "nama_tuk" }, { "data": "tanggal_asesmen" },
                { "data": "tujuan" }, { "data": null, "orderable": false }
            ],
            "columnDefs": [
                {"targets": 0, "render": (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1},
                {"targets": -1, "render": (data, type, row) => `
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info btn-edit" data-id="${row.id_asesmen}" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id_asesmen}" title="Hapus"><i class="fas fa-trash"></i></button>
                    </div>`}
            ],
        });

        // --- FUNGSI-FUNGSI AJAX UNTUK DROPDOWN DIHAPUS ---

        // Logika Form & Modal
        function resetForm() {
            form[0].reset();
            form.find('input[name="id_asesmen"]').val('');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
            form.find('select.select2').val('').trigger('change');
            modal.find('.modal-title').text('Formulir Pendaftaran Asesmen');
        }

        /** Membuka modal edit menjadi lebih sederhana */
        function openEditModal(id) {
            resetForm();
            $.get(`${baseUrl}/master/asesmen/getById/${id}`, function(response) {
                if (response.status) {
                    const asesmen = response.data;
                    form.find('[name="id_asesmen"]').val(asesmen.id_asesmen);
                    
                    // Langsung set semua value karena data dropdown sudah ada di HTML
                    form.find('[name="id_skema"]').val(asesmen.id_skema).trigger('change');
                    form.find('[name="id_tuk"]').val(asesmen.id_tuk).trigger('change');
                    form.find('[name="id_tanggal"]').val(asesmen.id_tanggal).trigger('change');
                    form.find('[name="tujuan"]').val(asesmen.tujuan).trigger('change');

                    modal.find('.modal-title').text('Edit Pendaftaran Asesmen');
                    modal.modal('show');
                }
            }).fail(() => Swal.fire('Error', 'Gagal mengambil data dari server.', 'error'));
        }

        // Event Listeners (tidak berubah)
        $('#btn-add-asesmen').on('click', () => { resetForm(); modal.modal('show'); });

        form.on('submit', function(e) {
            e.preventDefault();
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
            $.ajax({
                url: form.attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
                success: (response) => {
                    modal.modal('hide');
                    Swal.fire('Berhasil', response.message, 'success');
                    dataTable.ajax.reload();
                },
                error: (xhr) => {
                    if (xhr.status === 400) {
                        const errors = xhr.responseJSON.messages;
                        $.each(errors, (field, message) => form.find(`[name="${field}"]`).addClass('is-invalid').next('.invalid-feedback').text(message));
                    } else { Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error'); }
                },
                complete: () => submitBtn.html(originalBtnText).prop('disabled', false)
            });
        });

        $('#table-asesmen tbody').on('click', '.btn-edit', function() { openEditModal($(this).data('id')); });
        $('#table-asesmen tbody').on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Konfirmasi Hapus', text: "Anda yakin ingin menghapus data ini?", icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#d33', cancelButtonText: 'Batal', confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.get(`${baseUrl}/master/asesmen/delete/${id}`, (response) => {
                        Swal.fire('Dihapus!', response.message, 'success');
                        dataTable.ajax.reload();
                    }).fail((xhr) => Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal menghapus data.', 'error'));
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>