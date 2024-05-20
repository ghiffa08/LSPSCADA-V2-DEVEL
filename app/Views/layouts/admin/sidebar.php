<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="<?= site_url("/") ?>"><?= env("app_name") ?></a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="index.html"><?= substr(env("app_name"), 0, 3) ?></a>
        </div>

        <ul class="sidebar-menu">
            <li><a class="nav-link" href="<?= site_url('/dashboard') ?>"><i class="fas fa-fire"></i> <span>Dashboard</span></a></li>

            <?php if (in_groups('Peserta')) { ?>
                <li><a class="nav-link" href="<?= site_url('/skema-siswa') ?>"><i class="fas fa-fire"></i> <span>Pilih Skema</span></a></li>
                <li><a class="nav-link" href="<?= site_url('/apl1') ?>"><i class="fas fa-fire"></i> <span>FR. APL 1</span></a></li>
                <li><a class="nav-link" href="<?= site_url('/asesmen-mandiri') ?>"><i class="fas fa-fire"></i> <span>FR. APL 2</span></a></li>
            <?php }  ?>

            <?php if (in_groups('Admin')) { ?>
                <li class="menu-header">Master Data</li>
                <li class="dropdown">
                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-fire"></i><span>Kelola Kompetensi</span></a>
                    <ul class="dropdown-menu">
                        <li><a class="nav-link" href="<?= site_url('/skema') ?>"><span>Kelola Skema</span></a></li>
                        <li><a class="nav-link" href="<?= site_url('/unit') ?>"><span>Kelola Unit</span></a></li>
                        <li><a class="nav-link" href="<?= site_url('/elemen') ?>"><span>Kelola Elemen</span></a></li>
                        <li><a class="nav-link" href="<?= site_url('/subelemen') ?>"><span>Kelola Subelemen</span></a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-fire"></i><span>Master Data</span></a>
                    <ul class="dropdown-menu">
                        <li><a class="nav-link" href="<?= site_url('/tempat-tuk') ?>"><span>Kelola Tempat TUK</span></a></li>
                        <li><a class="nav-link" href="<?= site_url('/asesor') ?>"><span>Kelola Asesor</span></a></li>
                        <li><a class="nav-link" href="<?= site_url('/peserta') ?>"><span>Kelola Peserta</span></a></li>
                        <li><a class="nav-link" href="<?= site_url('/admin') ?>"><span>Kelola Admin</span></a></li>
                    </ul>
                </li>
                <li><a class="nav-link" href="<?= site_url('/persyaratan') ?>"><i class="fas fa-pencil-ruler"></i> <span>Input persyaratan</span></a></li>
            <?php }  ?>

        </ul>

        <div class="mt-4 mb-4 p-3 hide-sidebar-mini">
            <a href="https://getstisla.com/docs" class="btn btn-primary btn-lg btn-block btn-icon-split">
                <i class="fas fa-rocket"></i> Documentation
            </a>
        </div>
    </aside>
</div>