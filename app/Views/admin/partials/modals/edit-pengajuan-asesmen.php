<div class="modal fade" id="editPengajuanModal" tabindex="-1" role="dialog" aria-labelledby="editPengajuanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPengajuanModalLabel">Edit Pengajuan Asesmen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="edit-pengajuan-form">
                <?= csrf_field() ?>
                <input type="hidden" name="id_pengajuan" id="id_pengajuan">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Asesi</label>
                                <p class="form-control-static" id="nama_asesi_detail"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Skema Kompetensi</label>
                                <p class="form-control-static" id="nama_skema_detail"></p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status_pengajuan">Status Pengajuan</label>
                                <select name="status_pengajuan" id="status_pengajuan" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="diterima">Diterima</option>
                                    <option value="ditolak">Ditolak</option>
                                    <option value="selesai">Selesai</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status_asesmen">Status Asesmen</label>
                                <select name="status_asesmen" id="status_asesmen" class="form-control">
                                    <option value="proses">Proses</option>
                                    <option value="kompeten">Kompeten</option>
                                    <option value="belum_kompeten">Belum Kompeten</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="id_asesor">Tugaskan Asesor</label>
                                <select name="id_asesor" id="id_asesor" class="form-control select2">
                                    <option value="">-- Pilih Asesor --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Pisahkan script modal agar lebih rapi
    function openEditModal(id) {
        const modal = $('#editPengajuanModal');
        const form = $('#edit-pengajuan-form');
        const baseUrl = '<?= base_url() ?>';

        // Reset form
        form[0].reset();
        $('#id_asesor').empty().append('<option value="">-- Pilih Asesor --</option>');

        $.get(`${baseUrl}/api/pengajuan-asesmen/getById/${id}`, function(response) {
            if (response.status) {
                const pengajuan = response.data.pengajuan;
                const asesi = response.data.asesi;
                const asesmen = response.data.asesmen;
                const asesorList = response.asesorList;

                // Isi data statis
                $('#nama_asesi_detail').text(asesi.nama_lengkap || '-');
                $('#nama_skema_detail').text(asesmen.nama_skema || '-');

                // Isi form
                form.find('[name="id_pengajuan"]').val(pengajuan.id_pengajuan);
                form.find('[name="status_pengajuan"]').val(pengajuan.status_pengajuan);
                form.find('[name="status_asesmen"]').val(pengajuan.status_asesmen);

                // Isi dropdown asesor
                const asesorDropdown = $('#id_asesor');
                asesorList.forEach(function(asesor) {
                    asesorDropdown.append(new Option(asesor.nama_asesor, asesor.id_asesor));
                });

                // Pilih asesor yang sudah ada
                if (pengajuan.id_asesor) {
                    asesorDropdown.val(pengajuan.id_asesor).trigger('change');
                }

                modal.modal('show');
            } else {
                Swal.fire('Gagal', response.message || 'Data tidak ditemukan', 'error');
            }
        }).fail(() => Swal.fire('Error', 'Gagal mengambil data dari server.', 'error'));
    }

    // Handler submit form edit
    $('#edit-pengajuan-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        const baseUrl = '<?= base_url() ?>';

        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

        $.ajax({
            url: `${baseUrl}/api/pengajuan-asesmen/save`,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                $('#editPengajuanModal').modal('hide');
                Swal.fire('Berhasil', response.message, 'success');
                $('#table-pengajuan').DataTable().ajax.reload();
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.messages;
                Swal.fire('Gagal', 'Periksa kembali isian Anda', 'error');
            },
            complete: function() {
                submitBtn.html(originalBtnText).prop('disabled', false);
            }
        });
    });
</script>