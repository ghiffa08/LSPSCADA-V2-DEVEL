<!-- =========

	Template Name: Play
	Author: UIdeck
	Author URI: https://uideck.com/
	Support: https://uideck.com/support/
	Version: 1.1

========== -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $siteTitle ?> &mdash; <?= env("app_name") ?></title>


    <!-- Primary Meta Tags -->
    <meta name="title" content="Play - Free Open Source HTML Bootstrap Template by UIdeck">
    <meta name="description" content="Play - Free Open Source HTML Bootstrap Template by UIdeck Team">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://uideck.com/play/">
    <meta property="og:title" content="Play - Free Open Source HTML Bootstrap Template by UIdeck">
    <meta property="og:description" content="Play - Free Open Source HTML Bootstrap Template by UIdeck Team">
    <meta property="og:image" content="https://uideck.com/wp-content/uploads/2021/09/play-meta-bs.jpg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://uideck.com/play/">
    <meta property="twitter:title" content="Play - Free Open Source HTML Bootstrap Template by UIdeck">
    <meta property="twitter:description" content="Play - Free Open Source HTML Bootstrap Template by UIdeck Team">
    <meta property="twitter:image" content="https://uideck.com/wp-content/uploads/2021/09/play-meta-bs.jpg">

    <!--====== Favicon Icon ======-->
    <link rel="shortcut icon" href="<?= base_url('asset_img/logolsp.png') ?>" type="image/x-icon">

    <!-- ===== All CSS files ===== -->
    <link rel="stylesheet" href="<?= base_url() ?>/play-bootstrap-main/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?= base_url() ?>/play-bootstrap-main/assets/css/animate.css" />
    <link rel="stylesheet" href="<?= base_url() ?>/play-bootstrap-main/assets/css/lineicons.css" />
    <link rel="stylesheet" href="<?= base_url() ?>/play-bootstrap-main/assets/css/ud-styles.css" />

    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= base_url("stisla/assets/css/components.css") ?>">
</head>

<body>
    <!-- ====== Header Start ====== -->
    <header class="ud-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <nav class="navbar navbar-expand-lg">
                        <a class="navbar-brand" href="index.html">
                            <img src="<?= base_url('asset_img/logolsp-trans.png') ?>" alt="Logo" />
                        </a>
                        <button class="navbar-toggler">
                            <span class="toggler-icon"> </span>
                            <span class="toggler-icon"> </span>
                            <span class="toggler-icon"> </span>
                        </button>

                        <div class="navbar-collapse">
                            <ul id="nav" class="navbar-nav mx-auto">
                                <li class="nav-item">
                                    <a class="ud-menu-scroll" href="#home">Beranda</a>
                                </li>

                                <li class="nav-item">
                                    <a class="ud-menu-scroll" href="#about">Tentang Kami</a>
                                </li>
                                <li class="nav-item">
                                    <a class="ud-menu-scroll" href="#contact">Kontak</a>
                                </li>
                                <li class="nav-item">
                                    <a class="ud-menu-scroll" href="<?= site_url('/pendaftaran-uji-kompetensi') ?>">Pendaftaran Uji Kompetensi</a>
                                </li>
                            </ul>
                        </div>

                        <div class="navbar-btn d-none d-sm-inline-block">
                            <a class="ud-main-btn ud-white-btn" href="<?= site_url('/dashboard') ?>">
                                Login
                            </a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <!-- ====== Header End ====== -->

    <!-- ====== Hero Start ====== -->
    <section class="ud-hero" id="home">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-hero-content wow fadeInUp" data-wow-delay=".2s">
                        <h1 class="ud-hero-title">
                            Pendaftaran Sertifikasi Uji Kompetensi LSP SMKN 2 Kuningan
                        </h1>
                        <p class="ud-hero-desc">
                            Layanan Digital Untuk Melakukan Pendaftaran Sertifikasi Uji Kompetensi LSP - P1 SMK Negeri 2 Kuningan Secara Daring.
                        </p>
                        <ul class="ud-hero-buttons">
                            <li>
                                <a href="<?= site_url('/pendaftaran-uji-kompetensi') ?>" rel="nofollow noopener" class="ud-main-btn ud-white-btn">
                                    Daftar Sekarang
                                </a>
                            </li>
                            <li>
                                <a href="#about" rel="nofollow noopener" class="ud-main-btn ud-link-btn">
                                    Pelajari Lebih Lanjut <i class="lni lni-arrow-right"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="ud-hero-image wow fadeInUp" data-wow-delay=".25s">
                        <img src="<?= base_url() ?>/play-bootstrap-main/assets/images/lspapp.png" alt="hero-image" />
                        <img src="<?= base_url() ?>/play-bootstrap-main/assets/images/hero/dotted-shape.svg" alt="shape" class="shape shape-1" />
                        <img src="<?= base_url() ?>/play-bootstrap-main/assets/images/hero/dotted-shape.svg" alt="shape" class="shape shape-2" />
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ====== Hero End ====== -->

    <!-- ====== About Start ====== -->
    <section id="about" class="ud-about">
        <div class="container">
            <div class="ud-about-wrapper wow fadeInUp" data-wow-delay=".2s">
                <div class="ud-about-content-wrapper">
                    <div class="ud-about-content">
                        <span class="tag">Tentang LSP - P1 SMKN 2 Kuningan</span>
                        <h2>Lembaga Sertifikasi Profesi.</h2>
                        <p>
                            Lembaga sertifikasi profesi adalah lembaga pendukung BNSP yang bertanggung jawab melaksanakan sertifikasi kompetensi profesi. LSP – P1 yang dibentuk wajib berbadan hukum dan dibentuk oleh perusahaan yang diregistrasi oleh BNSP.
                        </p>

                        <p>
                            LSP – P1 mempunyai tugas mengembangkan standar kompetensi, melaksanakan uji kompetensi, menerbitkan sertifikat kompetensi serta melakukan verifikasi tempat uji kompetensi.Dalam melaksanakan tugas dan fungsi LSP – P1 mengacu pada pedoman yang dikeluarkan oleh BNSP.
                        </p>
                        <a href="javascript:void(0)" class="ud-main-btn">Tentang Kami</a>
                    </div>
                </div>
                <div class="ud-about-image">
                    <img src="<?= base_url() ?>/play-bootstrap-main/assets/images/about/about-image.svg" alt="about-image" />
                </div>
            </div>
        </div>
    </section>
    <!-- ====== About End ====== -->

    <!-- ====== FAQ Start ====== -->
    <section id="faq" class="ud-faq">
        <div class="shape">
            <img src="<?= base_url() ?>/play-bootstrap-main/assets/images/faq/shape.svg" alt="shape" />
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-section-title text-center mx-auto">
                        <span>FAQ</span>
                        <h2>Pertanyaan Yang Sering Diajukan</h2>
                        <p>
                            Berikut ini adalah daftar pertanyaan yang sering diajukan (FAQ)
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="ud-single-faq wow fadeInUp" data-wow-delay=".1s">
                        <div class="accordion">
                            <button class="ud-faq-btn collapsed" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                <span class="icon flex-shrink-0">
                                    <i class="lni lni-chevron-down"></i>
                                </span>
                                <span>Apa Itu LSP ?</span>
                            </button>
                            <div id="collapseOne" class="accordion-collapse collapse">
                                <div class="ud-faq-body">
                                    Lembaga Sertifikasi Profesi (LSP) adalah lembaga pelaksanaan kegiatan sertifikasi profesi yang memperoleh lisensi dari Badan Nasional Sertifikasi Profesi (BNSP). Lisensi diberikan melalui proses akreditasi oleh BNSP yang menyatakan bahwa LSP bersangkutan telah memenuhi syarat untuk melakukan kegiatan sertifikasi profesi.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ud-single-faq wow fadeInUp" data-wow-delay=".15s">
                        <div class="accordion">
                            <button class="ud-faq-btn collapsed" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                <span class="icon flex-shrink-0">
                                    <i class="lni lni-chevron-down"></i>
                                </span>
                                <span>Apa saja Fungsi dan Tugas LSP ?</span>
                            </button>
                            <div id="collapseTwo" class="accordion-collapse collapse">
                                <div class="ud-faq-body">
                                    LSP Berfungsi sebagai sertifikator yang menyelenggarakan sertifikasi kompetensi. Sedangkan tugas LSP adalah 1) Membuat materi uji kompetensi, 2) Menyediakan tenaga penguji (asesor), 3) Melakukan asesmen, 4) Menyusun kualifikasi dengan mengacu kepada KKNI, 5) Menjaga kinerja asesor dan TUK, 6) Membuat materi uji kompetensi, dan 7) Pengembangan skema sertifikasi.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ud-single-faq wow fadeInUp" data-wow-delay=".2s">
                        <div class="accordion">
                            <button class="ud-faq-btn collapsed" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                <span class="icon flex-shrink-0">
                                    <i class="lni lni-chevron-down"></i>
                                </span>
                                <span>Apa itu LSP P1 ?</span>
                            </button>
                            <div id="collapseThree" class="accordion-collapse collapse">
                                <div class="ud-faq-body">
                                    LSP P1 (LSP Pihak Pertama) dibentuk oleh lembaga pendidikan dan pelatihan (Lemdiklat) yang melatih pesertanya untuk kebutuhan industri. LSP P1 dapat menerbitkan sertifikat kompetensi sesuai dengan skema yang telah divalidasi oleh BNSP. LSP P1 merupakan bagian terpadu dari LPK (lembaga pelatihan kerja) yang memiliki lisensi sebagai LPK independen dari Kemenaker. Oleh karena itu, pelatihan menjadi bagian tidak terpisah dari proses ujian sertifikasi yang dilaksanakan oleh LSP P1 ini. LSP P1 dapat menggunakan SKK-NI maupun SKK-Khusus tergantung dari pilihan mereka.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="ud-single-faq wow fadeInUp" data-wow-delay=".1s">
                        <div class="accordion">
                            <button class="ud-faq-btn collapsed" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                <span class="icon flex-shrink-0">
                                    <i class="lni lni-chevron-down"></i>
                                </span>
                                <span>Apa Itu SKKNI ?</span>
                            </button>
                            <div id="collapseFour" class="accordion-collapse collapse">
                                <div class="ud-faq-body">
                                    SKKNI adalah kepanjangan dari Standar Kompetensi Kerja Nasional Indonesia yang merupakan rumusan kemampuan kerja yang mencakup aspek pengetahuan, keterampilan, dan/atau keahlian serta sikap kerja yang relevan dengan pelaksanaan tugas dan syarat jabatan yang ditetapkan. SKKNI dikembangkan melalui konsultasi dengan industri terkait, untuk memastikan kesesuaian kebutuhan di tempat kerja. SKKNI digunakan terutama untuk merancang dan mengimplementasikan pelatihan kerja, melakukan asesmen (penilaian) keluaran pelatihan, serta asesmen tingkat keterampilan dan keahlian terkini yang dimiliki oleh seseorang. SKKNI ditetapkan oleh Menteri Ketenagakerjaan.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ud-single-faq wow fadeInUp" data-wow-delay=".15s">
                        <div class="accordion">
                            <button class="ud-faq-btn collapsed" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                                <span class="icon flex-shrink-0">
                                    <i class="lni lni-chevron-down"></i>
                                </span>
                                <span>Apa itu Skema Sertifikasi ?</span>
                            </button>
                            <div id="collapseFive" class="accordion-collapse collapse">
                                <div class="ud-faq-body">
                                    Skema Sertifikasi Profesi merupakan persyaratan sertifikasi spesifik yang berkaitan dengan kategori profesi yang ditetapkan dengan menggunakan standar dan aturan khusus yang sama, serta prosedur yang sama. Dalam bahasa sehari-hari merupakan jenis-jenis produk sertifikasi profesi.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ud-single-faq wow fadeInUp" data-wow-delay=".2s">
                        <div class="accordion">
                            <button class="ud-faq-btn collapsed" data-bs-toggle="collapse" data-bs-target="#collapseSix">
                                <span class="icon flex-shrink-0">
                                    <i class="lni lni-chevron-down"></i>
                                </span>
                                <span>Apa manfaat sertifikasi bagi tenaga kerja ?</span>
                            </button>
                            <div id="collapseSix" class="accordion-collapse collapse">
                                <div class="ud-faq-body">
                                    Berikut adalah manfaat sertifikasi bagi tenaga kerja : 1) Membantu tenaga profesi meyakinkan kepada organisasi/industri/kliennya bahwa dirinya kompeten dalam bekerja atau menghasilkan produk atau jasa dan meningkatkan percaya diri tenaga profesi, 2) Membantu tenaga profesi dalam merencanakan karimnya dan mengukur tingkat pencapaian kompetensi dalam proses belajar di lembaga formal maupun secara mandiri,3) Membantu tenaga profesi dalam memenuhi persyaratan regulasi, 4) Membantu pengakuan kompetensilintas sektor dan lintas negara, dan 5) Membantu tenaga profes! dalam promosi profesinya di pasar bursa tenaga kerja.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ====== FAQ End ====== -->

    <!-- ====== Contact Start ====== -->
    <section id="contact" class="ud-contact">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-8 col-lg-7">
                    <div class="ud-contact-content-wrapper">
                        <div class="ud-contact-title">
                            <span>Kontak Kami</span>
                            <h2>
                                Dengan senang hati kami<br />
                                mendengar umpan balik dari Anda
                            </h2>
                        </div>
                        <div class="ud-contact-info-wrapper">
                            <div class="ud-single-info">
                                <div class="ud-info-icon">
                                    <i class="lni lni-map-marker"></i>
                                </div>
                                <div class="ud-info-meta">
                                    <h5>Alamat</h5>
                                    <p>Jalan Sukamulya No.77, Kec. Cigugur, Kuningan, Jawa Barat – 45552</p>
                                </div>
                            </div>
                            <div class="ud-single-info">
                                <div class="ud-info-icon">
                                    <i class="lni lni-envelope"></i>
                                </div>
                                <div class="ud-info-meta">
                                    <h5>Bagaimana Kami Dapat Membantu?</h5>
                                    <p>lspp1smkn2kuningan@gmail.com</p>
                                    <p>0232-872930</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-5">
                    <div class="ud-contact-form-wrapper wow fadeInUp" data-wow-delay=".2s">
                        <h3 class="ud-contact-form-title">Send us a Message</h3>
                        <form class="ud-contact-form" action="<?= site_url('/send-feedback') ?>" method="post">
                            <div class="ud-form-group">
                                <label for="fullName">Nama Lengkap*</label>
                                <input type="text" name="fullname" placeholder="Nama Lengkap" class="<?php if (session('errors.fullname')) : ?>is-invalid<?php endif ?>" value="<?= old('fullname') ?>" />
                                <?php if (session('errors.fullname')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.fullname') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="ud-form-group">
                                <label for="email">Email*</label>
                                <input type="email" name="email" placeholder="Email" class="<?php if (session('errors.email')) : ?>is-invalid<?php endif ?>" value="<?= old('email') ?>" />
                                <?php if (session('errors.email')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.email') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="ud-form-group">
                                <label for="phone">Nomor Handphone*</label>
                                <input type="text" name="phone" placeholder="Nomor Handphone" class="<?php if (session('errors.phone')) : ?>is-invalid<?php endif ?>" value="<?= old('phone') ?>" />
                                <?php if (session('errors.phone')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.phone') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="ud-form-group">
                                <label for="message">Umpan Balik*</label>
                                <textarea name="message" rows="1" placeholder="Masukan Umpan Balik Anda Disini" class="<?php if (session('errors.message')) : ?>is-invalid<?php endif ?>"><?= old('message') ?></textarea>
                                <?php if (session('errors.message')) { ?>
                                    <div class="invalid-feedback">
                                        <?= session('errors.message') ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="ud-form-group mb-0">
                                <button type="submit" class="ud-main-btn">
                                    Kirim
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ====== Contact End ====== -->

    <!-- ====== Footer Start ====== -->
    <footer class="ud-footer wow fadeInUp" data-wow-delay=".15s">
        <div class="shape shape-1">
            <img src="<?= base_url() ?>/play-bootstrap-main/assets/images/footer/shape-1.svg" alt="shape" />
        </div>
        <div class="shape shape-2">
            <img src="<?= base_url() ?>/play-bootstrap-main/assets/images/footer/shape-2.svg" alt="shape" />
        </div>
        <div class="shape shape-3">
            <img src="<?= base_url() ?>/play-bootstrap-main/assets/images/footer/shape-3.svg" alt="shape" />
        </div>
        <div class="ud-footer-widgets">
            <div class="container">
                <div class="row">
                    <div class="col-xl-8 col-lg-4 col-md-6">
                        <div class="ud-widget">
                            <a href="index.html" class="ud-footer-logo">
                                <img src="<?= base_url('asset_img/logolsp-trans.png') ?>" alt="logo" />
                            </a>
                            <p class="ud-widget-desc">
                                Kami menciptakan pengalaman digital untuk melakukan pendaftaran sertifikasi dengan menggunakan teknologi.
                            </p>
                            <ul class="ud-widget-socials">
                                <li>
                                    <a href="https://twitter.com/MusharofChy">
                                        <i class="lni lni-facebook-filled"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://twitter.com/MusharofChy">
                                        <i class="lni lni-twitter-filled"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://twitter.com/MusharofChy">
                                        <i class="lni lni-instagram-filled"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                        <div class="ud-widget">
                            <h5 class="ud-widget-title">Tentang Kami</h5>
                            <ul class="ud-widget-links">
                                <li>
                                    <a href="#home">Beranda</a>
                                </li>
                                <li>
                                    <a href="#about">Tentang Kami</a>
                                </li>
                                <li>
                                    <a href="#contact">Kontak</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('/pendaftaran-uji-kompetensi') ?>">Pendaftaran Uji Kompetensi</a>
                                </li>
                            </ul>
                        </div>
                    </div>


                </div>
            </div>
        </div>
        <div class="ud-footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-8">
                        <ul class="ud-footer-bottom-left">
                            <li>
                                <a href="javascript:void(0)">Privacy policy</a>
                            </li>
                            <li>
                                <a href="javascript:void(0)">Support policy</a>
                            </li>
                            <li>
                                <a href="javascript:void(0)">Terms of service</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <p class="ud-footer-bottom-right">
                            Copyright &copy; 2024 LSP - P1 SMK NEGERI 2 KUNINGAN Created By
                            <a href="#" rel="nofollow">Haikal Jibran A.</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- ====== Footer End ====== -->

    <!-- ====== Back To Top Start ====== -->
    <a href="javascript:void(0)" class="back-to-top">
        <i class="lni lni-chevron-up"> </i>
    </a>
    <!-- ====== Back To Top End ====== -->

    <!-- ====== All Javascript Files ====== -->
    <script src="<?= base_url() ?>/play-bootstrap-main/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url() ?>/play-bootstrap-main/assets/js/wow.min.js"></script>
    <script src="<?= base_url() ?>/play-bootstrap-main/assets/js/main.js"></script>
    <script src="<?= base_url(); ?>/stisla/node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
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
    <script src="<?= base_url("stisla/assets/js/custom.js") ?>"></script>

    <script>

    </script>
</body>

</html>