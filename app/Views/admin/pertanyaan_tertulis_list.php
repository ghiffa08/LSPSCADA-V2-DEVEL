<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Tabel Manajemen Ujian Tertulis</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" id="batch-download-btn">
                        <i class="fas fa-file-archive mr-1"></i> Download Terpilih (ZIP)
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-ujian" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 5%; text-align: center;">
                                    <input type="checkbox" id="select-all-ujian">
                                </th>
                                <th style="width: 5%">No</th>
                                <th>Nama Asesi</th>
                                <th>Skema Sertifikasi</th>
                                <th>Tanggal Ujian</th>
                                <th style="width: 15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan dimuat oleh DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<!-- Memuat script khusus untuk halaman ini -->
<script>
    const ujianListManager = (function() {
        'use strict';

        const config = {
            baseUrl: '<?= base_url(); ?>',
            selectors: {
                table: '#table-ujian',
                selectAllCheckbox: '#select-all-ujian',
                rowCheckbox: '.ujian-checkbox',
                batchDownloadBtn: '#batch-download-btn'
            },
            endpoints: {
                dataTable: 'api/pertanyaan-tertulis/get-data-table',
                delete: 'api/pertanyaan-tertulis/delete/',
                view: 'admin/pertanyaan-tertulis/cbt/',
                batchDownload: 'admin/pertanyaan-tertulis/batch-download' // Endpoint baru
            }
        };

        let dataTable;

        function init() {
            initDataTable();
            bindEvents();
        }

        function initDataTable() {
            const columns = [{
                    data: 'id_ujian',
                    orderable: false,
                    render: data => `<input type="checkbox" class="ujian-checkbox" value="${data}">`
                },
                {
                    data: 'nama_asesi'
                },
                {
                    data: 'nama_skema'
                },
                {
                    data: 'tanggal_ujian',
                    render: data => data ? new Date(data).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    }) : '-'
                }
            ];

            const indexedColumns = DataTableHelper.addIndexColumn(columns, 1);
            const columnsWithActions = DataTableHelper.addActionColumn(indexedColumns, {
                idField: 'id_ujian',
                buttons: [{
                    title: 'Lihat/Kerjakan',
                    icon: 'fas fa-edit',
                    className: 'btn-info',
                    url: data => `${config.baseUrl}/${config.endpoints.view}${data.id_apl1}`
                }],
                delete: {
                    title: 'Hapus'
                }
            });

            dataTable = DataTableHelper.initServerSideTable(
                config.selectors.table.substring(1),
                `${config.baseUrl}/${config.endpoints.dataTable}`,
                columnsWithActions, {
                    order: [
                        [4, 'desc']
                    ]
                }
            );
        }

        function bindEvents() {
            $(document).on('click', '.btn-delete', function() {
                deleteUjian($(this).data('id'));
            });
            $(config.selectors.selectAllCheckbox).on('change', function() {
                $(config.selectors.rowCheckbox, dataTable.rows({
                    page: 'current'
                }).nodes()).prop('checked', $(this).is(':checked'));
            });
            $(config.selectors.batchDownloadBtn).on('click', handleBatchDownload);
        }

        function handleBatchDownload() {
            const selectedIds = $(config.selectors.rowCheckbox + ':checked').map((_, el) => $(el).val()).get();

            if (selectedIds.length === 0) {
                Swal.fire('Tidak Ada Data Terpilih', 'Pilih setidaknya satu ujian untuk diunduh.', 'warning');
                return;
            }

            const form = $('<form>', {
                    method: 'POST',
                    action: `${config.baseUrl}/${config.endpoints.batchDownload}`
                })
                .append($('<input>', {
                    type: 'hidden',
                    name: '<?= csrf_token() ?>',
                    value: '<?= csrf_hash() ?>'
                }));
            selectedIds.forEach(id => form.append($('<input>', {
                type: 'hidden',
                name: 'ujian_ids[]',
                value: id
            })));
            $('body').append(form).submit().remove();
        }

        function deleteUjian(id) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Anda yakin ingin menghapus sesi ujian ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `${config.baseUrl}/${config.endpoints.delete}${id}`,
                        type: 'GET',
                        dataType: 'json',
                        success: response => {
                            if (response.success || response.status) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                dataTable.ajax.reload();
                            } else {
                                Swal.fire('Gagal!', response.message || 'Gagal menghapus data.', 'error');
                            }
                        },
                        error: () => Swal.fire('Error!', 'Terjadi kesalahan pada server.', 'error')
                    });
                }
            });
        }

        return {
            init
        };
    })();

    $(document).ready(() => ujianListManager.init());
</script>

<?= $this->endSection() ?>