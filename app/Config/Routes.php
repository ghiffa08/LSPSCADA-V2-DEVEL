<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ===================================================================
// #1. KONFIGURASI DASAR
// ===================================================================
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Dashboard');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();


// ===================================================================
// #2. ROUTES PUBLIK (Tidak Memerlukan Autentikasi)
// ===================================================================

// --- Halaman Utama & Landing Page ---
$routes->get('/', 'HomeController::index');
$routes->get('landingpage', 'LandingpageController::index');
$routes->get('skema-sertifikasi', 'LandingpageController::skema');
$routes->get('pendaftaran-uji-kompetensi', 'LandingpageController::ujikom');
$routes->get('monitoring-asesi', 'MonitoringController::index');

// --- Proses Pendaftaran & Asesmen Publik ---
$routes->get('asesmen', 'PengajuanAsesmenController::skema');
$routes->get('asesmen-detail/(:any)', 'PengajuanAsesmenController::skema_detail/$1');
$routes->get('asesmen-daftar/(:any)', 'PengajuanAsesmenController::skema_daftar/$1');
$routes->post('store-pengajuan', 'LandingpageController::store_pengajuan');
$routes->get('asesmen/get-step/(:num)', 'HomeController::getAsesmenStep/$1');
$routes->post('asesmen/validate-step', 'HomeController::validateStep');

// --- Asesmen Mandiri Publik ---
$routes->get('list-asesmen-mandiri', 'AsesmenMandiriController::index');
$routes->get('asesmen-mandiri/filter', 'AsesmenMandiriController::filterAsesmen');
$routes->get('asesmen-mandiri/(:any)', 'AsesmenMandiriController::asesmen/$1');
$routes->post('asesmen-mandiri/store', 'AsesmenMandiriController::store_asesmen');
$routes->post('send-feedback', 'AsesmenMandiriController::send_feedback');

// --- Pemindaian Tanda Tangan (QR Code) ---
$routes->group('scan', static function ($routes) {
    $routes->get('tanda-tangan-asesi/(:segment)', 'APL1Controller::scan_ttd_asesi/$1');
    $routes->get('tanda-tangan-admin/(:segment)', 'APL1Controller::scan_ttd_admin/$1');
    $routes->get('tanda-tangan-asesor/(:segment)', 'APL2Controller::scan_ttd_asesor/$1');
});

// --- API Publik (AJAX Endpoints) ---
$routes->post('pengajuan-submit', 'Api\PengajuanAsesmen::submit_pengajuan_ajax');
$routes->post('get-jadwal', 'AsesmenController::getJadwal');
$routes->post('get-tuk', 'AsesmenController::getTuk');
$routes->post('getUnit', 'UnitController::getUnit');
$routes->post('getElemen', 'ElemenController::getElemen');
$routes->post('kabupaten', 'AsesiController::getKabupaten');
$routes->post('kecamatan', 'AsesiController::getKecamatan');
$routes->post('kelurahan', 'AsesiController::getKelurahan');
$routes->post('getSekolah', 'Sekolah::getSekolah');
$routes->get('signature-show/(:segment)', 'DocumentController::signatureShow/$1');


// ===================================================================
// #3. ROUTES AUTENTIKASI (Login, OAuth, etc.)
// ===================================================================
$routes->get('auth/google', 'OAuthController::google');
$routes->get('OAuth/proses', 'OAuthController::proses');


// ===================================================================
// #4. ROUTES TERPROTEKSI (Memerlukan Login - Filter 'login')
// ===================================================================
$routes->group('', ['filter' => 'login'], static function ($routes) {

    // --------------------------------------------------------------------
    // Rute Umum & Profil Pengguna
    // --------------------------------------------------------------------
    $routes->get('dashboard', 'DashboardRouterController::index');
    $routes->get('settings', 'Settings::index');

    // Grup profil umum
    $routes->group('profile', static function ($routes) {
        $routes->get('/', 'ProfileController::index');
        $routes->post('update', 'ProfileController::update');
        $routes->post('change-password', 'ProfileController::changePassword');
        $routes->post('upload-avatar', 'ProfileController::uploadAvatar');
    });

    // --------------------------------------------------------------------
    // Generasi PDF
    // --------------------------------------------------------------------
    $routes->group('pdf', static function ($routes) {
        $routes->get('pmo/(:num)', 'CeklistPMOController::pdf/$1');
        $routes->get('feedback', 'FeedbackController::pdf');
        $routes->get('feedback/(:num)', 'FeedbackAsesiController::pdf/$1');
        $routes->get('observasi/(:num)', 'CeklistObservasiController::pdf/$1');
        $routes->get('apl1/(:any)', 'PengajuanAsesmenController::generateAPL1/$1');
        $routes->get('rekaman-asesmen/(:num)', 'RekamanAsesmenController::pdf/$1');
        $routes->get('rekaman', 'RekamanAsesmenController::pdf');
        $routes->get('laporan', 'LaporanAsesmenController::pdf');
        $routes->get('pertanyaan-tertulis', 'PertanyaanTertulisController::pdf');
    });

    // --------------------------------------------------------------------
    // Manajemen Admin
    // --------------------------------------------------------------------
    $routes->group('admin', static function ($routes) {
        $routes->get('/', 'AdminController::index');
        $routes->get('dashboard', 'AdminController::dashboard');
        $routes->post('store', 'AdminController::store');
        $routes->post('import', 'AdminController::import');
        $routes->post('update', 'AdminController::update');
        $routes->post('delete', 'AdminController::delete');
        $routes->get('profile/(:any)', 'UserController::profile/$1');

        $routes->group('kelola-users', static function ($routes) {
            $routes->get('/', 'KelolaUsersController::index');
            $routes->post('data', 'Api\UserManagement::getDataTable');
            $routes->get('stats', 'Api\UserManagement::getStatistics');
            $routes->get('details/(:num)', 'KelolaUsersController::getUserDetails/$1');
            $routes->post('update', 'KelolaUsersController::updateUser');
            $routes->post('toggle-status/(:num)', 'KelolaUsersController::toggleStatus/$1');
            $routes->post('delete/(:num)', 'KelolaUsersController::deleteUser/$1');
            $routes->post('restore/(:num)', 'KelolaUsersController::restoreUser/$1');
        });

        $routes->group('deleted-users', static function ($routes) {
            $routes->get('/', 'KelolaUsersController::deletedUsers');
            $routes->post('data-table', 'KelolaUsersController::getDeletedUsersData');
            $routes->get('get-statistics', 'KelolaUsersController::getDeletedStats');
            $routes->get('details/(:num)', 'KelolaUsersController::getUserArchivedDetails/$1');
            $routes->post('restore/(:num)', 'KelolaUsersController::restoreUser/$1');
            $routes->post('permanent-delete/(:num)', 'KelolaUsersController::permanentlyDeleteUser/$1');
            $routes->post('batch-action', 'KelolaUsersController::batchAction');
        });

        $routes->group('users', static function ($routes) {
            $routes->get('/', 'UserManagementController::index');
            $routes->get('create', 'UserManagementController::create');
            $routes->post('store', 'UserManagementController::store');
            $routes->get('edit/(:num)', 'UserManagementController::edit/$1');
            $routes->post('update/(:num)', 'UserManagementController::update/$1');
            $routes->post('delete/(:num)', 'UserManagementController::delete/$1');
            $routes->post('toggle-status/(:num)', 'UserManagementController::toggleStatus/$1');
            $routes->get('stats', 'UserManagementController::stats');
        });

        $routes->group('observasi', static function ($routes) {
            $routes->get('/', 'CeklistObservasiController::index');
            $routes->get('delete/(:num)', 'Api\Observasi::delete/$1');
            $routes->get('getById/(:num)', 'Api\Observasi::getById/$1');
            $routes->post('get-data-table', 'Api\Observasi::getDataTable');
            $routes->get('loadObservasi', 'Api\Observasi::loadObservasi');
        });

        $routes->group('feedback-asesi', static function ($routes) {
            $routes->get('/', 'FeedbackAsesiController::index');
            $routes->get('getById/(:num)', 'Api\FeedbackAsesi::getById/$1');
            $routes->get('delete/(:num)', 'Api\FeedbackAsesi::delete/$1');
            $routes->post('save', 'Api\FeedbackAsesi::save');
            $routes->post('get-data-table', 'Api\FeedbackAsesi::getDataTable');
        });

        $routes->group('komponen-feedback', static function ($routes) {
            $routes->get('/', 'KomponenFeedbackController::index');
            $routes->post('save', 'Api\KomponenFeedback::save');
            $routes->post('import', 'KomponenFeedbackController::import');
            $routes->get('download-template', 'KomponenFeedbackController::downloadTemplate');
            $routes->get('delete/(:num)', 'Api\KomponenFeedback::delete/$1');
            $routes->get('getById/(:num)', 'Api\KomponenFeedback::getById/$1');
            $routes->post('update-order', 'Api\KomponenFeedback::updateOrder');
            $routes->post('get-data-table', 'Api\KomponenFeedback::getDataTable');
            $routes->get('getMaxOrder', 'Api\KomponenFeedback::getMaxOrder');
            $routes->get('getAll', 'Api\KomponenFeedback::getAll');
        });

        $routes->group('rekaman-asesmen', static function ($routes) {
            $routes->get('/', 'RekamanAsesmenController::index');
            $routes->get('view/(:num)', 'RekamanAsesmenController::view/$1');
            $routes->get('pdf/(:num)', 'RekamanAsesmenController::pdf/$1');
            $routes->post('batch-pdf', 'RekamanAsesmenController::batchPdf');
            $routes->post('approve/(:num)', 'RekamanAsesmenController::approve/$1');
            $routes->post('reject/(:num)', 'RekamanAsesmenController::reject/$1');
            $routes->post('bulk-approve', 'RekamanAsesmenController::bulkApprove');
            $routes->delete('bulk-delete', 'RekamanAsesmenController::bulkDelete');
        });

        $routes->get('pertanyaan-tertulis', 'PertanyaanTertulisController::index');
    });

    // --------------------------------------------------------------------
    // Manajemen Asesor
    // --------------------------------------------------------------------
    $routes->group('asesor', static function ($routes) {
        $routes->get('/', 'AsesorController::index');
        $routes->get('dashboard', 'AsesorController::dashboard');
        $routes->post('store', 'AsesorController::store');
        $routes->post('update', 'AsesorController::update');
        $routes->post('delete', 'AsesorController::delete');

        $routes->group('observasi', static function ($routes) {
            $routes->get('/', 'CeklistObservasiController::index');
            $routes->get('ceklist', 'CeklistObservasiController::create');
            $routes->get('loadObservasi', 'Api\Observasi::loadObservasi');
            $routes->get('getSkemaDetails', 'Api\Observasi::getSkemaDetails');
            $routes->get('getValidatedApl1ByAsesmen', 'CeklistObservasiController::getValidatedApl1ByAsesmen');
            $routes->get('getApl1Details', 'Api\Observasi::getApl1Details');
            $routes->get('checkExistingObservation', 'Api\Observasi::checkExistingObservation');
            $routes->post('save', 'Api\Observasi::save');
            $routes->post('saveSettings', 'Api\Observasi::saveSettings');
            $routes->post('saveSingleKUK', 'Api\Observasi::saveSingleKUK');
            $routes->post('saveBatchKUK', 'Api\Observasi::saveBatchKUK');
            $routes->get('getStatistics', 'Api\Observasi::getStatistics');
            $routes->get('getProgressReport', 'Api\Observasi::getProgressReport');
            $routes->delete('delete/(:num)', 'Api\Observasi::deleteObservasi/$1');
            $routes->get('view/(:num)', 'CeklistObservasiController::view/$1');
            $routes->get('print/(:num)', 'CeklistObservasiController::printObservasi/$1');
            $routes->get('export/(:num)', 'CeklistObservasiController::exportObservasi/$1');
        });

        $routes->group('feedback', static function ($routes) {
            $routes->get('/', 'CeklistFeedbackController::index', ['as' => 'feedback-index']);
            $routes->get('form', 'CeklistFeedbackController::create', ['as' => 'feedback-form']);
            $routes->get('pdf/(:num)', 'CeklistFeedbackController::pdf/$1', ['as' => 'feedback-pdf']);
            $routes->group('api', static function ($routes) {
                $routes->get('get-skema-details', 'Api\FeedbackAsesi::getSkemaDetails');
                $routes->get('load-feedback', 'Api\FeedbackAsesi::loadFeedback');
                $routes->post('save', 'Api\FeedbackAsesi::save');
                $routes->delete('delete/(:num)', 'Api\FeedbackAsesi::deleteFeedback/$1');
            });
        });

        $routes->group('rekaman-asesmen', static function ($routes) {
            $routes->get('/', 'RekamanAsesmenController::ceklist');
            $routes->get('getAsesiByAsesmen', 'RekamanAsesmenController::getAsesiByAsesmen');
            $routes->get('loadRekamanAsesmen', 'RekamanAsesmenController::loadRekamanAsesmen');
            $routes->post('store', 'RekamanAsesmenController::store');
            $routes->post('/', 'Api\RekamanAsesmenApi::saveUnit');
        });

        $routes->group('pmo', static function ($routes) {
            $routes->get('/', 'CeklistPMOController::index');
            $routes->get('create', 'CeklistPMOController::show');
            $routes->get('getAsesiByAsesmen', 'PMOController::getAsesiByAsesmen');
            $routes->get('loadPMO', 'Api\PMO::loadPmo');
            $routes->post('store', 'PMOController::store');
            $routes->get('pdf/(:num)', 'PMOController::pdf/$1');
            $routes->post('batch-pdf', 'CeklistPMOController::batchPdf');
        });

        $routes->get('header-konfigurasi', 'HeaderKonfigurasiController::index');
    });

    // --------------------------------------------------------------------
    // Manajemen Asesi
    // --------------------------------------------------------------------
    $routes->group('asesi', static function ($routes) {
        $routes->get('/', 'AsesiController::index');
        $routes->get('dashboard', 'AsesiController::index');
        $routes->get('dashboard/data', 'AsesiController::getDashboardData');
        $routes->get('profile', 'AsesiController::profile');
        $routes->post('validateField', 'AsesiController::validateField');
        $routes->post('getSekolah', 'AsesiController::getSekolah');
        $routes->post('upload-documents', 'AsesiController::uploadDocuments');
        $routes->post('store', 'AsesiController::store');
        $routes->post('import', 'AsesiController::import');
        $routes->post('save', 'AsesiController::save');
        $routes->post('update-user-info', 'AsesiController::updateUserInfo');
        $routes->post('delete', 'AsesiController::delete');

        $routes->group('feedback', static function ($routes) {
            $routes->get('/', 'FeedbackAsesiController::asesiIndex');
            $routes->post('save', 'FeedbackAsesiController::asesiSave');
        });

        $routes->get('pmo', 'CeklistPMOController::index');
        $routes->get('pmo-ceklist/(:any)', 'CeklistPMOController::show/$1');

        $routes->get('pertanyaan-tertulis/cbt/(:any)', 'PertanyaanTertulisController::show/$1');
    });

    // --------------------------------------------------------------------
    // Manajemen Data Master
    // --------------------------------------------------------------------
    $routes->group('master', static function ($routes) {
        $routes->group('skema', static function ($routes) {
            $routes->get('/', 'SkemaController::index');
            $routes->post('save', 'Api\Skema::save');
            $routes->get('get/(:num)', 'Api\Skema::get/$1');
            $routes->post('import', 'SkemaController::import');
            $routes->get('download-template', 'SkemaController::downloadTemplate');
            $routes->get('delete/(:num)', 'Api\Skema::delete/$1');
            $routes->get('getById/(:num)', 'Api\Skema::getById/$1');
            $routes->post('get-data-table', 'Api\Skema::getDataTable');
        });

        $routes->group('unit', static function ($routes) {
            $routes->get('/', 'UnitController::index');
            $routes->post('save', 'Api\Unit::save');
            $routes->post('import', 'UnitController::import');
            $routes->get('download-template', 'UnitController::downloadTemplate');
            $routes->get('getById/(:num)', 'Api\Unit::getById/$1');
            $routes->get('delete/(:num)', 'Api\Unit::delete/$1');
            $routes->post('get-data-table', 'Api\Unit::getDataTable');
        });

        $routes->group('elemen', static function ($routes) {
            $routes->get('/', 'ElemenController::index');
            $routes->post('save', 'Api\Elemen::save');
            $routes->post('import', 'ElemenController::import');
            $routes->get('download-template', 'ElemenController::downloadTemplate');
            $routes->post('update', 'ElemenController::update');
            $routes->get('delete/(:num)', 'Api\Elemen::delete/$1');
            $routes->get('getById/(:num)', 'Api\Elemen::getById/$1');
            $routes->post('get-data-table', 'Api\Elemen::getDataTable');
        });

        $routes->group('kuk', static function ($routes) {
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

        $routes->group('asesmen', static function ($routes) {
            $routes->get('/', 'AsesmenController::index');
            $routes->post('save', 'Api\Asesmen::save');
            $routes->post('import', 'AsesmenController::import');
            $routes->delete('delete/(:num)', 'Api\Asesmen::delete/$1');
            $routes->get('getById/(:num)', 'Api\Asesmen::getById/$1');
            $routes->post('get-data-table', 'Api\Asesmen::getDataTable');
        });

        $routes->group('kelompok-kerja', static function ($routes) {
            $routes->get('/', 'KelompokKerjaController::index');
            $routes->get('detail/(:num)', 'KelompokKerjaController::detail/$1');
            $routes->post('save', 'KelompokKerjaController::save');
            $routes->post('import', 'KelompokKerjaController::import');
            $routes->post('update', 'KelompokKerjaController::update');
            $routes->get('delete/(:num)', 'Api\KelompokKerja::delete/$1');
            $routes->post('get-data-table', 'Api\KelompokKerja::getDataTable');
        });

        $routes->group('tuk', static function ($routes) {
            $routes->get('/', 'TUKController::index');
            $routes->post('save', 'Api\TUK::save');
            $routes->get('delete/(:num)', 'Api\TUK::delete/$1');
            $routes->get('getById/(:num)', 'Api\TUK::getById/$1');
            $routes->post('get-data-table', 'Api\TUK::getDataTable');
        });

        $routes->group('persyaratan', static function ($routes) {
            $routes->get('/', 'PersyaratanController::index');
            $routes->post('store', 'PersyaratanController::store');
            $routes->post('update', 'PersyaratanController::update');
            $routes->post('delete', 'PersyaratanController::delete');
        });

        $routes->group('tanggal', static function ($routes) {
            $routes->get('/', 'SettanggalController::index');
            $routes->post('save', 'Api\SetTanggal::save');
            $routes->get('delete/(:num)', 'Api\SetTanggal::delete/$1');
            $routes->get('getById/(:num)', 'Api\SetTanggal::getById/$1');
            $routes->post('get-data-table', 'Api\SetTanggal::getDataTable');
        });

        $routes->group('pmo-pertanyaan', static function ($routes) {
            $routes->get('/', 'PMOPertanyaanController::index');
            $routes->post('save', 'Api\PMOPertanyaan::save');
            $routes->post('import', 'PMOPertanyaanController::import');
            $routes->get('download-template', 'PMOPertanyaanController::downloadTemplate');
            $routes->get('delete/(:num)', 'Api\PMOPertanyaan::delete/$1');
            $routes->get('getById/(:num)', 'Api\PMOPertanyaan::getById/$1');
            $routes->post('get-data-table', 'Api\PMOPertanyaan::getDataTable');
        });

        $routes->group('pertanyaan-tertulis-soal', static function ($routes) {
            $routes->get('/', 'PertanyaanTertulisSoalController::index');
            $routes->post('save', 'Api\PertanyaanTertulisSoal::save');
            $routes->post('import', 'PertanyaanTertulisSoalController::import');
            $routes->get('download-template', 'PertanyaanTertulisSoalController::downloadTemplate');
            $routes->get('delete/(:num)', 'Api\PertanyaanTertulisSoal::delete/$1');
            $routes->get('getById/(:num)', 'Api\PertanyaanTertulisSoal::getById/$1');
            $routes->post('get-data-table', 'Api\PertanyaanTertulisSoal::getDataTable');
        });
    });

    // --------------------------------------------------------------------
    // Manajemen Formulir & Proses Inti Asesmen
    // --------------------------------------------------------------------
    $routes->group('asesmen', static function ($routes) {
        $routes->get('/', 'AsesmenController::index');
        $routes->post('save', 'Api\Asesmen::save');
        $routes->post('import', 'AsesmenController::import');
        $routes->delete('delete/(:num)', 'Api\Asesmen::delete/$1');
        $routes->get('getById/(:num)', 'Api\Asesmen::getById/$1');
        $routes->post('get-data-table', 'Api\Asesmen::getDataTable');

        $routes->group('persetujuan', static function ($routes) {
            $routes->get('/', 'AKController::index');
            $routes->post('store', 'AKController::store');
            $routes->get('pdf/(:any)', 'AKController::pdf/$1');
            $routes->post('import', 'AKController::import');
            $routes->post('update', 'AKController::update');
            $routes->post('delete', 'AKController::delete');
        });
    });

    $routes->group('apl', static function ($routes) {
        $routes->group('1', static function ($routes) {
            $routes->get('/', 'APL1Controller::index');
            $routes->post('store', 'APL1Controller::store');
            $routes->get('validasi', 'APL1Controller::validasi');
            $routes->get('send-email-validasi', 'APL1Controller::email_validasi');
            $routes->post('store-validasi', 'APL1Controller::store_validasi');
            $routes->post('store-email-validasi', 'APL1Controller::send_email_validasi');
            $routes->post('store-email-validasi-by-date', 'APL1Controller::send_email_validasi_by_date');
            $routes->post('getDateValidated', 'APL1Controller::getDateValidated');
            $routes->post('delete', 'APL1Controller::delete');
            $routes->get('pdf/(:any)', 'APL1Controller::pdf/$1');
        });

        $routes->group('2', static function ($routes) {
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

    // --------------------------------------------------------------------
    // Monitoring, Laporan & Feedback
    // --------------------------------------------------------------------
    $routes->get('monitoring', 'MonitoringController::index');

    $routes->group('feedback', static function ($routes) {
        $routes->get('/', 'FeedbackController::index');
        $routes->post('delete', 'FeedbackController::delete');
    });

    $routes->group('rekaman-asesmen', static function ($routes) {
        $routes->get('/', 'RekamanAsesmenController::index');
        $routes->get('create', 'RekamanAsesmenController::create');
        $routes->post('store', 'RekamanAsesmenController::store');
        $routes->post('load', 'RekamanAsesmenController::loadRekamanAsesmen');
        $routes->get('pdf/(:num)', 'RekamanAsesmenController::pdf/$1');
        $routes->get('delete/(:num)', 'RekamanAsesmenController::delete/$1');
    });

    $routes->group('laporan', function ($routes) {
        $routes->get('/', 'LaporanAsesmenController::index');
        $routes->post('asesmen/batch-pdf', 'LaporanAsesmenController::batchPdf');
        $routes->get('asesmen/pdf/(:num)', 'LaporanAsesmenController::generateLaporan/$1');
        $routes->get('view/(:num)', 'LaporanAsesmenController::view/$1');
        $routes->get('download/(:num)', 'LaporanAsesmenController::download/$1');
    });

    $routes->group('reports', static function ($routes) {
        $routes->get('rekaman-asesmen/statistics', 'ReportsController::rekamanStatistics');
        $routes->get('rekaman-asesmen/by-skema', 'ReportsController::rekamanBySkema');
        $routes->get('rekaman-asesmen/by-asesor', 'ReportsController::rekamanByAsesor');
        $routes->get('rekaman-asesmen/export/excel', 'ReportsController::exportRekamanExcel');
        $routes->get('rekaman-asesmen/export/pdf-summary', 'ReportsController::exportRekamanPdfSummary');
    });

    // --------------------------------------------------------------------
    // Fitur Lainnya
    // --------------------------------------------------------------------
    $routes->group('menu', ['filter' => 'auth'], static function ($routes) {
        $routes->get('/', 'Menu::index');
        $routes->get('items/(:num)', 'Menu::items/$1');
        $routes->match(['GET', 'POST'], 'create-group', 'Menu::createGroup');
        $routes->match(['GET', 'POST'], 'edit-group/(:num)', 'Menu::editGroup/$1');
        $routes->get('delete-group/(:num)', 'Menu::deleteGroup/$1');
        $routes->match(['GET', 'POST'], 'create-item/(:num)', 'Menu::createItem/$1');
        $routes->match(['GET', 'POST'], 'edit-item/(:num)', 'Menu::editItem/$1');
        $routes->get('delete-item/(:num)', 'Menu::deleteItem/$1');
        $routes->post('reorder-items', 'Menu::reorderItems');
    });

    $routes->group('dashboard', static function ($routes) {
        $routes->get('rekaman/widget/latest', 'DashboardController::latestRekaman');
        $routes->get('rekaman/widget/statistics', 'DashboardController::rekamanStatistics');
        $routes->get('rekaman/widget/pending', 'DashboardController::pendingRekaman');
    });
});


// ===================================================================
// #5. ROUTES API TERPROTEKSI (Filter 'login', Prefix 'api/')
// ===================================================================
$routes->group('api', ['filter' => 'login'], static function ($routes) {

    // --- Data Master & Dependensi ---
    $routes->post('get-unit', 'Api\Unit::getUnit');
    $routes->post('get-unit-json', 'Api\Unit::getUnitJSON');
    $routes->post('get-elemen', 'Api\Elemen::getElemen');
    $routes->post('get-elemen-json', 'Api\Elemen::getElemenJSON');
    $routes->post('get-kuk', 'Api\KUK::getKuk');
    $routes->get('get-skema-asesor', 'Api\AsesorSkema::getSkemaAsesor');

    // --- Pengajuan Asesmen ---
    $routes->get('getAsesmenJson', 'Api\PengajuanAsesmen::getAsesmenJson');
    $routes->get('getSkemaDetailJson', 'Api\PengajuanAsesmen::getSkemaDetailJson');
    $routes->get('check-registration-status', 'Api\PengajuanAsesmen::check_registration_status');
    $routes->group('pengajuan-asesmen', static function ($routes) {
        $routes->post('get-data-table', 'Api\PengajuanAsesmen::getDataTable');
        $routes->get('getById/(:any)', 'Api\PengajuanAsesmen::getById/$1');
        $routes->post('save', 'Api\PengajuanAsesmen::save');
        $routes->delete('delete/(:any)', 'Api\PengajuanAsesmen::delete/$1');
        $routes->post('validate/(:any)', 'Api\PengajuanAsesmen::validatePengajuan/$1');
    });

    // --- Observasi ---
    $routes->get('get-observasi', 'CeklistObservasiController::getObservasiData');
    $routes->get('get-asesi', 'CeklistObservasiController::getAsesiList');
    $routes->group('observasi', static function ($routes) {
        $routes->get('getValidatedApl1List', 'Api\Observasi::getValidatedApl1List');
        $routes->get('getValidatedApl1BySkema', 'Api\Observasi::getValidatedApl1BySkema');
        $routes->get('getApl1Details', 'Api\Observasi::getApl1Details');
        $routes->get('checkExistingObservation', 'Api\Observasi::checkExistingObservation');
        $routes->get('loadObservasi', 'Api\Observasi::loadObservasi');
        $routes->get('getSkemaDetails', 'Api\Observasi::getSkemaDetails');
        $routes->post('save', 'Api\Observasi::save');
        $routes->post('saveSingleKUK', 'Api\Observasi::saveSingleKUK');
        $routes->post('saveBatchKUK', 'Api\Observasi::saveBatchKUK');
        $routes->get('getStatistics', 'Api\Observasi::getStatistics');
        $routes->get('getProgressReport', 'Api\Observasi::getProgressReport');
        $routes->delete('deleteObservasi/(:num)', 'Api\Observasi::deleteObservasi/$1');
    });

    // --- Feedback Asesi ---
    $routes->group('feedback-asesi', static function ($routes) {
        $routes->get('get-skema-details', 'Api\FeedbackAsesi::getSkemaDetails');
        $routes->get('get-komponen', 'Api\FeedbackAsesi::getKomponen');
        $routes->get('load-feedback', 'Api\FeedbackAsesi::loadFeedback');
        $routes->get('check-existing', 'Api\FeedbackAsesi::checkExisting');
        $routes->post('save', 'Api\FeedbackAsesi::save');
        $routes->get('getById/(:num)', 'Api\FeedbackAsesi::getById/$1');
        $routes->get('delete/(:num)', 'Api\FeedbackAsesi::delete/$1');
        $routes->post('get-data-table', 'Api\FeedbackAsesi::getDataTable');
    });

    // --- PMO (Portofolio) ---
    $routes->group('pmo', static function ($routes) {
        $routes->post('list', 'Api\PMO::list');
        $routes->get('loadPmo', 'Api\PMO::loadPMO');
        $routes->post('save', 'Api\PMO::save');
        $routes->post('get-data-table', 'Api\PMO::getDataTable');
        $routes->get('delete/(:num)', 'Api\PMO::delete/$1');
    });

    // --- Rekaman Asesmen ---
    $routes->group('rekaman-asesmen', static function ($routes) {
        $routes->post('load', 'RekamanAsesmenController::loadRekamanAsesmen');
        $routes->get('get-data-table', 'Api\RekamanAsesmen::getDataTable');
        $routes->group('admin', function ($routes) {
            $routes->post('list', 'Api\RekamanAsesmenAdmin::list');
            $routes->post('get-data-table', 'Api\RekamanAsesmenAdmin::getDataTable');
            $routes->post('delete/(:num)', 'Api\RekamanAsesmenAdmin::delete/$1');
            $routes->delete('delete/(:num)', 'Api\RekamanAsesmenAdmin::delete/$1');
        });
    });

    $routes->group('rekaman', static function ($routes) {
        $routes->post('auto-save', 'Api\RekamanAsesmenApi::autoSave');
        $routes->post('create', 'Api\RekamanAsesmenApi::create');
        $routes->post('update', 'Api\RekamanAsesmenApi::update');
        $routes->post('save-bulk', 'Api\RekamanAsesmenApi::saveBulk');
        $routes->post('batch-save', 'Api\RekamanAsesmenApi::batchSave');
        $routes->post('(:num)/batch-kompetensi', 'Api\RekamanAsesmenApi::batchKompetensi/$1');
        $routes->get('list', 'Api\RekamanAsesmenApi::list');
        $routes->get('struktur/(:num)', 'Api\RekamanAsesmenApi::struktur/$1');
        $routes->get('(:num)/work-groups', 'Api\RekamanAsesmenApi::workGroups/$1');
        $routes->get('(:num)/existing-kompetensi', 'Api\RekamanAsesmenApi::existingKompetensi/$1');
        $routes->get('(:num)/progress', 'Api\RekamanAsesmenApi::progress/$1');
        $routes->get('apl1-list/(:num)', 'Api\RekamanAsesmenApi::apl1List/$1');
        $routes->get('apl1-detail/(:segment)', 'Api\RekamanAsesmenApi::apl1Detail/$1');
        $routes->get('check-existing/(:segment)', 'Api\RekamanAsesmenApi::checkExisting/$1');
        $routes->get('(:num)/export', 'Api\RekamanAsesmenApi::export/$1');
        $routes->get('(:num)/pdf-data', 'Api\RekamanAsesmenApi::pdfData/$1');
        $routes->get('(:num)', 'Api\RekamanAsesmenApi::show/$1');
        $routes->delete('(:num)', 'Api\RekamanAsesmenApi::delete/$1');
    });

    // --- Asesor ---
    $routes->group('asesor', static function ($routes) {
        $routes->group('rekaman', static function ($routes) {
            $routes->get('apl1/(:num)', 'RekamanAsesmenController::getApl1ByAsesmen/$1');
            $routes->get('load', 'RekamanAsesmenController::loadRekamanAsesmen');
            $routes->post('save/settings', 'RekamanAsesmenController::store');
            $routes->post('save/method', 'RekamanAsesmenController::store');
            $routes->post('save/batch', 'RekamanAsesmenController::store');
            $routes->post('save/recommendation', 'RekamanAsesmenController::store');
            $routes->post('save', 'RekamanAsesmenController::store');
            $routes->post('finalize', 'RekamanAsesmenController::finalize');
            $routes->get('history/(:segment)', 'RekamanAsesmenController::getRekamanHistory/$1');
            $routes->delete('(:num)', 'RekamanAsesmenController::delete/$1');
        });
    });

    // --- Pertanyaan Tertulis (CBT) ---
    $routes->group('pertanyaan-tertulis', static function ($routes) {
        $routes->get('loadUjian', 'Api\PertanyaanTertulis::loadUjian');
        $routes->post('save', 'Api\PertanyaanTertulis::save');
        $routes->post('get-data-table', 'Api\PertanyaanTertulis::getDataTable');
        $routes->get('delete/(:num)', 'Api\PertanyaanTertulis::delete/$1');
    });

    // --- Manajemen User ---
    $routes->group('user-management', static function ($routes) {
        $routes->post('get-data-table', 'Api\UserManagement::getDataTable');
        $routes->get('get-user-statistics-with-deleted', 'Api\UserManagement::getUserStatisticsWithDeleted');
        $routes->post('get-user-by-id', 'Api\UserManagement::getUserByIdPost');
        $routes->post('create-admin-user', 'Api\UserManagement::createAdminUser');
        $routes->post('create-asesor-user', 'Api\UserManagement::createAsesorUser');
        $routes->post('update-profile', 'Api\UserManagement::updateProfile');
        $routes->post('update-user', 'Api\UserManagement::updateUser');
        $routes->post('update-status', 'Api\UserManagement::updateStatus');
        $routes->post('soft-delete-user', 'Api\UserManagement::softDeleteUser');
        $routes->get('get-asesor-by-user-id', 'Api\UserManagement::getAsesorByUserId');
        $routes->get('get-all-asesor', 'Api\UserManagement::getAllAsesor');
        $routes->post('update-asesor', 'Api\UserManagement::updateAsesor');
        $routes->get('get-asesor-statistics', 'Api\UserManagement::getAsesorStatistics');
        $routes->get('get-active-skemas', 'Api\UserManagement::getActiveSkemas');
    });

    $routes->group('user', function ($routes) {
        $routes->post('getDataTable', 'Api\UserManagement::getDataTable');
        $routes->get('statistics', 'Api\UserManagement::getStatistics');
        $routes->get('getById/(:num)', 'Api\UserManagement::getById/$1');
        $routes->post('create', 'Api\UserManagement::create');
        $routes->post('update', 'Api\UserManagement::update');
        $routes->post('delete/(:num)', 'Api\UserManagement::delete/$1');
        $routes->post('toggle-status/(:num)', 'Api\UserManagement::toggleStatus/$1');
    });

    // --- Lain-lain ---
    $routes->group('headerkonfigurasi', function ($routes) {
        $routes->post('getDataTable', 'HeaderKonfigurasiController::getDataTable');
        $routes->post('save', 'HeaderKonfigurasiController::save');
        $routes->get('getById/(:num)', 'HeaderKonfigurasiController::getById/$1');
        $routes->get('delete/(:num)', 'HeaderKonfigurasiController::delete/$1');
    });
    $routes->post('get-date-validated-apl1', 'APL1Controller::getDateValidated');
    $routes->post('get-date-validated-apl2', 'APL2Controller::getDateValidated');
});


// ===================================================================
// #6. ROUTES LEGACY / KOMPATIBILITAS
// ===================================================================
$routes->group('', ['filter' => 'login'], static function ($routes) {
    $routes->group('kelola_apl1', static function ($routes) {
        $routes->get('/', 'PengajuanAsesmenController::index');
        $routes->post('store', 'APL1Controller::store');
        $routes->get('validasi', 'APL1Controller::validasi');
        $routes->get('send-email-validasi', 'APL1Controller::email_validasi');
        $routes->post('store-validasi', 'APL1Controller::store_validasi');
        $routes->post('store-email-validasi', 'APL1Controller::send_email_validasi');
        $routes->post('store-email-validasi-by-date', 'APL1Controller::send_email_validasi_by_date');
        $routes->post('getDateValidated', 'APL1Controller::getDateValidated');
        $routes->post('delete', 'APL1Controller::delete');
        $routes->get('pdf-(:any)', 'APL1Controller::pdf/$1');
    });

    $routes->group('kelola_apl2', static function ($routes) {
        $routes->get('/', 'APL2Controller::index');
        $routes->post('store', 'APL2Controller::store');
        $routes->get('validasi', 'APL2Controller::validasi');
        $routes->post('validasi-store', 'APL2Controller::store_validasi');
        $routes->get('send-email-validasi', 'APL2Controller::email_validasi');
        $routes->post('store-email-validasi', 'APL2Controller::send_email_validasi');
        $routes->post('store-email-validasi-by-date', 'APL2Controller::send_email_validasi_by_date');
        $routes->post('delete', 'APL2Controller::delete');
        $routes->get('pdf-(:any)', 'APL2Controller::pdf/$1');
    });

    $routes->group('persetujuan-asesmen', static function ($routes) {
        $routes->get('/', 'AKController::index');
        $routes->post('store', 'AKController::store');
        $routes->get('pdf-(:any)', 'AKController::pdf/$1');
        $routes->post('import', 'AKController::import');
        $routes->post('update', 'AKController::update');
        $routes->post('delete', 'AKController::delete');
    });

    $routes->group('asesor/rekaman-asesmen', static function ($routes) {
        $routes->get('/', 'RekamanAsesmenController::index', ['as' => 'asesor.rekaman']);
        $routes->get('getApl1ByAsesmen', 'RekamanAsesmenController::getAsesiByAsesmen', ['as' => 'asesor.rekaman.get_apl1']);
        $routes->get('loadRekamanAsesmen', 'RekamanAsesmenController::loadRekamanAsesmen', ['as' => 'asesor.rekaman.load']);
        $routes->post('store', 'RekamanAsesmenController::store', ['as' => 'asesor.rekaman.store']);
        $routes->post('finalize', 'RekamanAsesmenController::finalize', ['as' => 'asesor.rekaman.finalize']);
        $routes->get('history', 'RekamanAsesmenController::getRekamanHistory', ['as' => 'asesor.rekaman.history']);
        $routes->delete('delete/(:num)', 'RekamanAsesmenController::delete/$1', ['as' => 'asesor.rekaman.delete']);
        $routes->get('pdf/(:num)', 'RekamanAsesmenController::pdf/$1', ['as' => 'asesor.rekaman.pdf']);
    });
    $routes->get('asesor/rekaman-kompetensi', 'RekamanAsesmenController::index', ['as' => 'asesor.rekaman.kompetensi']);
});


// ===================================================================
// #7. ROUTES WEBSOCKET (Opsional)
// ===================================================================
$routes->group('ws', static function ($routes) {
    $routes->get('rekaman/updates/(:segment)', 'WebSocketController::rekamanUpdates/$1');
    $routes->post('rekaman/broadcast', 'WebSocketController::broadcastRekamanUpdate');
});


// ===================================================================
// #8. ROUTES KHUSUS DEVELOPMENT
// ===================================================================
if (ENVIRONMENT === 'development') {
    $routes->get('tes', 'TesController::index');
}
