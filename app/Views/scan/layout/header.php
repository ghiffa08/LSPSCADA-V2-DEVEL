<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title><?= $siteTitle ?> &mdash; <?= env("app_name") ?></title>

    <!-- ===== Favicon ===== -->
    <link rel="shortcut icon" href="<?= base_url('asset_img/logolsp.png') ?>" type="image/x-icon">

    <!-- ===== General CSS Files ===== -->
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/@fortawesome/fontawesome-free/css/all.min.css">

    <!-- ===== CSS Libraries ===== -->
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/node_modules/chocolat/dist/css/chocolat.css">


    <!-- ===== Template CSS ===== -->
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/assets/css/style.css">
    <link rel="stylesheet" href="<?= base_url() ?>/stisla/assets/css/components.css">

</head>
<style>
    body {
        margin: 0px;
        padding: 0px;
    }

    .background-navbar {
        content: " ";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 70px;
        background-color: #6777ef;
        z-index: -1;
    }
</style>