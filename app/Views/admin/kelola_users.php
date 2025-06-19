<?= $this->extend('layouts/admin/layout-admin') ?>

<?= $this->section('content') ?>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('pesan')) : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-check-circle"></i> Berhasil!</strong> <?= session()->getFlashdata('pesan') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('warning')) : ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-exclamation-triangle"></i> Peringatan!</strong> <?= session()->getFlashdata('warning') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-primary">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Admin</h4>
                </div>
                <div class="card-body" id="stat-admin">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-danger">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Asesor</h4>
                </div>
                <div class="card-body" id="stat-asesor">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-warning">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Asesi</h4>
                </div>
                <div class="card-body" id="stat-asesi">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-success">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Pengguna</h4>
                </div>
                <div class="card-body" id="stat-total">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Info Card -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-light">
            <div class="alert-title"><i class="fas fa-info-circle text-info"></i> Informasi</div>
            <div class="row">
                <div class="col-md-8">
                    Pengguna yang diarsipkan dapat dipulihkan atau dihapus permanen melalui halaman <strong>Arsip Pengguna</strong>.
                    Total pengguna arsip: <span class="badge badge-secondary" id="stat-deleted"><i class="fas fa-spinner fa-spin"></i></span>
                </div>
                <div class="col-md-4 text-right">
                    <a href="<?= site_url('/admin/deleted-users') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-archive"></i> Kelola Arsip
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users Management Section -->
<div class="section-body">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-table"></i> Daftar Pengguna</h4>
                    <div class="card-header-action">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addAdminModal">
                            <i class="fas fa-user-plus"></i> Admin
                        </button>
                        <button class="btn btn-danger" data-toggle="modal" data-target="#addAsesorModal">
                            <i class="fas fa-user-tie"></i> Asesor
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-filter"></i> Filter Role</h4>
                            <div class="card-header-action">
                                <a data-collapse="#filter-collapse" class="btn btn-icon btn-info" href="#"><i class="fas fa-minus"></i></a>
                            </div>
                        </div>
                        <div class="collapse show" id="filter-collapse">
                            <div class="card-body">
                                <div class="buttons">
                                    <button type="button" class="btn btn-outline-secondary role-filter active" data-role="">
                                        <i class="fas fa-users"></i> Semua
                                    </button>
                                    <button type="button" class="btn btn-outline-primary role-filter" data-role="Admin">
                                        <i class="fas fa-user-shield"></i> Admin
                                    </button>
                                    <button type="button" class="btn btn-outline-danger role-filter" data-role="Asesor">
                                        <i class="fas fa-user-tie"></i> Asesor
                                    </button>
                                    <button type="button" class="btn btn-outline-warning role-filter" data-role="Asesi">
                                        <i class="fas fa-user-graduate"></i> Asesi
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table id="users-table" class="table table-striped table-md">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th><i class="fas fa-user"></i> Username</th>
                                    <th><i class="fas fa-id-card"></i> Nama Lengkap</th>
                                    <th><i class="fas fa-envelope"></i> Email</th>
                                    <th><i class="fas fa-user-tag"></i> Role</th>
                                    <th><i class="fas fa-toggle-on"></i> Status</th>
                                    <th><i class="fas fa-calendar"></i> Dibuat</th>
                                    <th class="text-center"><i class="fas fa-cogs"></i> Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<!-- View User Details Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" role="dialog" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewUserModalLabel"><i class="fas fa-user-circle"></i> Detail Pengguna</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="user-details-content">
                    <!-- User details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning edit-user-role" style="display: none;">
                    <i class="fas fa-user-edit"></i> Edit Role
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">
                    <i class="fas fa-user-edit"></i> Edit Pengguna & Role
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" id="edit-user-id" name="id">

                    <div class="form-group">
                        <label for="edit-username"><i class="fas fa-user"></i> Username</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <input type="text" class="form-control" id="edit-username" name="username" readonly>
                        </div>
                        <small class="form-text text-muted">Username tidak dapat diubah</small>
                    </div>

                    <div class="form-group">
                        <label for="edit-nama-lengkap"><i class="fas fa-id-card"></i> Nama Lengkap</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-id-card"></i>
                                </div>
                            </div>
                            <input type="text" class="form-control" id="edit-nama-lengkap" name="nama_lengkap" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit-email"><i class="fas fa-envelope"></i> Email</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                            <input type="email" class="form-control" id="edit-email" name="email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-roles"><i class="fas fa-user-tag"></i> Role</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-user-tag"></i>
                                </div>
                            </div>
                            <select class="form-control" id="edit-roles" name="role" required>
                                <option value="">Pilih Role</option>
                                <option value="asesi">Asesi (Default)</option>
                                <option value="asesor">Asesor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <small class="form-text text-muted">
                            <strong>Perhatian:</strong> Mengubah role akan mempengaruhi hak akses pengguna.
                            <br>• <strong>Asesi:</strong> Dapat mengikuti asesmen
                            <br>• <strong>Asesor:</strong> Dapat melakukan asesmen
                            <br>• <strong>Admin:</strong> Dapat mengelola sistem
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Admin User Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" role="dialog" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAdminModalLabel"><i class="fas fa-user-shield text-primary"></i> Tambah Admin Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addAdminForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="admin-username">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <input type="text" class="form-control" id="admin-username" name="username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="admin-email">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                            <input type="email" class="form-control" id="admin-email" name="email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="admin-nama-lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-id-card"></i>
                                </div>
                            </div>
                            <input type="text" class="form-control" id="admin-nama-lengkap" name="nama_lengkap" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="admin-password">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </div>
                            </div>
                            <input type="password" class="form-control" id="admin-password" name="password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('admin-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Minimal 8 karakter</small>
                    </div>

                    <div class="form-group">
                        <label for="admin-password-confirm">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </div>
                            </div>
                            <input type="password" class="form-control" id="admin-password-confirm" name="password_confirm" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('admin-password-confirm')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Tambah Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Asesor User Modal -->
<div class="modal fade" id="addAsesorModal" tabindex="-1" role="dialog" aria-labelledby="addAsesorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAsesorModalLabel"><i class="fas fa-user-tie text-danger"></i> Tambah Asesor Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addAsesorForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="asesor-username">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <input type="text" class="form-control" id="asesor-username" name="username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="asesor-email">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                            <input type="email" class="form-control" id="asesor-email" name="email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="asesor-nama-lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-id-card"></i>
                                </div>
                            </div>
                            <input type="text" class="form-control" id="asesor-nama-lengkap" name="nama_lengkap" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="asesor-password">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </div>
                            </div>
                            <input type="password" class="form-control" id="asesor-password" name="password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('asesor-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Minimal 8 karakter</small>
                    </div>
                    <div class="form-group">
                        <label for="asesor-password-confirm">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </div>
                            </div>
                            <input type="password" class="form-control" id="asesor-password-confirm" name="password_confirm" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('asesor-password-confirm')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Separator untuk Data Asesor -->
                    <hr>
                    <h6 class="text-primary mb-3"><i class="fas fa-certificate"></i> Data Asesor</h6>

                    <div class="form-group">
                        <label for="asesor-nomor-registrasi">Nomor Registrasi Asesor</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-id-badge"></i>
                                </div>
                            </div>
                            <input type="text" class="form-control" id="asesor-nomor-registrasi" name="nomor_registrasi" placeholder="Contoh: ASR-2024-001">
                        </div>
                        <small class="form-text text-muted">Nomor registrasi unik asesor (opsional)</small>
                    </div>
                    <div class="form-group">
                        <label for="asesor-skema-sertifikasi">Skema Sertifikasi <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-award"></i>
                                </div>
                            </div>
                            <select class="form-control" id="asesor-skema-sertifikasi" name="bidang_kompetensi" required>
                                <option value="">Memuat skema sertifikasi...</option>
                            </select>
                        </div>
                        <small class="form-text text-muted">Pilih bidang kompetensi/skema sertifikasi asesor</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-user-tie"></i> Tambah Asesor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    // CSRF Token untuk AJAX requests
    const csrf_token_name = '<?= csrf_token() ?>';
    const csrf_hash = '<?= csrf_hash() ?>';

    // Function to toggle password visibility
    function togglePassword(inputId) {
        let input = document.getElementById(inputId);
        let button = input.nextElementSibling.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            button.classList.remove('fa-eye');
            button.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            button.classList.remove('fa-eye-slash');
            button.classList.add('fa-eye');
        }
    }

    $(document).ready(function() { // Setup CSRF for all AJAX requests
        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if (settings.type !== 'GET') {
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    if (typeof csrf_token_name !== 'undefined' && typeof csrf_hash !== 'undefined') {
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrf_hash);
                    }
                }
            },
            complete: function(xhr, status) {
                // Update CSRF token if provided in response headers
                var newToken = xhr.getResponseHeader('X-CSRF-TOKEN');
                if (newToken) {
                    csrf_hash = newToken;
                }
            }
        });

        // Load skema sertifikasi untuk form asesor
        function loadSkemasForAsesor() {
            $.ajax({
                url: '<?= site_url('/api/user-management/get-active-skemas') ?>',
                type: 'GET',
                success: function(response) {
                    if (response.status && response.data) {
                        let select = $('#asesor-skema-sertifikasi');
                        select.empty();
                        select.append('<option value="">Pilih Skema Sertifikasi</option>');

                        response.data.forEach(function(skema) {
                            select.append('<option value="' + skema.nama_skema + '">' + skema.nama_skema + '</option>');
                        });
                    } else {
                        $('#asesor-skema-sertifikasi').html('<option value="">Gagal memuat skema sertifikasi</option>');
                    }
                },
                error: function() {
                    $('#asesor-skema-sertifikasi').html('<option value="">Gagal memuat skema sertifikasi</option>');
                }
            });
        } // Load skemas saat modal asesor dibuka
        $('#addAsesorModal').on('show.bs.modal', function() {
            loadSkemasForAsesor();
        });

        // Function to load asesor details
        function loadAsesorDetails(userId) {
            $.ajax({
                url: '<?= site_url('/api/user-management/get-asesor-by-user-id') ?>',
                type: 'GET',
                data: {
                    user_id: userId
                },
                success: function(response) {
                    if (response.status && response.data) {
                        let asesor = response.data;
                        let asesorHtml = `
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="fas fa-user-tie"></i> Informasi Asesor</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td><strong>ID Asesor:</strong></td>
                                                    <td>${asesor.id_asesor}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Nomor Registrasi:</strong></td>
                                                    <td>${asesor.nomor_registrasi || '-'}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Skema Sertifikasi:</strong></td>
                                                    <td>
                                                        <span class="badge badge-info">${asesor.bidang_kompetensi || '-'}</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Append asesor details to existing content
                        $('#user-details-content').append(asesorHtml);
                    }
                },
                error: function() {
                    // Silent error, asesor details not critical
                }
            });
        }

        let usersTable = $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            stateSave: true,
            stateDuration: 300, // 5 minutes
            ajax: {
                url: '<?= site_url('/admin/kelola-users/data') ?>',
                type: 'POST',
                data: function(d) {
                    d.role_filter = $('.role-filter.active').data('role') || '';
                    d[csrf_token_name] = csrf_hash;
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable error:', error);
                    showAlert('error', 'Gagal memuat data pengguna');
                }
            },
            columns: [{
                    data: 0,
                    name: 'no',
                    orderable: false,
                    searchable: false,
                    width: '5%',
                    className: 'text-center'
                },
                {
                    data: 1,
                    name: 'username',
                    width: '15%'
                },
                {
                    data: 2,
                    name: 'nama_lengkap',
                    width: '20%'
                },
                {
                    data: 3,
                    name: 'email',
                    width: '20%'
                },
                {
                    data: 4,
                    name: 'roles',
                    orderable: false,
                    width: '15%'
                },
                {
                    data: 5,
                    name: 'active',
                    orderable: false,
                    width: '10%',
                    className: 'text-center'
                },
                {
                    data: 6,
                    name: 'created_at',
                    width: '10%',
                    className: 'text-center'
                },
                {
                    data: 7,
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    width: '5%',
                    className: 'text-center'
                }
            ],
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            order: [
                [6, 'desc']
            ], // Order by created_at desc
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> Memuat data...',
                lengthMenu: 'Tampilkan _MENU_ data per halaman',
                zeroRecords: 'Tidak ada data yang ditemukan',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                infoFiltered: '(disaring dari _MAX_ total data)',
                search: 'Cari:',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Selanjutnya',
                    previous: 'Sebelumnya'
                }
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            responsive: true,
            autoWidth: false,
            drawCallback: function(settings) {
                // Initialize tooltips for new elements
                $('[data-toggle="tooltip"]').tooltip();

                // Add loading states for action buttons
                $('.btn').on('click', function() {
                    let btn = $(this);
                    if (!btn.hasClass('no-loading')) {
                        let originalHtml = btn.html();
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                        setTimeout(() => {
                            btn.prop('disabled', false).html(originalHtml);
                        }, 2000);
                    }
                });
            },
            ajax: {
                url: '<?= site_url('/api/user-management/get-data-table') ?>',
                type: 'POST',
                data: function(d) {
                    // Add custom filter parameters
                    d.role_filter = $('.role-filter.active').data('role');
                }
            },
            columns: [{
                    data: null,
                    name: 'row_number',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                }, {
                    data: 'username',
                    name: 'username',
                    render: function(data, type, row) {
                        return data ? '<div class="badge badge-light"><i class="fas fa-user"></i> ' + data + '</div>' : '<span class="text-muted">-</span>';
                    }
                }, {
                    data: 'nama_lengkap',
                    name: 'nama_lengkap',
                    render: function(data, type, row) {
                        return data || '<span class="text-muted">-</span>';
                    }
                },
                {
                    data: 'email',
                    name: 'email',
                    render: function(data, type, row) {
                        return data || '<span class="text-muted">-</span>';
                    }
                }, {
                    data: 'roles',
                    name: 'roles',
                    orderable: false,
                    render: function(data, type, row) {
                        if (!data || data === 'No Role' || data === null || data === '') {
                            return '<span class="badge badge-secondary">No Role</span>';
                        }

                        // Split roles string into array
                        let rolesArray = data.split(', ');
                        let rolesBadges = '';

                        rolesArray.forEach(function(role) {
                            if (role && role.trim() !== '') {
                                let badgeClass = 'badge-secondary';
                                let icon = 'fas fa-user';
                                switch (role.toLowerCase()) {
                                    case 'admin':
                                        badgeClass = 'badge-primary';
                                        icon = 'fas fa-user-shield';
                                        break;
                                    case 'asesor':
                                        badgeClass = 'badge-danger';
                                        icon = 'fas fa-user-tie';
                                        break;
                                    case 'asesi':
                                        badgeClass = 'badge-warning';
                                        icon = 'fas fa-user-graduate';
                                        break;
                                }
                                rolesBadges += '<span class="badge ' + badgeClass + ' mr-1"><i class="' + icon + '"></i> ' + role + '</span>';
                            }
                        });

                        return rolesBadges || '<span class="badge badge-secondary">No Role</span>';
                    }
                },
                {
                    data: 'active',
                    name: 'active',
                    render: function(data, type, row) {
                        if (data == 1 || data === '1') {
                            return '<div class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</div>';
                        } else if (data == 0 || data === '0') {
                            return '<div class="badge badge-danger"><i class="fas fa-times-circle"></i> Tidak Aktif</div>';
                        } else {
                            return '<span class="badge badge-secondary">-</span>';
                        }
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data, type, row) {
                        if (data && data !== null && data !== '') {
                            let date = new Date(data);
                            if (!isNaN(date.getTime())) {
                                return '<small><i class="fas fa-calendar"></i> ' + date.toLocaleDateString('id-ID') + '<br><i class="fas fa-clock"></i> ' + date.toLocaleTimeString('id-ID') + '</small>';
                            }
                        }
                        return '<span class="text-muted">-</span>';
                    }
                }, {
                    data: null,
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let actions = '<div class="btn-group" role="group">';

                        // Detail button
                        actions += '<button class="btn btn-sm btn-icon btn-info view-user" data-id="' + row.id + '" title="Lihat Detail" data-toggle="tooltip">' +
                            '<i class="fas fa-eye"></i></button>'; // Edit button
                        actions += '<button class="btn btn-sm btn-icon btn-warning edit-user" data-id="' + row.id + '" title="Edit User & Role" data-toggle="tooltip">' +
                            '<i class="fas fa-edit"></i></button>';

                        // Status toggle button
                        if (row.active == 1) {
                            actions += '<button class="btn btn-sm btn-icon btn-danger toggle-status" data-id="' + row.id + '" data-status="0" title="Nonaktifkan" data-toggle="tooltip">' +
                                '<i class="fas fa-ban"></i></button>';
                        } else {
                            actions += '<button class="btn btn-sm btn-icon btn-success toggle-status" data-id="' + row.id + '" data-status="1" title="Aktifkan" data-toggle="tooltip">' +
                                '<i class="fas fa-check"></i></button>';
                        }

                        // Soft delete button
                        actions += '<button class="btn btn-sm btn-icon btn-secondary soft-delete-user" data-id="' + row.id + '" title="Hapus User" data-toggle="tooltip">' +
                            '<i class="fas fa-trash"></i></button>';

                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            order: [
                [6, 'desc']
            ], // Order by created_at desc
            pageLength: 25,
            responsive: true,
            language: {
                processing: "Memuat data...",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                },
                emptyTable: "Tidak ada data yang tersedia",
                zeroRecords: "Tidak ada data yang cocok dengan pencarian"
            },
            drawCallback: function() {
                // Initialize tooltips after table draw
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        // Role filter change event
        $('.role-filter').on('click', function() {
            $('.role-filter').removeClass('active');
            $(this).addClass('active');
            usersTable.ajax.reload();
        });

        // View user details
        $(document).on('click', '.view-user', function() {
            let userId = $(this).data('id');

            // Show loading in modal
            $('#user-details-content').html(`
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Memuat detail pengguna...</p>
                </div>
            `);

            $('#viewUserModal').modal('show'); // Load user details
            $.ajax({
                url: '<?= site_url('/api/user-management/get-user-by-id') ?>',
                type: 'POST',
                data: {
                    id: userId,
                    ...(csrf_token_name && csrf_hash ? {
                        [csrf_token_name]: csrf_hash
                    } : {})
                },
                success: function(response) {
                    if (response.status && response.data) {
                        let user = response.data;
                        let html = `
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-user"></i> Informasi Dasar</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td><strong>ID:</strong></td>
                                                    <td>${user.id}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Username:</strong></td>
                                                    <td>${user.username || '-'}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email:</strong></td>
                                                    <td>${user.email}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Nama Lengkap:</strong></td>
                                                    <td>${user.nama_lengkap || '-'}</td>
                                                </tr>                                                <tr>
                                                    <td><strong>Role:</strong></td>
                                                    <td>
                                                        <div id="user-roles-badges"></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status:</strong></td>
                                                    <td>
                                                        ${user.active == 1 
                                                            ? '<span class="badge badge-success">Aktif</span>' 
                                                            : '<span class="badge badge-danger">Nonaktif</span>'
                                                        }
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-calendar"></i> Informasi Waktu</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td><strong>Dibuat:</strong></td>
                                                    <td>${user.created_at ? new Date(user.created_at).toLocaleString('id-ID') : '-'}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Diupdate:</strong></td>
                                                    <td>${user.updated_at ? new Date(user.updated_at).toLocaleString('id-ID') : '-'}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Login Terakhir:</strong></td>
                                                    <td>${user.last_login ? new Date(user.last_login).toLocaleString('id-ID') : 'Belum pernah login'}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email Terverifikasi:</strong></td>
                                                    <td>
                                                        ${user.activate_hash 
                                                            ? '<span class="badge badge-warning">Belum Terverifikasi</span>' 
                                                            : '<span class="badge badge-success">Terverifikasi</span>'
                                                        }
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            ${user.reset_hash ? `
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Perhatian:</strong> User ini memiliki token reset password yang aktif.
                                </div>
                            ` : ''}
                        `;

                        $('#user-details-content').html(html);

                        // Generate role badges
                        let rolesHtml = '';
                        if (user.roles_array && user.roles_array.length > 0) {
                            user.roles_array.forEach(function(role) {
                                let badgeClass = 'badge-secondary';
                                switch (role.trim()) {
                                    case 'Admin':
                                        badgeClass = 'badge-primary';
                                        break;
                                    case 'Asesor':
                                        badgeClass = 'badge-info';
                                        break;
                                    case 'Asesi':
                                        badgeClass = 'badge-warning';
                                        break;
                                }
                                rolesHtml += '<span class="badge ' + badgeClass + ' mr-1">' + role.trim() + '</span>';
                            });
                        } else {
                            rolesHtml = '<span class="badge badge-secondary">No Role</span>';
                        }
                        $('#user-roles-badges').html(rolesHtml);

                        // Check if user is asesor and load asesor details
                        let hasAsesorRole = user.roles_array && user.roles_array.some(role => role.trim() === 'Asesor');
                        if (hasAsesorRole) {
                            loadAsesorDetails(user.id);
                        }

                        // Show edit role button and store user ID
                        $('.edit-user-role').show().data('user-id', user.id);
                    } else {
                        $('#user-details-content').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                ${response.message || 'Gagal memuat detail pengguna'}
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    $('#user-details-content').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Terjadi kesalahan saat memuat detail pengguna</strong><br>
                            <small>Silakan coba lagi atau hubungi administrator jika masalah berlanjut.</small>
                        </div>
                    `);
                }
            });
        });

        // Edit user
        $(document).on('click', '.edit-user', function() {
            let userId = $(this).data('id'); // Load user details for editing
            $.ajax({
                url: '<?= site_url('/api/user-management/get-user-by-id') ?>',
                type: 'POST',
                data: {
                    id: userId,
                    ...(csrf_token_name && csrf_hash ? {
                        [csrf_token_name]: csrf_hash
                    } : {})
                },
                success: function(response) {
                    if (response.status && response.data) {
                        let user = response.data;
                        $('#edit-user-id').val(user.id);
                        $('#edit-username').val(user.username);
                        $('#edit-nama-lengkap').val(user.nama_lengkap || '');
                        $('#edit-email').val(user.email || '');
                        // Set role dropdown value based on user's current role
                        let userRole = 'asesi'; // default role
                        if (user.roles) {
                            let roles = user.roles.toLowerCase();
                            if (roles.includes('admin')) {
                                userRole = 'admin';
                            } else if (roles.includes('asesor')) {
                                userRole = 'asesor';
                            }
                        }
                        $('#edit-roles').val(userRole).data('original-role', userRole);

                        $('#editUserModal').modal('show');
                    } else {
                        showAlert('error', 'Gagal memuat data pengguna: ' + (response.message || 'Response tidak valid'));
                    }
                },
                error: function(xhr, status, error) {
                    showAlert('error', 'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
                }
            });
        }); // Handle edit user form submission
        $('#editUserForm').on('submit', function(e) {
            e.preventDefault();

            let selectedRole = $('#edit-roles').val();
            let originalRole = $('#edit-roles').data('original-role');

            // Validate role selection
            if (!selectedRole) {
                showAlert('error', 'Silakan pilih role untuk pengguna');
                return;
            }

            // Show confirmation if changing role to admin
            if (selectedRole === 'admin' && originalRole !== 'admin') {
                Swal.fire({
                    title: 'Konfirmasi Perubahan Role',
                    text: 'Anda akan mengubah role pengguna menjadi Admin. Admin memiliki akses penuh ke sistem. Lanjutkan?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Ubah ke Admin',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitEditForm();
                    }
                });
                return;
            }

            // Submit form directly if not changing to admin
            submitEditForm();

            function submitEditForm() {
                let formData = $('#editUserForm').serialize();

                let submitBtn = $('#editUserForm').find('button[type="submit"]');
                let originalText = submitBtn.html();

                // Disable submit button and show loading
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                $.ajax({
                    url: '<?= site_url('/api/user-management/update-user') ?>',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.status) {
                            $('#editUserModal').modal('hide');
                            showAlert('success', response.message);
                            usersTable.ajax.reload(null, false); // Reload table without resetting pagination
                            loadStatistics(); // Reload statistics after role change
                        } else {
                            showAlert('error', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = 'Terjadi kesalahan saat menyimpan data';
                        try {
                            let response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            errorMessage += ': ' + error;
                        }

                        showAlert('error', errorMessage);
                    },
                    complete: function() {
                        // Re-enable submit button
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            }
        });

        // Toggle user status
        $(document).on('click', '.toggle-status', function() {
            let userId = $(this).data('id');
            let newStatus = $(this).data('status');
            let button = $(this);
            let statusText = newStatus == 1 ? 'mengaktifkan' : 'menonaktifkan';

            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin ingin ' + statusText + ' pengguna ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, ' + statusText + '!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button and show loading
                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: '<?= site_url('/api/user-management/update-status') ?>',
                        type: 'POST',
                        data: {
                            id: userId,
                            status: newStatus
                        },
                        success: function(response) {
                            if (response.status) {
                                showAlert('success', response.message);
                                usersTable.ajax.reload(null, false); // Reload table without resetting pagination
                                loadStatistics(); // Reload statistics
                            } else {
                                showAlert('error', response.message);
                            }
                        },
                        error: function() {
                            showAlert('error', 'Terjadi kesalahan saat mengubah status');
                        },
                        complete: function() {
                            // Re-enable button
                            button.prop('disabled', false);
                        }
                    });
                }
            });
        }); // Soft delete user
        $(document).on('click', '.delete-user', function() {
            let userId = $(this).data('id');
            let userName = $(this).data('name');
            let button = $(this);

            Swal.fire({
                title: 'Konfirmasi Pengarsipan',
                text: `Apakah Anda yakin ingin mengarsipkan pengguna "${userName}"? Data akan disembunyikan namun dapat dipulihkan kembali.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Arsipkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button and show loading
                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: '<?= site_url('/admin/kelola-users/delete') ?>/' + userId,
                        type: 'POST',
                        data: {
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        success: function(response) {
                            if (response.status) {
                                showAlert('success', response.message);
                                usersTable.ajax.reload(null, false);
                                loadStatistics();
                            } else {
                                showAlert('error', response.message);
                            }
                        },
                        error: function() {
                            showAlert('error', 'Terjadi kesalahan saat mengarsipkan pengguna');
                        },
                        complete: function() {
                            button.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                        }
                    });
                }
            });
        });

        // Handle add admin form submission
        $('#addAdminForm').on('submit', function(e) {
            e.preventDefault();

            // Validate password confirmation
            let password = $('#admin-password').val();
            let passwordConfirm = $('#admin-password-confirm').val();

            if (password !== passwordConfirm) {
                showAlert('error', 'Password dan konfirmasi password tidak cocok');
                return;
            }

            if (password.length < 8) {
                showAlert('error', 'Password minimal 8 karakter');
                return;
            }

            let formData = $(this).serialize();
            let submitBtn = $(this).find('button[type="submit"]');
            let originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Membuat...');

            $.ajax({
                url: '<?= site_url('/api/user-management/create-admin-user') ?>',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status) {
                        $('#addAdminModal').modal('hide');
                        $('#addAdminForm')[0].reset();
                        showAlert('success', response.message);
                        usersTable.ajax.reload(null, false);
                        loadStatistics();
                    } else {
                        showAlert('error', response.message);
                    }
                },
                error: function() {
                    showAlert('error', 'Terjadi kesalahan saat membuat admin');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Handle add asesor form submission
        $('#addAsesorForm').on('submit', function(e) {
            e.preventDefault();

            // Validate password confirmation
            let password = $('#asesor-password').val();
            let passwordConfirm = $('#asesor-password-confirm').val();

            if (password !== passwordConfirm) {
                showAlert('error', 'Password dan konfirmasi password tidak cocok');
                return;
            }

            if (password.length < 8) {
                showAlert('error', 'Password minimal 8 karakter');
                return;
            }

            let formData = $(this).serialize();
            let submitBtn = $(this).find('button[type="submit"]');
            let originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Membuat...');

            $.ajax({
                url: '<?= site_url('/api/user-management/create-asesor-user') ?>',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status) {
                        $('#addAsesorModal').modal('hide');
                        $('#addAsesorForm')[0].reset();
                        showAlert('success', response.message);
                        usersTable.ajax.reload(null, false);
                        loadStatistics();
                    } else {
                        showAlert('error', response.message);
                    }
                },
                error: function() {
                    showAlert('error', 'Terjadi kesalahan saat membuat asesor');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Load statistics via AJAX
        function loadStatistics() {
            $.ajax({
                url: '<?= site_url('/api/user-management/get-user-statistics-with-deleted') ?>',
                type: 'GET',
                success: function(response) {
                    if (response.status && response.data) {
                        let stats = response.data;
                        $('#stat-admin').text(stats.role_admin || 0);
                        $('#stat-asesor').text(stats.role_asesor || 0);
                        $('#stat-asesi').text(stats.role_asesi || 0);
                        $('#stat-deleted').text(stats.deleted_users || 0);

                        // Use the backend's total_users field instead of summing role counts
                        // This prevents double-counting users with multiple roles
                        $('#stat-total').text(stats.total_users || 0);
                    } else {
                        // Show error in stats cards
                        $('#stat-admin, #stat-asesor, #stat-asesi, #stat-total, #stat-deleted').text('-');
                    }
                },
                error: function() {
                    $('#stat-admin, #stat-asesor, #stat-asesi, #stat-total, #stat-deleted').text('-');
                }
            });
        }

        // Helper function to show alerts using SweetAlert2
        function showAlert(type, message) {
            let icon = type === 'success' ? 'success' : 'error';
            let title = type === 'success' ? 'Berhasil!' : 'Error!';

            Swal.fire({
                icon: icon,
                title: title,
                text: message,
                timer: type === 'success' ? 3000 : undefined,
                showConfirmButton: type !== 'success',
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                showCloseButton: true
            });
        }

        // Load statistics on page load
        loadStatistics();

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
<?= $this->endSection() ?>