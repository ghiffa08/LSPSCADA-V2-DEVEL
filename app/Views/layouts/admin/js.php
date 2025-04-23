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

<!-- ===== Filepond ===== -->
<script src="<?= base_url("/stisla/node_modules/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/filepond-plugin-image-filter/filepond-plugin-image-filter.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/filepond/filepond.js") ?>"></script>
<!-- ===== End ===== -->

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

<script>
    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginImageCrop,
        FilePondPluginImageExifOrientation,
        FilePondPluginImageFilter,
        FilePondPluginImageResize,
        FilePondPluginFileValidateSize,
        FilePondPluginFileValidateType,
    )

    // Filepond: Image Preview
    FilePond.create(document.querySelector(".filepond-tanda-tangan"), {
        credits: null,
        allowImagePreview: true,
        allowImageFilter: true,
        allowImageExifOrientation: false,
        allowImageCrop: true,
        imageCropAspectRatio: '1:1',
        imageResizeTargetWidth: 200,
        imageResizeTargetHeight: 200,
        imageFilterColorMatrix: [
            0.2126, 0.7152, 0.0722, 0, 0,
            0.2126, 0.7152, 0.0722, 0, 0,
            0.2126, 0.7152, 0.0722, 0, 0,
            0, 0, 0, 1, 0,
        ],
        acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg"],
        fileValidateTypeDetectType: (source, type) =>
            new Promise((resolve, reject) => {
                // Do custom type detection here and return with promise
                resolve(type)
            }),
        maxFileSize: '2MB',
        storeAsFile: true,
    })
</script>