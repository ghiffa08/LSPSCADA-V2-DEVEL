<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section("content"); ?>
<?php dd($asesorList) ?>
<h2 class="section-title">Kelola Pengajuan Asesmen</h2>
<p class="section-lead">Validasi dan kelola semua data pengajuan asesmen dari asesi pada halaman ini.</p>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Pengajuan</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-pengajuan" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Asesi</th>
                                <th>Skema</th>
                                <th>Tgl Pengajuan</th>
                                <th>Status Pengajuan</th>
                                <th>Status Asesmen</th>
                                <th>Asesor</th>
                                <th>Aksi</th>
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
<?= $this->include('admin/partials/modals/edit-pengajuan-asesmen') ?>
<?= $this->include('admin/partials/modals/validate-pengajuan-asesmen') ?>
<?= $this->endSection() ?>


<?= $this->section('js') ?>

<script>
    $(document).ready(function() {
        const baseUrl = '<?= base_url() ?>';
        let dataTable;

        // =================================================================
        //  Inisialisasi DataTable
        // =================================================================
        dataTable = $('#table-pengajuan').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "order": [
                [3, "desc"]
            ], // Urutkan berdasarkan tanggal pengajuan terbaru
            "ajax": {
                "url": `${baseUrl}/api/pengajuan-asesmen/get-data-table`,
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
                    "data": "status_pengajuan"
                },
                {
                    "data": "status_asesmen"
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
                "targets": 3, // Format Tanggal
                "render": data => new Date(data).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })
            }, {
                "targets": 4, // Status Pengajuan
                "render": function(data, type, row) {
                    if (data === 'pending') {
                        return `<button class="btn btn-sm btn-outline-warning btn-validate" data-id="${row.id_pengajuan}">
                                <i class="fas fa-check-double"></i> Validasi
                            </button>`;
                    }

                    // [PERBAIKAN] Menambahkan tooltip untuk melihat catatan penolakan jika ada
                    if (data === 'ditolak' && row.catatan) {
                        return `<span class="badge badge-danger" style="cursor:pointer;" title="Alasan: ${row.catatan}">Ditolak</span>`;
                    }

                    const statusMap = {
                        'diterima': '<span class="badge badge-success">Diterima</span>',
                        'ditolak': '<span class="badge badge-danger">Ditolak</span>',
                        'selesai': '<span class="badge badge-info">Selesai</span>'
                    };
                    return statusMap[data] || data;
                }
            }, {
                "targets": 5, // Status Asesmen
                "render": function(data) {
                    const statusMap = {
                        'proses': '<span class="badge badge-primary">Proses</span>',
                        'kompeten': '<span class="badge badge-success">Kompeten</span>',
                        'belum_kompeten': '<span class="badge badge-danger">Belum Kompeten</span>'
                    };
                    return statusMap[data] || data;
                }
            }, {
                "targets": 6, // Nama Asesor
                "render": data => data || '<span class="text-muted">Belum Ditentukan</span>'
            }, {
                "targets": -1, // Aksi
                "render": (data, type, row) => `
                <div class="btn-group">
                    <button class="btn btn-sm btn-info btn-edit" data-id="${row.id_pengajuan}" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id_pengajuan}" title="Hapus"><i class="fas fa-trash"></i></button>
                </div>`
            }],
        });

        // =================================================================
        //  Event Handlers untuk Tombol Aksi
        // =================================================================
        $('#table-pengajuan tbody').on('click', '.btn-validate', function() {
            const id = $(this).data('id');
            openValidateModal(id);
        });

        $('#table-pengajuan tbody').on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            openEditModal(id);
        });

        $('#table-pengajuan tbody').on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Anda yakin ingin menghapus data pengajuan ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `${baseUrl}/api/pengajuan-asesmen/delete/${id}`,
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

        // =================================================================
        // Logika untuk Modal Validasi
        // =================================================================

        function openValidateModal(id) {
            const modal = $('#validatePengajuanModal');
            const form = $('#validate-pengajuan-form');

            // [PERBAIKAN] Reset form dan state modal setiap kali dibuka
            form[0].reset();
            $('#catatan-penolakan-section').hide();
            $('#catatan_penolakan').prop('required', false);
            $('input[name="status_pengajuan"][value="diterima"]').prop('checked', true);

            $('#validate_nama_asesi, #validate_nama_skema, #validate_tujuan, #validate_tanggal').html(': Memuat...');
            $('#pdf_viewer').attr('src', 'about:blank');
            $('#doc_pas_foto, #doc_ktp, #doc_pendidikan').addClass('disabled').attr('href', '#');

            $.get(`${baseUrl}/api/pengajuan-asesmen/getById/${id}`, function(response) {
                if (response.status) {
                    const {
                        pengajuan,
                        asesi,
                        asesmen,
                        dokumen
                    } = response.data;

                    $('#validate_id_pengajuan').val(pengajuan.id_pengajuan);
                    // [PERBAIKAN] Menambahkan ':' agar sejajar dengan UI baru
                    $('#validate_nama_asesi').text(': ' + (asesi.nama_lengkap || '-'));
                    $('#validate_nama_skema').text(': ' + (asesmen.nama_skema || '-'));
                    $('#validate_tujuan').text(': ' + (asesmen.tujuan || '-'));
                    $('#validate_tanggal').text(': ' + new Date(pengajuan.created_at).toLocaleString('id-ID'));

                    // Set link dokumen
                    if (dokumen.pas_foto) {
                        $('#doc_pas_foto').removeClass('disabled').attr('href', `${baseUrl}uploads/asesi_dokumen/${dokumen.pas_foto}`);
                    }
                    if (dokumen.ktp) {
                        $('#doc_ktp').removeClass('disabled').attr('href', `${baseUrl}uploads/asesi_dokumen/${dokumen.ktp}`);
                    }
                    if (dokumen.bukti_pendidikan) {
                        $('#doc_pendidikan').removeClass('disabled').attr('href', `${baseUrl}uploads/asesi_dokumen/${dokumen.bukti_pendidikan}`);
                    }

                    // [PENTING] Ganti dengan URL yang benar untuk generate PDF Anda
                    $('#pdf_viewer').attr('src', `${baseUrl}/pdf/apl1/${id}`);

                    modal.modal('show');
                } else {
                    Swal.fire('Gagal', response.message || 'Data tidak ditemukan', 'error');
                }
            }).fail(() => Swal.fire('Error', 'Gagal mengambil data dari server.', 'error'));
        }

        // Handler untuk submit form validasi
        $('#validate-pengajuan-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            const id_pengajuan = form.find('#validate_id_pengajuan').val();

            if (!id_pengajuan) {
                Swal.fire('Error', 'ID Pengajuan tidak ditemukan. Coba muat ulang halaman.', 'error');
                return;
            }

            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').prop('disabled', true);

            $.ajax({
                url: `${baseUrl}/api/pengajuan-asesmen/validate/${id_pengajuan}`,
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    $('#validatePengajuanModal').modal('hide');
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

        // [BARU] Event handler untuk menampilkan/menyembunyikan catatan penolakan
        $('input[name="status_pengajuan"]').on('change', function() {
            if (this.value === 'ditolak') {
                $('#catatan-penolakan-section').slideDown();
                $('#catatan_penolakan').prop('required', true);
            } else {
                $('#catatan-penolakan-section').slideUp();
                $('#catatan_penolakan').prop('required', false).val('');
            }
        });

        // [BARU] Reset form ketika modal ditutup
        $('#validatePengajuanModal').on('hidden.bs.modal', function() {
            const form = $('#validate-pengajuan-form');
            form[0].reset();
            $('#catatan-penolakan-section').hide();
            $('#catatan_penolakan').prop('required', false);
            $('input[name="status_pengajuan"][value="diterima"]').prop('checked', true);
        });

        // =================================================================
        // Logika untuk Modal Edit
        // =================================================================

        function openEditModal(id) {
            const modal = $('#editPengajuanModal');
            const form = $('#edit-pengajuan-form');

            // Reset form
            form[0].reset();
            $('#edit_nama_asesi, #edit_nama_skema, #edit_tujuan, #edit_tanggal').html(': Memuat...');
            $('#id_asesor').empty().append('<option value="">-- Pilih Asesor --</option>'); // Reset dropdown asesor

            $.get(`${baseUrl}/api/pengajuan-asesmen/getById/${id}`, function(response) {
                if (response.status) {
                    const {
                        pengajuan,
                        asesi,
                        asesmen,
                        asesorList
                    } = response.data;

                    $('#edit_id_pengajuan').val(pengajuan.id_pengajuan);
                    $('#edit_nama_asesi').text(': ' + (asesi.nama_lengkap || '-'));
                    $('#edit_nama_skema').text(': ' + (asesmen.nama_skema || '-'));
                    $('#edit_tujuan').text(': ' + (asesmen.tujuan || '-'));
                    $('#edit_tanggal').text(': ' + new Date(pengajuan.created_at).toLocaleString('id-ID'));

                    // Isi form dengan data yang ada
                    $('#edit_id_asesor').val(pengajuan.id_asesor || '');
                    $(`input[name="status_pengajuan"][value="${pengajuan.status_pengajuan}"]`).prop('checked', true);
                    $(`input[name="status_asesmen"][value="${pengajuan.status_asesmen}"]`).prop('checked', true);

                    // Isi dropdown asesor dengan pengecekan
                    if (asesorList && Array.isArray(asesorList)) {
                        const asesorDropdown = $('#id_asesor');
                        asesorList.forEach(function(asesor) {
                            asesorDropdown.append(new Option(asesor.nama_asesor, asesor.id_asesor));
                        });
                    } else {
                        console.warn('asesorList tidak tersedia atau bukan array');
                    }

                    // Pilih asesor yang sudah ada
                    if (pengajuan.id_asesor) {
                        $('#id_asesor').val(pengajuan.id_asesor).trigger('change');
                    }

                    modal.modal('show');
                } else {
                    Swal.fire('Gagal', response.message || 'Data tidak ditemukan', 'error');
                }
            }).fail(() => Swal.fire('Error', 'Gagal mengambil data dari server.', 'error'));
        }

        // Handler untuk submit form edit
        $('#edit-pengajuan-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            const id_pengajuan = form.find('#edit_id_pengajuan').val();

            if (!id_pengajuan) {
                Swal.fire('Error', 'ID Pengajuan tidak ditemukan. Coba muat ulang halaman.', 'error');
                return;
            }

            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').prop('disabled', true);

            $.ajax({
                url: `${baseUrl}/api/pengajuan-asesmen/edit/${id_pengajuan}`,
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    $('#editPengajuanModal').modal('hide');
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

        // Reset form ketika modal edit ditutup
        $('#editPengajuanModal').on('hidden.bs.modal', function() {
            const form = $('#edit-pengajuan-form');
            form[0].reset();
            // Reset radio buttons
            $('input[name="status_pengajuan"]').prop('checked', false);
            $('input[name="status_asesmen"]').prop('checked', false);
        });
    });
</script>
<?= $this->endSection() ?>