<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section("content"); ?>

<h2 class="section-title">Kelola Asesmen Mandiri (APL2)</h2>
<p class="section-lead">Validasi dan kelola data Asesmen Mandiri dari asesi pada halaman ini.</p>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data APL2</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-apl2" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Asesi</th>
                                <th>Skema Sertifikasi</th>
                                <th>Tanggal Submit</th>
                                <th>Status Validasi</th>
                                <th>Asesor</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section("modals") ?>
<div class="modal fade" id="validateAPL2Modal" tabindex="-1" role="dialog" aria-labelledby="validateAPL2ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="validateAPL2ModalLabel">Validasi APL2</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="validate-apl2-form">
                <div class="modal-body">
                    <input type="hidden" name="id_pengajuan" id="validate_id_pengajuan_apl2">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Asesi</label>
                                <p id="validate_nama_asesi_apl2">: Memuat...</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Skema Sertifikasi</label>
                                <p id="validate_nama_skema_apl2">: Memuat...</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Asesor</label>
                                <p id="validate_nama_asesor_apl2">: Memuat...</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Submit</label>
                                <p id="validate_tanggal_submit">: Memuat...</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status Validasi</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status_pengajuan" id="status_validated" value="validated">
                            <label class="form-check-label" for="status_validated">
                                Validated
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status_pengajuan" id="status_unvalid" value="unvalid">
                            <label class="form-check-label" for="status_unvalid">
                                Unvalid
                            </label>
                        </div>
                    </div>
                    <div class="form-group" id="catatan-penolakan-section-apl2" style="display: none;">
                        <label for="catatan_penolakan_apl2">Catatan Penolakan</label>
                        <textarea class="form-control" name="catatan_penolakan" id="catatan_penolakan_apl2" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Validasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script>
    $(document).ready(function() {
        const baseUrl = '<?= base_url() ?>';
        let dataTable;

        dataTable = $('#table-apl2').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "order": [[3, "desc"]],
            "ajax": {
                "url": `${baseUrl}/api/apl2/get-data-table`,
                "type": "POST"
            },
            "columns": [{
                    "data": null,
                    "orderable": false
                },
                {
                    "data": "nama_asesi"
                },
                {
                    "data": "nama_skema"
                },
                {
                    "data": "created_at"
                },
                {
                    "data": "validasi_apl2"
                },
                {
                    "data": "nama_asesor"
                },
                {
                    "data": null,
                    "orderable": false
                }
            ],
            "columnDefs": [{
                "targets": 0,
                "render": (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
            }, {
                "targets": 3,
                "render": data => new Date(data).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })
            }, {
                "targets": 4,
                "render": function(data, type, row) {
                    if (data === 'pending') {
                        return `<button class="btn btn-sm btn-outline-warning btn-validate-apl2" data-id="${row.id_pengajuan}">
                                <i class="fas fa-check-double"></i> Validasi
                            </button>`;
                    }
                    const statusMap = {
                        'validated': '<span class="badge badge-success">Validated</span>',
                        'unvalid': '<span class="badge badge-danger">Unvalid</span>'
                    };
                    return statusMap[data] || data;
                }
            }, {
                "targets": 5,
                "render": data => data || '<span class="text-muted">Belum Ditentukan</span>'
            }, {
                "targets": -1,
                "render": (data, type, row) => `
                <div class="btn-group">
                    <button class="btn btn-sm btn-info btn-edit-apl2" data-id="${row.id_pengajuan}" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete-apl2" data-id="${row.id_pengajuan}" title="Hapus"><i class="fas fa-trash"></i></button>
                </div>`
            }],
        });

        $('#table-apl2 tbody').on('click', '.btn-validate-apl2', function() {
            const id = $(this).data('id');
            openValidateAPL2Modal(id);
        });

        $('#table-apl2 tbody').on('click', '.btn-edit-apl2', function() {
            const id = $(this).data('id');
            openValidateAPL2Modal(id, true); // true untuk edit
        });

        $('#table-apl2 tbody').on('click', '.btn-delete-apl2', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Anda yakin ingin menghapus data APL2 ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `${baseUrl}/api/apl2/delete/${id}`,
                        type: 'DELETE',
                        success: function(response) {
                            Swal.fire('Dihapus!', response.message, 'success');
                            dataTable.ajax.reload();
                        },
                        error: function(xhr) {
                            const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus data.';
                            Swal.fire('Gagal', errorMsg, 'error');
                        }
                    });
                }
            });
        });

        function openValidateAPL2Modal(id, isEdit = false) {
            const modal = $('#validateAPL2Modal');
            const form = $('#validate-apl2-form');

            form[0].reset();
            $('#catatan-penolakan-section-apl2').hide();
            $('#catatan_penolakan_apl2').prop('required', false);
            $('input[name="status_pengajuan"][value="validated"]').prop('checked', true);

            $('#validate_nama_asesi_apl2, #validate_nama_skema_apl2, #validate_nama_asesor_apl2, #validate_tanggal_submit').html(': Memuat...');

            $.get(`${baseUrl}/api/apl2/getById/${id}`, function(response) {
                if (response.status) {
                    const { pengajuan } = response.data;

                    $('#validate_id_pengajuan_apl2').val(pengajuan.id_pengajuan);
                    $('#validate_nama_asesi_apl2').text(': ' + (pengajuan.nama_asesi || '-'));
                    $('#validate_nama_skema_apl2').text(': ' + (pengajuan.nama_skema || '-'));
                    $('#validate_nama_asesor_apl2').text(': ' + (pengajuan.nama_asesor || '-'));
                    $('#validate_tanggal_submit').text(': ' + new Date(pengajuan.created_at).toLocaleString('id-ID'));

                    // Jika edit dan ada validasi, set nilai
                    if (isEdit && pengajuan.validasi_apl2) {
                        $(`input[name="status_pengajuan"][value="${pengajuan.validasi_apl2}"]`).prop('checked', true);
                        if (pengajuan.validasi_apl2 === 'unvalid') {
                            $('#catatan-penolakan-section-apl2').show();
                            $('#catatan_penolakan_apl2').val(pengajuan.catatan || '');
                        }
                    }

                    modal.modal('show');
                } else {
                    Swal.fire('Gagal', response.message || 'Data tidak ditemukan', 'error');
                }
            }).fail(() => Swal.fire('Error', 'Gagal mengambil data dari server.', 'error'));
        }

        $('#validate-apl2-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            const id_pengajuan = form.find('#validate_id_pengajuan_apl2').val();

            if (!id_pengajuan) {
                Swal.fire('Error', 'ID Pengajuan tidak ditemukan. Coba muat ulang halaman.', 'error');
                return;
            }

            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').prop('disabled', true);

            $.ajax({
                url: `${baseUrl}/api/apl2/validate/${id_pengajuan}`,
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    $('#validateAPL2Modal').modal('hide');
                    Swal.fire('Berhasil', response.message, 'success');
                    dataTable.ajax.reload();
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON ? (xhr.responseJSON.messages.error || 'Terjadi kesalahan.') : 'Terjadi kesalahan server';
                    Swal.fire('Gagal', errorMsg, 'error');
                },
                complete: function() {
                    submitBtn.html(originalBtnText).prop('disabled', false);
                }
            });
        });

        $('input[name="status_pengajuan"]').on('change', function() {
            if (this.value === 'unvalid') {
                $('#catatan-penolakan-section-apl2').slideDown();
                $('#catatan_penolakan_apl2').prop('required', true);
            } else {
                $('#catatan-penolakan-section-apl2').slideUp();
                $('#catatan_penolakan_apl2').prop('required', false).val('');
            }
        });

        $('#validateAPL2Modal').on('hidden.bs.modal', function() {
            const form = $('#validate-apl2-form');
            form[0].reset();
            $('#catatan-penolakan-section-apl2').hide();
            $('#catatan_penolakan_apl2').prop('required', false);
            $('input[name="status_pengajuan"][value="validated"]').prop('checked', true);
        });
    });
</script>
<?= $this->endSection() ?>