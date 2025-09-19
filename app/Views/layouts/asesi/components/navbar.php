 <div class="navbar-bg"></div>
 <nav class="navbar navbar-expand-lg main-navbar container">
     <a href="<?= site_url('/') ?>" class="navbar-brand sidebar-gone-hide"><?= env("app_name") ?></a>
     <div class="navbar-nav">
         <a href="#" class="nav-link sidebar-gone-show" data-toggle="sidebar"><i class="fas fa-bars"></i></a>
     </div>
     <ul class="ml-auto navbar-nav navbar-right">
         <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                 <figure class="avatar avatar-sm mr-2 bg-info text-white" data-initial="<?= getInitials(user()->fullname); ?>"></figure>
                 <div class="d-sm-none d-lg-inline-block">Hi, <?= user()->nama_lengkap ?></div>
             </a>
             <div class="dropdown-menu dropdown-menu-right">
                 <div class="dropdown-title">Logged in 5 min ago</div>
                 <a href="<?= site_url('profile/' . user()->id); ?>" class="dropdown-item has-icon">
                     <i class="far fa-user"></i> Profile
                 </a>
                 <div class="dropdown-divider"></div>
                 <a href="<?= base_url('logout'); ?>" class="dropdown-item has-icon text-danger">
                     <i class="fas fa-sign-out-alt"></i> Logout
                 </a>
             </div>
         </li>
     </ul>
 </nav>