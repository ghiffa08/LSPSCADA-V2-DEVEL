<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<h2 class="section-title">Manajemen Komponen Umpan Balik</h2>
<p class="section-lead">Kelola semua pernyataan komponen umpan balik asesi pada halaman ini.</p>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Komponen Umpan Balik</h4>
                <div class="card-header-action">
                    <div class="btn-group">
                        <button class="btn btn-primary" id="btnAddKomponen">
                            <i class="fas fa-plus"></i> Tambah Komponen
                        </button>

                        <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#importExcelModal">
                            <i class="fas fa-upload"></i> Import Excel
                        </button>

                        <a href="<?= base_url('download-template-komponen-feedback') ?>" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    Anda dapat mengubah urutan komponen dengan drag and drop pada ikon <i class="fas fa-grip-vertical"></i> di sebelah kiri tabel.
                </div>
                
                <!-- Loading spinner -->
                <div id="loading-spinner" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data komponen...</p>
                </div>
                
                <div class="table-responsive" id="table-container" style="display: none;">
                    <table class="table table-striped" id="sortable-table">
                        <thead>
                            <tr>
                                <th width="5%"
                                    class="text-center">
                                    <i class="fas fa-sort"></i>
                                </th>
                                <th width="5%">No</th>
                                <th width="10%">ID</th>
                                <th width="60%">Pernyataan</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="komponen-body">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<!-- Modals Section -->
<?= $this->section("modals") ?>

<?= $this->include('admin/partials/modals/save-form-komponen') ?>

<?= $this->include('admin/partials/modals/import-excel-komponen') ?>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    $(document).ready(function() {
        // Global variables
        let isProcessing = false;
        
        // Load data
        function loadKomponenData() {
            // Show loading spinner
            $('#loading-spinner').show();
            $('#table-container').hide();
            
            $.ajax({
                url: '<?= base_url('admin/komponen-feedback/getAll') ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        renderKomponenTable(response.data);
                        initializeSortable();
                        
                        // Hide spinner and show table only after everything is ready
                        $('#loading-spinner').hide();
                        $('#table-container').fadeIn('fast');
                    } else {
                        // Hide spinner and show error message
                        $('#loading-spinner').hide();
                        showErrorMessage('Gagal memuat data komponen');
                    }
                },
                error: function(xhr, status, error) {
                    // Hide spinner and show error message
                    $('#loading-spinner').hide();
                    showErrorMessage('Terjadi kesalahan: ' + error);
                }
            });
        }
        
        // Render komponen table
        function renderKomponenTable(data) {
            const tbody = $('#komponen-body');
            tbody.empty();
            
            if (data.length === 0) {
                tbody.append(`
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data komponen</td>
                    </tr>
                `);
                return;
            }
            
            data.forEach((item, index) => {
                tbody.append(`
                    <tr data-id="${item.id_komponen}">
                        <td>
                            <div class="sort-handler">
                                <i class="fas fa-grip-vertical"></i>
                            </div>
                        </td>
                        <td>${index + 1}</td>
                        <td>${item.id_komponen}</td>
                        <td>${item.pernyataan}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info btn-edit" data-id="${item.id_komponen}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-delete" data-id="${item.id_komponen}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        }
        
        // Initialize jQuery UI Sortable
        function initializeSortable() {
            $("#sortable-table tbody").sortable({
                handle: '.sort-handler',
                helper: function(e, ui) {
                    // Fix cell width during drag
                    ui.children().each(function() {
                        $(this).width($(this).width());
                    });
                    return ui;
                },
                update: function(event, ui) {
                    if (isProcessing) return;
                    
                    const reorderedItems = [];
                    $('#sortable-table tbody tr').each(function(index) {
                        const id = $(this).data('id');
                        reorderedItems.push({
                            id: id,
                            position: index
                        });
                    });
                    
                    updateOrderOnServer(reorderedItems);
                }
            }).disableSelection();
        }
        
        // Update order on server
        function updateOrderOnServer(items) {
            isProcessing = true;
            
            // Show loading indicator
            Swal.fire({
                title: 'Menyimpan perubahan urutan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '<?= base_url('admin/komponen-feedback/updateOrder') ?>',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ items: items }),
                success: function(response) {
                    isProcessing = false;
                    Swal.close();
                    
                    if (response.status) {
                        showSuccessMessage('Urutan berhasil diperbarui');
                        loadKomponenData(); // Reload to reflect the new order
                    } else {
                        showErrorMessage(response.message || 'Gagal memperbarui urutan');
                    }
                },
                error: function(xhr, status, error) {
                    isProcessing = false;
                    Swal.close();
                    showErrorMessage('Terjadi kesalahan saat memperbarui urutan');
                }
            });
        }
        
        // Function to manually reset the form
        function resetKomponenForm() {
            const form = $('#add-komponen-form');
            
            // Reset form
            form[0].reset();
            
            // Clear the id_komponen field (for add mode)
            $('[name="id_komponen"]').val('');
            
            // Reset any validation styling
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
            
            // Reset select2 if used
            if (form.find('.select2').length) {
                form.find('.select2').val('').trigger('change');
            }
        }
        
        // Function to load the next order number
        function loadNextOrderNumber() {
            $.ajax({
                url: '<?= base_url('admin/komponen-feedback/getMaxOrder') ?>',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        $('#urutan').val(response.nextOrder);
                    }
                },
                error: function(xhr) {
                    console.error("Failed to get next order number", xhr);
                }
            });
        }
        
        // Handle the "Add Component" button click
        $('#btnAddKomponen').on('click', function() {
            resetKomponenForm();
            $('#komponenModalLabel').text('Tambah Komponen Umpan Balik');
            loadNextOrderNumber();
            $('#saveKomponenModal').modal('show');
        });
        
        // Handle form submission
        $('#add-komponen-form').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('[type="submit"]');
            const originalBtnText = submitBtn.html();
            
            // Disable submit button and show loading
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').attr('disabled', true);
            
            // Submit form via AJAX
            $.ajax({
                url: '<?= base_url('admin/komponen-feedback/save') ?>',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        showSuccessMessage(response.message || 'Komponen berhasil disimpan');
                        
                        // Close modal and reload data
                        $('#saveKomponenModal').modal('hide');
                        loadKomponenData();
                    } else {
                        // Show error message
                        showErrorMessage(response.message || 'Terjadi kesalahan saat menyimpan data');
                    }
                },
                error: function(xhr, status, error) {
                    showErrorMessage('Terjadi kesalahan saat menyimpan data');
                },
                complete: function() {
                    // Re-enable submit button
                    submitBtn.html(originalBtnText).attr('disabled', false);
                }
            });
        });
        
        // Handle edit button click
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            
            resetKomponenForm();
            $('#komponenModalLabel').text('Edit Komponen Umpan Balik');
            
            // Show loading indicator in modal
            $('#saveKomponenModal').modal('show');
            const modalBody = $('#saveKomponenModal .modal-body');
            const originalContent = modalBody.html();
            
            modalBody.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data komponen...</p>
                </div>
            `);
            
            // Get data from server
            $.ajax({
                url: '<?= base_url('admin/komponen-feedback/getById') ?>/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Restore original content
                    modalBody.html(originalContent);
                    
                    if (response.status && response.data) {
                        const data = response.data;
                        
                        // Fill form fields
                        $('[name="id_komponen"]').val(data.id_komponen);
                        $('[name="pernyataan"]').val(data.pernyataan);
                        $('[name="urutan"]').val(data.urutan);
                    } else {
                        $('#saveKomponenModal').modal('hide');
                        showErrorMessage('Gagal memuat data komponen');
                    }
                },
                error: function() {
                    // Restore original content
                    modalBody.html(originalContent);
                    $('#saveKomponenModal').modal('hide');
                    showErrorMessage('Terjadi kesalahan saat memuat data');
                }
            });
        });
        
        // Handle delete button click
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            
            Swal.fire({
                title: 'Hapus Komponen Umpan Balik?',
                text: 'Data yang dihapus tidak dapat dikembalikan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading spinner
                    Swal.fire({
                        title: 'Menghapus komponen...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    $.ajax({
                        url: '<?= base_url('admin/komponen-feedback/delete') ?>/' + id,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            Swal.close();
                            
                            if (response.status) {
                                showSuccessMessage(response.message || 'Komponen berhasil dihapus');
                                loadKomponenData();
                            } else {
                                showErrorMessage(response.message || 'Gagal menghapus komponen');
                            }
                        },
                        error: function() {
                            Swal.close();
                            showErrorMessage('Terjadi kesalahan saat menghapus data');
                        }
                    });
                }
            });
        });
        
        // Helper functions for notifications
        function showSuccessMessage(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: message,
                timer: 1500,
                showConfirmButton: false
            });
        }
        
        function showErrorMessage(message) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: message
            });
        }
        
        // Add custom CSS for sortable styling
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .sort-handler {
                    cursor: move;
                    text-align: center;
                    vertical-align: middle;
                }
                .sort-handler i {
                    color: #6777ef;
                }
                .ui-sortable-helper {
                    display: table;
                    background-color: #f8f9fa;
                    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
                }
                .ui-sortable-placeholder {
                    visibility: visible !important;
                    background-color: #f1f1f1;
                    height: 55px;
                }
                #loading-spinner {
                    min-height: 200px;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                }
            `)
            .appendTo('head');
        
        // File upload handling
        window.updateFileLabel = function(input) {
            const fileName = input.files[0]?.name || 'Pilih file Excel';
            $(input).next('.custom-file-label').html(fileName);
        };
        
        // Initialize
        loadKomponenData();
    });
</script>
<?= $this->endSection() ?>
