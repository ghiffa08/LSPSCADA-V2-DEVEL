<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title><?= $siteTitle ?> &mdash; <?= env("app_name") ?></title>

    <!-- ===== Favicon ===== -->
    <link rel="shortcut icon" href="<?= base_url('asset_img/logolsp.png') ?>" type="image/x-icon">

    <!-- ===== General CSS Files ===== -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/stisla/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/stisla/node_modules/@fortawesome/fontawesome-free/css/all.min.css">

    <!-- ===== CSS Libraries ===== -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/stisla/node_modules/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/stisla/node_modules/selectric/public/selectric.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/stisla/node_modules/select2/dist/css/select2.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/stisla/node_modules/filepond/filepond.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/stisla/node_modules/filepond-plugin-image-preview/filepond-plugin-image-preview.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/stisla/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/stisla/node_modules/datatables.net-select-bs4/css/select.bootstrap4.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/stisla/node_modules/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url() ?>/intro.js/minified/introjs.min.css">

    <!-- ===== Template CSS ===== -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/stisla/assets/css/style.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/stisla/assets/css/components.css">
    <?= $this->renderSection('css') ?>
</head>
<!-- <style>
    :root {
        --primary-color: #3056d3;
        --success-color: #63ed7a;
        --danger-color: #fc544b;
        --gray-color: #e9ecef;
        --text-dark: #34395e;
        --text-muted: #6c757d;
        --transition-default: all 0.3s ease;
        --shadow-sm: 0 .125rem .25rem rgba(0, 0, 0, .075);
        --shadow-md: 0 .5rem 1rem rgba(0, 0, 0, .15);
        --shadow-lg: 0 1rem 3rem rgba(0, 0, 0, .175);
        --border-radius: .25rem;
    }

    /* --- Base Stepper Layout --- */
    .stepper-wrapper {
        margin-bottom: 2rem;
    }

    .stepper {
        display: flex;
        position: relative;
        background-color: #fff;
        border-radius: var(--border-radius);
        /* box-shadow: var(--shadow-md); */
        margin-bottom: 2rem;
        overflow: hidden;
    }

    /* --- Step Items --- */
    .stepper-item {
        flex: 1;
        position: relative;
        padding: 1rem 0.5rem;
        cursor: pointer;
        text-align: center;
        z-index: 2;
        transition: var(--transition-default);
    }

    /* Step Connector Line */
    .stepper-item:not(:last-child)::after {
        content: '';
        position: absolute;
        height: 2px;
        background-color: var(--gray-color);
        top: 50%;
        width: 100%;
        right: 0;
        transform: translateY(-50%);
        z-index: 1;
    }

    /* Circle Indicator Styles */
    .step-counter {
        position: relative;
        z-index: 5;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin: 0 auto;
        background-color: var(--gray-color);
        color: var(--text-muted);
        font-weight: 600;
        border: 3px solid #fff;
        transition: var(--transition-default);
        box-shadow: var(--shadow-sm);
    }

    /* Step Label */
    .step-label {
        display: block;
        margin-top: 0.75rem;
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-muted);
        transition: var(--transition-default);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Step Description */
    .step-desc {
        color: var(--text-muted);
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: none;
    }

    /* --- Active State --- */
    .stepper-item.active .step-counter {
        background-color: var(--primary-color);
        color: #fff;
        box-shadow: 0 0 0 4px rgba(103, 119, 239, 0.25);
    }

    .stepper-item.active .step-label {
        color: var(--primary-color);
        font-weight: 700;
    }

    /* --- Completed State --- */
    .stepper-item.completed .step-counter {
        background-color: var(--success-color);
        color: #fff;
    }

    .stepper-item.completed:not(:last-child)::after {
        background-color: var(--success-color);
    }

    /* --- Hover Effects --- */
    .stepper-item:not(.active):hover .step-counter {
        transform: scale(1.05);
        background-color: #dee2e6;
    }

    /* --- Steps Content --- */
    .step-content {
        position: relative;
        display: none;
        padding: 1.5rem;
        background-color: #fff;
        /* border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm); */
        border: 1px solid var(--gray-color);
        margin-bottom: 1.5rem;
        animation: fadeIn 0.5s ease forwards;
    }

    .step-content.active {
        display: block;
    }

    /* --- Step Navigation --- */
    .step-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
    }

    /* --- Responsive Design --- */
    @media (max-width: 991.98px) {
        .stepper {
            flex-wrap: wrap;
        }

        .stepper-item {
            flex-basis: 33.33%;
            margin-bottom: 0.5rem;
        }

        .stepper-item:not(:last-child)::after {
            width: 50%;
        }
    }

    @media (max-width: 767.98px) {
        .stepper-item {
            flex-basis: 50%;
        }
    }

    @media (max-width: 575.98px) {
        .stepper {
            flex-direction: column;
        }

        .stepper-item {
            width: 100%;
            padding: 0.75rem 0;
            text-align: left;
            display: flex;
            align-items: center;
        }

        .stepper-item:not(:last-child)::after {
            height: 100%;
            width: 2px;
            top: 50%;
            left: 20px;
        }

        .step-counter {
            margin: 0 1rem 0 0;
        }

        .step-label {
            margin-top: 0;
        }

        .step-desc {
            display: block;
        }
    }
</style> -->