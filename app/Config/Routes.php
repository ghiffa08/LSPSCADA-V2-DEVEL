<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =====================================================
// Configuration Settings
// =====================================================
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Dashboard');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

// =====================================================
// Public Routes (No Authentication Required)
// =====================================================

// Home and Public Pages
$routes->get('/', 'HomeController::index');
$routes->get('skema-sertifikasi', 'HomeController::skema');
$routes->get('pendaftaran-uji-kompetensi', 'PengajuanAsesmenController::index');
$routes->post('store-pengajuan', 'PengajuanAsesmenController::store');

// Assessment Related
$routes->get('asesmen-mandiri/(:any)', 'HomeController::asesmen/$1');
$routes->post('store-asesmen-mandiri', 'HomeController::store_asesmen');
$routes->post('send-feedback', 'HomeController::send_feedback');

// Signature Scanning
$routes->group('scan', function ($routes) {
    $routes->get('tanda-tangan-asesi/(:segment)', 'APL1Controller::scan_ttd_asesi/$1');
    $routes->get('tanda-tangan-admin/(:segment)', 'APL1Controller::scan_ttd_admin/$1');
    $routes->get('tanda-tangan-asesor/(:segment)', 'APL2Controller::scan_ttd_asesor/$1');
});

// =====================================================
// Authenticated Routes
// =====================================================
$routes->group('', ['filter' => 'login'], function ($routes) {

    // Dashboard and User Profile
    $routes->get('dashboard', 'AsesiController::index');
    $routes->get('settings', 'Settings::index');
    $routes->get('profile', 'AsesiController::profile');

    // PDF Generation
    $routes->group('pdf', function ($routes) {
        $routes->get('pmo', 'PMOController::pdf');
        $routes->get('feedback', 'FeedbackController::pdf');
        $routes->get('observasi/(:num)/(:any)', 'CeklistObservasiController::pdf/$1/$2');
        $routes->get('apl1/(:any)', 'APL1Controller::pdf/$1');
        $routes->get('rekaman', 'RekamanAsesmenController::pdf');
        $routes->get('laporan', 'LaporanAsesmenController::pdf');
    });

    // Assessment Management
    $routes->group('asesmen', function ($routes) {
        $routes->get('/', 'AsesmenController::index');
        $routes->post('save', 'Api\Asesmen::save');
        $routes->post('import', 'AsesmenController::import');
        $routes->get('delete/(:num)', 'Api\Asesmen::delete/$1');
        $routes->get('getById/(:num)', 'Api\Asesmen::getById/$1');
        $routes->post('get-data-table', 'Api\Asesmen::getDataTable');

        // Assessment Approval
        $routes->group('persetujuan', function ($routes) {
            $routes->get('/', 'AKController::index');
            $routes->post('store', 'AKController::store');
            $routes->get('pdf/(:any)', 'AKController::pdf/$1');
            $routes->post('import', 'AKController::import');
            $routes->post('update', 'AKController::update');
            $routes->post('delete', 'AKController::delete');
        });
    });

    // User Management
    $routes->group('admin', function ($routes) {
        $routes->get('/', 'AdminController::index');
        $routes->post('store', 'AdminController::store');
        $routes->post('import', 'AdminController::import');
        $routes->post('update', 'AdminController::update');
        $routes->post('delete', 'AdminController::delete');
        $routes->get('profile/(:any)', 'UserController::profile/$1');

        // Observation
        $routes->group('observasi', function ($routes) {
            $routes->get('/', 'CeklistObservasiController::index');
            $routes->get('delete/(:num)', 'Api\Observasi::delete/$1');
            $routes->get('getById/(:num)', 'Api\Observasi::getById/$1');
            $routes->post('get-data-table', 'Api\Observasi::getDataTable');
            $routes->get('loadObservasi', 'Api\Observasi::loadObservasi');
        });
    });

    // Candidate Management
    $routes->group('asesi', function ($routes) {
        $routes->get('/', 'AsesiController::index');
        $routes->post('store', 'AsesiController::store');
        $routes->post('import', 'AsesiController::import');
        $routes->post('save', 'AsesiController::save');
        $routes->post('delete', 'AsesiController::delete');
    });

    // Assessor
    $routes->group('asesor', function ($routes) {
        $routes->get('/', 'AsesorController::index');
        $routes->post('store', 'AsesorController::store');
        $routes->post('update', 'AsesorController::update');
        $routes->post('delete', 'AsesorController::delete');

        // Observation
        $routes->group('observasi', function ($routes) {
            $routes->get('/', 'CeklistObservasiController::index');
            $routes->get('ceklist', 'CeklistObservasiController::create');
            $routes->get('loadObservasi', 'Api\Observasi::loadObservasi');
            $routes->get('getSkemaDetails', 'Api\Observasi::getSkemaDetails');
            $routes->post('save', 'Api\Observasi::save');
        });
    });

    // Master Data Management
    $routes->group('master', function ($routes) {

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

        // Competency Units
        $routes->group('unit', function ($routes) {
            $routes->get('/', 'UnitController::index');
            $routes->post('save', 'Api\Unit::save');
            $routes->post('import', 'UnitController::import');
            $routes->get('download-template', 'UnitController::downloadTemplate');
            $routes->get('getById/(:num)', 'Api\Unit::getById/$1');
            $routes->get('delete/(:num)', 'Api\Unit::delete/$1');
            $routes->post('get-data-table', 'Api\Unit::getDataTable');
        });

        // Elements
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

        // Kelompok Unjuk Kerjas
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

        // Work Groups
        $routes->group('kelompok-kerja', function ($routes) {
            $routes->get('/', 'KelompokKerjaController::index');
            $routes->get('detail/(:num)', 'KelompokKerjaController::detail/$1');
            $routes->post('save', 'KelompokKerjaController::save');
            $routes->post('import', 'KelompokKerjaController::import');
            $routes->post('update', 'KelompokKerjaController::update');
            $routes->delete('delete/(:num)', 'KelompokKerjaController::delete/$1');
            $routes->post('get-data-table', 'KelompokKerjaController::getDataTable');
        });

        // Assessment Venues (TUK)
        $routes->group('tuk', function ($routes) {
            $routes->get('/', 'TUKController::index');
            $routes->post('save', 'Api\TUK::save');
            $routes->get('delete/(:num)', 'Api\TUK::delete/$1');
            $routes->get('getById/(:num)', 'Api\TUK::getById/$1');
            $routes->post('get-data-table', 'Api\TUK::getDataTable');
        });

        // Requirements
        $routes->group('persyaratan', function ($routes) {
            $routes->get('/', 'PersyaratanController::index');
            $routes->post('store', 'PersyaratanController::store');
            $routes->post('update', 'PersyaratanController::update');
            $routes->post('delete', 'PersyaratanController::delete');
        });

        // Date Settings
        $routes->group('tanggal', function ($routes) {
            $routes->get('/', 'SettanggalController::index');
            $routes->post('save', 'Api\SetTanggal::save');
            $routes->get('delete/(:num)', 'Api\SetTanggal::delete/$1');
            $routes->get('getById/(:num)', 'Api\SetTanggal::getById/$1');
            $routes->post('get-data-table', 'Api\SetTanggal::getDataTable');
        });
    });

    // APL Forms Management
    $routes->group('apl', function ($routes) {
        // APL01 Form
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

        // APL02 Form
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

    // Monitoring
    $routes->get('monitoring', 'MonitoringController::index');

    // Feedback
    $routes->group('feedback', function ($routes) {
        $routes->get('/', 'FeedbackController::index');
        $routes->post('delete', 'FeedbackController::delete');
    });

    // Menu Management
    $routes->group('menu', ['filter' => 'auth'], function ($routes) {
        $routes->get('/', 'Menu::index');
        $routes->get('items/(:num)', 'Menu::items/$1');

        // Group CRUD
        $routes->match(['get', 'post'], 'create-group', 'Menu::createGroup');
        $routes->match(['get', 'post'], 'edit-group/(:num)', 'Menu::editGroup/$1');
        $routes->get('delete-group/(:num)', 'Menu::deleteGroup/$1');

        // Item CRUD
        $routes->match(['get', 'post'], 'create-item/(:num)', 'Menu::createItem/$1');
        $routes->match(['get', 'post'], 'edit-item/(:num)', 'Menu::editItem/$1');
        $routes->get('delete-item/(:num)', 'Menu::deleteItem/$1');
        $routes->post('reorder-items', 'Menu::reorderItems');
    });
});

// =====================================================
// API Routes
// =====================================================
$routes->group('api', function ($routes) {
    $routes->post('get-jadwal', 'AsesmenController::getJadwal');
    $routes->post('get-tuk', 'AsesmenController::getTuk');
    $routes->post('get-unit', 'Api\Unit::getUnit');
    $routes->post('get-unit-json', 'Api\Unit::getUnitJSON');
    $routes->post('get-elemen', 'Api\ELemen::getElemen');
    $routes->get('get-observasi', 'CeklistObservasiController::getObservasiData');
    $routes->get('get-asesi', 'CeklistObservasiController::getAsesiList');

    // Location Data
    $routes->post('kabupaten', 'APL1Controller::kabupaten');
    $routes->post('kecamatan', 'APL1Controller::kecamatan');
    $routes->post('desa', 'APL1Controller::desa');

    // Validation Dates
    $routes->post('get-date-validated-apl1', 'APL1Controller::getDateValidated');
    $routes->post('get-date-validated-apl2', 'APL2Controller::getDateValidated');

    // Signature
    $routes->get('signature-show/(:segment)', 'DocumentController::signatureShow/$1');
});

// =====================================================
// Test Route (Temporary)
// =====================================================
$routes->get('tes', 'TesController::index');
