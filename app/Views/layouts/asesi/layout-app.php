<!DOCTYPE html>
<html lang="en">

<!-- ===== Header ===== -->

<?= $this->include("layouts/asesi/components/header"); ?>

<!-- ===== End Header ===== -->

<?= $this->renderSection("styles") ?>

<body class="layout-4 bg-secondary">
    <div id="app">
        <div class="main-wrapper container">

            <!-- ===== Navbar ===== -->

            <?= $this->include("layouts/asesi/components/navbar"); ?>


            <!-- ===== End Navbar ===== -->

            <!-- ===== Navbar Secondary ===== -->

            <?= $this->include("layouts/asesi/components/navbar-secondary"); ?>

            <!-- ===== End Navbar Secondary ===== -->

            <div class="main-sidebar">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand sidebar-gone-show"><a href="index.html">Stisla</a></div>
                    <ul class="sidebar-menu">
                        <li class="menu-header">Dashboard</li>
                        <li class="<?= strpos(current_url(), '/asesi/dashboard') !== false ? 'active' : '' ?>"><a class="nav-link" href="<?= site_url("/asesi/dashboard") ?>"><i class="fas fa-fire"></i> <span>Dashboard</span></a></li>
                        <li class="<?= strpos(current_url(), '/profile') !== false ? 'active' : '' ?>"><a class="nav-link" href="<?= site_url("/asesi/profile") ?>"><i class="fas fa-user"></i> <span>Data diri</span></a></li>
                        <li class="<?= strpos(current_url(), '/list-asesmen-mandiri') !== false || strpos(current_url(), '/asesmen-mandiri') !== false ? 'active' : '' ?>"><a class="nav-link" href="<?= site_url("/list-asesmen-mandiri") ?>"><i class="fas fa-paper-plane"></i> <span>Asesmen Mandiri</span></a></li>
                        <!-- <li class="<?= strpos(current_url(), '/asesi/asesmen') !== false ? 'active' : '' ?>"><a class="nav-link" href="<?= site_url("/asesi/asesmen") ?>"><i class="fas fa-paper-plane"></i> <span>Permohonan Asesmen</span></a></li>
                        <li class="<?= strpos(current_url(), '/asesi/asesmen') !== false ? 'active' : '' ?>"><a class="nav-link" href="<?= site_url("/asesi/asesmen") ?>"><i class="fas fa-paper-plane"></i> <span>Pertanyaan Tertulis</span></a></li>
                        <li class="<?= strpos(current_url(), '/asesi/asesmen') !== false ? 'active' : '' ?>"><a class="nav-link" href="<?= site_url("/asesi/asesmen") ?>"><i class="fas fa-paper-plane"></i> <span>Umpan Balik</span></a></li> -->

                        <!-- <li class="dropdown">
                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-fire"></i><span>Dashboard</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="index-0.html">General Dashboard</a></li>
                                <li><a class="nav-link" href="index.html">Ecommerce Dashboard</a></li>
                            </ul>
                        </li>

                        <li class="dropdown">
                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-th"></i> <span>Bootstrap</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="bootstrap-alert.html">Alert</a></li>
                                <li><a class="nav-link" href="bootstrap-badge.html">Badge</a></li>
                                <li><a class="nav-link" href="bootstrap-breadcrumb.html">Breadcrumb</a></li>
                                <li><a class="nav-link" href="bootstrap-buttons.html">Buttons</a></li>
                                <li><a class="nav-link" href="bootstrap-card.html">Card</a></li>
                                <li><a class="nav-link" href="bootstrap-carousel.html">Carousel</a></li>
                                <li><a class="nav-link" href="bootstrap-collapse.html">Collapse</a></li>
                                <li><a class="nav-link" href="bootstrap-dropdown.html">Dropdown</a></li>
                                <li><a class="nav-link" href="bootstrap-form.html">Form</a></li>
                                <li><a class="nav-link" href="bootstrap-list-group.html">List Group</a></li>
                                <li><a class="nav-link" href="bootstrap-media-object.html">Media Object</a></li>
                                <li><a class="nav-link" href="bootstrap-modal.html">Modal</a></li>
                                <li><a class="nav-link" href="bootstrap-nav.html">Nav</a></li>
                                <li><a class="nav-link" href="bootstrap-navbar.html">Navbar</a></li>
                                <li><a class="nav-link" href="bootstrap-pagination.html">Pagination</a></li>
                                <li><a class="nav-link" href="bootstrap-popover.html">Popover</a></li>
                                <li><a class="nav-link" href="bootstrap-progress.html">Progress</a></li>
                                <li><a class="nav-link" href="bootstrap-table.html">Table</a></li>
                                <li><a class="nav-link" href="bootstrap-tooltip.html">Tooltip</a></li>
                                <li><a class="nav-link" href="bootstrap-typography.html">Typography</a></li>
                            </ul>
                        </li> -->

                    </ul>

                </aside>
            </div>

            <!-- ===== Main Content ===== -->


            <div class="main-content">
                <!-- Profile Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="avatar avatar-xl">
                                            <?php
                                            // Call the helper to get asesi data
                                            $asesiData = asesi_data();
                                            // Determine the photo URL, using a placeholder as a fallback
                                            $photoUrl = ($asesiData && !empty($asesiData['pas_foto']))
                                                ? base_url('uploads/asesi_dokumen/' . $asesiData['pas_foto'])
                                                : 'https://placehold.co/150x150/EBF0F9/7F8AB0?text=Foto';
                                            ?>
                                            <img alt="image" src="<?= $photoUrl ?>" class="rounded-circle">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <p class="mb-1 text-muted">Selamat Datang,</p>
                                        <h6 class="mb-0 font-weight-bold"><?= esc(user()->nama_lengkap ?? 'Nama Pengguna'); ?></h6>
                                    </div>
                                    <div class="col-auto">
                                        <a href="<?= site_url("/asesi/profile") ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="section-header">
                        <h1><?= $siteTitle ?></h1>
                        <div class="section-header-breadcrumb">
                            <div class="breadcrumb-item active d-none d-sm-block"><a href="<?= site_url("/asesi") ?>">Beranda</a></div>
                            <div class="breadcrumb-item d-none d-sm-block"><?= $siteTitle ?></div>
                        </div>
                    </div>

                    <div class="section-body">

                        <?= $this->renderSection("content") ?>
                    </div>
                </section>
            </div>

            <!-- ===== End Main Content ===== -->

            <!-- ===== Footer ===== -->

            <?= $this->include("layouts/asesi/components/footer") ?>

            <!-- ===== End Footer ===== -->
        </div>
    </div>

    <!-- ===== Script JS ===== -->

    <?= $this->include("layouts/asesi/utility/js") ?>

    <!-- ===== End Script JS ===== -->

    <!-- ===== Script reCAPTCHA ===== -->

    <?= $this->renderSection("js") ?>

    <!-- ===== End Script reCAPTCHA ===== -->

</body>

</html>