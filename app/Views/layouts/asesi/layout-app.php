<!DOCTYPE html>
<html lang="en">

<!-- ===== Header ===== -->

<?= $this->include("layouts/asesi/components/header"); ?>

<!-- ===== End Header ===== -->

<body class="layout-3">
    <div id="app">
        <div class="main-wrapper container">

            <!-- ===== Navbar ===== -->

            <?= $this->include("layouts/asesi/components/navbar"); ?>

            <!-- ===== End Navbar ===== -->

            <!-- ===== Navbar Secondary ===== -->

            <?= $this->include("layouts/asesi/components/navbar-secondary"); ?>

            <!-- ===== End Navbar Secondary ===== -->

            <!-- ===== Main Content ===== -->

            <div class="main-content">
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