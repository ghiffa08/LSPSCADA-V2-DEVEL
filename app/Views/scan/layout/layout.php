<!DOCTYPE html>
<html lang="en">

<?= $this->include('scan/layout/header') ?>

<body>

    <?= $this->include('scan/layout/navbar') ?>

    <div style="height: 100vh;" class="d-flex justify-content-center align-items-center">

        <div class="container">

            <?= $this->renderSection('content') ?>

        </div>

    </div>
    <!-- ===== Footer ===== -->

    <?= $this->include("layouts/landingpage/footer") ?>

    <!-- ===== End Footer ===== -->
    </div>


    <!-- General JS Scripts -->
    <script src="<?= base_url("/stisla/node_modules/jquery/dist/jquery.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/node_modules/popper.js/dist/umd/popper.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/node_modules/bootstrap/dist/js/bootstrap.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/node_modules/jquery.nicescroll/dist/jquery.nicescroll.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/node_modules/moment/min/moment.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/assets/js/stisla.js") ?>"></script>
    <!-- JS Libraies -->

    <!-- Page Specific JS File -->
    <script src="<?= base_url("/stisla/node_modules/chocolat/dist/js/jquery.chocolat.min.js") ?>"></script>

    <!-- Template JS File -->
    <script src="<?= base_url("/stisla/assets/js/scripts.js") ?>"></script>
    <script src="<?= base_url("/stisla/assets/js/custom.js") ?>"></script>
</body>

</html>