<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<h2 class="section-title">Manajemen Elemen Kompetensi</h2>
<p class="section-lead">Kelola semua data elemen kompetensi untuk setiap unit.</p>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Elemen</h4>
                <div class="card-header-action">
                      <div class="btn-group">
                      <button class="btn btn-primary" id="btn-add-elemen">
                        <i class="fas fa-plus"></i> Tambah Elemen
                    </button>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#importExcelModal">
                            <i class="fas fa-upload"></i> Import Excel
                        </button>
                        <a href="<?= site_url('/export-kuk') ?>" class="btn btn-primary">
                            <i class="fas fa-download"></i> Export Excel
                        </a>
                    </div>
                    
                    
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-elemen" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Skema</th>
                                <th>Nama Unit</th>
                                <th>Kode Elemen</th>
                                <th>Nama Elemen</th>
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
<?= $this->include('admin/partials/modals/save-form-elemen') ?>

<!-- Import Excel Modal -->
<?= $this->include('admin/partials/modals/import-excel-elemen') ?>
<!-- End Import Excel Modal -->

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    $(document).ready(function() {
        // =================================================================
        // Konfigurasi & Inisialisasi
        // =================================================================
        const modal = $('#addElemenModal');
        const form = $('#add-elemen-form');
        const baseUrl = '<?= base_url() ?>';
        const skemaSelect = $('#id_skema');
        const unitSelect = $('#id_unit');
        let dataTable;

        // =================================================================
        // Inisialisasi DataTable
        // =================================================================
        dataTable = $('#table-elemen').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "order": [],
            "ajax": {
                "url": `${baseUrl}/master/elemen/get-data-table`,
                "type": "POST"
            },
            "columns": [{
                "data": null,
                "orderable": false
            }, {
                "data": "nama_skema"
            }, {
                "data": "nama_unit"
            }, {
                "data": "kode_elemen"
            }, {
                "data": "nama_elemen"
            }, {
                "data": null,
                "orderable": false
            }],
            "columnDefs": [{
                "targets": 0,
                "render": (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
            }, {
                "targets": -1,
                "render": (data, type, row) => `
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info btn-edit" data-id="${row.id_elemen}" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id_elemen}" title="Hapus"><i class="fas fa-trash"></i></button>
                    </div>`
            }],
        });

        // =================================================================
        // Logika Dropdown Berantai & Form
        // =================================================================

        /**
         * Memuat data Unit berdasarkan ID Skema dan memilih unit tertentu jika disediakan.
         * @param {string} skemaId - ID Skema yang dipilih.
         * @param {string|null} unitToSelect - ID Unit yang ingin dipilih setelah data dimuat.
         */
        function loadUnits(skemaId, unitToSelect = null) {
            if (!skemaId) {
                unitSelect.html('<option value="">Pilih Skema Terlebih Dahulu</option>').prop('disabled', true).trigger('change');
                return;
            }
            unitSelect.html('<option value="">Memuat data...</option>').prop('disabled', true);

            $.post(`${baseUrl}/api/get-unit-json`, { id_skema: skemaId }, function(response) {
                unitSelect.html('<option value="">Pilih Unit</option>').prop('disabled', false);
                if (response.status === 'success' && response.data) {
                    response.data.forEach(unit => {
                        unitSelect.append(new Option(`${unit.kode_unit} - ${unit.nama_unit}`, unit.id_unit));
                    });
                }
                if (unitToSelect) {
                    unitSelect.val(unitToSelect);
                }
                unitSelect.trigger('change');
            }, 'json').fail(() => unitSelect.html('<option value="">Gagal memuat data</option>'));
        }

        /** Membersihkan form ke kondisi awal. */
        function resetForm() {
            form[0].reset();
            form.find('input[name="id_elemen"]').val('');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
            skemaSelect.val('').trigger('change');
            modal.find('.modal-title').text('Tambah Elemen');
        }

        /** Membuka modal edit dan mengisi data. */
        function openEditModal(id) {
            resetForm();
            $.get(`${baseUrl}/master/elemen/getById/${id}`, function(response) {
                if (response.status) {
                    const elemen = response.data;
                    form.find('[name="id_elemen"]').val(elemen.id_elemen);
                    form.find('[name="kode_elemen"]').val(elemen.kode_elemen);
                    form.find('[name="nama_elemen"]').val(elemen.nama_elemen);

                    skemaSelect.val(elemen.id_skema);
                    loadUnits(elemen.id_skema, elemen.id_unit);

                    modal.find('.modal-title').text('Edit Elemen');
                    modal.modal('show');
                }
            }).fail(() => Swal.fire('Error', 'Gagal mengambil data dari server.', 'error'));
        }

        // =================================================================
        // Event Listeners
        // =================================================================

        // Tombol "Tambah Elemen"
        $('#btn-add-elemen').on('click', () => {
            resetForm();
            modal.modal('show');
        });

        // Dropdown Skema berubah
        skemaSelect.on('change', function() {
            loadUnits($(this).val());
        });

        // Submit form untuk Simpan atau Update
        form.on('submit', function(e) {
            e.preventDefault();
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    modal.modal('hide');
                    Swal.fire('Berhasil', response.message, 'success');
                    dataTable.ajax.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 400) {
                        const errors = xhr.responseJSON.messages;
                        $.each(errors, (field, message) => {
                            const element = form.find(`[name="${field}"]`);
                            element.addClass('is-invalid').next('.invalid-feedback').text(message);
                        });
                    } else {
                        Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                    }
                },
                complete: function() {
                    submitBtn.html(originalBtnText).prop('disabled', false);
                }
            });
        });

        // Tombol Edit di dalam tabel
        $('#table-elemen tbody').on('click', '.btn-edit', function() {
            openEditModal($(this).data('id'));
        });

        // Tombol Hapus di dalam tabel
        $('#table-elemen tbody').on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Anda yakin ingin menghapus data ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.get(`${baseUrl}/master/elemen/delete/${id}`, function(response) {
                        Swal.fire('Dihapus!', response.message, 'success');
                        dataTable.ajax.reload();
                    }).fail((xhr) => {
                        const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus data.';
                        Swal.fire('Gagal', errorMsg, 'error');
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>