<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =====================================================
// KONFIGURASI DASAR
// =====================================================
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Dashboard');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

// =====================================================
// ROUTES PUBLIK (TANPA AUTENTIKASI)
// =====================================================

// Halaman Utama dan Publik
$routes->get('/', 'HomeController::index');
$routes->get('skema-sertifikasi', 'HomeController::skema');

// Pendaftaran dan Pengajuan
$routes->get('pendaftaran-uji-kompetensi', 'PengajuanAsesmenController::index');
$routes->post('store-pengajuan', 'PengajuanAsesmenController::store');

// Asesmen Mandiri
$routes->get('asesmen-mandiri/(:any)', 'HomeController::asesmen/$1');
$routes->post('store-asesmen', 'HomeController::store_asesmen');
$routes->post('send-feedback', 'HomeController::send_feedback');

// Pemindaian Tanda Tangan
$routes->group('scan', function ($routes) {
    $routes->get('tanda-tangan-asesi/(:segment)', 'APL1Controller::scan_ttd_asesi/$1');
    $routes->get('tanda-tangan-admin/(:segment)', 'APL1Controller::scan_ttd_admin/$1');
    $routes->get('tanda-tangan-asesor/(:segment)', 'APL2Controller::scan_ttd_asesor/$1');
});


// Google OAuth 
$routes->get('auth/google', 'OAuthController::google');
$routes->get('OAuth/proses', 'OAuthController::proses');

// =====================================================
// ROUTES TERAUTENTIKASI
// =====================================================
$routes->group('', ['filter' => 'login'], function ($routes) {
    // Dashboard router: redirect ke dashboard sesuai role
    $routes->get('dashboard', 'DashboardRouterController::index');

    // Dashboard dan Profil Pengguna
    $routes->get('settings', 'Settings::index');
    $routes->get('profile', 'AsesiController::profile');

    // Pembuatan PDF
    $routes->group('pdf', function ($routes) {
        $routes->get('pmo', 'PMOController::pdf');
        $routes->get('feedback', 'FeedbackController::pdf');
        $routes->get('observasi/(:num)', 'CeklistObservasiController::pdf/$1');
        $routes->get('feedback/(:num)', 'FeedbackAsesiController::pdf/$1');
        $routes->get('apl1/(:num)', 'APL1Controller::pdf/$1');
        $routes->get('rekaman/(:num)', 'RekamanAsesmenController::pdf/$1');
        $routes->get('rekaman', 'RekamanAsesmenController::pdf');
        $routes->get('laporan', 'LaporanAsesmenController::pdf');
    });

    // Manajemen Asesmen
    $routes->group('asesmen', function ($routes) {
        $routes->get('/', 'AsesmenController::index');
        $routes->post('save', 'Api\Asesmen::save');
        $routes->post('import', 'AsesmenController::import');
        $routes->get('delete/(:num)', 'Api\Asesmen::delete/$1');
        $routes->get('getById/(:num)', 'Api\Asesmen::getById/$1');
        $routes->post('get-data-table', 'Api\Asesmen::getDataTable');

        // Persetujuan Asesmen
        $routes->group('persetujuan', function ($routes) {
            $routes->get('/', 'AKController::index');
            $routes->post('store', 'AKController::store');
            $routes->get('pdf/(:any)', 'AKController::pdf/$1');
            $routes->post('import', 'AKController::import');
            $routes->post('update', 'AKController::update');
            $routes->post('delete', 'AKController::delete');
        });
    });

    // Manajemen Admin
    $routes->group('admin', function ($routes) {
        $routes->get('/', 'AdminController::index');
        $routes->get('dashboard', 'AdminController::dashboard');
        $routes->post('store', 'AdminController::store');
        $routes->post('import', 'AdminController::import');
        $routes->post('update', 'AdminController::update');
        $routes->post('delete', 'AdminController::delete');
        $routes->get('profile/(:any)', 'UserController::profile/$1');

        // Kelola Users Routes       
        $routes->get('kelola-users', 'KelolaUsersController::index');
        $routes->get('deleted-users', 'KelolaUsersController::deletedUsers');
        $routes->post('kelola-users/data', 'KelolaUsersController::getUsersData');
        $routes->post('deleted-users/data-table', 'KelolaUsersController::getDeletedUsersData');
        $routes->get('kelola-users/stats', 'KelolaUsersController::getStats');
        $routes->get('deleted-users/get-statistics', 'KelolaUsersController::getDeletedStats');
        $routes->get('kelola-users/details/(:num)', 'KelolaUsersController::getUserDetails/$1');
        $routes->get('deleted-users/details/(:num)', 'KelolaUsersController::getUserArchivedDetails/$1');
        $routes->post('kelola-users/update', 'KelolaUsersController::updateUser');
        $routes->post('kelola-users/toggle-status/(:num)', 'KelolaUsersController::toggleStatus/$1');
        $routes->post('kelola-users/delete/(:num)', 'KelolaUsersController::deleteUser/$1');
        $routes->post('kelola-users/restore/(:num)', 'KelolaUsersController::restoreUser/$1');
        $routes->post('deleted-users/restore/(:num)', 'KelolaUsersController::restoreUser/$1');
        $routes->post('deleted-users/permanent-delete/(:num)', 'KelolaUsersController::permanentlyDeleteUser/$1');
        $routes->post('deleted-users/batch-action', 'KelolaUsersController::batchAction');

        // User Management Routes
        $routes->group('users', function ($routes) {
            $routes->get('/', 'UserManagementController::index');
            $routes->get('create', 'UserManagementController::create');
            $routes->post('store', 'UserManagementController::store');
            $routes->get('edit/(:num)', 'UserManagementController::edit/$1');
            $routes->post('update/(:num)', 'UserManagementController::update/$1');
            $routes->post('delete/(:num)', 'UserManagementController::delete/$1');
            $routes->post('toggle-status/(:num)', 'UserManagementController::toggleStatus/$1');
            $routes->get('stats', 'UserManagementController::stats');
        });

        // Observasi Admin
        $routes->group('observasi', function ($routes) {
            $routes->get('/', 'CeklistObservasiController::index');
            $routes->get('delete/(:num)', 'Api\Observasi::delete/$1');
            $routes->get('getById/(:num)', 'Api\Observasi::getById/$1');
            $routes->post('get-data-table', 'Api\Observasi::getDataTable');
            $routes->get('loadObservasi', 'Api\Observasi::loadObservasi');
        });

        // Feedback Asesi Admin
        $routes->group('feedback-asesi', function ($routes) {
            $routes->get('/', 'FeedbackAsesiController::index');
            $routes->get('getById/(:num)', 'Api\FeedbackAsesi::getById/$1');
            $routes->get('delete/(:num)', 'Api\FeedbackAsesi::delete/$1');
            $routes->post('save', 'Api\FeedbackAsesi::save');
            $routes->post('get-data-table', 'Api\FeedbackAsesi::getDataTable');
        });

        // Komponen Feedback
        $routes->group('komponen-feedback', function ($routes) {
            $routes->get('/', 'KomponenFeedbackController::index');
            $routes->post('save', 'Api\KomponenFeedback::save');
            $routes->post('import', 'KomponenFeedbackController::import');
            $routes->get('download-template', 'KomponenFeedbackController::downloadTemplate');
            $routes->get('delete/(:num)', 'Api\KomponenFeedback::delete/$1');
            $routes->get('getById/(:num)', 'Api\KomponenFeedback::getById/$1');
            $routes->post('update-order', 'Api\KomponenFeedback::updateOrder');
            $routes->post('get-data-table', 'Api\KomponenFeedback::getDataTable');
            $routes->get('getMaxOrder', 'Api\KomponenFeedback::getMaxOrder');
            $routes->post('updateOrder', 'Api\KomponenFeedback::updateOrder');
            $routes->get('getAll', 'Api\KomponenFeedback::getAll'); // New route for getting all komponen
        });
    });

    // Manajemen Asesi
    $routes->group('asesi', function ($routes) {
        $routes->get('/', 'AsesiController::index');
        $routes->get('dashboard', 'AsesiController::index');
        $routes->get('profile', 'AsesiController::profile');
        $routes->post('store', 'AsesiController::store');
        $routes->post('import', 'AsesiController::import');
        $routes->post('save', 'AsesiController::save');
        $routes->post('update-user-info', 'AsesiController::updateUserInfo');
        $routes->post('delete', 'AsesiController::delete');

        // Fitur Umpan Balik Asesi
        $routes->group('feedback', function ($routes) {
            $routes->get('/', 'FeedbackAsesiController::asesiIndex');
            $routes->post('save', 'FeedbackAsesiController::asesiSave');
        });
    });

    // Manajemen Asesor
    $routes->group('asesor', function ($routes) {
        $routes->get('/', 'AsesorController::index');
        $routes->get('dashboard', 'AsesorController::dashboard');
        $routes->post('store', 'AsesorController::store');
        $routes->post('update', 'AsesorController::update');
        $routes->post('delete', 'AsesorController::delete');        // Observasi Asesor
        $routes->group('observasi', function ($routes) {
            $routes->get('/', 'CeklistObservasiController::index');
            $routes->get('ceklist', 'CeklistObservasiController::create');
            $routes->get('loadObservasi', 'Api\Observasi::loadObservasi');
            $routes->get('getSkemaDetails', 'Api\Observasi::getSkemaDetails');
            $routes->get('getAsesiByAsesmen', 'CeklistObservasiController::getAsesiByAsesmen');
            $routes->post('save', 'Api\Observasi::save');
        });

        // New REST API for Observasi (Production-ready)
        $routes->group('api/observasi', function ($routes) {
            $routes->get('/', 'Api\ObservasiV2Controller::index');
            $routes->get('(:num)', 'Api\ObservasiV2Controller::show/$1');
            $routes->post('/', 'Api\ObservasiV2Controller::create');
            $routes->put('(:num)', 'Api\ObservasiV2Controller::update/$1');
            $routes->get('kuk-structure/(:num)', 'Api\ObservasiV2Controller::getKukStructure/$1');
            $routes->post('batch', 'Api\ObservasiV2Controller::batchSave');
            $routes->get('(:num)/summary', 'Api\ObservasiV2Controller::getSummary/$1');
            $routes->post('validate', 'Api\ObservasiV2Controller::validateObservationData');
        });

        // Feedback Asesi Asesor
        $routes->group('feedback', function ($routes) {
            $routes->get('/', 'FeedbackAsesiController::create');
            $routes->get('getSkemaDetails', 'Api\FeedbackAsesi::getSkemaDetails');
            $routes->get('loadFeedback', 'Api\FeedbackAsesi::loadFeedback');
            $routes->post('save', 'Api\FeedbackAsesi::save');
        });

        // PERBAIKAN: Rekaman Asesmen routes
        $routes->group('rekaman-asesmen', function ($routes) {
            $routes->get('/', 'RekamanAsesmenController::index');
            $routes->get('create', 'RekamanAsesmenController::create');
            $routes->get('getAsesiByAsesmen', 'RekamanAsesmenController::getAsesiByAsesmen');
            $routes->get('loadRekamanAsesmen', 'RekamanAsesmenController::loadRekamanAsesmen');
            $routes->post('store', 'RekamanAsesmenController::store'); // PERBAIKAN: Tambahkan ini
            $routes->get('pdf/(:num)', 'RekamanAsesmenController::pdf/$1');
        });

        // PMO routes
        $routes->group('pmo', function ($routes) {
            $routes->get('/', 'PMOController::index');
            $routes->get('create', 'PMOController::index'); // Alias untuk create
            $routes->get('getAsesiByAsesmen', 'PMOController::getAsesiByAsesmen'); // SAMA SEPERTI OBSERVASI
            $routes->get('loadPMO', 'PMOController::loadPMO');
            $routes->post('store', 'PMOController::store');
            $routes->get('pdf/(:num)', 'PMOController::pdf/$1');
        });
    });

    // Manajemen Data Master
    $routes->group('master', function ($routes) {
        // Skema
        $routes->group('skema', function ($routes) {
            $routes->get('/', 'SkemaController::index');
            $routes->post('save', 'Api\Skema::save');
            $routes->get('get/(:num)', 'Api\Skema::get/$1');
            $routes->post('import', 'SkemaController::import');
            $routes->get('download-template', 'SkemaController::downloadTemplate');
            $routes->get('delete/(:num)', 'Api\Skema::delete/$1');
            $routes->get('getById/(:num)', 'Api\Skema::getById/$1');
            $routes->post('get-data-table', 'Api\Skema::getDataTable');
        });

        // Unit Kompetensi
        $routes->group('unit', function ($routes) {
            $routes->get('/', 'UnitController::index');
            $routes->post('save', 'Api\Unit::save');
            $routes->post('import', 'UnitController::import');
            $routes->get('download-template', 'UnitController::downloadTemplate');
            $routes->get('getById/(:num)', 'Api\Unit::getById/$1');
            $routes->get('delete/(:num)', 'Api\Unit::delete/$1');
            $routes->post('get-data-table', 'Api\Unit::getDataTable');
        });

        // Elemen
        $routes->group('elemen', function ($routes) {
            $routes->get('/', 'ElemenController::index');
            $routes->post('save', 'Api\Elemen::save');
            $routes->post('import', 'ElemenController::import');
            $routes->get('download-template', 'ElemenController::downloadTemplate');
            $routes->post('update', 'ElemenController::update');
            $routes->get('delete/(:num)', 'Api\Elemen::delete/$1');
            $routes->get('getById/(:num)', 'Api\Elemen::getById/$1');
            $routes->post('get-data-table', 'Api\Elemen::getDataTable');
        });

        // KUK (Kriteria Unjuk Kerja)
        $routes->group('kuk', function ($routes) {
            $routes->get('/', 'KUKController::index');
            $routes->post('save', 'Api\KUK::save');
            $routes->post('import', 'KUKController::import');
            $routes->get('download-template', 'KUKController::downloadTemplate');
            $routes->get('edit/(:num)', 'KUKController::edit/$1');
            $routes->post('update', 'KUKController::update');
            $routes->get('delete/(:num)', 'Api\KUK::delete/$1');
            $routes->get('getById/(:num)', 'Api\KUK::getById/$1');
            $routes->post('get-data-table', 'Api\KUK::getDataTable');
        });

        // Kelompok Kerja
        $routes->group('kelompok-kerja', function ($routes) {
            $routes->get('/', 'KelompokKerjaController::index');
            $routes->get('detail/(:num)', 'KelompokKerjaController::detail/$1');
            $routes->post('save', 'KelompokKerjaController::save');
            $routes->post('import', 'KelompokKerjaController::import');
            $routes->post('update', 'KelompokKerjaController::update');
            $routes->delete('delete/(:num)', 'Api\KelompokKerja::delete/$1');
            $routes->post('get-data-table', 'Api\KelompokKerja::getDataTable');
        });

        // TUK (Tempat Uji Kompetensi)
        $routes->group('tuk', function ($routes) {
            $routes->get('/', 'TUKController::index');
            $routes->post('save', 'Api\TUK::save');
            $routes->get('delete/(:num)', 'Api\TUK::delete/$1');
            $routes->get('getById/(:num)', 'Api\TUK::getById/$1');
            $routes->post('get-data-table', 'Api\TUK::getDataTable');
        });

        // Persyaratan
        $routes->group('persyaratan', function ($routes) {
            $routes->get('/', 'PersyaratanController::index');
            $routes->post('store', 'PersyaratanController::store');
            $routes->post('update', 'PersyaratanController::update');
            $routes->post('delete', 'PersyaratanController::delete');
        });

        // Pengaturan Tanggal
        $routes->group('tanggal', function ($routes) {
            $routes->get('/', 'SettanggalController::index');
            $routes->post('save', 'Api\SetTanggal::save');
            $routes->get('delete/(:num)', 'Api\SetTanggal::delete/$1');
            $routes->get('getById/(:num)', 'Api\SetTanggal::getById/$1');
            $routes->post('get-data-table', 'Api\SetTanggal::getDataTable');
        });

        // Pertanyaan PMO
        $routes->group('pertanyaan-pmo', function ($routes) {
            $routes->get('/', 'PMOPertanyaanController::index');
            $routes->post('save', 'Api\PMOPertanyaan::save');
            $routes->post('import', 'PMOPertanyaanController::import');
            $routes->get('download-template', 'PMOPertanyaanController::downloadTemplate');
            $routes->get('delete/(:num)', 'Api\PMOPertanyaan::delete/$1');
            $routes->get('getById/(:num)', 'Api\PMOPertanyaan::getById/$1');
            $routes->post('get-data-table', 'Api\PMOPertanyaan::getDataTable');
        });
    });

    // Manajemen Formulir APL
    $routes->group('apl', function ($routes) {
        // Formulir APL01
        $routes->group('1', function ($routes) {
            $routes->get('/', 'APL1Controller::index');
            $routes->post('store', 'APL1Controller::store');
            $routes->get('validasi', 'APL1Controller::validasi');
            $routes->get('send-email-validasi', 'APL1Controller::email_validasi');
            $routes->post('store-validasi', 'APL1Controller::store_validasi');
            $routes->post('store-email-validasi', 'APL1Controller::send_email_validasi');
            $routes->post('store-email-validasi-by-date', 'APL1Controller::send_email_validasi_by_date');
            $routes->post('delete', 'APL1Controller::delete');
            $routes->get('pdf/(:any)', 'APL1Controller::pdf/$1');
        });

        // Formulir APL02
        $routes->group('2', function ($routes) {
            $routes->get('/', 'APL2Controller::index');
            $routes->post('store', 'APL2Controller::store');
            $routes->get('validasi', 'APL2Controller::validasi');
            $routes->post('validasi-store', 'APL2Controller::store_validasi');
            $routes->get('send-email-validasi', 'APL2Controller::email_validasi');
            $routes->post('store-email-validasi', 'APL2Controller::send_email_validasi');
            $routes->post('store-email-validasi-by-date', 'APL2Controller::send_email_validasi_by_date');
            $routes->post('delete', 'APL2Controller::delete');
            $routes->get('pdf/(:any)', 'APL2Controller::pdf/$1');
        });
    });

    // Monitoring dan Feedback
    $routes->get('monitoring', 'MonitoringController::index');
    $routes->group('feedback', function ($routes) {
        $routes->get('/', 'FeedbackController::index');
        $routes->post('delete', 'FeedbackController::delete');
    });

    // Umpan Balik Asesi
    $routes->group('asesor/feedback', function ($routes) {
        $routes->get('/', 'FeedbackAsesiController::index');
        $routes->get('create', 'FeedbackAsesiController::create');
        $routes->get('edit/(:num)', 'FeedbackAsesiController::edit/$1');
        $routes->post('save', 'FeedbackAsesiController::save');
    });

    $routes->group('asesi/feedback', function ($routes) {
        $routes->get('/', 'FeedbackAsesiController::asesiIndex');
        $routes->post('save', 'FeedbackAsesiController::asesiSave');
    });

    // Rekaman Asesmen
    $routes->group('rekaman-asesmen', function ($routes) {
        $routes->get('/', 'RekamanAsesmenController::index'); // untuk admin
        $routes->get('create', 'RekamanAsesmenController::create'); // untuk asesor
        $routes->post('store', 'RekamanAsesmenController::store');
        $routes->post('load', 'RekamanAsesmenController::loadRekamanAsesmen');
        $routes->get('pdf/(:num)', 'RekamanAsesmenController::pdf/$1');
        $routes->get('delete/(:num)', 'RekamanAsesmenController::delete/$1');
    });

    // Komponen Umpan Balik
    $routes->group('komponen-feedback', function ($routes) {
        $routes->get('/', 'KomponenFeedbackController::index');
        $routes->post('import', 'KomponenFeedbackController::import');
        $routes->get('download-template', 'KomponenFeedbackController::downloadTemplate');
    });

    // PDF Routes for Feedback Asesi
    $routes->get('pdf/feedback/(:num)', 'FeedbackAsesiController::pdf/$1');

    // Manajemen Menu (dengan filter auth)
    $routes->group('menu', ['filter' => 'auth'], function ($routes) {
        $routes->get('/', 'Menu::index');
        $routes->get('items/(:num)', 'Menu::items/$1');        // Group CRUD
        $routes->match(['GET', 'POST'], 'create-group', 'Menu::createGroup');
        $routes->match(['GET', 'POST'], 'edit-group/(:num)', 'Menu::editGroup/$1');
        $routes->get('delete-group/(:num)', 'Menu::deleteGroup/$1');

        // Item CRUD
        $routes->match(['GET', 'POST'], 'create-item/(:num)', 'Menu::createItem/$1');
        $routes->match(['GET', 'POST'], 'edit-item/(:num)', 'Menu::editItem/$1');
        $routes->get('delete-item/(:num)', 'Menu::deleteItem/$1');
        $routes->post('reorder-items', 'Menu::reorderItems');
    });

    // Profile Routes
    $routes->group('profile', ['filter' => 'login'], function ($routes) {
        $routes->get('/', 'ProfileController::index');
        $routes->post('update', 'ProfileController::update');
        $routes->post('change-password', 'ProfileController::changePassword');
        $routes->post('upload-avatar', 'ProfileController::uploadAvatar');
    });
});

// =====================================================
// ROUTES API
// =====================================================
$routes->group('api', function ($routes) {
    // Data Asesmen
    $routes->post('get-jadwal', 'AsesmenController::getJadwal');
    $routes->post('get-tuk', 'AsesmenController::getTuk');
    $routes->post('get-unit', 'Api\Unit::getUnit');
    $routes->post('get-unit-json', 'Api\Unit::getUnitJSON');
    $routes->post('get-elemen', 'Api\ELemen::getElemen');
    $routes->get('get-observasi', 'CeklistObservasiController::getObservasiData');
    $routes->get('get-asesi', 'CeklistObservasiController::getAsesiList');

    // Feedback Asesi API
    $routes->group('feedback-asesi', function ($routes) {
        $routes->get('get-skema-details', 'Api\FeedbackAsesi::getSkemaDetails');
        $routes->get('get-komponen', 'Api\FeedbackAsesi::getKomponen');
        $routes->get('load-feedback', 'Api\FeedbackAsesi::loadFeedback');
        $routes->get('check-existing', 'Api\FeedbackAsesi::checkExisting');
        $routes->post('save', 'Api\FeedbackAsesi::save');
        $routes->get('getById/(:num)', 'Api\FeedbackAsesi::getById/$1');
        $routes->get('delete/(:num)', 'Api\FeedbackAsesi::delete/$1');
        $routes->post('get-data-table', 'Api\FeedbackAsesi::getDataTable');
    });

    // Data Lokasi
    $routes->post('kabupaten', 'APL1Controller::kabupaten');
    $routes->post('kecamatan', 'APL1Controller::kecamatan');
    $routes->post('desa', 'APL1Controller::desa');

    // Rekaman Asesmen API
    $routes->group('rekaman-asesmen', function ($routes) {
        $routes->post('load', 'RekamanAsesmenController::loadRekamanAsesmen');
        $routes->get('get-data-table', 'Api\RekamanAsesmen::getDataTable');
    });

    // Tanggal Validasi
    $routes->post('get-date-validated-apl1', 'APL1Controller::getDateValidated');
    $routes->post('get-date-validated-apl2', 'APL2Controller::getDateValidated');

    // Tanda Tangan
    $routes->get('signature-show/(:segment)', 'DocumentController::signatureShow/$1');
});

// =====================================================
// ROUTE TESTING (SEMENTARA)
// =====================================================
$routes->get('tes', 'TesController::index');

// =====================================================
// API ROUTTES
// =====================================================
$routes->group('api', function ($routes) {
    // User Management API
    $routes->group('user-management', function ($routes) {
        $routes->post('get-data-table', 'Api\UserManagement::getDataTable');
        $routes->get('get-user-statistics-with-deleted', 'Api\UserManagement::getUserStatisticsWithDeleted');
        $routes->post('get-user-by-id', 'Api\UserManagement::getUserByIdPost');
        $routes->post('create-admin-user', 'Api\UserManagement::createAdminUser');
        $routes->post('create-asesor-user', 'Api\UserManagement::createAsesorUser');
        $routes->post('update-profile', 'Api\UserManagement::updateProfile');
        $routes->post('update-user', 'Api\UserManagement::updateUser');
        $routes->post('update-status', 'Api\UserManagement::updateStatus');
        $routes->post('soft-delete-user', 'Api\UserManagement::softDeleteUser');

        // Asesor management routes
        $routes->get('get-asesor-by-user-id', 'Api\UserManagement::getAsesorByUserId');
        $routes->get('get-all-asesor', 'Api\UserManagement::getAllAsesor');
        $routes->post('update-asesor', 'Api\UserManagement::updateAsesor');
        $routes->get('get-asesor-statistics', 'Api\UserManagement::getAsesorStatistics');

        // Skema routes
        $routes->get('get-active-skemas', 'Api\UserManagement::getActiveSkemas');
    });

    // Add this new route for asesor skema
    $routes->get('get-skema-asesor', 'Api\AsesorSkema::getSkemaAsesor');
});
