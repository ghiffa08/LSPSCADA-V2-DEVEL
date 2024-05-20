<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title><?= $siteTitle ?> &mdash; <?= env("app_name") ?></title>
    <link rel="shortcut icon" href="<?= base_url('asset_img/logolsp.png') ?>" type="image/x-icon">

    <!-- General CSS -->
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/bootstrap-icons/font/bootstrap-icons.min.css">
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/datatables.net-select-bs4/css/select.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/selectric/public/selectric.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/select2/dist/css/select2.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/chocolat/dist/css/chocolat.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/summernote/dist/summernote-bs4.css">

    <link rel="stylesheet" href="<?= base_url() ?>/bs-stepper/css/bs-stepper.min.css">

    <!-- Template CSS -->
    <link rel="stylesheet" href="<?= base_url("stisla/assets/css/style.css") ?>">
    <link rel="stylesheet" href="<?= base_url("stisla/assets/css/components.css") ?>">
    <!-- Start GA -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-94034622-3"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-94034622-3');
    </script>
    <!-- /END GA -->
</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">

            <!-- navbar -->
            <?= $this->include("layouts/admin/navbar"); ?>
            <!-- end -->

            <!-- sidebar -->
            <?= $this->include("layouts/admin/sidebar"); ?>
            <!-- end -->

            <!-- Main Content -->
            <div class="main-content">
                <section class="section">
                    <div class="section-header">
                        <h1><?= $siteTitle ?></h1>
                    </div>

                    <div class="section-body">

                        <!-- Main Section -->
                        <?= $this->renderSection("content"); ?>
                        <!-- end -->

                    </div>
                </section>
            </div>
            <footer class="main-footer">
                <div class="footer-left">
                    Copyright &copy; 2024 <div class="bullet"></div> LSP SMKN 2 Kuningan.
                </div>
                <div class="footer-right">

                </div>
            </footer>
        </div>
    </div>

    <!-- Modal -->
    <?= $this->renderSection('modals') ?>


    <!-- General JS Scripts -->
    <script src="<?= base_url("/stisla/node_modules/jquery/dist/jquery.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/node_modules/popper.js/dist/umd/popper.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/node_modules/bootstrap/dist/js/bootstrap.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/node_modules/jquery.nicescroll/dist/jquery.nicescroll.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/node_modules/moment/min/moment.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/assets/js/stisla.js") ?>"></script>



    <?= $this->renderSection('js') ?>

    <script src="<?= base_url(); ?>/bs-stepper/js/bs-stepper.min.js"></script>
    <!-- BS-Stepper Init -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.stepper = new Stepper(document.querySelector('.bs-stepper'))
        })
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

    <!-- Page Specific JS File -->

    <!-- Template JS File -->
    <script src="<?= base_url("stisla/assets/js/scripts.js") ?>"></script>
    <script src="<?= base_url("stisla/assets/js/custom.js") ?>"></script>


    <!-- <script>
        function bersihkan() {
            $('#inputName').val('');
            $('#inputDescription').val('');

        }
        $('.tombol-tutup').on('click', function() {
            if ($('.sukses').is(":visible")) {
                window.location.href = "<?php echo current_url() . "?" . $_SERVER['QUERY_STRING'] ?>";
            }
            $('.alert').hide();
            bersihkan();
        });

        $('#tombolSimpan').on('click', function() {
            var $name = $('#inputName').val();
            var $description = $('#inputDescription').val();


            $.ajax({
                url: "<?php echo site_url("group/simpan") ?>",
                type: "POST",
                data: {
                    name: $name,
                    description: $description,
                },
                success: function(hasil) {
                    var $obj = $.parseJSON(hasil);
                    if ($obj.sukses == false) {
                        $('.sukses').hide();
                        $('.error').show();
                        $('.error').html($obj.error);
                    } else {
                        $('.error').hide();
                        $('.sukses').show();
                        $('.sukses').html($obj.sukses);
                        $('.modal').hide();
                        Swal.fire({
                            title: 'Sukses!',
                            text: $obj.sukses,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                        window.location.href = "<?php echo current_url() . "?" . $_SERVER['QUERY_STRING'] ?>";
                    }
                }
            });
            bersihkan();

        });
    </script> -->
</body>

</html>