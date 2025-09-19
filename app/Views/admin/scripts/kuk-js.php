<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section('js') ?>
<script>
    $(document).ready(function() {
        // Konfigurasi
        const modal = $('#addKukModal');
        const form = $('#add-kuk-form');
        const baseUrl = '<?= base_url() ?>';
        const skemaSelect = $('#id_skema');
        const unitSelect = $('#id_unit');
        const elemenSelect = $('#id_elemen');
        let dataTable;

        // Inisialisasi DataTable
        dataTable = $('#table-kuk').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "order": [],
            "ajax": { "url": `${baseUrl}/master/kuk/get-data-table`, "type": "POST" },
            "columns": [
                { "data": null, "orderable": false },
                { "data": "nama_skema" },
                { "data": "nama_unit" },
                { "data": "nama_elemen" },
                { "data": "kode_kuk" },
                { "data": "pertanyaan" },
                { "data": null, "orderable": false }
            ],
            "columnDefs": [
                {"targets": 0, "render": (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1},
                {"targets": -1, "render": (data, type, row) => `
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info btn-edit" data-id="${row.id_kuk}" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id_kuk}" title="Hapus"><i class="fas fa-trash"></i></button>
                    </div>`}
            ],
        });

        // Logika Dropdown Berantai
        function loadUnits(skemaId, callback) {
            unitSelect.html('<option value="">Memuat...</option>').prop('disabled', true);
            elemenSelect.html('<option value="">Pilih Unit Dahulu</option>').prop('disabled', true).trigger('change');
            if (!skemaId) {
                unitSelect.html('<option value="">Pilih Skema Dahulu</option>').trigger('change');
                return;
            }
            $.post(`${baseUrl}/api/unit/getUnitJSON`, { id_skema: skemaId }, (response) => {
                unitSelect.html('<option value="">Pilih Unit</option>').prop('disabled', false);
                if (response.status === 'success') {
                    response.data.forEach(unit => unitSelect.append(new Option(`${unit.kode_unit} - ${unit.nama_unit}`, unit.id_unit)));
                }
                if (typeof callback === 'function') callback();
            }, 'json').fail(() => unitSelect.html('<option value="">Gagal</option>'));
        }

        function loadElemen(unitId, callback) {
            elemenSelect.html('<option value="">Memuat...</option>').prop('disabled', true);
            if (!unitId) {
                elemenSelect.html('<option value="">Pilih Unit Dahulu</option>').trigger('change');
                return;
            }
            $.post(`${baseUrl}/api/elemen/getElemenJSON`, { id_unit: unitId }, (response) => {
                elemenSelect.html('<option value="">Pilih Elemen</option>').prop('disabled', false);
                if (response.status === 'success') {
                    response.data.forEach(el => elemenSelect.append(new Option(`${el.kode_elemen} - ${el.nama_elemen}`, el.id_elemen)));
                }
                if (typeof callback === 'function') callback();
            }, 'json').fail(() => elemenSelect.html('<option value="">Gagal</option>'));
        }

        skemaSelect.on('change', function() { loadUnits($(this).val()); });
        unitSelect.on('change', function() { loadElemen($(this).val()); });

        // Logika Form & Modal
        function resetForm() {
            form[0].reset();
            form.find('input[name="id_kuk"]').val('');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
            skemaSelect.val('').trigger('change');
            modal.find('.modal-title').text('Tambah KUK');
        }

        function openEditModal(id) {
            resetForm();
            $.get(`${baseUrl}/master/kuk/getById/${id}`, function(response) {
                if (response.status) {
                    const kuk = response.data;
                    form.find('[name="id_kuk"]').val(kuk.id_kuk);
                    form.find('[name="kode_kuk"]').val(kuk.kode_kuk);
                    form.find('[name="pertanyaan"]').val(kuk.pertanyaan);
                    
                    skemaSelect.val(kuk.id_skema);
                    loadUnits(kuk.id_skema, () => {
                        unitSelect.val(kuk.id_unit);
                        loadElemen(kuk.id_unit, () => {
                            elemenSelect.val(kuk.id_elemen).trigger('change');
                        });
                    });

                    modal.find('.modal-title').text('Edit KUK');
                    modal.modal('show');
                }
            }).fail(() => Swal.fire('Error', 'Gagal mengambil data dari server.', 'error'));
        }

        // Event Listeners
        $('#btn-add-kuk').on('click', () => { resetForm(); modal.modal('show'); });

        form.on('submit', function(e) {
            e.preventDefault();
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
            form.find('.is-invalid').removeClass('is-invalid').text('');
            $.ajax({
                url: form.attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
                success: (response) => {
                    modal.modal('hide');
                    Swal.fire('Berhasil', response.message, 'success');
                    dataTable.ajax.reload();
                },
                error: (xhr) => {
                    if (xhr.status === 400) {
                        const errors = xhr.responseJSON.messages;
                        $.each(errors, (field, message) => form.find(`[name="${field}"]`).addClass('is-invalid').next('.invalid-feedback').text(message));
                    } else {
                        Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                    }
                },
                complete: () => submitBtn.html(originalBtnText).prop('disabled', false)
            });
        });

        $('#table-kuk tbody').on('click', '.btn-edit', function() { openEditModal($(this).data('id')); });
        $('#table-kuk tbody').on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Konfirmasi Hapus', text: "Anda yakin ingin menghapus data ini?", icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#d33', cancelButtonText: 'Batal', confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.get(`${baseUrl}/master/kuk/delete/${id}`, (response) => {
                        Swal.fire('Dihapus!', response.message, 'success');
                        dataTable.ajax.reload();
                    }).fail((xhr) => Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal menghapus data.', 'error'));
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>