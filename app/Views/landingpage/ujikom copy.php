<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $siteTitle ?> &mdash; <?= env("app_name") ?></title>

    <link rel="shortcut icon" href="<?= base_url('asset_img/logolsp.png') ?>" type="image/x-icon">

    <link rel="stylesheet" href="<?= base_url() ?>/assets/compiled/css/app.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/compiled/css/iconly.css">

    <link rel="stylesheet" href="<?= base_url() ?>/assets/extensions/filepond/filepond.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/extensions/@fortawesome/fontawesome-free/css/all.min.css">

    <link rel="stylesheet" href="<?= base_url() ?>/bs-stepper/css/bs-stepper.min.css">

</head>

<body>
    <script src="<?= base_url() ?>/assets/static/js/initTheme.js"></script>
    <nav class="navbar navbar-light">
        <div class="container d-block">
            <a href="index.html"><i class="bi bi-chevron-left"></i></a>
            <a class="navbar-brand ms-4" href="index.html">
                <img src="<?= base_url() ?>/assets/compiled/svg/logo.svg">
            </a>
        </div>
    </nav>


    <div class="container">

        <div class="row">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">bs-stepper</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="bs-stepper">
                            <div class="bs-stepper-header" role="tablist">

                                <div class="step" data-target="#pengajuan">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="pengajuan" id="pengajuan-trigger">
                                        <span class="bs-stepper-circle">1</span>
                                        <span class="bs-stepper-label">Data Pengajuan</span>
                                    </button>
                                </div>
                                <div class="line"></div>
                                <div class="step" data-target="#data-diri">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="data-diri" id="data-diri-trigger">
                                        <span class="bs-stepper-circle">2</span>
                                        <span class="bs-stepper-label">Profil Peserta</span>
                                    </button>
                                </div>
                                <div class="line"></div>
                                <div class="step" data-target="#dokumen">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="dokumen" id="dokumen-trigger">
                                        <span class="bs-stepper-circle">3</span>
                                        <span class="bs-stepper-label">Dokumen Portofolio</span>
                                    </button>
                                </div>
                                <div class="line"></div>
                                <div class="step" data-target="#asesmen">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="asesmen" id="asesmen-trigger">
                                        <span class="bs-stepper-circle">4</span>
                                        <span class="bs-stepper-label">Asesmen Mandiri</span>
                                    </button>
                                </div>
                            </div>
                            <div class="bs-stepper-content">

                                <div id="pengajuan" class="content" role="tabpanel" aria-labelledby="pengajuan-trigger">

                                    <input type="hidden" name="id" value="<?= isset($dataSkemaSiswa['id_skema_siswa']) ? $dataSkemaSiswa['id_skema_siswa'] : '' ?>">
                                    <input type="hidden" name="id_siswa" value="<?= isset($dataSkemaSiswa['id_siswa']) ? $dataSkemaSiswa['id_siswa'] : user()->id ?>">
                                    <input type="hidden" name="email_siswa" value="<?= isset($dataSkemaSiswa['email_siswa']) ? $dataSkemaSiswa['email_siswa'] : user()->email ?>">

                                    <div class="form-group">
                                        <label>Skema</label>
                                        <select class="form-control selectric" name="id_skema">
                                            <option value="">Pilih Skema</option>
                                            <?php

                                            if (isset($listSkema)) {
                                                foreach ($listSkema as $row) {
                                                    isset($dataSkemaSiswa['id_skema']) && $dataSkemaSiswa['id_skema'] == $row['id_skema'] ? $pilih = 'selected' : $pilih = null;
                                                    echo '<option ' . $pilih . ' value="' . $row['id_skema'] . '">' . $row['nama_skema'] . '</option>';
                                                }
                                            }

                                            ?>
                                        </select>
                                    </div>


                                    <button class="btn btn-primary" onclick="stepper.next()">Next</button>
                                </div>
                                <div id="data-diri" class="content" role="tabpanel" aria-labelledby="data-diri-trigger">
                                    <?= csrf_field(); ?>
                                    <div class="form-group">
                                        <label>Nama Lengkap<span class="text-danger">*</span></label>
                                        <input type="hidden" name="id_siswa" value="<?= user()->id ?>">
                                        <input type="text" class="form-control <?php if (session('errors.nama_siswa')) : ?>is-invalid<?php endif ?>" name="nama_siswa" value="<?= isset($dataAPL1['nama_siswa']) ? $dataAPL1['nama_siswa'] : user()->fullname ?>" readonly>
                                        <?php if (session('errors.nama_siswa')) { ?>
                                            <div class="invalid-feedback">
                                                <?= session('errors.nama_siswa') ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-12 col-md-4">
                                            <label>Email<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control <?php if (session('errors.email')) : ?>is-invalid<?php endif ?>" name="email" value="<?= isset($dataAPL1['email']) ? $dataAPL1['email'] : user()->email ?>" readonly>
                                            <?php if (session('errors.email')) { ?>
                                                <div class="invalid-feedback">
                                                    <?= session('errors.email') ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="form-group col-12 col-md-4">
                                            <label>Nomor Handphone<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control <?php if (session('errors.no_hp')) : ?>is-invalid<?php endif ?>" name="no_hp" value="<?= isset($dataAPL1['no_hp']) ? $dataAPL1['no_hp'] : user()->no_telp ?>" readonly>
                                            <?php if (session('errors.no_hp')) { ?>
                                                <div class="invalid-feedback">
                                                    <?= session('errors.no_hp') ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class=" form-group col-12 col-md-4">
                                            <label>Nomor Telpon</label>
                                            <input type="text" class="form-control <?php if (session('errors.telpon_rumah')) : ?>is-invalid<?php endif ?>" name="telpon_rumah" value="<?= isset($dataAPL1['telpon_rumah']) ? $dataAPL1['telpon_rumah'] : old('telpon_rumah') ?>" placeholder="Masukan Nomor Telpon Rumah">
                                            <?php if (session('errors.telpon_rumah')) { ?>
                                                <div class="invalid-feedback">
                                                    <?= session('errors.telpon_rumah') ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Nomor KTP/NIK/Paspor<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php if (session('errors.ktp')) : ?>is-invalid<?php endif ?>" name="ktp" value="<?= isset($dataAPL1['nik']) ? $dataAPL1['nik'] : old('ktp') ?>" placeholder="Masukan Nomor Induk Kependudukan">
                                        <?php if (session('errors.ktp')) { ?>
                                            <div class="invalid-feedback">
                                                <?= session('errors.ktp') ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-12 col-md-6">
                                            <label>Tempat Lahir<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control <?php if (session('errors.tempat_lahir')) : ?>is-invalid<?php endif ?>" name="tempat_lahir" value="<?= isset($dataAPL1['tempat_lahir']) ? $dataAPL1['tempat_lahir'] : old('tempat_lahir') ?>" placeholder="Masukan Tempat Lahir">
                                            <?php if (session('errors.tempat_lahir')) { ?>
                                                <div class="invalid-feedback">
                                                    <?= session('errors.tempat_lahir') ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="form-group col-12 col-md-6">
                                            <label>Tanggal Lahir<span class="text-danger">*</span></label>
                                            <input type="date" class="form-control <?php if (session('errors.tanggal_lahir')) : ?>is-invalid<?php endif ?>" name="tanggal_lahir" value="<?= isset($dataAPL1['tanggal_lahir']) ? $dataAPL1['tanggal_lahir'] : old('tanggal_lahir') ?>">
                                            <?php if (session('errors.tanggal_lahir')) { ?>
                                                <div class="invalid-feedback">
                                                    <?= session('errors.tanggal_lahir') ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Jenis Kelamin</label>
                                        <div class="selectgroup w-100 <?php if (session('errors.jenis_kelamin')) : ?>is-invalid<?php endif ?>">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="jenis_kelamin" value="Laki-Laki" class="selectgroup-input" <?php echo (isset($dataAPL1['jenis_kelamin']) && $dataAPL1['jenis_kelamin'] == "Laki-Laki") ? "checked" : (old('jenis_kelamin') == "Laki-Laki" ? "checked" : ""); ?>>
                                                <span class="selectgroup-button">Laki-Laki</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="jenis_kelamin" value="Perempuan" class="selectgroup-input" <?php echo (isset($dataAPL1['jenis_kelamin']) && $dataAPL1['jenis_kelamin'] == "Perempuan") ? "checked" : (old('jenis_kelamin') == "Perempuan" ? "checked" : ""); ?>>
                                                <span class="selectgroup-button">Perempuan</span>
                                            </label>
                                        </div>
                                        <?php if (session('errors.jenis_kelamin')) { ?>
                                            <div class="invalid-feedback">
                                                <?= session('errors.jenis_kelamin') ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="form-group">
                                        <label>Kebangsaan<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php if (session('errors.kebangsaan')) : ?>is-invalid<?php endif ?>" name="kebangsaan" placeholder="WNI/WNA" value="<?= isset($dataAPL1['kebangsaan']) ? $dataAPL1['kebangsaan'] : old('kebangsaan') ?>">
                                        <?php if (session('errors.kebangsaan')) { ?>
                                            <div class="invalid-feedback">
                                                <?= session('errors.kebangsaan') ?>
                                            </div>
                                        <?php } ?>
                                    </div>


                                    <button class="btn btn-primary" onclick="stepper.previous()">Previous</button>
                                    <button class="btn btn-primary" onclick="stepper.next()">Next</button>
                                </div>
                                <div id="dokumen" class="content" role="tabpanel" aria-labelledby="dokumen-trigger">

                                    <button class="btn btn-primary" onclick="stepper.previous()">Previous</button>
                                    <button class="btn btn-primary" onclick="stepper.next()">Next</button>
                                </div>
                                <div id="asesmen" class="content" role="tabpanel" aria-labelledby="asesmen-trigger">

                                    <button class="btn btn-primary" onclick="stepper.previous()">Previous</button>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        Visit <a href="https://github.com/Johann-S/bs-stepper/#how-to-use-it">bs-stepper documentation</a> for more examples and information about the plugin.
                    </div>
                </div>

            </div>
        </div>


    </div>


    <script src="<?= base_url() ?>/assets/compiled/js/app.js"></script>

    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-filter/filepond-plugin-image-filter.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond/filepond.js"></script>

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

    <script src="<?= base_url(); ?>/bs-stepper/js/bs-stepper.min.js"></script>
    <!-- BS-Stepper Init -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.stepper = new Stepper(document.querySelector('.bs-stepper'))
        })
    </script>

</body>

</html>