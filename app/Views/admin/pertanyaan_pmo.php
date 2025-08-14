<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="section-body">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-question-circle"></i> Master Pertanyaan PMO</h4>
                    <div class="card-header-action">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalForm">
                                <i class="fas fa-plus"></i> Tambah Pertanyaan
                            </button>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" onclick="downloadTemplate()">
                                        <i class="fas fa-download"></i> Download Template
                                    </a>
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalImport">
                                        <i class="fas fa-upload"></i> Import Data
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="dataTable">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">Kode Unit</th>
                                    <th width="20%">Nama Unit</th>
                                    <th width="10%">Ref. KUK</th>
                                    <th width="35%">Pertanyaan</th>
                                    <th width="10%">Jenis</th>
                                    <th width="5%">Urutan</th>
                                    <th width="5%">Status</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via DataTables -->
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

<!-- Modal Form -->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Pertanyaan PMO</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formPertanyaan" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="id" id="pertanyaan_id">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Skema <span class="text-danger">*</span></label>
                                <!-- PERBAIKAN: Hapus onchange inline, gunakan event handler -->
                                <select class="form-control select2" name="id_skema" id="id_skema" required>
                                    <option value="">Pilih Skema</option>
                                    <?php if (isset($listSkema) && is_array($listSkema)): ?>
                                        <?php foreach ($listSkema as $skema): ?>
                                            <option value="<?= $skema['id_skema'] ?>"><?= $skema['nama_skema'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unit Kompetensi <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="id_unit" id="id_unit" required>
                                    <option value="">Pilih Skema Terlebih Dahulu</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Referensi KUK</label>
                        <input type="text" class="form-control" name="kuk_reference" id="kuk_reference"
                            placeholder="Contoh: KUK 1.1, KUK 2.3">
                        <small class="text-muted">Opsional - Referensi ke Kriteria Unjuk Kerja tertentu</small>
                    </div>

                    <div class="form-group">
                        <label>Pertanyaan <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="pertanyaan" id="pertanyaan" rows="3"
                            placeholder="Masukkan pertanyaan untuk mendukung observasi" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jenis Jawaban <span class="text-danger">*</span></label>
                                <select class="form-control" name="jenis_jawaban" id="jenis_jawaban" required>
                                    <option value="ya_tidak">Ya / Tidak</option>
                                    <option value="pilihan_ganda">Pilihan Ganda</option>
                                    <option value="essay">Essay</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Urutan</label>
                                <input type="number" class="form-control" name="urutan" id="urutan"
                                    min="0" value="0" placeholder="Urutan pertanyaan">
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="pilihan_jawaban_group" style="display: none;">
                        <label>Pilihan Jawaban</label>
                        <div id="pilihan_container">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control pilihan-input"
                                    placeholder="Masukkan pilihan jawaban">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-success" onclick="addPilihan()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" name="is_active"
                                id="is_active" value="1" checked>
                            <label class="custom-control-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data Pertanyaan PMO</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formImport" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>File Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" name="file_excel"
                            accept=".xls,.xlsx" required>
                        <small class="text-muted">Format: .xls atau .xlsx</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Petunjuk:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data sesuai format yang disediakan</li>
                            <li>Pastikan kode unit sudah ada di database</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // PERBAIKAN: Definisikan function di global scope SEBELUM document ready
    window.loadUnits = function() {
        console.log('loadUnits() called');

        const skemaSelect = document.getElementById('id_skema');
        const unitSelect = document.getElementById('id_unit');

        if (!skemaSelect || !unitSelect) {
            console.error('Element not found');
            return;
        }

        const skemaId = skemaSelect.value;
        console.log('Selected skema ID:', skemaId);

        if (!skemaId) {
            unitSelect.innerHTML = '<option value="">Pilih Skema Terlebih Dahulu</option>';
            unitSelect.disabled = true;
            return;
        }

        // Show loading
        unitSelect.innerHTML = '<option value="">Loading...</option>';
        unitSelect.disabled = true;

        // AJAX call - SAMA PERSIS DENGAN ELEMEN
        $.ajax({
            url: '<?= base_url('api/get-unit') ?>',
            type: 'POST',
            data: {
                id_skema: skemaId,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'html',
            success: function(response) {
                console.log('Units loaded successfully');
                unitSelect.innerHTML = response;
                unitSelect.disabled = false;
            },
            error: function(xhr, status, error) {
                console.error('Error loading units:', error);
                unitSelect.innerHTML = '<option value="">Error loading units</option>';
                unitSelect.disabled = true;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data unit kompetensi',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        });
    };

    // PERBAIKAN: Global functions lainnya juga didefinisikan di window scope
    window.togglePilihanJawaban = function() {
        const jenisJawaban = document.getElementById('jenis_jawaban').value;
        const pilihanGroup = document.getElementById('pilihan_jawaban_group');

        if (jenisJawaban === 'pilihan_ganda') {
            pilihanGroup.style.display = 'block';
        } else {
            pilihanGroup.style.display = 'none';
        }
    };

    window.addPilihan = function() {
        const container = document.getElementById('pilihan_container');
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
        <input type="text" class="form-control pilihan-input" placeholder="Masukkan pilihan jawaban">
        <div class="input-group-append">
            <button type="button" class="btn btn-danger" onclick="removePilihan(this)">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    `;
        container.appendChild(div);
    };

    window.removePilihan = function(button) {
        button.closest('.input-group').remove();
    };

    window.editData = function(id) {
        $.ajax({
            url: `<?= base_url('api/pmo-pertanyaan/getById') ?>/${id}`,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    const data = response.data;

                    // Fill form
                    document.getElementById('pertanyaan_id').value = data.id || '';

                    // Set skema dan trigger loadUnits
                    const skemaSelect = document.getElementById('id_skema');
                    skemaSelect.value = data.id_skema || '';

                    // PERBAIKAN: Trigger loadUnits secara langsung
                    window.loadUnits();

                    // Set unit value setelah delay
                    setTimeout(() => {
                        document.getElementById('id_unit').value = data.id_unit || '';
                    }, 1000);

                    document.getElementById('kuk_reference').value = data.kuk_reference || '';
                    document.getElementById('pertanyaan').value = data.pertanyaan || '';
                    document.getElementById('jenis_jawaban').value = data.jenis_jawaban || 'ya_tidak';
                    document.getElementById('urutan').value = data.urutan || 0;
                    document.getElementById('is_active').checked = data.is_active == 1;

                    // Handle pilihan jawaban
                    if (data.jenis_jawaban === 'pilihan_ganda' && data.pilihan_jawaban) {
                        window.togglePilihanJawaban();
                        const container = document.getElementById('pilihan_container');
                        container.innerHTML = '';

                        data.pilihan_jawaban.forEach((pilihan, index) => {
                            const isFirst = index === 0;
                            const div = document.createElement('div');
                            div.className = 'input-group mb-2';
                            div.innerHTML = `
                            <input type="text" class="form-control pilihan-input" 
                                   value="${pilihan}" placeholder="Masukkan pilihan jawaban">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-${isFirst ? 'success' : 'danger'}" 
                                        onclick="${isFirst ? 'addPilihan()' : 'removePilihan(this)'}">
                                    <i class="fas fa-${isFirst ? 'plus' : 'minus'}"></i>
                                </button>
                            </div>
                        `;
                            container.appendChild(div);
                        });
                    } else {
                        window.togglePilihanJawaban();
                    }

                    $('#modalForm').modal('show');
                }
            },
            error: function(xhr) {
                console.error('Error loading data:', xhr);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error!', 'Gagal memuat data', 'error');
                }
            }
        });
    };

    window.deleteData = function(id) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Pertanyaan?',
                text: 'Data yang dihapus tidak dapat dikembalikan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `<?= base_url('api/pmo-pertanyaan/delete') ?>/${id}`,
                        type: 'DELETE',
                        success: function(response) {
                            if (response.status === 'success') {
                                dataTable.ajax.reload();
                                Swal.fire('Berhasil!', response.message, 'success');
                            }
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            Swal.fire('Error!', response?.message || 'Gagal menghapus data', 'error');
                        }
                    });
                }
            });
        }
    };

    window.downloadTemplate = function() {
        window.location.href = '<?= base_url('master/pertanyaan-pmo/download-template') ?>';
    };

    $(document).ready(function() {
        'use strict';

        let dataTable;

        // Initialize DataTable
        dataTable = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url('api/pmo-pertanyaan/get-data-table') ?>',
                type: 'POST',
                data: function(d) {
                    d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    orderable: false
                },
                {
                    data: 'kode_unit'
                },
                {
                    data: 'nama_unit'
                },
                {
                    data: 'kuk_reference'
                },
                {
                    data: 'pertanyaan',
                    render: function(data) {
                        return data && data.length > 50 ? data.substring(0, 50) + '...' : (data || '');
                    }
                },
                {
                    data: 'jenis_jawaban'
                },
                {
                    data: 'urutan'
                },
                {
                    data: 'is_active',
                    render: function(data) {
                        return data == 1 ?
                            '<span class="badge badge-success">Aktif</span>' :
                            '<span class="badge badge-secondary">Tidak Aktif</span>';
                    }
                },
                {
                    data: 'id',
                    render: function(data) {
                        return `
                        <div class="btn-group">
                            <button class="btn btn-sm btn-warning" onclick="editData(${data})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteData(${data})" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                    },
                    orderable: false
                }
            ],
            order: [
                [6, 'asc'],
                [0, 'asc']
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            }
        });

        // Initialize Select2
        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih...',
            dropdownParent: $('#modalForm')
        });

        // PERBAIKAN: Event handler menggunakan jQuery on()
        $('#id_skema').on('change', function() {
            console.log('Skema changed via jQuery event');
            window.loadUnits();
        });

        // Jenis jawaban change
        $('#jenis_jawaban').on('change', function() {
            window.togglePilihanJawaban();
        });

        // Form submission
        $('#formPertanyaan').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Collect pilihan jawaban
            if (document.getElementById('jenis_jawaban').value === 'pilihan_ganda') {
                const pilihan = [];
                document.querySelectorAll('.pilihan-input').forEach(input => {
                    if (input.value.trim()) {
                        pilihan.push(input.value.trim());
                    }
                });
                formData.append('pilihan_jawaban', JSON.stringify(pilihan));
            }

            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            $.ajax({
                url: '<?= base_url('api/pmo-pertanyaan/save') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('.invalid-feedback').hide();
                    $('.form-control').removeClass('is-invalid');
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#modalForm').modal('hide');
                        dataTable.ajax.reload();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Berhasil!', response.message, 'success');
                        }
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    if (response && response.messages) {
                        Object.keys(response.messages).forEach(field => {
                            $(`[name="${field}"]`).addClass('is-invalid');
                            $(`[name="${field}"]`).siblings('.invalid-feedback').text(response.messages[field]).show();
                        });
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error!', response?.message || 'Terjadi kesalahan', 'error');
                    }
                }
            });
        });

        // Reset modal when hidden
        $('#modalForm').on('hidden.bs.modal', function() {
            document.getElementById('formPertanyaan').reset();
            $('.invalid-feedback').hide();
            $('.form-control').removeClass('is-invalid');
            document.getElementById('pertanyaan_id').value = '';

            // Reset unit dropdown
            const unitSelect = document.getElementById('id_unit');
            unitSelect.innerHTML = '<option value="">Pilih Skema Terlebih Dahulu</option>';
            unitSelect.disabled = true;

            document.getElementById('pilihan_container').innerHTML = `
            <div class="input-group mb-2">
                <input type="text" class="form-control pilihan-input" placeholder="Masukkan pilihan jawaban">
                <div class="input-group-append">
                    <button type="button" class="btn btn-success" onclick="addPilihan()">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        `;
            document.getElementById('pilihan_jawaban_group').style.display = 'none';
            $('.select2').trigger('change');
        });

        // Import form
        $('#formImport').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            $.ajax({
                url: '<?= base_url('master/pertanyaan-pmo/import') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Mengimport...',
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                    }
                },
                success: function(response) {
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }
                    $('#modalImport').modal('hide');

                    if (response.status === 'success') {
                        dataTable.ajax.reload();
                        let message = response.message;
                        if (response.errors && response.errors.length > 0) {
                            message += '\n\nError:\n' + response.errors.slice(0, 5).join('\n');
                            if (response.errors.length > 5) {
                                message += `\n... dan ${response.errors.length - 5} error lainnya`;
                            }
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Import Selesai!', message, 'success');
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }

                    document.getElementById('formImport').reset();
                },
                error: function(xhr) {
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                        Swal.fire('Error!', 'Gagal mengimport data', 'error');
                    }
                }
            });
        });

        console.log('PMO Pertanyaan form initialized successfully');
    });
</script>
<?= $this->endSection() ?>