<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title><?= lang('Auth.loginTitle') ?> | <?= env("app_name") ?></title>

    <link rel="shortcut icon" href="<?= base_url() ?>/assets/img/favicon.png" type="image/x-icon">

    <!-- General CSS Files -->
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="<?= base_url(); ?>/stisla/node_modules/bootstrap-social/assets/css/bootstrap.css">
    <link rel="stylesheet" href="<?= base_url(); ?>/stisla/node_modules/bootstrap-social/bootstrap-social.css">

    <!-- Template CSS -->
    <link rel="stylesheet" href="<?= base_url(); ?>/stisla/assets/css/style.css">
    <link rel="stylesheet" href="<?= base_url(); ?>/stisla/assets/css/components.css">
</head>

<body>
    <div id="app">
        <?= $this->renderSection('main') ?>
    </div>

    <!-- General JS Scripts -->
    <script src="<?= base_url("/stisla/node_modules/jquery/dist/jquery.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/node_modules/popper.js/dist/umd/popper.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/node_modules/bootstrap/dist/js/bootstrap.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/node_modules/jquery.nicescroll/dist/jquery.nicescroll.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/node_modules/moment/min/moment.min.js") ?>"></script>
    <script src="<?= base_url("/stisla/assets/js/stisla.js") ?>"></script>

    <!-- JS Libraies -->

    <!-- Template JS File -->
    <script src="<?= base_url(); ?>/stisla/assets/js/scripts.js"></script>
    <script src="<?= base_url(); ?>/stisla/assets/js/custom.js"></script>

    <!-- Page Specific JS File -->
</body>

</html>