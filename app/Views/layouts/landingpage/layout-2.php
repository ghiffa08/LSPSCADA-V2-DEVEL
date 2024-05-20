<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title><?= $siteTitle ?> &mdash; <?= env("app_name") ?></title>


    <!-- Favicons -->
    <link rel="shortcut icon" href="<?= base_url('asset_img/logolsp.png') ?>" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Source+Sans+Pro:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="<?= base_url() ?>/HeroBiz/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url() ?>/HeroBiz/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url() ?>/HeroBiz/assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="<?= base_url() ?>/HeroBiz/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="<?= base_url() ?>/HeroBiz/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/selectric/public/selectric.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/select2/dist/css/select2.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/extensions/filepond/filepond.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/datatables.net-select-bs4/css/select.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/assets/css/components.css">

    <!-- Variables CSS Files. Uncomment your preferred color scheme -->
    <!-- <link href="<?= base_url() ?>/HeroBiz/assets/css/variables.css" rel="stylesheet"> -->
    <link href="<?= base_url() ?>/HeroBiz/assets/css/variables-blue.css" rel="stylesheet">
    <!-- <link href="<?= base_url() ?>/HeroBiz/assets/css/variables-green.css" rel="stylesheet"> -->
    <!-- <link href="<?= base_url() ?>/HeroBiz/assets/css/variables-orange.css" rel="stylesheet"> -->
    <!-- <link href="<?= base_url() ?>/HeroBiz/assets/css/variables-purple.css" rel="stylesheet"> -->
    <!-- <link href="<?= base_url() ?>/HeroBiz/assets/css/variables-red.css" rel="stylesheet"> -->
    <!-- <link href="<?= base_url() ?>/HeroBiz/assets/css/variables-pink.css" rel="stylesheet"> -->

    <!-- Template Main CSS File -->
    <link href="<?= base_url() ?>/HeroBiz/assets/css/main.css" rel="stylesheet">

    <!-- =======================================================
  * Template Name: HeroBiz
  * Template URL: https://bootstrapmade.com/herobiz-bootstrap-business-template/
  * Updated: Mar 17 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

    <!-- ======= Header ======= -->
    <?= $this->include("layouts/landingpage/navbar"); ?>
    <!-- End Header -->

    <?= $this->renderSection("hero"); ?>

    <main id="main">

        <?= $this->renderSection("content"); ?>

    </main><!-- End #main -->

    <!-- ======= Footer ======= -->
    <?= $this->include("layouts/landingpage/footer"); ?>
    <!-- End Footer -->

    <a href="#" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="<?= base_url() ?>/HeroBiz/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url() ?>/HeroBiz/assets/vendor/aos/aos.js"></script>
    <script src="<?= base_url() ?>/HeroBiz/assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="<?= base_url() ?>/HeroBiz/assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="<?= base_url() ?>/HeroBiz/assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="<?= base_url() ?>/HeroBiz/assets/vendor/php-email-form/validate.js"></script>


    <!-- JS Libraies -->
    <script src="<?= base_url("/stisla/node_modules/jquery/dist/jquery.min.js") ?>"></script>
    <script src="<?= base_url() ?>/stisla/node_modules/selectric/public/jquery.selectric.min.js"></script>
    <script src="<?= base_url() ?>/stisla/node_modules/select2/dist/js/select2.full.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-filter/filepond-plugin-image-filter.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond/filepond.js"></script>

    <?= $this->renderSection("js-reCAPTCHA"); ?>

    <!-- Data Tables -->
    <script src="<?= base_url() ?>/stisla/node_modules/datatables/media/js/jquery.dataTables.min.js"></script>
    <script src="<?= base_url() ?>/stisla/node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url() ?>/stisla/node_modules/datatables.net-select-bs4/js/select.bootstrap4.min.js"></script>

    <script>
        $(function() {
            $(".my-datatables").each(function() {
                $(this).DataTable({
                    "responsive": true,
                    "lengthChange": false,
                    "autoWidth": true,
                    "searching": false, // menonaktifkan fitur pencarian
                    "paging": false, // menonaktifkan pagination
                    "info": false
                });
            });
        });
    </script>

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
                confirmButtonText: 'OK'
            });
        <?php endif; ?>
    </script>

    <script src="<?= base_url("stisla/assets/js/scripts.js") ?>"></script>
    <script src="<?= base_url("stisla/assets/js/custom.js") ?>"></script>


    <!-- Template Main JS File -->
    <script src="<?= base_url() ?>/HeroBiz/assets/js/main.js"></script>


    <script>
        $(document).ready(function() {
            $("#id_provinsi").change(function(e) {
                var id_provinsi = $("#id_provinsi").val();
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('/kabupaten') ?>",
                    data: {
                        id_provinsi: id_provinsi
                    },
                    success: function(response) {
                        $("#id_kabupaten").html(response);
                    }
                });
            });
            $("#id_kabupaten").change(function(e) {
                var id_kabupaten = $("#id_kabupaten").val();
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('/kecamatan') ?>",
                    data: {
                        id_kabupaten: id_kabupaten
                    },
                    success: function(response) {
                        $("#id_kecamatan").html(response);
                    }
                });
            });
            $("#id_kecamatan").change(function(e) {
                var id_kecamatan = $("#id_kecamatan").val();
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('/desa') ?>",
                    data: {
                        id_kecamatan: id_kecamatan
                    },
                    success: function(response) {
                        $("#id_desa").html(response);
                    }
                });
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

        // Filepond:
        FilePond.create(document.querySelector(".filepond-ktp"), {
            credits: null,
            allowImagePreview: false,
            allowMultiple: false,
            allowFileEncode: false,
            required: false,
            acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg", "application/pdf"],
            fileValidateTypeDetectType: (source, type) =>
                new Promise((resolve, reject) => {
                    // Do custom type detection here and return with promise
                    resolve(type)
                }),
            storeAsFile: true,
        })

        // Filepond: Basic
        FilePond.create(document.querySelector(".filepond-bukti-pendidikan"), {
            credits: null,
            allowImagePreview: false,
            allowMultiple: false,
            allowFileEncode: false,
            required: false,
            acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg", "application/pdf"],
            fileValidateTypeDetectType: (source, type) =>
                new Promise((resolve, reject) => {
                    // Do custom type detection here and return with promise
                    resolve(type)
                }),
            storeAsFile: true,
        })

        // Filepond: Multiple Files
        FilePond.create(document.querySelector(".multiple-files-filepond"), {
            credits: null,
            allowImagePreview: false,
            allowMultiple: true,
            allowFileEncode: false,
            required: false,
            storeAsFile: true,
        })

        // Filepond: With Validation
        FilePond.create(document.querySelector(".with-validation-filepond"), {
            credits: null,
            allowImagePreview: false,
            allowMultiple: true,
            allowFileEncode: false,
            required: true,
            acceptedFileTypes: ["image/png"],
            fileValidateTypeDetectType: (source, type) =>
                new Promise((resolve, reject) => {
                    // Do custom type detection here and return with promise
                    resolve(type)
                }),
            storeAsFile: true,
        })

        // Filepond: ImgBB with server property
        FilePond.create(document.querySelector(".imgbb-filepond"), {
            credits: null,
            allowImagePreview: false,
            server: {
                process: (fieldName, file, metadata, load, error, progress, abort) => {
                    // We ignore the metadata property and only send the file

                    const formData = new FormData()
                    formData.append(fieldName, file, file.name)

                    const request = new XMLHttpRequest()
                    // you can change it by your client api key
                    request.open(
                        "POST",
                        "https://api.imgbb.com/1/upload?key=762894e2014f83c023b233b2f10395e2"
                    )

                    request.upload.onprogress = (e) => {
                        progress(e.lengthComputable, e.loaded, e.total)
                    }

                    request.onload = function() {
                        if (request.status >= 200 && request.status < 300) {
                            load(request.responseText)
                        } else {
                            error("oh no")
                        }
                    }

                    request.onreadystatechange = function() {
                        if (this.readyState == 4) {
                            if (this.status == 200) {
                                let response = JSON.parse(this.responseText)

                                Toastify({
                                    text: "Success uploading to imgbb! see console f12",
                                    duration: 3000,
                                    close: true,
                                    gravity: "bottom",
                                    position: "right",
                                    backgroundColor: "#4fbe87",
                                }).showToast()
                            } else {
                                Toastify({
                                    text: "Failed uploading to imgbb! see console f12",
                                    duration: 3000,
                                    close: true,
                                    gravity: "bottom",
                                    position: "right",
                                    backgroundColor: "#ff0000",
                                }).showToast()
                            }
                        }
                    }

                    request.send(formData)
                },
            },
            storeAsFile: true,
        })

        // Filepond: Image Preview
        FilePond.create(document.querySelector(".image-preview-filepond"), {
            credits: null,
            allowImagePreview: true,
            allowImageFilter: false,
            allowImageExifOrientation: false,
            allowImageCrop: false,
            acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg"],
            fileValidateTypeDetectType: (source, type) =>
                new Promise((resolve, reject) => {
                    // Do custom type detection here and return with promise
                    resolve(type)
                }),
            storeAsFile: true,
        })

        // Filepond: Image Crop
        FilePond.create(document.querySelector(".image-crop-filepond"), {
            credits: null,
            allowImagePreview: true,
            allowImageFilter: false,
            allowImageExifOrientation: false,
            allowImageCrop: true,
            acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg"],
            fileValidateTypeDetectType: (source, type) =>
                new Promise((resolve, reject) => {
                    // Do custom type detection here and return with promise
                    resolve(type)
                }),
            storeAsFile: true,
        })

        // Filepond: Image Exif Orientation
        FilePond.create(document.querySelector(".image-exif-filepond"), {
            credits: null,
            allowImagePreview: true,
            allowImageFilter: false,
            allowImageExifOrientation: true,
            allowImageCrop: false,
            acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg"],
            fileValidateTypeDetectType: (source, type) =>
                new Promise((resolve, reject) => {
                    // Do custom type detection here and return with promise
                    resolve(type)
                }),
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
            storeAsFile: true,
        })

        // Filepond: Image Resize
        FilePond.create(document.querySelector(".image-resize-filepond"), {
            credits: null,
            allowImagePreview: true,
            allowImageFilter: false,
            allowImageExifOrientation: false,
            allowImageCrop: false,
            allowImageResize: true,
            imageResizeTargetWidth: 200,
            imageResizeTargetHeight: 200,
            imageResizeMode: "cover",
            imageResizeUpscale: true,
            acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg"],
            fileValidateTypeDetectType: (source, type) =>
                new Promise((resolve, reject) => {
                    // Do custom type detection here and return with promise
                    resolve(type)
                }),
            storeAsFile: true,
        })
    </script>


    <script>
        function showCard(cardNumber) {
            for (let i = 1; i <= 3; i++) {
                const tab = document.getElementById('tab-' + i);
                tab.style.display = 'none';

                const button = document.getElementById('nextToCard' + i);
                button.classList.remove('active');
            }

            const activeTab = document.getElementById('tab-' + cardNumber);
            activeTab.style.display = 'block';

            const activeButton = document.getElementById('nextToCard' + cardNumber);
            activeButton.classList.add('active');
        }
    </script>

</body>

</html>