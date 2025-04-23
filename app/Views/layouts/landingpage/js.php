<!-- ===== General JS Scripts ===== -->

<script src="<?= base_url("/stisla/node_modules/jquery/dist/jquery.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/popper.js/dist/umd/popper.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/bootstrap/dist/js/bootstrap.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/jquery.nicescroll/dist/jquery.nicescroll.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/moment/min/moment.min.js") ?>"></script>
<script src="<?= base_url("/stisla/assets/js/stisla.js") ?>"></script>

<!-- ===== JS Libraies ===== -->

<!-- ===== Select ===== -->
<script src="<?= base_url("/stisla/node_modules/selectric/public/jquery.selectric.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/select2/dist/js/select2.full.min.js") ?>"></script>
<!-- ===== End ===== -->

<!-- ===== Sweet Alert2 ===== -->
<script src="<?= base_url("/stisla/node_modules/sweetalert2/dist/sweetalert2.min.js") ?>"></script>
<!-- ===== End ===== -->

<!-- ===== Data Tables ===== -->
<script src="<?= base_url("/stisla/node_modules/datatables/media/js/jquery.dataTables.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js") ?>"></script>
<script src="<?= base_url("/stisla/node_modules/datatables.net-select-bs4/js/select.bootstrap4.min.js") ?>"></script>
<!-- ===== End ===== -->

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

<!-- ===== Intro.js ===== -->
<script src="<?= base_url("/intro.js/minified/intro.min.js") ?>"></script>
<!-- ===== End ===== -->

<!-- Template JS File -->
<script src="<?= base_url("/stisla/assets/js/scripts.js") ?>"></script>
<script src="<?= base_url("/stisla/assets/js/custom.js") ?>"></script>



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

<!-- ===== Script Nav-Tab ===== -->
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
<!-- ===== End ===== -->

<!-- ===== Script Dynamic Dependent ===== -->
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

<!-- ===== End ===== -->

<!-- ===== Script Dynamic Dependent ===== -->
<!-- <script>
    $(document).ready(function() {
        $("#id_skema").change(function(e) {
            var id_skema = $("#id_skema").val();
            $.ajax({
                type: "POST",
                url: "<?= base_url('/get-jadwal') ?>",
                data: {
                    id_skema: id_skema
                },
                success: function(response) {
                    $("#id_tanggal").html(response.options_tanggal);
                    $("#id_tuk").html(response.options_tuk);
                    $("#id_asesmen").val(response.id_asesmen);
                },
                dataType: 'json'
            });
        });
    });
</script> -->
<script>
    $(document).ready(function() {
        // Membuat fungsi untuk memperbarui jadwal
        function updateJadwal() {
            var id_skema = $("#id_skema").val();
            $.ajax({
                type: "POST",
                url: "<?= base_url('/get-jadwal') ?>",
                data: {
                    id_skema: id_skema
                },
                success: function(response) {
                    $("#id_tanggal").html(response.options_tanggal);
                    $("#id_tuk").html(response.options_tuk);
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

<script>
    function showSpinnerAndSubmit() {
        var button = document.getElementById('submit-button');
        var buttonContent = document.getElementById('button-content');
        var spinner = document.getElementById('spinner');

        // Menampilkan spinner
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Loading...';
        // Menonaktifkan tombol
        button.disabled = true;

        // Mengirimkan formulir setelah menampilkan spinner
        var form = document.getElementById('upload-form');
        form.submit();
    }
</script>