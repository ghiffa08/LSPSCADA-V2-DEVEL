<?= $this->extend('layouts/admin/layout-admin') ?>

<?= $this->section('title') ?>
Arsip Pengguna
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Statistics Cards for Archived Users -->
<div class="row">
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-secondary">
                <i class="fas fa-archive"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Arsip</h4>
                </div>
                <div class="card-body" id="stat-archived">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-info">
                <i class="fas fa-history"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Dapat Dipulihkan</h4>
                </div>
                <div class="card-body" id="stat-restorable">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Butuh Perhatian</h4>
                </div>
                <div class="card-body" id="stat-attention">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Information Alert -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-light">
            <div class="alert-title"><i class="fas fa-info-circle text-info"></i> Informasi Arsip</div>
            <div class="row">
                <div class="col-md-8">
                    <p class="mb-2">
                        <i class="fas fa-shield-alt text-success"></i> Pengguna yang diarsipkan dapat <strong>dipulihkan kembali</strong> dengan semua data utuh.<br>
                        <i class="fas fa-exclamation-triangle text-warning"></i> Penghapusan permanen akan <strong>menghilangkan semua data</strong> dan tidak dapat dikembalikan.<br>
                        <i class="fas fa-clock text-info"></i> Data arsip disimpan secara aman untuk keperluan audit dan pemulihan.
                    </p>
                </div>
                <div class="col-md-4 text-right">
                    <div class="buttons">
                        <a href="<?= site_url('/admin/kelola-users') ?>" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Kelola Pengguna
                        </a>
                        <button class="btn btn-outline-success" id="bulk-restore-btn" disabled>
                            <i class="fas fa-undo"></i> Pulihkan Terpilih
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Archived Users Management -->
<div class="section-body">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-list"></i> Daftar Pengguna Terarsip</h4>
                    <div class="card-header-action">
                        <div class="buttons">
                            <button class="btn btn-outline-danger" id="bulk-permanent-delete-btn" disabled>
                                <i class="fas fa-trash-alt"></i> Hapus Permanen Terpilih
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Action Filter Section -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h4><i class="fas fa-filter"></i> Filter & Aksi Massal</h4>
                            <div class="card-header-action">
                                <a data-collapse="#filter-collapse" class="btn btn-icon btn-info" href="#"><i class="fas fa-minus"></i></a>
                            </div>
                        </div>
                        <div class="collapse show" id="filter-collapse">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="form-group mb-2">
                                            <label>Filter berdasarkan Role:</label>
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
                                    <div class="col-md-6 text-right">
                                        <div class="form-group mb-2">
                                            <label>Aksi Massal:</label><br>
                                            <div class="pretty p-default p-curve p-bigger">
                                                <input type="checkbox" id="select-all-checkbox" />
                                                <div class="state p-primary">
                                                    <label>Pilih Semua</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table id="deleted-users-table" class="table table-striped table-md">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 3%;">
                                        <div class="custom-checkbox custom-control">
                                            <input type="checkbox" data-checkboxes="mygroup" data-checkbox-role="dad" class="custom-control-input" id="checkbox-all">
                                            <label for="checkbox-all" class="custom-control-label">&nbsp;</label>
                                        </div>
                                    </th>
                                    <th class="text-center" style="width: 3%;">#</th>
                                    <th><i class="fas fa-user"></i> Username</th>
                                    <th><i class="fas fa-id-card"></i> Nama Lengkap</th>
                                    <th><i class="fas fa-envelope"></i> Email</th>
                                    <th><i class="fas fa-user-tag"></i> Role</th>
                                    <th><i class="fas fa-calendar-times"></i> Diarsipkan</th>
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
<!-- View Archived User Details Modal -->
<div class="modal fade" id="viewArchivedUserModal" tabindex="-1" role="dialog" aria-labelledby="viewArchivedUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewArchivedUserModalLabel"><i class="fas fa-archive"></i> Detail Pengguna Terarsip</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="archived-user-details-content">
                    <!-- User details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        let deletedUsersTable = $('#deleted-users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= site_url('/admin/deleted-users/data-table') ?>',
                type: 'POST',
                data: function(d) {
                    d.role_filter = $('.role-filter.active').data('role');
                }
            },
            columns: [{
                data: null,
                name: 'checkbox',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return '<div class="custom-checkbox custom-control">' +
                        '<input type="checkbox" data-checkboxes="mygroup" class="custom-control-input user-checkbox" id="checkbox-' + row.id + '" value="' + row.id + '">' +
                        '<label for="checkbox-' + row.id + '" class="custom-control-label">&nbsp;</label>' +
                        '</div>';
                }
            }, {
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
            }, {
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
                    if (!data || data === 'No Role' || data === null || data === '' || data === 'null') {
                        return '<span class="badge badge-secondary"><i class="fas fa-user"></i> No Role</span>';
                    }

                    // Handle if data is already an array or a string
                    let rolesArray = [];
                    if (Array.isArray(data)) {
                        rolesArray = data;
                    } else if (typeof data === 'string') {
                        rolesArray = data.split(', ').filter(role => role && role.trim() !== '' && role !== 'null');
                    }

                    // Remove duplicates and empty values
                    rolesArray = [...new Set(rolesArray)].filter(role => role && role.trim() !== '' && role !== 'null');

                    if (rolesArray.length === 0) {
                        return '<span class="badge badge-secondary"><i class="fas fa-user"></i> No Role</span>';
                    }

                    let rolesBadges = '';
                    rolesArray.forEach(function(role) {
                        if (role && role.trim() !== '' && role !== 'null') {
                            let badgeClass = 'badge-secondary';
                            let icon = 'fas fa-user';
                            switch (role.toLowerCase().trim()) {
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
                            rolesBadges += '<span class="badge ' + badgeClass + ' mr-1 mb-1"><i class="' + icon + '"></i> ' + role.trim() + '</span>';
                        }
                    });

                    return rolesBadges || '<span class="badge badge-secondary"><i class="fas fa-user"></i> No Role</span>';
                }
            }, {
                data: 'deleted_at',
                name: 'deleted_at',
                render: function(data, type, row) {
                    if (data && data !== null && data !== '') {
                        let date = new Date(data);
                        if (!isNaN(date.getTime())) {
                            return '<small><i class="fas fa-calendar-times"></i> ' + date.toLocaleDateString('id-ID') + '<br><i class="fas fa-clock"></i> ' + date.toLocaleTimeString('id-ID') + '</small>';
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

                    // View details button
                    actions += '<button class="btn btn-sm btn-icon btn-info view-archived-user" data-id="' + row.id + '" title="Lihat Detail" data-toggle="tooltip">' +
                        '<i class="fas fa-eye"></i></button>';

                    // Restore button
                    actions += '<button class="btn btn-sm btn-icon btn-success restore-user" data-id="' + row.id + '" title="Pulihkan User" data-toggle="tooltip">' +
                        '<i class="fas fa-undo"></i></button>';

                    // Permanent delete button
                    actions += '<button class="btn btn-sm btn-icon btn-danger permanent-delete-user" data-id="' + row.id + '" title="Hapus Permanen" data-toggle="tooltip">' +
                        '<i class="fas fa-trash-alt"></i></button>';

                    actions += '</div>';
                    return actions;
                }
            }],
            order: [
                [6, 'desc']
            ], // Order by deleted_at desc
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
                emptyTable: "Tidak ada pengguna yang diarsipkan",
                zeroRecords: "Tidak ada data yang cocok dengan pencarian"
            },
            drawCallback: function() {
                // Initialize tooltips after table draw
                $('[data-toggle="tooltip"]').tooltip();
                updateBulkActionButtons();
            }
        });

        // Role filter change event
        $('.role-filter').on('click', function() {
            $('.role-filter').removeClass('active');
            $(this).addClass('active');
            deletedUsersTable.ajax.reload();
        });

        // Checkbox handling for bulk actions
        $(document).on('change', '#checkbox-all', function() {
            let checked = $(this).is(':checked');
            $('.user-checkbox').prop('checked', checked);
            updateBulkActionButtons();
        });

        $(document).on('change', '.user-checkbox', function() {
            updateBulkActionButtons();

            // Update select all checkbox
            let totalCheckboxes = $('.user-checkbox').length;
            let checkedCheckboxes = $('.user-checkbox:checked').length;
            $('#checkbox-all').prop('checked', totalCheckboxes === checkedCheckboxes);
        });

        function updateBulkActionButtons() {
            let checkedCount = $('.user-checkbox:checked').length;
            $('#bulk-restore-btn, #bulk-permanent-delete-btn').prop('disabled', checkedCount === 0);

            if (checkedCount > 0) {
                $('#bulk-restore-btn').text('Pulihkan Terpilih (' + checkedCount + ')');
                $('#bulk-permanent-delete-btn').text('Hapus Permanen Terpilih (' + checkedCount + ')');
            } else {
                $('#bulk-restore-btn').html('<i class="fas fa-undo"></i> Pulihkan Terpilih');
                $('#bulk-permanent-delete-btn').html('<i class="fas fa-trash-alt"></i> Hapus Permanen Terpilih');
            }
        }

        // View archived user details
        $(document).on('click', '.view-archived-user', function() {
            let userId = $(this).data('id');

            // Show loading in modal
            $('#archived-user-details-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
            $('#viewArchivedUserModal').modal('show');

            // Load user details via AJAX
            $.ajax({
                url: '<?= site_url('/admin/deleted-users/get-user-details') ?>',
                type: 'GET',
                data: {
                    id: userId
                },
                success: function(response) {
                    if (response.status && response.data) {
                        let user = response.data;
                        let rolesHtml = '';

                        // Handle roles
                        if (user.roles && user.roles !== 'No Role') {
                            let rolesArray = Array.isArray(user.roles) ? user.roles : user.roles.split(', ');
                            rolesArray.forEach(function(role) {
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
                                rolesHtml += '<span class="badge ' + badgeClass + ' mr-1"><i class="' + icon + '"></i> ' + role + '</span>';
                            });
                        } else {
                            rolesHtml = '<span class="badge badge-secondary">No Role</span>';
                        }

                        let userDetailsHtml = `
                        <div class="alert alert-warning">
                            <i class="fas fa-archive"></i> <strong>Status:</strong> Pengguna ini sedang dalam status arsip.
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-info-circle"></i> Informasi Dasar</h4>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong><i class="fas fa-hashtag"></i> ID:</strong></td>
                                                <td>${user.id}</td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="fas fa-user"></i> Username:</strong></td>
                                                <td><div class="badge badge-light">${user.username}</div></td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="fas fa-id-card"></i> Nama:</strong></td>
                                                <td>${user.nama_lengkap || '-'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="fas fa-envelope"></i> Email:</strong></td>
                                                <td>${user.email || '-'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="fas fa-user-tag"></i> Role:</strong></td>
                                                <td>${rolesHtml}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-history"></i> Informasi Arsip</h4>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong><i class="fas fa-calendar-plus"></i> Dibuat:</strong></td>
                                                <td>${user.created_at ? new Date(user.created_at).toLocaleString('id-ID') : '-'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="fas fa-calendar-times"></i> Diarsipkan:</strong></td>
                                                <td>${user.deleted_at ? new Date(user.deleted_at).toLocaleString('id-ID') : '-'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong><i class="fas fa-sign-in-alt"></i> Login Terakhir:</strong></td>
                                                <td>${user.last_active ? new Date(user.last_active).toLocaleString('id-ID') : '<span class="text-muted">Tidak ada record</span>'}</td>
                                            </tr>
                                        </table>
                                        <div class="mt-3">
                                            <button class="btn btn-success btn-sm restore-user" data-id="${user.id}">
                                                <i class="fas fa-undo"></i> Pulihkan
                                            </button>
                                            <button class="btn btn-danger btn-sm permanent-delete-user" data-id="${user.id}">
                                                <i class="fas fa-trash-alt"></i> Hapus Permanen
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                        $('#archived-user-details-content').html(userDetailsHtml);
                    } else {
                        $('#archived-user-details-content').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat detail pengguna</div>');
                    }
                },
                error: function() {
                    $('#archived-user-details-content').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan saat memuat data</div>');
                }
            });
        });

        // Restore user
        $(document).on('click', '.restore-user', function() {
            let userId = $(this).data('id');
            let button = $(this);

            Swal.fire({
                title: 'Konfirmasi Pemulihan',
                text: 'Apakah Anda yakin ingin memulihkan pengguna ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-undo"></i> Ya, Pulihkan!',
                cancelButtonText: '<i class="fas fa-times"></i> Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button and show loading
                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: '<?= site_url('/admin/deleted-users/restore') ?>',
                        type: 'POST',
                        data: {
                            id: userId
                        },
                        success: function(response) {
                            if (response.status) {
                                showAlert('success', response.message);
                                deletedUsersTable.ajax.reload(null, false);
                                loadStatistics();

                                // Close modal if open
                                $('#viewArchivedUserModal').modal('hide');
                            } else {
                                showAlert('error', response.message);
                            }
                        },
                        error: function() {
                            showAlert('error', 'Terjadi kesalahan saat memulihkan pengguna');
                        },
                        complete: function() {
                            button.prop('disabled', false).html('<i class="fas fa-undo"></i>');
                        }
                    });
                }
            });
        });

        // Permanent delete user
        $(document).on('click', '.permanent-delete-user', function() {
            let userId = $(this).data('id');
            let button = $(this);

            Swal.fire({
                title: 'Konfirmasi Penghapusan Permanen',
                text: 'Apakah Anda yakin ingin menghapus pengguna ini secara permanen? Tindakan ini tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash-alt"></i> Ya, Hapus Permanen!',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                input: 'text',
                inputPlaceholder: 'Ketik "HAPUS PERMANEN" untuk konfirmasi',
                inputValidator: (value) => {
                    if (value !== 'HAPUS PERMANEN') {
                        return 'Ketik "HAPUS PERMANEN" untuk konfirmasi!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button and show loading
                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: '<?= site_url('/admin/deleted-users/permanent-delete') ?>',
                        type: 'POST',
                        data: {
                            id: userId
                        },
                        success: function(response) {
                            if (response.status) {
                                showAlert('success', response.message);
                                deletedUsersTable.ajax.reload(null, false);
                                loadStatistics();

                                // Close modal if open
                                $('#viewArchivedUserModal').modal('hide');
                            } else {
                                showAlert('error', response.message);
                            }
                        },
                        error: function() {
                            showAlert('error', 'Terjadi kesalahan saat menghapus pengguna permanen');
                        },
                        complete: function() {
                            button.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                        }
                    });
                }
            });
        });

        // Bulk restore
        $('#bulk-restore-btn').on('click', function() {
            let selectedIds = $('.user-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                showAlert('error', 'Pilih minimal satu pengguna untuk dipulihkan');
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Pemulihan Massal',
                text: `Apakah Anda yakin ingin memulihkan ${selectedIds.length} pengguna terpilih?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-undo"></i> Ya, Pulihkan Semua!',
                cancelButtonText: '<i class="fas fa-times"></i> Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let button = $(this);
                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memulihkan...');

                    $.ajax({
                        url: '<?= site_url('/admin/deleted-users/bulk-restore') ?>',
                        type: 'POST',
                        data: {
                            ids: selectedIds
                        },
                        success: function(response) {
                            if (response.status) {
                                showAlert('success', response.message);
                                deletedUsersTable.ajax.reload(null, false);
                                loadStatistics();
                                $('#checkbox-all').prop('checked', false);
                            } else {
                                showAlert('error', response.message);
                            }
                        },
                        error: function() {
                            showAlert('error', 'Terjadi kesalahan saat memulihkan pengguna');
                        },
                        complete: function() {
                            button.prop('disabled', false).html('<i class="fas fa-undo"></i> Pulihkan Terpilih');
                        }
                    });
                }
            });
        });

        // Bulk permanent delete
        $('#bulk-permanent-delete-btn').on('click', function() {
            let selectedIds = $('.user-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                showAlert('error', 'Pilih minimal satu pengguna untuk dihapus permanen');
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Penghapusan Permanen Massal',
                text: `Apakah Anda yakin ingin menghapus ${selectedIds.length} pengguna terpilih secara permanen? Tindakan ini tidak dapat dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash-alt"></i> Ya, Hapus Permanen!',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                input: 'text',
                inputPlaceholder: 'Ketik "HAPUS PERMANEN MASSAL" untuk konfirmasi',
                inputValidator: (value) => {
                    if (value !== 'HAPUS PERMANEN MASSAL') {
                        return 'Ketik "HAPUS PERMANEN MASSAL" untuk konfirmasi!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let button = $(this);
                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menghapus...');

                    $.ajax({
                        url: '<?= site_url('/admin/deleted-users/bulk-permanent-delete') ?>',
                        type: 'POST',
                        data: {
                            ids: selectedIds
                        },
                        success: function(response) {
                            if (response.status) {
                                showAlert('success', response.message);
                                deletedUsersTable.ajax.reload(null, false);
                                loadStatistics();
                                $('#checkbox-all').prop('checked', false);
                            } else {
                                showAlert('error', response.message);
                            }
                        },
                        error: function() {
                            showAlert('error', 'Terjadi kesalahan saat menghapus pengguna permanen');
                        },
                        complete: function() {
                            button.prop('disabled', false).html('<i class="fas fa-trash-alt"></i> Hapus Permanen Terpilih');
                        }
                    });
                }
            });
        });

        // Load statistics via AJAX
        function loadStatistics() {
            $.ajax({
                url: '<?= site_url('/admin/deleted-users/get-statistics') ?>',
                type: 'GET',
                success: function(response) {
                    if (response.status && response.data) {
                        let stats = response.data;
                        $('#stat-archived').text(stats.total_archived || 0);
                        $('#stat-restorable').text(stats.restorable || 0);
                        $('#stat-attention').text(stats.needs_attention || 0);
                    } else {
                        $('#stat-archived, #stat-restorable, #stat-attention').text('-');
                    }
                },
                error: function() {
                    $('#stat-archived, #stat-restorable, #stat-attention').text('-');
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