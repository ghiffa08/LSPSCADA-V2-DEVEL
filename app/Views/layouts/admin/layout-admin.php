<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title><?= $siteTitle ?> &mdash; <?= env("app_name") ?></title>
    <link rel="shortcut icon" href="<?= base_url('asset_img/logolsp.png') ?>" type="image/x-icon">

    <!-- General CSS -->
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/@fortawesome/fontawesome-free/css/all.min.css">

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/datatables.net-select-bs4/css/select.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/selectric/public/selectric.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/select2/dist/css/select2.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/chocolat/dist/css/chocolat.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/summernote/dist/summernote-bs4.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/filepond/filepond.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/filepond-plugin-image-preview/filepond-plugin-image-preview.css">

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
        };

        FilePond.create(document.querySelector(".tanda_tangan"), filePondConfig);
        FilePond.create(document.querySelector(".edit_tanda_tangan"), filePondConfig);
    </script>

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
        $("document").ready(function() {

            $("#filterTable").dataTable({
                "searching": true
            });

            //Get a reference to the new datatable
            var table = $('#filterTable').DataTable();

            //Take the category filter drop down and append it to the datatables_filter div. 
            //You can use this same idea to move the filter anywhere withing the datatable that you want.
            $("#filterTable_filter.dataTables_filter").append($("#categoryFilter"));

            //Get the column index for the Category column to be used in the method below ($.fn.dataTable.ext.search.push)
            //This tells datatables what column to filter on when a user selects a value from the dropdown.
            //It's important that the text used here (Category) is the same for used in the header of the column to filter
            var categoryIndex = 0;
            $("#filterTable th").each(function(i) {
                if ($($(this)).html() == "Status") {
                    categoryIndex = i;
                    return false;
                }
            });

            //Use the built in datatables API to filter the existing rows by the Category column
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var selectedItem = $('#categoryFilter').val()
                    var category = data[categoryIndex];
                    if (selectedItem === "" || category.includes(selectedItem)) {
                        return true;
                    }
                    return false;
                }
            );

            //Set the change event for the Category Filter dropdown to redraw the datatable each time
            //a user selects a new filter.
            $("#categoryFilter").change(function(e) {
                table.draw();
            });

            table.draw();
        });
    </script>

    <script>
        $("document").ready(function() {
            // Inisialisasi DataTables
            var table = $('#emailAPL1').DataTable({
                "searching": true
            });

            // Buat fungsi pencarian khusus untuk rentang tanggal validasi
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var minDate = $('#min').val();
                var maxDate = $('#max').val();
                var currentDate = data[5]; // Kolom ke-5 adalah kolom "Tanggal Validasi" pada tabel

                // Ubah format tanggal menjadi objek Date
                var currentDateFormat = new Date(currentDate);
                var minDateFormat = new Date(minDate);
                var maxDateFormat = new Date(maxDate);

                // Jika rentang tanggal kosong, tampilkan semua data
                if (minDate === '' && maxDate === '') {
                    return true;
                }

                // Lakukan pencarian berdasarkan rentang tanggal
                if ((minDate === '' || currentDateFormat >= minDateFormat) &&
                    (maxDate === '' || currentDateFormat <= maxDateFormat)) {
                    return true;
                }
                return false;
            });

            // Set ulang tabel ketika ada perubahan pada rentang tanggal
            $("#min, #max").on('change', function() {
                table.draw();
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