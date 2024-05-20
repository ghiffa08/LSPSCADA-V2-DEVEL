<?= $this->renderSection('js') ?>



<!-- JS Libraies -->
<script src="<?= base_url(); ?>/stisla/node_modules/summernote/dist/summernote-bs4.js"></script>
<!--SweetAlert JS-->
<script src="<?= base_url(); ?>/stisla/node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
<!-- Tambahkan script SweetAlert2 di bagian bawah halaman -->
<script>
    <?php if (session()->getFlashdata('pesan')) : ?>
        Swal.fire({
            title: 'Sukses!',
            text: '<?= session()->getFlashdata('pesan') ?>',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('warning')) : ?>
        Swal.fire({
            title: 'Peringatan!',
            text: '<?= session()->getFlashdata('warning') ?>',
            icon: 'warning',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: true,
            confirmButtonText: 'OK',
        }).then((result) => {
            if (result.isConfirmed) {
                var modalId = '<?= session()->getFlashdata('modal_id') ?>';
                $('#' + modalId).modal('show');
            }
        });
    <?php endif; ?>
</script>
<!-- <script src="/path/to/jquery.dm-uploader.min.js"></script> -->
<script src="<?= base_url(); ?>/stisla/node_modules/chocolat/dist/js/jquery.chocolat.min.js"></script>
<script src="<?= base_url(); ?>/stisla/node_modules/selectric/public/jquery.selectric.min.js"></script>
<script src="<?= base_url(); ?>/stisla/node_modules/select2/dist/js/select2.full.min.js"></script>

<script src="<?= base_url(); ?>/stisla/node_modules/jquery_upload_preview/assets/js/jquery.uploadPreview.min.js"></script>
<script>
    $.uploadPreview({
        input_field: "#image-upload", // Default: .image-upload
        preview_box: "#image-preview", // Default: .image-preview
        label_field: "#image-label", // Default: .image-label
        label_default: "Pilih File", // Default: Choose File
        label_selected: "Change File", // Default: Change File
        no_label: false, // Default: false
        success_callback: null // Default: null
    });
    $.uploadPreview({
        input_field: "#ktp-upload", // Default: .image-upload
        preview_box: "#ktp-preview", // Default: .image-preview
        label_field: "#ktp-label", // Default: .image-label
        label_default: "Pilih File", // Default: Choose File
        label_selected: "Change File", // Default: Change File
        no_label: false, // Default: false
        success_callback: null // Default: null
    });
    $.uploadPreview({
        input_field: "#kartu-pelajar-upload", // Default: .image-upload
        preview_box: "#kartu-pelajar-preview", // Default: .image-preview
        label_field: "#kartu-pelajar-label", // Default: .image-label
        label_default: "Pilih File", // Default: Choose File
        label_selected: "Change File", // Default: Change File
        no_label: false, // Default: false
        success_callback: null // Default: null
    });
</script>

<!-- Data Tables -->
<script src="<?= base_url() ?>/stisla/node_modules/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/stisla/node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/stisla/node_modules/datatables.net-select-bs4/js/select.bootstrap4.min.js"></script>



<script>
    $(function() {
        $("#table-2").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": true,
            // "scrollX": true
        });
    });
</script>