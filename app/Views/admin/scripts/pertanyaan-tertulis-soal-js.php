<script>
    const soalManager = (function() {
        'use strict';

        const config = {
            selectors: {
                modal: '#addSoalModal',
                form: '#add-soal-form',
                table: '#table-soal',
                jenisSoal: '#jenis_soal',
                containerPG: '#container-pilihan-ganda',
                containerBS: '#container-benar-salah',
                inputsPG: '#pilihan-ganda-inputs',
                addPilihanBtn: '#add-pilihan-btn',
                richEditor: '#soal' // Selector untuk Summernote
            },
            endpoints: {
                dataTable: 'master/pertanyaan-tertulis-soal/get-data-table',
                save: 'master/pertanyaan-tertulis-soal/save',
                getById: 'master/pertanyaan-tertulis-soal/getById/',
                delete: 'master/pertanyaan-tertulis-soal/delete/'
            }
        };

        let dataTable;

        function init() {
            initDataTable();
            initFormHandling();
            initDynamicOptions();
            initRichEditor(); // Inisialisasi Summernote
            $('.select2').select2({
                dropdownParent: $(config.selectors.modal)
            });
        }

        // Fungsi baru untuk inisialisasi Summernote
        function initRichEditor() {
            $(config.selectors.richEditor).summernote({
                height: 250,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });
        }

        function initDataTable() {
            const columns = [{
                data: 'id_soal'
            }, {
                data: 'nama_skema'
            }, {
                data: 'soal',
                render: function(data) {
                    // Tampilkan hanya teks dari HTML untuk preview di tabel
                    return $('<div>').html(data).text().substr(0, 100) + '...';
                }
            }, {
                data: 'jenis_soal'
            }];
            const indexedColumns = DataTableHelper.addIndexColumn(columns);
            const columnsWithActions = DataTableHelper.addActionColumn(indexedColumns, {
                idField: 'id_soal',
                edit: {
                    title: 'Edit'
                },
                delete: {
                    title: 'Hapus'
                }
            });
            dataTable = DataTableHelper.initServerSideTable(config.selectors.table.substring(1), `<?= site_url() ?>/${config.endpoints.dataTable}`, columnsWithActions, {
                order: [
                    [1, 'asc']
                ]
            });
        }

        function initDynamicOptions() {
            $(config.selectors.jenisSoal).on('change', function() {
                toggleOptionContainers($(this).val());
            }).trigger('change');

            $(config.selectors.addPilihanBtn).on('click', () => addPilihanInput());

            $(config.selectors.inputsPG).on('click', '.remove-pilihan-btn', function() {
                $(this).closest('.input-group').remove();
            });
        }

        function toggleOptionContainers(jenis) {
            $(config.selectors.containerPG).toggle(jenis === 'PILIHAN_GANDA');
            $(config.selectors.containerBS).toggle(jenis === 'BENAR_SALAH');
            if (jenis === 'PILIHAN_GANDA' && $(config.selectors.inputsPG).children().length === 0) {
                addPilihanInput();
            }
        }

        function addPilihanInput(value = '', isBenar = false) {
            const index = $(config.selectors.inputsPG).children().length;
            const checked = isBenar ? 'checked' : '';
            const inputHtml = `
                <div class="input-group mb-2">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <input type="radio" name="is_benar" value="${index}" ${checked} required>
                        </div>
                    </div>
                    <input type="text" name="pilihan[]" class="form-control" placeholder="Teks Pilihan Jawaban" value="${value}" required>
                    <div class="input-group-append">
                        <button class="btn btn-danger remove-pilihan-btn" type="button"><i class="fas fa-trash"></i></button>
                    </div>
                </div>`;
            $(config.selectors.inputsPG).append(inputHtml);
        }

        // --- FUNGSI BARU: Helper untuk mengelola tombol submit ---
        function toggleSubmitButton(button, isDisabled, text) {
            button.prop('disabled', isDisabled);
            if (isDisabled) {
                button.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
            } else {
                button.html(text || 'Simpan');
            }
        }

        function initFormHandling() {
            $(config.selectors.modal).on('hidden.bs.modal', () => resetForm());
            $(config.selectors.form).on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const formData = form.serialize();

                // --- PERBAIKAN: Memanggil fungsi helper lokal ---
                toggleSubmitButton(submitBtn, true);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            Swal.fire('Berhasil', response.message, 'success');
                            $(config.selectors.modal).modal('hide');
                            DataTableHelper.reloadTable(dataTable);
                        } else {
                            Swal.fire('Gagal', response.message || 'Terjadi kesalahan', 'error');
                        }
                    },
                    error: () => Swal.fire('Error', 'Gagal terhubung ke server.', 'error'),
                    // --- PERBAIKAN: Memanggil fungsi helper lokal ---
                    complete: () => toggleSubmitButton(submitBtn, false, 'Simpan')
                });
            });

            $(document).on('click', '.btn-edit', function() {
                editSoal($(this).data('id'));
            });

            $(document).on('click', '.btn-delete', function() {
                deleteSoal($(this).data('id'));
            });
        }

        function editSoal(id) {
            resetForm();
            $.get(`<?= site_url() ?>/${config.endpoints.getById}${id}`, function(response) {
                if (response.status) {
                    const data = response.data;
                    $('#id_soal').val(data.id_soal);
                    $('#id_skema').val(data.id_skema).trigger('change');

                    $(config.selectors.richEditor).summernote('code', data.soal);

                    $('#jenis_soal').val(data.jenis_soal).trigger('change');
                    $('#urutan').val(data.urutan);
                    $('#aktif').val(data.aktif).trigger('change');

                    if (data.jenis_soal === 'PILIHAN_GANDA' && data.pilihan) {
                        data.pilihan.forEach(p => addPilihanInput(p.pilihan, p.is_benar === 'Y'));
                    } else if (data.jenis_soal === 'BENAR_SALAH' && data.pilihan) {
                        const jawabanBenar = data.pilihan.find(p => p.is_benar === 'Y');
                        if (jawabanBenar && jawabanBenar.pilihan === 'Benar') {
                            $('#jawaban_benar').prop('checked', true);
                        } else {
                            $('#jawaban_salah').prop('checked', true);
                        }
                    }

                    $(config.selectors.modal).modal('show');
                    $('.modal-title').text('Edit Soal Tertulis');
                } else {
                    Swal.fire('Gagal', response.message, 'error');
                }
            });
        }

        function deleteSoal(id) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Anda yakin ingin menghapus soal ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.get(`<?= site_url() ?>/${config.endpoints.delete}${id}`, function(response) {
                        if (response.status) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            DataTableHelper.reloadTable(dataTable);
                        } else {
                            Swal.fire('Gagal!', response.message, 'error');
                        }
                    });
                }
            });
        }

        function resetForm() {
            $(config.selectors.form)[0].reset();
            $('.select2').val(null).trigger('change');
            $('#id_soal').val('');

            $(config.selectors.richEditor).summernote('code', '');

            $(config.selectors.inputsPG).empty();
            toggleOptionContainers('PILIHAN_GANDA');
            $('.modal-title').text('Tambah Soal Tertulis');
        }

        return {
            init
        };
    })();

    $(document).ready(() => soalManager.init());
</script>