<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Layout &rsaquo; Top Navigation &mdash; Stisla</title>

    <!-- General CSS Files -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <!-- CSS Libraries -->

    <!-- Template CSS -->
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/assets/css/style.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/assets/css/components.css">

    <link rel="stylesheet" href="<?= base_url() ?>/assets/extensions/filepond/filepond.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.css">
    <link rel="stylesheet" href="<?= base_url() ?>/bs-stepper/css/bs-stepper.min.css">
</head>

<body class="layout-3">
    <div id="app">
        <div class="main-wrapper container">
            <!-- <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar container">
                <a href="index.html" class="navbar-brand sidebar-gone-hide">LSP SMK NEGERI 2 KUNINGAN</a>
                <div class="navbar-nav">
                    <a href="#" class="nav-link sidebar-gone-show" data-toggle="sidebar"><i class="fas fa-bars"></i></a>
                </div>
                <div class="nav-collapse">
                    <a class="sidebar-gone-show nav-collapse-toggle nav-link" href="#">
                        <i class="fas fa-ellipsis-v"></i>
                    </a>
                </div>

            </nav> -->

            <!-- <nav class="navbar navbar-secondary navbar-expand-lg">
                <div class="container">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-fire"></i><span>Dashboard</span></a>
                            <ul class="dropdown-menu">
                                <li class="nav-item"><a href="index-0.html" class="nav-link">General Dashboard</a></li>
                                <li class="nav-item"><a href="index.html" class="nav-link">Ecommerce Dashboard</a></li>
                            </ul>
                        </li>
                        <li class="nav-item active">
                            <a href="#" class="nav-link"><i class="far fa-heart"></i><span>Top Navigation</span></a>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="far fa-clone"></i><span>Multiple Dropdown</span></a>
                            <ul class="dropdown-menu">
                                <li class="nav-item"><a href="#" class="nav-link">Not Dropdown Link</a></li>
                                <li class="nav-item dropdown"><a href="#" class="nav-link has-dropdown">Hover Me</a>
                                    <ul class="dropdown-menu">
                                        <li class="nav-item"><a href="#" class="nav-link">Link</a></li>
                                        <li class="nav-item dropdown"><a href="#" class="nav-link has-dropdown">Link 2</a>
                                            <ul class="dropdown-menu">
                                                <li class="nav-item"><a href="#" class="nav-link">Link</a></li>
                                                <li class="nav-item"><a href="#" class="nav-link">Link</a></li>
                                                <li class="nav-item"><a href="#" class="nav-link">Link</a></li>
                                            </ul>
                                        </li>
                                        <li class="nav-item"><a href="#" class="nav-link">Link 3</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav> -->

            <!-- Main Content -->
            <div class="main-content">
                <section class="section">
                    <div class="section-header">
                        <h1><?= $siteTitle ?></h1>
                        <div class="section-header-breadcrumb">
                            <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                            <!-- <div class="breadcrumb-item"><a href="#">Layout</a></div> -->
                            <div class="breadcrumb-item"><?= $siteTitle ?></div>
                        </div>
                    </div>

                    <div class="section-body">
                        <?php if (session()->has('errors')) : ?>
                            <ul class="alert alert-danger">
                                <?php foreach (session('errors') as $error) : ?>
                                    <li><?= $error ?></li>
                                <?php endforeach ?>
                            </ul>
                        <?php endif ?>

                        <form id="setting-form" action="<?= site_url('/store-pengajuan'); ?>" method="POST" enctype="multipart/form-data">
                            <div id="stepper" class="bs-stepper">
                                <div class="bs-stepper-header" role="tablist">
                                    <div class="step" data-target="#test-nl-1">
                                        <button type="button" class="step-trigger" role="tab" id="steppertrigger1" aria-controls="test-nl-1">
                                            <span class="bs-stepper-circle">
                                                <span class="fas fa-user" aria-hidden="true"></span>
                                            </span>
                                            <span class="bs-stepper-label">Data Pengajuan</span>
                                        </button>
                                    </div>
                                    <div class="bs-stepper-line"></div>
                                    <div class="step" data-target="#test-nl-2">
                                        <button type="button" class="step-trigger" role="tab" id="steppertrigger2" aria-controls="test-nl-2">
                                            <span class="bs-stepper-circle">
                                                <span class="fas fa-map-marked" aria-hidden="true"></span>
                                            </span>
                                            <span class="bs-stepper-label">Profil Peserta</span>
                                        </button>
                                    </div>
                                    <div class="bs-stepper-line"></div>
                                    <div class="step" data-target="#test-nl-3">
                                        <button type="button" class="step-trigger" role="tab" id="steppertrigger3" aria-controls="test-nl-3">
                                            <span class="bs-stepper-circle">
                                                <span class="fas fa-save" aria-hidden="true"></span>
                                            </span>
                                            <span class="bs-stepper-label">Dokumen Portofolio</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="bs-stepper-content">
                                    <div id="test-nl-1" role="tabpanel" class="bs-stepper-pane" aria-labelledby="steppertrigger1">

                                        <div class="card">
                                            <div class="card-header">
                                                <h4>Pilih Skema Sertifikasi</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label>Skema</label>
                                                    <select class="form-control selectric <?php if (session('errors.skema_sertifikasi')) : ?>is-invalid<?php endif ?>" name="skema_sertifikasi">
                                                        <option value="">Pilih Skema</option>
                                                        <?php

                                                        foreach ($listSkema as $row) {

                                                            old('skema_sertifikasi') == $row['id_skema'] ? $pilih = 'selected' : $pilih = null;

                                                            echo '<option ' . $pilih . ' value="' . $row['id_skema'] . '">' . $row['nama_skema'] . '</option>';
                                                        }

                                                        ?>
                                                    </select>
                                                    <?php if (session('errors.skema_sertifikasi')) { ?>
                                                        <div class="invalid-feedback">
                                                            <?= session('errors.skema_sertifikasi') ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>

                                                <div class="form-group">
                                                    <label>Jadwal Uji Kompetensi</label>
                                                    <select class="form-control selectric <?php if (session('errors.jadwal_sertifikasi')) : ?>is-invalid<?php endif ?>" name="jadwal_sertifikasi">
                                                        <option value="">Jadwal Uji Kompetensi</option>
                                                        <?php

                                                        foreach ($listSettanggal as $row) {

                                                            old('tanggal_sertifikasi') == $row['id_tanggal'] ? $pilih = 'selected' : $pilih = null;

                                                            echo '<option ' . $pilih . ' value="' . $row['id_tanggal'] . '">' . $row['tanggal'] . ' - ' . $row['keterangan'] . '</option>';
                                                        }

                                                        ?>
                                                    </select>
                                                    <?php if (session('errors.jadwal_sertifikasi')) { ?>
                                                        <div class="invalid-feedback">
                                                            <?= session('errors.jadwal_sertifikasi') ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>

                                                <div class="form-group">
                                                    <label>Tempat Uji Kompetensi</label>
                                                    <select class="form-control selectric <?php if (session('errors.tuk')) : ?>is-invalid<?php endif ?>" name="tuk">
                                                        <option value="">Tempat Uji Kompetensi</option>
                                                        <?php

                                                        foreach ($listTUK as $row) {

                                                            old('id_tanggal') == $row['id_tuk'] ? $pilih = 'selected' : $pilih = null;

                                                            echo '<option ' . $pilih . ' value="' . $row['id_tuk'] . '">' . $row['nama_tuk'] . '</option>';
                                                        }

                                                        ?>
                                                    </select>
                                                    <?php if (session('errors.tuk')) { ?>
                                                        <div class="invalid-feedback">
                                                            <?= session('errors.tuk') ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-body">
                                                <button type="button" class="btn btn-primary" onclick="stepper.next()">Next</button>
                                            </div>
                                        </div>

                                    </div>
                                    <div id="test-nl-2" role="tabpanel" class="bs-stepper-pane" aria-labelledby="steppertrigger2">

                                        <div class="card">
                                            <div class="card-header">
                                                <h4>Data Diri Pemohon</h4>
                                            </div>
                                            <div class="card-body">
                                                <?= csrf_field(); ?>
                                                <div class="form-group">
                                                    <label>Nama Lengkap<span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control <?php if (session('errors.nama_siswa')) : ?>is-invalid<?php endif ?>" name="nama_siswa" value="<?= old('nama_siswa') ?>" placeholder="Masukan Nama Lengkap">
                                                    <?php if (session('errors.nama_siswa')) { ?>
                                                        <div class="invalid-feedback">
                                                            <?= session('errors.nama_siswa') ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-12 col-md-4">
                                                        <label>Email<span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control <?php if (session('errors.email')) : ?>is-invalid<?php endif ?>" name="email" value="<?= old('email') ?>" placeholder="Masukan Alamat Email">
                                                        <?php if (session('errors.email')) { ?>
                                                            <div class="invalid-feedback">
                                                                <?= session('errors.email') ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="form-group col-12 col-md-4">
                                                        <label>Nomor Handphone<span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control <?php if (session('errors.no_hp')) : ?>is-invalid<?php endif ?>" name="no_hp" value="<?= old('no_hp') ?>" placeholder="Masukan Nomor Handphone/Whatsapp">
                                                        <?php if (session('errors.no_hp')) { ?>
                                                            <div class="invalid-feedback">
                                                                <?= session('errors.no_hp') ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <div class=" form-group col-12 col-md-4">
                                                        <label>Nomor Telpon</label>
                                                        <input type="number" class="form-control <?php if (session('errors.telpon_rumah')) : ?>is-invalid<?php endif ?>" name="telpon_rumah" value="<?= old('telpon_rumah') ?>" placeholder="Masukan Nomor Telpon Rumah">
                                                        <?php if (session('errors.telpon_rumah')) { ?>
                                                            <div class="invalid-feedback">
                                                                <?= session('errors.telpon_rumah') ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Nomor KTP/NIK/Paspor<span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control <?php if (session('errors.nik')) : ?>is-invalid<?php endif ?>" name="nik" value="<?= old('nik') ?>" placeholder="Masukan Nomor Induk Kependudukan">
                                                    <?php if (session('errors.nik')) { ?>
                                                        <div class="invalid-feedback">
                                                            <?= session('errors.nik') ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-12 col-md-6">
                                                        <label>Tempat Lahir<span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control <?php if (session('errors.tempat_lahir')) : ?>is-invalid<?php endif ?>" name="tempat_lahir" value="<?= old('tempat_lahir') ?>" placeholder="Masukan Tempat Lahir">
                                                        <?php if (session('errors.tempat_lahir')) { ?>
                                                            <div class="invalid-feedback">
                                                                <?= session('errors.tempat_lahir') ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="form-group col-12 col-md-6">
                                                        <label>Tanggal Lahir<span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control <?php if (session('errors.tanggal_lahir')) : ?>is-invalid<?php endif ?>" name="tanggal_lahir" value="<?= old('tanggal_lahir') ?>">
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
                                                            <input type="radio" name="jenis_kelamin" value="Laki-Laki" class="selectgroup-input" <?= (old('jenis_kelamin') == "Laki-Laki" ? "checked" : ""); ?>>
                                                            <span class="selectgroup-button">Laki-Laki</span>
                                                        </label>
                                                        <label class="selectgroup-item">
                                                            <input type="radio" name="jenis_kelamin" value="Perempuan" class="selectgroup-input" <?= (old('jenis_kelamin') == "Perempuan" ? "checked" : ""); ?>>
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
                                                    <input type="text" class="form-control <?php if (session('errors.kebangsaan')) : ?>is-invalid<?php endif ?>" name="kebangsaan" value="<?= old('kebangsaan') ?>" placeholder="WNI/WNA">
                                                    <?php if (session('errors.kebangsaan')) { ?>
                                                        <div class="invalid-feedback">
                                                            <?= session('errors.kebangsaan') ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header">
                                                <h4>Riwayat Pendidikan Pemohon</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label>Pendidikan Terakhir<span class="text-danger">*</span></label>
                                                    <select name="pendidikan_terakhir" class="form-control select2 <?php if (session('errors.pendidikan_terakhir')) : ?>is-invalid<?php endif ?>">
                                                        <option value="">Pilih Pendidikan Terakhir</option>
                                                        <option value="SD">SD</option>
                                                        <option value="SMP">SMP</option>
                                                        <option value="SMA/SMK">SMA/SMK</option>
                                                        <option value="Diploma">Diploma</option>
                                                        <option value="Sarjana">Sarjana</option>
                                                        <option value="Magister">Magister</option>
                                                        <option value="Doktor">Doktor</option>
                                                    </select>
                                                    <?php if (session('errors.pendidikan_terakhir')) { ?>
                                                        <div class="invalid-feedback">
                                                            <?= session('errors.pendidikan_terakhir') ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="form-group">
                                                    <label>Nama Sekolah / Perguruan Tinggi<span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control <?php if (session('errors.nama_sekolah')) : ?>is-invalid<?php endif ?>" name="nama_sekolah" value="<?= old('nama_sekolah') ?>" placeholder="Masukan Nama Sekolah Atau Perguruan Tinggi">
                                                    <?php if (session('errors.nama_sekolah')) { ?>
                                                        <div class="invalid-feedback">
                                                            <?= session('errors.nama_sekolah') ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="form-group">
                                                    <label>Jurusan / Program Studi<span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control <?php if (session('errors.jurusan')) : ?>is-invalid<?php endif ?>" name="jurusan" value="<?= old('jurusan') ?>" placeholder="Masukan Nama Jurusan Atau Program Studi">
                                                    <?php if (session('errors.jurusan')) { ?>
                                                        <div class="invalid-feedback">
                                                            <?= session('errors.jurusan') ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header">
                                                <h4>Alamat Domisili Pemohon</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-row">
                                                    <div class="form-group col-12 col-md-6">
                                                        <label>Provinsi<span class="text-danger">*</span></label>
                                                        <select class="form-control select2 <?php if (session('errors.provinsi')) : ?>is-invalid<?php endif ?>" name="provinsi" id="id_provinsi">
                                                            <option value="">Provinsi</option>
                                                            <?php
                                                            foreach ($provinsi as $key => $row) {
                                                                old('provinsi') == $row['id'] ? $pilih = 'selected' : $pilih = null;

                                                                echo '<option ' . $pilih . ' value="' . $row['id'] . '">' . $row['nama'] . '</option>';
                                                            }
                                                            ?>

                                                        </select>
                                                        <?php if (session('errors.provinsi')) { ?>
                                                            <div class="invalid-feedback">
                                                                <?= session('errors.provinsi') ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="form-group col-12 col-md-6">
                                                        <label>Kabupaten/Kota<span class="text-danger">*</span></label>
                                                        <select class="form-control select2 <?php if (session('errors.kabupaten')) : ?>is-invalid<?php endif ?>" name="kabupaten" id="id_kabupaten">
                                                            <option value="">Kabupaten/Kota</option>
                                                            <option value="<?= old('kabupaten') ?>" <?= (old('kabupaten')) ? 'selected' : '' ?>>tes</option>

                                                        </select>
                                                        <?php if (session('errors.kabupaten')) { ?>
                                                            <div class="invalid-feedback">
                                                                <?= session('errors.kabupaten') ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-12 col-md-6">
                                                        <label>Kecamatan<span class="text-danger">*</span></label>
                                                        <select class="form-control select2 <?php if (session('errors.kecamatan')) : ?>is-invalid<?php endif ?>" name="kecamatan" id="id_kecamatan">
                                                            <option value="">Kecamatan</option>
                                                        </select>
                                                        <?php if (session('errors.kecamatan')) { ?>
                                                            <div class="invalid-feedback">
                                                                <?= session('errors.kecamatan') ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="form-group col-12 col-md-6">
                                                        <label>Kelurahan/Desa<span class="text-danger">*</span></label>
                                                        <select class="form-control select2 <?php if (session('errors.kelurahan')) : ?>is-invalid<?php endif ?>" name="kelurahan" id="id_desa">
                                                            <option value="">Kelurahan/Desa</option>
                                                        </select>
                                                        <?php if (session('errors.kelurahan')) { ?>
                                                            <div class="invalid-feedback">
                                                                <?= session('errors.kelurahan') ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-12 col-md-4">
                                                        <label>RT<span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control <?php if (session('errors.rt')) : ?>is-invalid<?php endif ?>" name="rt" value="<?= old('rt') ?>" placeholder="Masukan RT">
                                                        <?php if (session('errors.rt')) { ?>
                                                            <div class="invalid-feedback">
                                                                <?= session('errors.rt') ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="form-group col-12 col-md-4">
                                                        <label>RW<span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control <?php if (session('errors.rw')) : ?>is-invalid<?php endif ?>" name="rw" value="<?= old('rw') ?>" placeholder="Masukan RW">
                                                        <?php if (session('errors.rw')) { ?>
                                                            <div class="invalid-feedback">
                                                                <?= session('errors.rw') ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="form-group col-12 col-md-4">
                                                        <label>Kode Pos<span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control <?php if (session('errors.kode_pos')) : ?>is-invalid<?php endif ?>" name="kode_pos" value="<?= old('kode_pos') ?>" placeholder="Masukan Kode Pos">
                                                        <?php if (session('errors.kode_pos')) { ?>
                                                            <div class="invalid-feedback">
                                                                <?= session('errors.kode_pos') ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h4>Data Pekerjaan</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label>Pekerjaan<span class="text-danger">*</span></label>
                                                    <select name="pekerjaan" class="form-control select2 <?php if (session('errors.pekerjaan')) : ?>is-invalid<?php endif ?>">
                                                        <option value="">Pilih Pekerjaan</option>
                                                        <option value="Pelajar/Mahasiswa" <?= (old('pekerjaan') == "Pelajar/Mahasiswa" ? "selected" : ""); ?>>Pelajar/Mahasiswa</option>
                                                        <option value="Pegawai" <?= (old('pekerjaan') == "Pegawai" ? "selected" : ""); ?>>Pegawai</option>
                                                        <option value="Wiraswasta" <?= (old('pekerjaan') == "Wiraswasta" ? "selected" : ""); ?>>Wiraswasta</option>
                                                        <option value="Petani" <?= (old('pekerjaan') == "Petani" ? "selected" : ""); ?>>Petani</option>
                                                        <option value="Guru" <?= (old('pekerjaan') == "Guru" ? "selected" : ""); ?>>Guru</option>
                                                        <option value="Dokter" <?= (old('pekerjaan') == "Dokter" ? "selected" : ""); ?>>Dokter</option>
                                                    </select>
                                                    <?php if (session('errors.pekerjaan')) { ?>
                                                        <div class="invalid-feedback">
                                                            <?= session('errors.pekerjaan') ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="form-group">
                                                    <label>Nama Instansi</label>
                                                    <input type="text" class="form-control <?php if (session('errors.nama_lembaga')) : ?>is-invalid<?php endif ?>" name="nama_lembaga" value="<?= old('nama_lembaga') ?>" placeholder="Organisasi / Tempat Kerja / Instansi Terkait / Freelance / - (bila tidak ada)">
                                                    <?php if (session('errors.nama_lembaga')) { ?>
                                                        <div class="invalid-feedback">
                                                            <?= session('errors.nama_lembaga') ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="form-group">
                                                    <label>Jabatan</label>
                                                    <input type="text" class="form-control <?php if (session('errors.jabatan')) : ?>is-invalid<?php endif ?>" name="jabatan" value="<?= old('jabatan') ?>" placeholder="Jabatan di Perusahaan">
                                                    <?php if (session('errors.jabatan')) { ?>
                                                        <div class="invalid-feedback">
                                                            <?= session('errors.jabatan') ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="form-group">
                                                    <label>Alamat Lembaga / Perusahaan</label>
                                                    <textarea class="form-control <?php if (session('errors.alamat_perusahaan')) : ?>is-invalid<?php endif ?>" name="alamat_perusahaan" id="inputDescription"><?= old('alamat_perusahaan') ?></textarea>
                                                    <?php if (session('errors.alamat_perusahaan')) { ?>
                                                        <div class="invalid-feedback">
                                                            <?= session('errors.alamat_perusahaan') ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-12 col-md-6">
                                                        <label>Email Perusahaan</label>
                                                        <input type="text" class="form-control <?php if (session('errors.email_perusahaan')) : ?>is-invalid<?php endif ?>" name="email_perusahaan" value="<?= old('email_perusahaan') ?>" placeholder="Masukan Nomor Email Perusahaan">
                                                        <?php if (session('errors.email_perusahaan')) { ?>
                                                            <div class="invalid-feedback">
                                                                <?= session('errors.email_perusahaan') ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="form-group col-12 col-md-6">
                                                        <label>Nomor Telp Perusahaan</label>
                                                        <input type="text" class="form-control <?php if (session('errors.no_telp_perusahaan')) : ?>is-invalid<?php endif ?>" name="no_telp_perusahaan" value="<?= old('no_telp_perusahaan') ?>" placeholder="Masukan Nomor Telpon Perusahaan">
                                                        <?php if (session('errors.no_telp_perusahaan')) { ?>
                                                            <div class="invalid-feedback">
                                                                <?= session('errors.no_telp_perusahaan') ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-body">
                                                <button type="button" class="btn btn-primary" onclick="stepper.previous()">Previous</button>
                                                <button type="button" class="btn btn-primary" onclick="stepper.next()">Next</button>
                                            </div>
                                        </div>

                                    </div>
                                    <div id="test-nl-3" role="tabpanel" class="bs-stepper-pane text-center" aria-labelledby="steppertrigger3">

                                        <div class="card">
                                            <div class="card-header">
                                                <h4>Bukti Kelengkapan Pemohon</h4>
                                            </div>
                                            <div class=" card-body">

                                                <div class="form-row">
                                                    <div class="col-12 col-md-4">
                                                        <div class="form-group">
                                                            <label for="">Pas Foto</label>
                                                            <input type="file" name="pas_foto" class="image-preview-filepond">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-4">
                                                        <div class="form-group">
                                                            <label for="">Identitas Pribadi (KTP / Kartu Pelajar)</label>
                                                            <input type="file" name="file_ktp" class="filepond-ktp">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-4">
                                                        <div class="form-group">
                                                            <label for="">Bukti Pendidikan</label>
                                                            <input type="file" name="bukti_pendidikan" class="filepond-bukti-pendidikan">
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-body">
                                                <button type="button" class="btn btn-primary" onclick="stepper.previous()">Previous</button>
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i><span class="pl-2">Upload </span></button>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
            <footer class="main-footer">
                <div class="footer-left">
                    Copyright &copy; 2018 <div class="bullet"></div> Design By <a href="https://nauv.al/">Muhamad Nauval Azhar</a>
                </div>
                <div class="footer-right">
                    2.3.0
                </div>
            </footer>
        </div>
    </div>

    <!-- General JS Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="<?= base_url() ?>/stisla/assets/js/stisla.js"></script>

    <!-- JS Libraies -->

    <!-- Page Specific JS File -->

    <!-- Template JS File -->
    <script src="<?= base_url() ?>/stisla/assets/js/scripts.js"></script>
    <script src="<?= base_url() ?>/stisla/assets/js/custom.js"></script>

    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-filter/filepond-plugin-image-filter.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/filepond/filepond.js"></script>

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

    <script src="<?= base_url(); ?>/bs-stepper/js/bs-stepper.min.js"></script>
    <!-- BS-Stepper Init -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.stepper = new Stepper(document.querySelector('.bs-stepper'))
        })

        var stepper1
        var stepper2
        var stepper3
        var stepper4
        var stepperForm

        document.addEventListener('DOMContentLoaded', function() {
            stepper1 = new Stepper(document.querySelector('#stepper1'))
            stepper2 = new Stepper(document.querySelector('#stepper2'), {
                linear: false
            })
            stepper3 = new Stepper(document.querySelector('#stepper3'), {
                linear: false,
                animation: true
            })
            stepper4 = new Stepper(document.querySelector('#stepper4'))

            var stepperFormEl = document.querySelector('#stepperForm')
            stepperForm = new Stepper(stepperFormEl, {
                animation: true
            })

            var btnNextList = [].slice.call(document.querySelectorAll('.btn-next-form'))
            var stepperPanList = [].slice.call(stepperFormEl.querySelectorAll('.bs-stepper-pane'))
            var inputMailForm = document.getElementById('inputMailForm')
            var inputPasswordForm = document.getElementById('inputPasswordForm')
            var form = stepperFormEl.querySelector('.bs-stepper-content form')

            btnNextList.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    stepperForm.next()
                })
            })

            stepperFormEl.addEventListener('show.bs-stepper', function(event) {
                form.classList.remove('was-validated')
                var nextStep = event.detail.indexStep
                var currentStep = nextStep

                if (currentStep > 0) {
                    currentStep--
                }

                var stepperPan = stepperPanList[currentStep]

                if ((stepperPan.getAttribute('id') === 'test-form-1' && !inputMailForm.value.length) ||
                    (stepperPan.getAttribute('id') === 'test-form-2' && !inputPasswordForm.value.length)) {
                    event.preventDefault()
                    form.classList.add('was-validated')
                }
            })
        })
    </script>
</body>

</html>