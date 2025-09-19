<?= $this->extend('layouts/admin/layout-admin') ?>

<?= $this->section('content') ?>
<h2 class="section-title">Manajemen Pengguna</h2>
<p class="section-lead">Kelola semua pengguna, peran, dan status akun dari halaman ini.</p>

<div class="row">
    <div class="col-lg-3 col-md-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-primary"><i class="fas fa-user-shield"></i></div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Admin</h4>
                </div>
                <div class="card-body" id="stat-admin"><i class="fas fa-spinner fa-spin"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-danger"><i class="fas fa-user-tie"></i></div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Asesor</h4>
                </div>
                <div class="card-body" id="stat-asesor"><i class="fas fa-spinner fa-spin"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-warning"><i class="fas fa-user-graduate"></i></div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Asesi</h4>
                </div>
                <div class="card-body" id="stat-asesi"><i class="fas fa-spinner fa-spin"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-success"><i class="fas fa-users"></i></div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Aktif</h4>
                </div>
                <div class="card-body" id="stat-total"><i class="fas fa-spinner fa-spin"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-table"></i> Daftar Pengguna</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addAdminModal"><i class="fas fa-user-plus"></i> Tambah Admin</button>
                    <button class="btn btn-danger" data-toggle="modal" data-target="#addAsesorModal"><i class="fas fa-user-tie"></i> Tambah Asesor</button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-light d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-info-circle text-info"></i> Pengguna yang diarsipkan dapat dipulihkan/dihapus permanen. Total arsip: <span class="badge badge-secondary" id="stat-deleted"><i class="fas fa-spinner fa-spin"></i></span></div>
                    <a href="#" class="btn btn-outline-secondary btn-sm"><i class="fas fa-archive"></i> Kelola Arsip</a>
                </div>
                <div class="form-group">
                    <label class="form-label">Filter Berdasarkan Role</label>
                    <div class="selectgroup w-100">
                        <label class="selectgroup-item"><input type="radio" name="role_filter" value="" class="selectgroup-input" checked><span class="selectgroup-button">Semua</span></label>
                        <label class="selectgroup-item"><input type="radio" name="role_filter" value="Admin" class="selectgroup-input"><span class="selectgroup-button">Admin</span></label>
                        <label class="selectgroup-item"><input type="radio" name="role_filter" value="Asesor" class="selectgroup-input"><span class="selectgroup-button">Asesor</span></label>
                        <label class="selectgroup-item"><input type="radio" name="role_filter" value="Asesi" class="selectgroup-input"><span class="selectgroup-button">Asesi</span></label>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="users-table" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Bergabung</th>
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

<?= $this->section('modals') ?>
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-shield"></i> Tambah Admin Baru</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="addAdminForm">
                <div class="modal-body">
                    <input type="hidden" name="role" value="Admin">
                    <div class="form-group"><label>Username <span class="text-danger">*</span></label><input type="text" name="username" class="form-control" required></div>
                    <div class="form-group"><label>Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required></div>
                    <div class="form-group"><label>Nama Lengkap <span class="text-danger">*</span></label><input type="text" name="nama_lengkap" class="form-control" required></div>
                    <div class="form-group"><label>Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary btn-block">Simpan Admin</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addAsesorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-tie"></i> Tambah Asesor Baru</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="addAsesorForm">
                <div class="modal-body">
                    <input type="hidden" name="role" value="Asesor">
                    <div class="form-group"><label>Username <span class="text-danger">*</span></label><input type="text" name="username" class="form-control" required></div>
                    <div class="form-group"><label>Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required></div>
                    <div class="form-group"><label>Nama Lengkap <span class="text-danger">*</span></label><input type="text" name="nama_lengkap" class="form-control" required></div>
                    <div class="form-group"><label>Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required></div>
                    <div class="form-group"><label>Nomor Registrasi</label><input type="text" name="nomor_registrasi" class="form-control"></div>
                    <div class="form-group">
                        <label>Skema Sertifikasi <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="skema_id" style="width: 100%;" required>
                            <option value="">Pilih Skema</option>
                            <?php foreach ($listSkema as $skema) : ?>
                                <option value="<?= $skema['id_skema'] ?>"><?= $skema['nama_skema'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-danger btn-block">Simpan Asesor</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit"></i> Edit Pengguna</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" name="id">
                    <div class="form-group"><label>Username</label><input type="text" name="username" class="form-control" readonly></div>
                    <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" class="form-control" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="form-group">
                        <label>Role</label>
                        <select class="form-control" name="role" required>
                            <option value="Admin">Admin</option>
                            <option value="Asesor">Asesor</option>
                            <option value="Asesi">Asesi</option>
                        </select>
                    </div>
                    <div class="form-group" id="edit-skema-field" style="display: none;">
                        <label>Skema Sertifikasi (untuk Asesor)</label>
                        <select class="form-control select2" name="skema_id" style="width: 100%;">
                            <option value="">Pilih Skema</option>
                            <?php foreach ($listSkema as $skema) : ?>
                                <option value="<?= $skema['id_skema'] ?>"><?= $skema['nama_skema'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button></div>
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

        function loadStatistics() {
            $('#stat-admin, #stat-asesor, #stat-asesi, #stat-total, #stat-deleted').html('<i class="fas fa-spinner fa-spin"></i>');
            $.get(`${baseUrl}/api/user/statistics`, function(response) {
                if (response.status) {
                    const stats = response.data;
                    $('#stat-admin').text(stats.admin || 0);
                    $('#stat-asesor').text(stats.asesor || 0);
                    $('#stat-asesi').text(stats.asesi || 0);
                    $('#stat-total').text(stats.total || 0);
                    $('#stat-deleted').text(stats.deleted || 0);
                }
            });
        }

        dataTable = $('#users-table').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "order": [
                [6, 'desc']
            ],
            "ajax": {
                "url": `${baseUrl}/api/user/getDataTable`,
                "type": "POST",
                "data": function(d) {
                    d.role_filter = $('input[name="role_filter"]:checked').val();
                }
            },
            "columns": [{
                    "data": null,
                    "orderable": false,
                    "className": "text-center"
                },
                {
                    "data": "username"
                }, {
                    "data": "nama_lengkap"
                }, {
                    "data": "email"
                },
                {
                    "data": "roles",
                    "orderable": false
                }, {
                    "data": "active",
                    "className": "text-center"
                },
                {
                    "data": "created_at",
                    "className": "text-center"
                },
                {
                    "data": null,
                    "orderable": false,
                    "className": "text-center"
                }
            ],
            "columnDefs": [{
                    "targets": 0,
                    "render": (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                {
                    "targets": 4,
                    "render": function(data) {
                        if (!data) return '<span class="badge badge-light">N/A</span>';
                        return data.split(',').map(role => {
                            role = role.trim();
                            let badgeClass = 'badge-secondary';
                            if (role === 'Admin') badgeClass = 'badge-primary';
                            if (role === 'Asesor') badgeClass = 'badge-danger';
                            if (role === 'Asesi') badgeClass = 'badge-warning';
                            return `<span class="badge ${badgeClass}">${role}</span>`;
                        }).join(' ');
                    }
                },
                {
                    "targets": 5,
                    "render": (data) => data == 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>'
                },
                {
                    "targets": 6,
                    "render": (data) => new Date(data).toLocaleDateString('id-ID')
                },
                {
                    "targets": -1,
                    "render": function(data, type, row) {
                        const isActive = row.active == 1;
                        const toggleBtn = `<button class="btn btn-sm btn-icon ${isActive ? 'btn-outline-secondary' : 'btn-success'} btn-toggle-status" data-id="${row.id}" title="${isActive ? 'Nonaktifkan' : 'Aktifkan'}"><i class="fas fa-fw fa-${isActive ? 'toggle-off' : 'toggle-on'}"></i></button>`;
                        return `<div class="btn-group">
                    <button class="btn btn-sm btn-icon btn-info btn-edit" data-id="${row.id}" title="Edit"><i class="fas fa-fw fa-pencil-alt"></i></button>
                    ${toggleBtn}
                    <button class="btn btn-sm btn-icon btn-danger btn-delete" data-id="${row.id}" title="Arsipkan"><i class="fas fa-fw fa-archive"></i></button>
                </div>`;
                    }
                }
            ]
        });

        $('input[name="role_filter"]').on('change', () => dataTable.ajax.reload());

        function handleFormSubmit(formId, url, modalId) {
            $(formId).on('submit', function(e) {
                e.preventDefault();
                const form = $(this),
                    submitBtn = form.find('button[type="submit"]'),
                    originalBtnText = submitBtn.html();
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            $(modalId).modal('hide');
                            Swal.fire('Berhasil!', response.message, 'success');
                            dataTable.ajax.reload();
                            loadStatistics();
                        } else {
                            const errors = response.errors ? Object.values(response.errors).join('<br>') : (response.message || 'Periksa kembali data Anda.');
                            Swal.fire('Gagal!', errors, 'error');
                        }
                    },
                    error: () => Swal.fire('Error!', 'Terjadi kesalahan pada server.', 'error'),
                    complete: () => submitBtn.html(originalBtnText).prop('disabled', false)
                });
            });
        }

        handleFormSubmit('#addAdminForm', `${baseUrl}/api/user/create`, '#addAdminModal');
        handleFormSubmit('#addAsesorForm', `${baseUrl}/api/user/create`, '#addAsesorModal');
        handleFormSubmit('#editUserForm', `${baseUrl}/api/user/update`, '#editUserModal');

        $('#users-table tbody').on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            $.get(`${baseUrl}/api/user/getById/${id}`, function(response) {
                if (response.status) {
                    const user = response.data;
                    const form = $('#editUserForm');
                    form[0].reset();
                    $('.select2').val('').trigger('change');
                    form.find('[name="id"]').val(user.id);
                    form.find('[name="username"]').val(user.username);
                    form.find('[name="nama_lengkap"]').val(user.nama_lengkap);
                    
                    console.log(user.nama_lengkap);
                    form.find('[name="email"]').val(user.email);
                    const role = user.groups[0] || 'Asesi';
                    form.find('[name="role"]').val(role).trigger('change');

                    if (role === 'Asesor') {
                        $('#edit-skema-field').show();
                        if (user.asesor_data) {
                            form.find('[name="skema_id"]').val(user.asesor_data.id_skema).trigger('change');
                        }
                    } else {
                        $('#edit-skema-field').hide();
                    }
                    $('#editUserModal').modal('show');
                }
            });
        });

        $('#editUserForm [name="role"]').on('change', function() {
            if ($(this).val() === 'Asesor') {
                $('#edit-skema-field').slideDown();
            } else {
                $('#edit-skema-field').slideUp();
            }
        });

        $('#users-table tbody').on('click', '.btn-toggle-status, .btn-delete', function() {
            const id = $(this).data('id');
            const isDelete = $(this).hasClass('btn-delete');
            const actionUrl = isDelete ? `${baseUrl}/api/user/delete/${id}` : `${baseUrl}/api/user/toggle-status/${id}`;
            const text = isDelete ? "Anda akan mengarsipkan user ini." : "Status user akan diubah.";
            const confirmText = isDelete ? "Ya, Arsipkan" : "Ya, Ubah Status";

            Swal.fire({
                    title: 'Anda Yakin?',
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: confirmText
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        $.post(actionUrl, (response) => {
                            if (response.status) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                dataTable.ajax.reload(null, false);
                                loadStatistics();
                            } else {
                                Swal.fire('Gagal!', response.message || 'Aksi gagal.', 'error');
                            }
                        }).fail(() => Swal.fire('Error!', 'Terjadi kesalahan server.', 'error'));
                    }
                });
        });

        loadStatistics();
    });
</script>
<?= $this->endSection() ?>