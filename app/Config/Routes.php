<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


// $routes['default_controller'] = 'Dashboard';
// $routes['404_override'] = '';
// $routes['translate_uri_dashes'] = false;

$routes->get('/', 'LandingpageController::index');

// LANDINGPAGE

$routes->get('/landingpage', 'LandingpageController::index');

$routes->get('/skema-sertifikasi', 'LandingpageController::skema');

$routes->get('/pendaftaran-uji-kompetensi', 'LandingpageController::ujikom');

$routes->post('/store-pengajuan', 'LandingpageController::store_pengajuan');

$routes->get('/asesmen-mandiri/(:any)', 'LandingpageController::asesmen/$1');

$routes->post('/store-asesmen-mandiri', 'LandingpageController::store_asesmen');

$routes->get('/scan-tanda-tangan-asesi/(:segment)', 'APL1Controller::scan_ttd_asesi/$1');

$routes->get('/scan-tanda-tangan-admin/(:segment)', 'APL1Controller::scan_ttd_admin/$1');

$routes->get('/scan-tanda-tangan-asesor/(:segment)', 'APL2Controller::scan_ttd_asesor/$1');

$routes->post('/send-feedback', 'LandingpageController::send_feedback');




// DASHBOARD

$routes->get('/tes', 'TesController::index');

$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'login']);

$routes->get('/settings', 'Settings::index', ['filter' => 'login']);;

$routes->get('/profile/(:any)', 'UserController::profile/$1', ['filter' => 'login']);

$routes->post('/user-update', 'UserController::updateUser');

$routes->group('admin', ['filter' => 'login'], function ($routes) {
    $routes->get('/', 'AdminController::index');
    $routes->post('store', 'AdminController::store');
    $routes->post('import', 'AdminController::import');
    $routes->post('update', 'AdminController::update');
    $routes->post('delete', 'AdminController::delete');
});

$routes->get('/asesor', 'AsesorController::index', ['filter' => 'login']);
$routes->post('/store-asesor', 'AsesorController::store');
$routes->post('/update-asesor', 'AsesorController::update');
$routes->post('/delete-asesor', 'AsesorController::delete');

$routes->get('/skema', 'SkemaController::index', ['filter' => 'login']);
$routes->post('/store-skema', 'SkemaController::store');
$routes->post('/import-skema', 'SkemaController::import');
$routes->post('/update-skema', 'SkemaController::update');
$routes->post('/delete-skema', 'SkemaController::delete');

$routes->get('/unit', 'UnitController::index', ['filter' => 'login']);
$routes->post('/store-unit', 'UnitController::store');
$routes->post('/import-unit', 'UnitController::import');
$routes->post('/update-unit', 'UnitController::update');
$routes->post('/delete-unit', 'UnitController::delete');

$routes->get('/elemen', 'ElemenController::index', ['filter' => 'login']);
$routes->post('/store-elemen', 'ElemenController::store');
$routes->post('/import-elemen', 'ElemenController::import');
$routes->post('/update-elemen', 'ElemenController::update');
$routes->post('/delete-elemen', 'ElemenController::delete');

$routes->get('/subelemen', 'SubelemenController::index', ['filter' => 'login']);
$routes->post('/store-subelemen', 'SubelemenController::store');
$routes->post('/import-subelemen', 'SubelemenController::import');
$routes->post('/update-subelemen', 'SubelemenController::update');
$routes->post('/delete-subelemen', 'SubelemenController::delete');

$routes->get('/tempat-tuk', 'TUKController::index', ['filter' => 'login']);
$routes->post('/store-tuk', 'TUKController::store');
$routes->post('/update-tuk', 'TUKController::update');
$routes->post('/delete-tuk', 'TUKController::delete');


$routes->get('/monitoring-asesi', 'MonitoringController::index', ['filter' => 'login']);

$routes->group('kelola_apl1', ['filter' => 'login'], function ($routes) {
    $routes->get('/', 'APL1Controller::index');
    $routes->post('store', 'APL1Controller::store');
    $routes->get('validasi', 'APL1Controller::validasi');
    $routes->get('send-email-validasi', 'APL1Controller::email_validasi');
    $routes->post('store-validasi', 'APL1Controller::store_validasi');
    $routes->post('store-email-validasi', 'APL1Controller::send_email_validasi');
    $routes->post('store-email-validasi-by-date', 'APL1Controller::send_email_validasi_by_date');
    $routes->post('delete', 'APL1Controller::delete');
    $routes->get('pdf-(:any)', 'APL1Controller::pdf/$1');
});

$routes->group('kelola_apl2', ['filter' => 'login'], function ($routes) {
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

$routes->group('persetujuan-asesmen', ['filter' => 'login'], function ($routes) {
    $routes->get('/', 'AKController::index');
    $routes->post('store', 'AKController::store');
    $routes->get('pdf-(:any)', 'AKController::pdf/$1');
    $routes->post('import', 'AKController::import');
    $routes->post('update', 'AKController::update');
    $routes->post('delete', 'AKController::delete');
});

$routes->group('asesmen', ['filter' => 'login'], function ($routes) {
    $routes->get('/', 'AsesmenController::index');
    $routes->post('store', 'AsesmenController::store');
    $routes->post('import', 'AsesmenController::import');
    $routes->post('update', 'AsesmenController::update');
    $routes->post('delete', 'AsesmenController::delete');
});

$routes->get('/settanggal', 'SettanggalController::index', ['filter' => 'login']);
$routes->post('/store-settanggal', 'SettanggalController::store');
$routes->post('/update-settanggal', 'SettanggalController::update');
$routes->post('/delete-settanggal', 'SettanggalController::delete');

// $routes->resource('persyaratan', ['controller' => 'PersyaratanController']);
$routes->get('/persyaratan', 'PersyaratanController::index', ['filter' => 'login']);
$routes->post('/store-persyaratan', 'PersyaratanController::store');
$routes->post('/update-persyaratan', 'PersyaratanController::update');
$routes->post('/delete-persyaratan', 'PersyaratanController::delete');

$routes->get('/umpan-balik', 'FeedbackController::index');
$routes->post('/delete-umpan-balik', 'FeedbackController::delete');

// AJAX
$routes->post('/get-jadwal', 'AsesmenController::getJadwal');
$routes->post('/get-tuk', 'AsesmenController::getTuk');
$routes->post('/getUnit', 'UnitController::getUnit');
$routes->post('/getElemen', 'ElemenController::getElemen');
$routes->post('/kabupaten', 'APL1Controller::kabupaten');
$routes->post('/kecamatan', 'APL1Controller::kecamatan');
$routes->post('/desa', 'APL1Controller::desa');
$routes->post('/getDateValidated1', 'APL1Controller::getDateValidated');
$routes->post('/getDateValidated2', 'APL2Controller::getDateValidated');
