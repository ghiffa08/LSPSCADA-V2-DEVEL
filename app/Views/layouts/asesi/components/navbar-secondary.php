<nav class="navbar navbar-secondary navbar-expand-lg">
    <div class="container">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#secondaryNavbar"
                aria-controls="secondaryNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="secondaryNavbar">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="<?= site_url('/asesi') ?>" class="nav-link <?= current_url() == site_url('/asesi') ? 'active' : '' ?>">
                        <i class="fas fa-home mr-2"></i><span>Beranda</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('/asesi/pengajuan') ?>" class="nav-link <?= strpos(current_url(), '/asesi/pengajuan') !== false ? 'active' : '' ?>">
                        <i class="fas fa-file-signature mr-2"></i><span>Pengajuan Sertifikasi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('/asesi/asesmen-mandiri') ?>" class="nav-link <?= strpos(current_url(), '/asesi/asesmen-mandiri') !== false ? 'active' : '' ?>">
                        <i class="fas fa-tasks mr-2"></i><span>Asesmen Mandiri</span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle <?= strpos(current_url(), '/asesi/dokumen') !== false ? 'active' : '' ?>"
                       aria-expanded="false">
                        <i class="fas fa-folder-open mr-2"></i><span>Dokumen</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="<?= site_url('/asesi/dokumen/upload') ?>" class="dropdown-item <?= current_url() == site_url('/asesi/dokumen/upload') ? 'active' : '' ?>">
                                Upload Dokumen
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('/asesi/dokumen/status') ?>" class="dropdown-item <?= current_url() == site_url('/asesi/dokumen/status') ? 'active' : '' ?>">
                                Status Dokumen
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('/asesi/jadwal') ?>" class="nav-link <?= strpos(current_url(), '/asesi/jadwal') !== false ? 'active' : '' ?>">
                        <i class="fas fa-calendar-alt mr-2"></i><span>Jadwal Asesmen</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('/asesi/feedback') ?>" class="nav-link <?= strpos(current_url(), '/asesi/feedback') !== false ? 'active' : '' ?>">
                        <i class="fas fa-comments mr-2"></i><span>Umpan Balik</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('/logout') ?>" class="nav-link">
                        <i class="fas fa-sign-out-alt mr-2"></i><span>Keluar</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>