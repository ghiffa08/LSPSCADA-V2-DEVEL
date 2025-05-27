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

            <?php if (in_groups('Admin')) { ?>
                <li class="menu-header">Master Data</li>
                <li class="dropdown">
                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-fire"></i><span>Kelola Kompetensi</span></a>
                    <ul class="dropdown-menu">
                        <li><a class="nav-link" href="<?= site_url('/skema') ?>"><span>Skema</span></a></li>
                        <li><a class="nav-link" href="<?= site_url('/unit') ?>"><span>Unit</span></a></li>
                        <li><a class="nav-link" href="<?= site_url('/elemen') ?>"><span>Elemen</span></a></li>
                        <li><a class="nav-link" href="<?= site_url('/subelemen') ?>"><span>Subelemen</span></a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-fire"></i><span>Kelola User</span></a>
                    <ul class="dropdown-menu">
                        <li><a class="nav-link" href="<?= site_url('/admin') ?>"><span>Admin</span></a></li>
                        <li><a class="nav-link" href="<?= site_url('/asesor') ?>"><span>Asesor</span></a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="nav-link has-dropdown"><i class="fas fa-fire"></i><span>Kelola Asesmen</span></a>
                    <ul class="dropdown-menu">
                        <li><a class="nav-link" href="<?= site_url('/asesmen') ?>"><i class="fas fa-pencil-ruler"></i> <span>Asesmen</span></a></li>
                        <li><a class="nav-link" href="<?= site_url('/tempat-tuk') ?>"><i class="fas fa-pencil-ruler"></i> <span>TUK</span></a></li>
                        <li><a class="nav-link" href="<?= site_url('/settanggal') ?>"><i class="fas fa-pencil-ruler"></i> <span>Set Tanggal</span></a></li>
                        <li><a class="nav-link" href="<?= site_url('/persyaratan') ?>"><i class="fas fa-pencil-ruler"></i> <span>Input persyaratan</span></a></li>
                    </ul>
                </li>
                <li><a class="nav-link" href="<?= site_url('/kelola_apl1') ?>"><i class="fas fa-pencil-ruler"></i> <span>Kelola FR.APL.01</span></a></li>
                <li><a class="nav-link" href="<?= site_url('/kelola_apl2') ?>"><i class="fas fa-pencil-ruler"></i> <span>Kelola FR.APL.02</span></a></li>
                <li><a class="nav-link" href="<?= site_url('/kelola_apl1/validasi') ?>"><i class="fas fa-pencil-ruler"></i> <span>Validasi FR.APL.01</span></a></li>
                <li><a class="nav-link" href="<?= site_url('/persetujuan-asesmen') ?>"><i class="fas fa-pencil-ruler"></i> <span>Persetujuan Asesmen</span></a></li>
                <li><a class="nav-link" href="<?= site_url('/monitoring-asesi') ?>"><i class="fas fa-pencil-ruler"></i> <span>Monitoring</span></a></li>

            <?php }  ?> <?php if (in_groups('Asesor')) { ?>
                <li><a class="nav-link" href="<?= site_url('/asesor/dashboard') ?>"><i class="fas fa-tachometer-alt"></i> <span>Dashboard Asesor</span></a></li>
                <li><a class="nav-link" href="<?= site_url('/kelola_apl2') ?>"><i class="fas fa-pencil-ruler"></i> <span>Kelola FR.APL.02</span></a></li>
                <li><a class="nav-link" href="<?= site_url('/kelola_apl2/validasi') ?>"><i class="fas fa-pencil-ruler"></i> <span>Validasi FR.APL.02</span></a></li>
                <li><a class="nav-link" href="<?= site_url('/persetujuan-asesmen') ?>"><i class="fas fa-pencil-ruler"></i> <span>Persetujuan Asesmen</span></a></li>
                <li><a class="nav-link" href="<?= site_url('/monitoring-asesi') ?>"><i class="fas fa-pencil-ruler"></i> <span>Monitoring</span></a></li>
            <?php }  ?>

        </ul>


    </aside>
</div>