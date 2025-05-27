<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= esc((!empty($siteTitle) ? $siteTitle : 'Page')) ?> &mdash; <?= esc(env("app_name")) ?></title>
    <link rel="shortcut icon" href="<?= base_url('assets/images/logolsp.png') ?>" type="image/x-icon">

    <!-- CSS Files -->
    <?= $this->include('layouts/admin/css') ?>

    <?= $this->renderSection('css') ?>

</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <!-- Navbar -->
            <?= $this->include("layouts/admin/navbar") ?>

            <!-- Sidebar -->
            <?= service('sidebar')->render(session()->get('user_permissions') ?? []) ?>

            <!-- Main Content -->
            <div class="main-content">
                <section class="section">
                    <div class="section-header">
                        <h1><?= esc($siteTitle) ?></h1>
                    </div>

                    <div class="section-body">
                        <!-- Main Section -->
                        <?= $this->renderSection("content") ?>
                    </div>
                </section>
            </div>

            <!-- Footer -->
            <?= $this->include("layouts/admin/footer") ?>
        </div>
    </div>

    <!-- Modals -->
    <?= $this->renderSection('modals') ?>

    <!-- JS Files -->
    <?= $this->include('layouts/admin/js') ?>

    <!-- Page Specific JS -->
    <?= $this->renderSection('js') ?>
</body>

</html>