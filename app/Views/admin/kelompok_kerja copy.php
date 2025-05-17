<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Kelompok Kerja</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#kelompokKerjaModal" data-mode="add">
                        <i class="fas fa-plus-circle mr-1"></i> Tambah Kelompok Kerja
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="table-kelompok-kerja">
                        <thead>
                            <tr>
                                <th class="text-center" width="5%">#</th>
                                <th>Nama Kelompok</th>
                                <th>Skema</th>
                                <th class="text-center">Jumlah Unit</th>
                                <th class="text-center" width="15%">Aksi</th>
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



<?= $this->endSection(); ?>

<!-- Modals Section -->
<?= $this->section("modals") ?>
<!-- Add-Modal -->
<!-- Kelompok Kerja Modal - Store and Edit -->
<form action="<?= base_url('/master/kelompok-kerja/save') ?>" method="POST" id="form-kelompok-kerja">
    <div class="modal fade" id="kelompokKerjaModal" data-backdrop="static" tabindex="-1" aria-labelledby="kelompokKerjaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-users-cog mr-2"></i><span id="modalTitle">Kelompok Kerja</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="mode" id="form-mode" value="add">

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-light alert-has-icon">
                                <div class="alert-icon"><i class="far fa-lightbulb"></i></div>
                                <div class="alert-body">
                                    <div class="alert-title">Informasi</div>
                                    <span id="modalInfo">Silakan isi formulir untuk menambahkan kelompok kerja baru.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="kelompok-wrapper">
                        <!-- Kelompok cards will be dynamically inserted here -->
                    </div>

                    <div class="text-center mb-4">
                        <button type="button" class="btn btn-primary btn-lg" id="btn-add-kelompok">
                            <i class="fas fa-plus-circle mr-1"></i> Tambah Kelompok Baru
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Simpan Semua
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Template for Kelompok Card -->
<template id="kelompok-template">
    <div class="card card-primary kelompok-block mb-4" id="kelompok-__INDEX__">
        <div class="card-header">
            <h4><i class="fas fa-users mr-1"></i> Kelompok Kerja #__NUMBER__</h4>
            <div class="card-header-action">
                <button type="button" class="btn btn-danger btn-sm btn-remove-kelompok" data-toggle="tooltip" title="Hapus Kelompok">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <input type="hidden" name="kelompok[__INDEX__][id_kelompok]" class="kelompok-id" value="">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nama Kelompok <span class="text-danger">*</span></label>
                        <input type="text" name="kelompok[__INDEX__][nama_kelompok]" class="form-control kelompok-nama" required placeholder="Masukkan nama kelompok">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Skema <span class="text-danger">*</span></label>
                        <select name="kelompok[__INDEX__][id_skema]" class="form-control skema-select" data-index="__INDEX__" required>
                            <option value="">-- Pilih Skema --</option>
                            <?php foreach ($listSkema as $s): ?>
                                <option value="<?= $s['id_skema'] ?>"><?= $s['nama_skema'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Unit Kompetensi <span class="text-danger">*</span></label>
                <div class="unit-wrapper">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i> Pilih skema terlebih dahulu untuk menampilkan unit kompetensi
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Delete-Modal -->
<?= form_open('/master/kelompok-kerja/delete', ['method' => 'post']) ?>
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Data </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h5>Apakah anda yakin akan menghapus:
                    <span class="text-danger font-weight-bold" id="Nama"></span>?
                </h5>
                <input type="hidden" name="id" id="Id">
            </div>
            <div class="modal-footer bg-whitesmoke br">
                <button type="submit" class="btn btn-danger btn-lg btn-block">Hapus</button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    $(document).ready(function() {
        $('.btn-delete').on('click', function() {
            var id = $(this).data('id');
            var nama = $(this).data('nama');

            $('#Id').val(id);
            $('#Nama').text(nama);
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih opsi...'
        });

        // Custom file input
        $('input[type="file"]').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });


    });
</script>

<script>
    /**
     * Kelompok Kerja Module
     */
    const KelompokKerjaModule = (function() {
        'use strict';

        // Private variables
        let dataTable;
        const tableId = 'table-kelompok-kerja';
        const baseUrl = '<?= base_url(); ?>' || '';

        /**
         * Initialize the module
         */
        function init() {
            initDataTable();
            bindEvents();
        }

        /**
         * Initialize DataTable
         */
        function initDataTable() {
            // Define columns
            const columns = [{
                    data: 'nama_kelompok'
                },
                {
                    data: 'nama_skema'
                },
                {
                    data: 'jumlah_unit',
                    className: 'text-center',
                    render: function(data) {
                        return `<span class="badge badge-primary">${data}</span>`;
                    }
                }
            ];

            // Add index and action columns
            const indexedColumns = DataTableHelper.addIndexColumn(columns);
            const columnsWithActions = DataTableHelper.addActionColumn(indexedColumns, {
                idField: 'id_kelompok',
                edit: {
                    title: 'Edit'
                },
                delete: {
                    title: 'Hapus'
                }
            });

            // Additional options
            const options = {
                order: [
                    [1, 'asc']
                ], // Order by nama_kelompok
                responsive: true
            };

            // Initialize DataTable
            dataTable = DataTableHelper.initServerSideTable(
                tableId,
                `${baseUrl}/master/kelompok-kerja/get-data-table`,
                columnsWithActions,
                options
            );
        }

        /**
         * Bind event handlers
         */
        function bindEvents() {
            // Edit button click handler
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                openModal('edit', id);
            });

            // Delete button click handler
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                confirmDelete(id);
            });

            // Add button click handler
            $('#btn-add').on('click', function() {
                openModal('add');
            });

            // Form submit handler
            $('#form-kelompok-kerja').on('submit', function(e) {
                e.preventDefault();
                saveData();
            });
        }

        /**
         * Open modal for add or edit
         * 
         * @param {string} mode - 'add' or 'edit'
         * @param {number} id - ID of record to edit
         */
        function openModal(mode, id = null) {
            // Reset form
            $('#form-kelompok-kerja')[0].reset();

            // Set modal title and action
            if (mode === 'add') {
                $('#modal-title').text('Tambah Kelompok Kerja');
                $('#form-kelompok-kerja').data('id', '');
            } else {
                $('#modal-title').text('Edit Kelompok Kerja');
                $('#form-kelompok-kerja').data('id', id);

                // Fetch data for editing
                $.ajax({
                    url: `${baseUrl}/master/kelompok-kerja/${id}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            $('#nama_kelompok').val(response.data.nama_kelompok);
                            $('#id_skema').val(response.data.id_skema);
                        } else {
                            showAlert('error', response.message);
                        }
                    },
                    error: function() {
                        showAlert('error', 'Terjadi kesalahan saat mengambil data');
                    }
                });
            }

            // Show modal
            $('#modal-kelompok-kerja').modal('show');
        }

        /**
         * Save data from form
         */
        function saveData() {
            const id = $('#form-kelompok-kerja').data('id');
            const url = id ? `${baseUrl}/master/kelompok-kerja/${id}` : `${baseUrl}/master/kelompok-kerja`;
            const method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                type: method,
                data: $('#form-kelompok-kerja').serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        $('#modal-kelompok-kerja').modal('hide');
                        showAlert('success', response.message);
                        DataTableHelper.reloadTable(dataTable);
                    } else {
                        showAlert('error', response.message);
                    }
                },
                error: function(xhr) {
                    showAlert('error', 'Terjadi kesalahan saat menyimpan data');
                }
            });
        }

        /**
         * Confirm and delete record
         * 
         * @param {number} id - ID of record to delete
         */
        function confirmDelete(id) {
            Swal.fire({
                title: 'Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteData(id);
                }
            });
        }

        /**
         * Delete record
         * 
         * @param {number} id - ID of record to delete
         */
        function deleteData(id) {
            $.ajax({
                url: `${baseUrl}/master/kelompok-kerja/${id}`,
                type: 'DELETE',
                data: {
                    [csrfToken]: csrfHash
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        showAlert('success', response.message);
                        DataTableHelper.reloadTable(dataTable);
                    } else {
                        showAlert('error', response.message);
                    }
                },
                error: function() {
                    showAlert('error', 'Terjadi kesalahan saat menghapus data');
                }
            });
        }

        /**
         * Show alert message
         * 
         * @param {string} type - Alert type (success, error, warning, info)
         * @param {string} message - Alert message
         */
        function showAlert(type, message) {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Berhasil' : 'Error',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }

        // Public API
        return {
            init,
            openModal,
            reloadTable: function() {
                DataTableHelper.reloadTable(dataTable);
            }
        };
    })();

    // Initialize on document ready
    $(document).ready(function() {
        KelompokKerjaModule.init();
    });
</script>

<script>
    /**
     * Kelompok Kerja Form Management
     * 
     * This module handles all functionality related to the Kelompok Kerja form including:
     * - Dynamic form element creation and removal
     * - Ajax loading of unit options based on selected schema
     * - Form validation and submission
     * - Support for both add and edit modes
     */
    const KelompokKerjaModule = (function() {
        'use strict';

        // Configuration
        const config = {
            baseUrl: '<?= base_url() ?>',
            selectors: {
                modal: '#kelompokKerjaModal',
                form: '#form-kelompok-kerja',
                wrapper: '#kelompok-wrapper',
                addKelompokBtn: '#btn-add-kelompok',
                template: '#kelompok-template',
                modeInput: '#form-mode',
                modalTitle: '#modalTitle',
                modalInfo: '#modalInfo'
            },
            endpoints: {
                getUnit: '<?= site_url('api/get-unit') ?>',
                getKelompokDetail: '<?= site_url('master/kelompok-kerja/detail') ?>',
                save: '<?= site_url('master/kelompok-kerja/save') ?>'
            },
            messages: {
                addInfo: 'Silakan isi formulir untuk menambahkan kelompok kerja baru.',
                editInfo: 'Silakan edit informasi kelompok kerja yang ada.',
                confirmClose: 'Perubahan yang belum disimpan akan hilang',
                confirmRemove: 'Kelompok ini akan dihapus dari formulir',
                noUnits: 'Tidak ada unit kompetensi tersedia untuk skema ini',
                loadError: 'Gagal memuat unit kompetensi. Silakan coba lagi.',
                validateMin: 'Tambahkan minimal satu kelompok kerja'
            },
            titles: {
                add: 'Tambah Kelompok Kerja',
                edit: 'Edit Kelompok Kerja'
            }
        };

        // State management
        const state = {
            kelompokIndex: 0,
            unitCache: {}, // Cache unit options by skema ID to reduce server requests
            formChanged: false,
            mode: 'add', // 'add' or 'edit'
            editId: null
        };

        /**
         * Initialize the module
         */
        function init() {
            attachEventHandlers();
            initializeTooltips();
        }

        /**
         * Attach all necessary event handlers
         */
        function attachEventHandlers() {
            // Form change detection
            $(config.selectors.form).on('input change', 'input, select', function() {
                state.formChanged = true;
            });

            // Modal close confirmation
            $('.close, button[data-dismiss="modal"]').on('click', handleModalClose);

            // Modal show event - reset form
            $(config.selectors.modal).on('show.bs.modal', function(e) {
                // Only run if triggered by a button click (has relatedTarget)
                if (e.relatedTarget) {
                    const mode = $(e.relatedTarget).data('mode') || 'add';
                    const id = $(e.relatedTarget).data('id');
                    resetForm();
                    setupFormMode(mode, id);
                }
            });

            // Add new kelompok
            $(config.selectors.addKelompokBtn).on('click', addNewKelompok);

            // Dynamic event handlers for elements that may be added/removed
            $(document).on('click', '.btn-add-unit', addNewUnit);
            $(document).on('click', '.btn-remove-unit', removeUnit);
            $(document).on('click', '.btn-remove-kelompok', removeKelompok);
            $(document).on('change', '.skema-select', loadUnitsForSkema);

            // Form validation and submission
            $(config.selectors.form).on('submit', validateAndSubmitForm);

            // Input validation
            $(document).on('input', '[required]', clearValidationError);
        }

        /**
         * Initialize tooltips for all elements with data-toggle="tooltip"
         */
        function initializeTooltips() {
            $('[data-toggle="tooltip"]').tooltip();
        }

        /**
         * Setup form based on mode (add or edit)
         */
        function setupFormMode(mode, id) {
            $(config.selectors.modeInput).val(mode);
            state.mode = mode;

            if (mode === 'edit') {
                $(config.selectors.modalTitle).text(config.titles.edit);
                $(config.selectors.modalInfo).text(config.messages.editInfo);
                state.editId = id;
                loadKelompokData(id);
            } else {
                $(config.selectors.modalTitle).text(config.titles.add);
                $(config.selectors.modalInfo).text(config.messages.addInfo);
                state.editId = null;
                addNewKelompok(); // Add initial empty kelompok
            }
        }

        /**
         * Reset the form to its initial state
         */
        function resetForm() {
            $(config.selectors.wrapper).empty();
            state.kelompokIndex = 0;
            state.formChanged = false;
            // Tidak perlu reset unitCache di sini agar cache tetap tersedia
        }

        /**
         * Load kelompok data for editing
         */
        function loadKelompokData(id) {
            // Show loading
            $(config.selectors.wrapper).html(`
            <div class="text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3">Memuat data kelompok kerja...</p>
            </div>
        `);

            // Fetch kelompok data
            $.ajax({
                url: `${config.endpoints.getKelompokDetail}/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        renderKelompokForEdit(response.data);
                    } else {
                        showErrorMessage('Gagal memuat data kelompok');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error loading kelompok data:", error);
                    showErrorMessage('Gagal memuat data. Silakan coba lagi.');
                }
            });
        }

        /**
         * Render kelompok data for editing
         */
        function renderKelompokForEdit(kelompokData) {
            $(config.selectors.wrapper).empty();

            if (!kelompokData || kelompokData.length === 0) {
                addNewKelompok();
                return;
            }

            // Reset cache untuk skema yang akan diedit
            kelompokData.forEach(kelompok => {
                if (kelompok.id_skema) {
                    delete state.unitCache[kelompok.id_skema];
                }
            });

            // Add each kelompok
            kelompokData.forEach(function(kelompok) {
                const index = state.kelompokIndex;
                const newKelompok = createKelompokElement(index, index + 1);
                $(config.selectors.wrapper).append(newKelompok);

                // Set values
                $(`#kelompok-${index} .kelompok-id`).val(kelompok.id_kelompok);
                $(`#kelompok-${index} .kelompok-nama`).val(kelompok.nama_kelompok);
                $(`#kelompok-${index} .skema-select`).val(kelompok.id_skema);

                // Simpan unit yang harus dipilih
                if (kelompok.units && kelompok.units.length > 0) {
                    $(`#kelompok-${index}`).data('units', kelompok.units);
                }

                // Load units dan set nilai setelah load selesai
                const selectElement = $(`#kelompok-${index} .skema-select`);
                if (kelompok.id_skema) {
                    loadUnitsForSkema.call(selectElement[0], true); // Parameter true untuk mode edit
                }

                state.kelompokIndex++;
            });

            initializeTooltips();
            state.formChanged = false;
        }

        /**
         * Set selected units after they've been loaded
         */
        function setSelectedUnits(kelompokElement, units) {
            const unitWrapper = $(kelompokElement).find('.unit-wrapper');
            const firstSelect = unitWrapper.find('select').first();

            // If no select yet, wait for units to load
            if (firstSelect.length === 0) {
                return;
            }

            // Remove all except first unit select
            unitWrapper.find('.input-group').not(':first').remove();

            // Set first unit
            if (units.length > 0) {
                firstSelect.val(units[0].id_unit);
            }

            // Add additional units
            for (let i = 1; i < units.length; i++) {
                const btn = unitWrapper.find('.btn-add-unit').first();
                btn.trigger('click');

                // Find the newest select and set its value
                const selects = unitWrapper.find('select');
                $(selects[i]).val(units[i].id_unit);
            }
        }

        /**
         * Confirm before closing modal if form has changes
         */
        function handleModalClose(e) {
            if (state.formChanged) {
                e.preventDefault();
                Swal.fire({
                    title: 'Yakin ingin menutup?',
                    text: config.messages.confirmClose,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, tutup',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        state.formChanged = false;
                        $(config.selectors.modal).modal('hide');
                    }
                });
            }
        }

        /**
         * Create a new kelompok element from template
         */
        function createKelompokElement(index, number) {
            const template = $(config.selectors.template).html();
            return template
                .replace(/__INDEX__/g, index)
                .replace(/__NUMBER__/g, number);
        }

        /**
         * Add a new kelompok (work group) to the form
         */
        function addNewKelompok() {
            const index = state.kelompokIndex;
            const newKelompok = createKelompokElement(index, index + 1);

            $(config.selectors.wrapper).append(newKelompok);

            // Initialize tooltips for new elements
            initializeTooltips();

            // Increment index for next kelompok
            state.kelompokIndex++;
            state.formChanged = true;

            // Scroll to newly added kelompok
            scrollToElement($(`#kelompok-${index}`));
        }

        /**
         * Load unit options based on selected skema
         */
        function loadUnitsForSkema(isEditMode = false) {
            const skemaId = $(this).val();
            if (!skemaId) return;

            const index = $(this).data('index');
            const kelompokElement = $(`#kelompok-${index}`);
            const wrapper = kelompokElement.find('.unit-wrapper');

            // Show loading indicator
            wrapper.html(`
        <div class="text-center p-3">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2">Memuat unit kompetensi...</p>
        </div>
    `);

            // Check cache first
            if (state.unitCache[skemaId]) {
                renderUnitOptions(wrapper, index, state.unitCache[skemaId]);
                if (isEditMode || kelompokElement.data('units')) {
                    setSelectedUnitsAfterDelay(kelompokElement);
                }
                return;
            }

            // Fetch units from server
            $.ajax({
                type: "POST",
                url: config.endpoints.getUnit,
                data: {
                    id_skema: skemaId,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        state.unitCache[skemaId] = response.data;
                        renderUnitOptions(wrapper, index, response.data);

                        if (isEditMode || kelompokElement.data('units')) {
                            setSelectedUnitsAfterDelay(kelompokElement);
                        }
                    } else {
                        showErrorMessage(wrapper, 'Gagal memuat unit kompetensi');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error loading units:", error);
                    showErrorMessage(wrapper, config.messages.loadError);
                }
            });
        }

        /**
         * Render unit options in the wrapper
         */
        function renderUnitOptions(wrapper, index, options) {
            if (!options || options.length === 0) {
                wrapper.html(`
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i> ${config.messages.noUnits}
                </div>
            `);
                return;
            }

            let optionsHtml = '<option value="">-- Pilih Unit --</option>';
            options.forEach(function(unit) {
                optionsHtml += `<option value="${unit.id_unit}">${unit.kode_unit} - ${unit.nama_unit}</option>`;
            });

            wrapper.html(`
            <div class="input-group mb-2">
                <select name="kelompok[${index}][unit_ids][]" class="form-control" required>
                    ${optionsHtml}
                </select>
                <div class="input-group-append">
                    <button type="button" class="btn btn-success btn-add-unit" data-toggle="tooltip" title="Tambah Unit">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        `);

            // Initialize tooltips for new elements
            initializeTooltips();
            state.formChanged = true;
        }

        function setSelectedUnitsAfterDelay(kelompokElement) {
            // Beri sedikit delay untuk memastikan select options sudah ter-render
            setTimeout(() => {
                const units = kelompokElement.data('units');
                if (units) {
                    setSelectedUnits(kelompokElement, units);
                    kelompokElement.removeData('units');
                }
            }, 100);
        }

        /**
         * Show error message in the wrapper
         */
        function showErrorMessage(wrapper, message) {
            wrapper.html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle mr-1"></i> ${message}
            </div>
        `);
        }

        /**
         * Add a new unit selection field
         */
        function addNewUnit() {
            const wrapper = $(this).closest('.unit-wrapper');
            const kelompokId = $(this).closest('.kelompok-block').attr('id');
            const index = kelompokId.split('-')[1];

            // Get the select element to clone its options
            const firstSelect = wrapper.find('select').first();
            const optionsHtml = firstSelect.html();

            const newUnit = `
            <div class="input-group mb-2">
                <select name="kelompok[${index}][unit_ids][]" class="form-control" required>
                    ${optionsHtml}
                </select>
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger btn-remove-unit" data-toggle="tooltip" title="Hapus Unit">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;

            wrapper.append(newUnit);
            initializeTooltips();
            state.formChanged = true;
        }

        /**
         * Remove a unit selection field
         */
        function removeUnit() {
            const unitGroup = $(this).closest('.input-group');

            unitGroup.fadeOut(300, function() {
                $(this).remove();
                state.formChanged = true;
            });
        }

        /**
         * Remove a kelompok (work group)
         */
        function removeKelompok() {
            const kelompokBlock = $(this).closest('.kelompok-block');

            Swal.fire({
                title: 'Hapus kelompok?',
                text: config.messages.confirmRemove,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    kelompokBlock.slideUp(400, function() {
                        $(this).remove();
                        updateKelompokNumbers();
                        state.formChanged = true;
                    });
                }
            });
        }

        /**
         * Update kelompok numbers after removal
         */
        function updateKelompokNumbers() {
            $('.kelompok-block').each(function(idx) {
                $(this).find('.card-header h4').html('<i class="fas fa-users mr-1"></i> Kelompok Kerja #' + (idx + 1));
            });
        }

        /**
         * Validate and submit the form
         */
        function validateAndSubmitForm(e) {
            e.preventDefault();
            let isValid = true;

            // Check if at least one kelompok exists
            if ($('.kelompok-block').length === 0) {
                Swal.fire({
                    title: 'Validasi Gagal!',
                    text: config.messages.validateMin,
                    icon: 'error'
                });
                isValid = false;
                return false;
            }

            // Check required fields
            $(config.selectors.form).find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');

                    // Scroll to first invalid input if not done yet
                    if (isValid) {
                        scrollToElement($(this));
                        isValid = false;
                    }
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                return false;
            }

            // Show loading indicator
            Swal.fire({
                title: 'Menyimpan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit form via AJAX
            $.ajax({
                type: 'POST',
                url: config.endpoints.save,
                data: $(config.selectors.form).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            state.formChanged = false;
                            $(config.selectors.modal).modal('hide');

                            // Reload the page or table
                            if (typeof dataTable !== 'undefined') {
                                dataTable.ajax.reload();
                            } else {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: response.message || 'Terjadi kesalahan saat menyimpan data',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error submitting form:", error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
                        icon: 'error'
                    });
                }
            });

            return false;
        }

        /**
         * Clear validation error on input change
         */
        function clearValidationError() {
            if ($(this).val()) {
                $(this).removeClass('is-invalid');
            }
        }

        /**
         * Scroll to an element
         */
        function scrollToElement(element) {
            $('html, body').animate({
                scrollTop: element.offset().top - 100
            }, 500);
        }

        /**
         * Public API
         */
        return {
            init: init,
            openModal: function(mode, id = null) {
                // First reset the form
                resetForm();

                // Then setup the mode
                setupFormMode(mode, id);

                // Open the modal
                $(config.selectors.modal).modal('show');
            }
        };
    })();

    // Initialize the module when document is ready
    $(document).ready(function() {
        KelompokKerjaModule.init();
    });
</script>

<?= $this->endSection() ?>