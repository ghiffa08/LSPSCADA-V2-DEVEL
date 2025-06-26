<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Asesor Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .debug-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .debug-output {
            background-color: #f1f1f1;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            max-height: 250px;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1>Debug Asesor Registration</h1>

        <div class="debug-section">
            <h3>Form Data</h3>
            <pre id="form-data-output" class="debug-output">Waiting for form submission...</pre>
        </div>

        <div class="debug-section">
            <h3>AJAX Response</h3>
            <pre id="ajax-response" class="debug-output">Waiting for AJAX response...</pre>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Create Asesor</h2>
            </div>
            <div class="card-body">
                <form id="addAsesorForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nomor_registrasi" class="form-label">Nomor Registrasi (optional)</label>
                            <input type="text" class="form-control" id="nomor_registrasi" name="nomor_registrasi" placeholder="Contoh: ASR-2024-001">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="skema-sertifikasi" class="form-label">Skema Sertifikasi</label>
                        <select class="form-control select2" id="skema-sertifikasi" name="skema_ids[]" multiple required>
                            <!-- Options will be loaded via AJAX -->
                        </select>
                        <small class="form-text text-muted">Pilih bidang kompetensi/skema sertifikasi asesor (bisa pilih lebih dari satu)</small>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load skema sertifikasi options
            $.ajax({
                url: '<?= site_url('/api/user-management/get-active-skemas') ?>',
                type: 'GET',
                success: function(response) {
                    if (response.status && response.data) {
                        let select = $('#skema-sertifikasi');
                        select.empty();

                        // Format options: ID as value
                        response.data.forEach(function(skema) {
                            select.append('<option value="' + skema.id_skema + '">' + skema.kode_skema + ' - ' + skema.nama_skema + '</option>');
                        });

                        // Initialize Select2
                        select.select2({
                            placeholder: "Pilih Skema Sertifikasi",
                            allowClear: true
                        });

                        $('#ajax-response').text("Loaded " + response.data.length + " skemas:\n" + JSON.stringify(response.data, null, 2));
                    } else {
                        $('#skema-sertifikasi').html('<option value="">Gagal memuat skema sertifikasi</option>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#skema-sertifikasi').html('<option value="">Gagal memuat skema sertifikasi</option>');
                    $('#ajax-response').text("Error loading skemas: " + error + "\n" + xhr.responseText);
                }
            });

            // Handle form submission
            $('#addAsesorForm').on('submit', function(e) {
                e.preventDefault();

                // Validate password confirmation
                let password = $('#password').val();
                let passwordConfirm = $('#password_confirm').val();

                if (password !== passwordConfirm) {
                    alert('Password dan konfirmasi password tidak cocok');
                    return;
                }

                if (password.length < 8) {
                    alert('Password minimal 8 karakter');
                    return;
                }

                // Get selected skema values
                let skemaIds = $('#skema-sertifikasi').val();

                // Validasi skema
                if (!skemaIds || skemaIds.length === 0) {
                    alert('Pilih minimal satu skema sertifikasi');
                    return;
                }

                // Get form data sebagai objek untuk dimanipulasi
                let formData = {};
                $(this).serializeArray().forEach(function(item) {
                    formData[item.name] = item.value;
                });

                // Secara eksplisit menambahkan skema_ids ke formData
                formData['skema_ids'] = skemaIds;

                // Show form data in debug output
                $('#form-data-output').text(JSON.stringify(formData, null, 2));

                // Submit button control
                let submitBtn = $(this).find('button[type="submit"]');
                let originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Membuat...');

                $.ajax({
                    url: '<?= site_url('/api/user-management/create-asesor-user') ?>',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#ajax-response').text(JSON.stringify(response, null, 2));

                        if (response.status) {
                            alert('Success! Asesor created successfully.');
                            $('#addAsesorForm')[0].reset();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error response:', xhr.responseText);
                        $('#ajax-response').text('Error: ' + error + '\n' + xhr.responseText);

                        try {
                            let errorResponse = JSON.parse(xhr.responseText);
                            alert('Error: ' + (errorResponse.message || 'Terjadi kesalahan saat membuat asesor'));
                        } catch (e) {
                            alert('Error: Terjadi kesalahan saat membuat asesor');
                        }
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
</body>

</html>