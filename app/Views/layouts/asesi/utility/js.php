<!-- ===== General JS Scripts ===== -->

<script src="<?= base_url("/assets/stisla/node_modules/jquery/dist/jquery.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/popper.js/dist/umd/popper.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/bootstrap/dist/js/bootstrap.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/jquery.nicescroll/dist/jquery.nicescroll.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/moment/min/moment.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/assets/js/stisla.js") ?>"></script>

<!-- ===== JS Libraies ===== -->

<!-- ===== Select ===== -->
<script src="<?= base_url("/assets/stisla/node_modules/selectric/public/jquery.selectric.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/select2/dist/js/select2.full.min.js") ?>"></script>
<!-- ===== End ===== -->

<!-- ===== Sweet Alert2 ===== -->
<script src="<?= base_url("/assets/stisla/node_modules/sweetalert2/dist/sweetalert2.min.js") ?>"></script>
<!-- ===== End ===== -->

<!-- ===== Data Tables ===== -->
<script src="<?= base_url("/assets/stisla/node_modules/datatables/media/js/jquery.dataTables.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/datatables.net-select-bs4/js/select.bootstrap4.min.js") ?>"></script>
<!-- ===== End ===== -->

<!-- ===== Filepond ===== -->
<script src="<?= base_url("/assets/stisla/node_modules/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/filepond-plugin-image-filter/filepond-plugin-image-filter.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/node_modules/filepond/filepond.js") ?>"></script>
<!-- ===== End ===== -->

<!-- ===== Intro.js ===== -->
<script src="<?= base_url("/intro.js/minified/intro.min.js") ?>"></script>
<!-- ===== End ===== -->

<!-- Template JS File -->
<script src="<?= base_url("/assets/stisla/assets/js/scripts.js") ?>"></script>
<script src="<?= base_url("/assets/stisla/assets/js/custom.js") ?>"></script>



<!-- ===== Config Filepond ===== -->
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

    const filePondConfig = {
        credits: null,
        allowImagePreview: false,
        allowMultiple: false,
        allowFileEncode: false,
        required: false,
        acceptedFileTypes: ["application/pdf", "image/png", "image/jpg", "image/jpeg"],
        fileValidateTypeDetectType: (source, type) =>
            new Promise((resolve, reject) => {
                // Do custom type detection here and return with promise
                resolve(type)
            }),
        maxFileSize: '2MB',
        storeAsFile: true,
    };

    FilePond.create(document.querySelector(".filepond-ktp"), filePondConfig);
    FilePond.create(document.querySelector(".filepond-bukti-pendidikan"), filePondConfig);
    FilePond.create(document.querySelector(".filepond-raport"), filePondConfig);
    FilePond.create(document.querySelector(".filepond-sertifikat-pkl"), filePondConfig);

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


    // Filepond: Image Preview
    FilePond.create(document.querySelector(".image-preview-filepond"), {
        credits: null,
        allowImagePreview: true,
        allowImageFilter: false,
        allowImageExifOrientation: false,
        allowImageCrop: true,
        imageCropAspectRatio: '1:1',
        acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg"],
        fileValidateTypeDetectType: (source, type) =>
            new Promise((resolve, reject) => {
                // Do custom type detection here and return with promise
                resolve(type)
            }),
        maxFileSize: '2MB',
        storeAsFile: true,
    })

    // Filepond: Image Filter
    FilePond.create(document.querySelector(".image-filter-filepond"), {
        credits: null,
        allowImagePreview: true,
        allowImageFilter: true,
        allowImageExifOrientation: false,
        allowImageCrop: false,
        imageFilterColorMatrix: [
            0.299, 0.587, 0.114, 0, 0, 0.299, 0.587, 0.114, 0, 0, 0.299, 0.587, 0.114,
            0, 0, 0.0, 0.0, 0.0, 1, 0,
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
<!-- ===== End ===== -->

<!-- ===== Config Data Tables ===== -->
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
<!-- ===== End ===== -->

<!-- ===== Config Sweet Alert2 ===== -->
<script>
    <?php if (session()->getFlashdata('pesan')) : ?>
        Swal.fire({
            title: 'Berhasil!',
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
            confirmButtonText: 'OK'
        });
    <?php endif; ?>
</script>
<!-- ===== End ===== -->




<!-- ===== End ===== -->

<!-- ===== Script Dynamic Dependent ===== -->
<script>
    $(document).ready(function() {
        // Membuat fungsi untuk memperbarui jadwal
        function updateJadwal() {
            var id_skema = $("#id_skema").val();
            $.ajax({
                type: "POST",
                url: "<?= base_url('/api/get-jadwal') ?>",
                data: {
                    id_skema: id_skema
                },
                success: function(response) {
                    $("#id_tanggal").html(response.options_tanggal);
                    $("#id_asesmen").val(response.id_asesmen);
                },
                dataType: 'json'
            });
        }

        // Memanggil fungsi untuk memperbarui jadwal saat halaman dimuat
        updateJadwal();

        // Menggunakan event keyup, change, dan paste pada input id_skema
        $("#id_skema").on('keyup change paste', function(e) {
            updateJadwal();
        });
    });
</script>
<!-- ===== End ===== -->