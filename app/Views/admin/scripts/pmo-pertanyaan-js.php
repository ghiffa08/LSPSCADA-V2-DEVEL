<script>
    /**
     * PMO Pertanyaan Management Module
     */
    const pmoPertanyaanManager = (function() {
        'use strict';

        const config = {
            baseUrl: '<?= base_url(); ?>' || '',
            selectors: {
                modal: '#addPmoPertanyaanModal',
                form: '#add-pmo-pertanyaan-form',
                table: '#table-pmo-pertanyaan',
                importForm: '#import-excel-form',
                importModal: '#importExcelModal',
                importBtn: '#import-btn',
                select2Elements: '.select2',
                modalTitle: '.modal-title',
                skemaSelect: '#id_skema',
                unitSelect: '#id_unit',
                elemenSelect: '#id_elemen',
                kukSelect: '#id_kuk',
                jenisJawabanSelect: '#jenis_jawaban',
                pilihanGandaContainer: '#pilihan-ganda-container',
                pilihanGandaInputs: '#pilihan-ganda-inputs',
                addPilihanBtn: '#add-pilihan-btn'
            },
            endpoints: {
                save: 'master/pmo-pertanyaan/save',
                getById: 'master/pmo-pertanyaan/getById/',
                delete: 'master/pmo-pertanyaan/delete/',
                dataTable: 'master/pmo-pertanyaan/get-data-table',
                getUnits: 'api/get-unit',
                getElements: 'api/get-elemen',
                getKuk: 'api/get-kuk'
            },
            formFields: {
                idPertanyaan: '[name="id_pertanyaan"]',
                skema: '[name="id_skema"]',
                unit: '[name="id_unit"]',
                elemen: '[name="id_elemen"]',
                kuk: '[name="id_kuk"]',
                pertanyaan: '[name="pertanyaan"]',
                jenisJawaban: '[name="jenis_jawaban"]',
                urutan: '[name="urutan"]',
                aktif: '[name="aktif"]'
            }
        };

        let dataTable;
        let editData = null;

        function init() {
            initDataTable();
            initFormHandling();
            initSelect2();
            initFileInputs();
            initImportHandler();
            initDependentDropdowns();
            bindEvents();
            initPilihanGanda();
        }

        function initPilihanGanda() {
            // Tampilkan/sembunyikan kontainer berdasarkan pilihan jenis jawaban
            $(config.selectors.jenisJawabanSelect).on('change', function() {
                if ($(this).val() === 'PILIHAN_GANDA') {
                    $(config.selectors.pilihanGandaContainer).slideDown();
                    // Jika belum ada input, tambahkan satu secara default
                    if ($(config.selectors.pilihanGandaInputs).children().length === 0) {
                        addPilihanInput();
                    }
                } else {
                    $(config.selectors.pilihanGandaContainer).slideUp();
                }
            });

            // Tambah opsi baru
            $(config.selectors.addPilihanBtn).on('click', function() {
                addPilihanInput();
            });

            // Hapus opsi
            $(config.selectors.pilihanGandaInputs).on('click', '.remove-pilihan-btn', function() {
                $(this).closest('.input-group').remove();
            });
        }

        function addPilihanInput(value = '') {
            const inputHtml = `
                <div class="input-group mb-2">
                    <input type="text" name="pilihan[]" class="form-control" placeholder="Teks Pilihan Jawaban" value="${value}" required>
                    <div class="input-group-append">
                        <button class="btn btn-danger remove-pilihan-btn" type="button"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `;
            $(config.selectors.pilihanGandaInputs).append(inputHtml);
        }

        function initDataTable() {
            const columns = [{
                data: 'id_pertanyaan'
            }, {
                data: 'nama_skema'
            }, {
                data: 'pertanyaan'
            }, {
                data: 'jenis_jawaban'
            }, {
                data: 'aktif',
                render: data => data === 'Y' ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Tidak Aktif</span>'
            }];
            const indexedColumns = DataTableHelper.addIndexColumn(columns);
            const columnsWithActions = DataTableHelper.addActionColumn(indexedColumns, {
                idField: 'id_pertanyaan',
                edit: {
                    title: 'Edit'
                },
                delete: {
                    title: 'Hapus'
                }
            });
            dataTable = DataTableHelper.initServerSideTable(config.selectors.table.substring(1), `${config.baseUrl}/${config.endpoints.dataTable}`, columnsWithActions, {
                order: [
                    [1, 'asc']
                ],
                responsive: true
            });
        }

        function initDependentDropdowns() {
            $(config.selectors.skemaSelect).on('change', function() {
                loadUnits($(this).val());
            });
            $(config.selectors.unitSelect).on('change', function() {
                loadElements($(this).val());
            });
            $(config.selectors.elemenSelect).on('change', function() {
                loadKuk($(this).val());
            });
        }

        function loadUnits(skemaId, callback) {
            const unitSelect = $(config.selectors.unitSelect);
            if (!skemaId) {
                unitSelect.empty().append('<option value="">Pilih Unit</option>').prop('disabled', true).trigger('change');
                return;
            }
            unitSelect.prop('disabled', true).empty().append('<option value="">Loading...</option>').trigger('change');
            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getUnits}`,
                type: 'POST',
                data: {
                    id_skema: skemaId
                },
                dataType: 'html',
                success: function(response) {
                    unitSelect.html(response).prop('disabled', false);
                    if (editData && editData.id_unit) {
                        unitSelect.val(editData.id_unit).trigger('change');
                    } else {
                        unitSelect.trigger('change');
                    }
                    if (typeof callback === 'function') callback();
                },
                error: function() {
                    showNotification('error', 'Gagal', 'Gagal memuat data unit');
                }
            });
        }

        function loadElements(unitId, callback) {
            const elemenSelect = $(config.selectors.elemenSelect);
            if (!unitId) {
                elemenSelect.empty().append('<option value="">Pilih Elemen</option>').prop('disabled', true).trigger('change');
                return;
            }
            elemenSelect.prop('disabled', true).empty().append('<option value="">Loading...</option>').trigger('change');
            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getElements}`,
                type: 'POST',
                data: {
                    id_unit: unitId
                },
                dataType: 'html',
                success: function(response) {
                    elemenSelect.html(response).prop('disabled', false);
                    if (editData && editData.id_elemen) {
                        elemenSelect.val(editData.id_elemen).trigger('change');
                    } else {
                        elemenSelect.trigger('change');
                    }
                    if (typeof callback === 'function') callback();
                },
                error: function() {
                    showNotification('error', 'Gagal', 'Gagal memuat data elemen');
                }
            });
        }

        function loadKuk(elemenId, callback) {
            const kukSelect = $(config.selectors.kukSelect);
            if (!elemenId) {
                kukSelect.empty().append('<option value="">Pilih KUK</option>').prop('disabled', true).trigger('change');
                return;
            }
            kukSelect.prop('disabled', true).empty().append('<option value="">Loading...</option>').trigger('change');
            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getKuk}`,
                type: 'POST',
                data: {
                    id_elemen: elemenId
                },
                dataType: 'html',
                success: function(response) {
                    kukSelect.html(response).prop('disabled', false);
                    if (editData && editData.id_kuk) {
                        kukSelect.val(editData.id_kuk).trigger('change');
                        editData = null;
                    } else {
                        kukSelect.trigger('change');
                    }
                    if (typeof callback === 'function') callback();
                },
                error: function() {
                    showNotification('error', 'Gagal', 'Gagal memuat data KUK');
                }
            });
        }

        function initFormHandling() {
            $(config.selectors.modal).on('hidden.bs.modal', function() {
                resetForm(config.selectors.form);
                editData = null;
            });
            $(config.selectors.form).on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const formData = form.serialize();
                const url = form.attr('action');
                toggleSubmitButton(submitBtn, true);
                form.find('.is-invalid').removeClass('is-invalid');
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            showNotification('success', 'Berhasil', response.message || 'Data berhasil disimpan!');
                            $(config.selectors.modal).modal('hide');
                            reloadTable();
                        } else {
                            if (response.errors) {
                                showValidationErrors(form, response.errors);
                            } else {
                                showNotification('error', 'Gagal', response.message || 'Terjadi kesalahan');
                            }
                        }
                    },
                    error: function(xhr) {
                        showNotification('error', 'Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan server');
                    },
                    complete: function() {
                        toggleSubmitButton(submitBtn, false);
                    }
                });
            });
        }

        function initSelect2() {
            $(config.selectors.select2Elements).each(function() {
                $(this).select2({
                    width: '100%',
                    placeholder: $(this).data('placeholder') || "Pilih",
                    dropdownParent: $(config.selectors.modal)
                });
            });
        }

        function initFileInputs() {
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        }

        function initImportHandler() {
            $(config.selectors.importForm).on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const formData = new FormData(this);
                const importBtn = $(config.selectors.importBtn);
                toggleSubmitButton(importBtn, true, '<span class="spinner-border spinner-border-sm"></span> Importing...');
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        handleImportResponse(response);
                    },
                    error: function(xhr, status, error) {
                        showNotification('error', 'Kesalahan Sistem', 'Terjadi kesalahan: ' + error);
                    },
                    complete: function() {
                        toggleSubmitButton(importBtn, false, 'Import Data');
                    }
                });
            });
        }

        function handleImportResponse(response) {
            if (response.status === 'success') {
                showNotification('success', 'Impor Berhasil', response.message, () => {
                    $(config.selectors.importModal).modal('hide');
                    reloadTable();
                });
            } else {
                showNotification('error', 'Impor Gagal', response.message);
            }
        }

        function bindEvents() {
            $(document).on('click', '.btn-edit', function() {
                editPertanyaan($(this).data('id'));
            });
            $(document).on('click', '.btn-delete', function() {
                deletePertanyaan($(this).data('id'));
            });
        }

        function editPertanyaan(id) {
            resetForm(config.selectors.form);
            $(config.selectors.modal).modal('show');
            $(config.selectors.modal).find(config.selectors.modalTitle).text('Edit Pertanyaan PMO');
            const form = $(config.selectors.form);
            form.find('input, textarea, select').prop('disabled', true);
            $.ajax({
                url: `${config.baseUrl}/${config.endpoints.getById}${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    form.find('input, textarea, select').prop('disabled', false);
                    if (response.status) {
                        const data = response.data;
                        editData = data;
                        form.append(`<input type="hidden" name="id_pertanyaan" value="${data.id_pertanyaan}">`);
                        form.find(config.formFields.pertanyaan).val(data.pertanyaan);
                        form.find(config.formFields.jenisJawaban).val(data.jenis_jawaban).trigger('change');
                        form.find(config.formFields.urutan).val(data.urutan);
                        form.find(config.formFields.aktif).val(data.aktif).trigger('change');
                        form.find(config.formFields.skema).val(data.id_skema).trigger('change');

                        // Jika ada pilihan jawaban, tampilkan
                        if (data.jenis_jawaban === 'PILIHAN_GANDA' && data.pilihan) {
                            data.pilihan.forEach(p => addPilihanInput(p.pilihan));
                        }
                    } else {
                        showNotification('error', 'Gagal', response.message || 'Gagal mengambil data');
                        $(config.selectors.modal).modal('hide');
                    }
                },
                error: function() {
                    form.find('input, textarea, select').prop('disabled', false);
                    showNotification('error', 'Gagal', 'Gagal mengambil data');
                    $(config.selectors.modal).modal('hide');
                }
            });
        }

        function deletePertanyaan(id) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Anda yakin ingin menghapus data ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `${config.baseUrl}/${config.endpoints.delete}${id}`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                showNotification('success', 'Berhasil', response.message);
                                reloadTable();
                            } else {
                                showNotification('error', 'Gagal', response.message);
                            }
                        },
                        error: function() {
                            showNotification('error', 'Gagal', 'Terjadi kesalahan pada server');
                        }
                    });
                }
            });
        }

        function resetForm(formSelector) {
            const form = $(formSelector);
            form[0].reset();
            form.find(config.selectors.select2Elements).val('').trigger('change');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
            form.find(config.formFields.idPertanyaan).remove();
            form.attr('action', `${config.baseUrl}/${config.endpoints.save}`);
            $(config.selectors.modal).find(config.selectors.modalTitle).text('Tambah Pertanyaan PMO');
            $(config.selectors.unitSelect).empty().append('<option value="">Pilih Unit</option>').prop('disabled', true);
            $(config.selectors.elemenSelect).empty().append('<option value="">Pilih Elemen</option>').prop('disabled', true);
            $(config.selectors.kukSelect).empty().append('<option value="">Pilih KUK</option>').prop('disabled', true);
            // Reset pilihan ganda
            $(config.selectors.pilihanGandaInputs).empty();
            $(config.selectors.pilihanGandaContainer).hide();
        }

        function showValidationErrors(form, errors) {
            $.each(errors, function(field, message) {
                const input = form.find(`[name="${field}"]`);
                if (input.length) {
                    input.addClass('is-invalid');
                    input.next('.invalid-feedback').text(message);
                }
            });
        }

        function toggleSubmitButton(button, isDisabled, text) {
            button.prop('disabled', isDisabled);
            if (text) {
                button.html(text);
            } else {
                button.html(isDisabled ? '<i class="fas fa-spinner fa-spin"></i> Menyimpan...' : 'Simpan');
            }
        }

        function showNotification(icon, title, text, callback) {
            const options = {
                icon: icon,
                title: title,
                text: text
            };
            if (icon === 'success') {
                options.timer = 2000;
                options.showConfirmButton = false;
            }
            Swal.fire(options).then(() => {
                if (typeof callback === 'function') callback();
            });
        }

        function reloadTable() {
            if (dataTable) {
                DataTableHelper.reloadTable(dataTable);
            }
        }

        return {
            init
        };
    })();

    $(document).ready(function() {
        pmoPertanyaanManager.init();
    });
</script>