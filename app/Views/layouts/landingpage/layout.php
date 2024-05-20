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

</head>

<body>
    <script src="<?= base_url() ?>/assets/static/js/initTheme.js"></script>
    <div id="app">
        <div id="main" class="layout-horizontal">

            <!-- navbar -->
            <?= $this->include("layouts/landingpage/navbar"); ?>
            <!-- end -->

            <div class="content-wrapper container">

                <div class="page-heading">
                    <h3><?= $siteTitle ?></h3>
                </div>
                <div class="page-content">

                    <!-- Main Section -->
                    <?= $this->renderSection("content"); ?>
                    <!-- end -->

                </div>

            </div>

            <footer>
                <div class="container">
                    <div class="footer clearfix mb-0 text-muted">
                        <div class="float-start">
                            <p>2024 &copy; LSP SMK NEGERI 2 KUNINGAN</p>
                        </div>
                        <div class="float-end">
                            <p>Crafted with <span class="text-danger"><i class="bi bi-heart"></i></span> by <a href="https://saugi.me">Saugi</a></p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="<?= base_url() ?>/assets/static/js/components/dark.js"></script>
    <script src="<?= base_url() ?>/assets/static/js/pages/horizontal-layout.js"></script>
    <script src="<?= base_url() ?>/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>

    <script src="<?= base_url() ?>/assets/compiled/js/app.js"></script>


    <script src="<?= base_url() ?>/assets/extensions/apexcharts/apexcharts.min.js"></script>
    <script src="<?= base_url() ?>/assets/static/js/pages/dashboard.js"></script>



</body>

</html>