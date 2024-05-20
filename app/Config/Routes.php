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




// DASHBOARD

$routes->get('/tes', 'TesController::index');

$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'login']);

$routes->get('/settings', 'Settings::index', ['filter' => 'login']);
// $routes->get('/groups-setting', 'Settings::groups_setting', ['filter' => 'role:LSP,Superadmin']);
$routes->get('/groups-setting', 'Settings::groups_setting', ['filter' => 'login']);
$routes->post('/store-group', 'Settings::store_group');
$routes->post('/update-group', 'Settings::update_group');
$routes->post('/delete-group', 'Settings::delete_group');
$routes->post('/group-users-update', 'Settings::update_group_user');

$routes->get('/profile/(:any)', 'UserController::profile/$1', ['filter' => 'login']);
$routes->post('/user-update', 'UserController::updateUser');
$routes->post('/user-save', 'UserController::simpanUser');
$routes->post('/delete-user', 'UserController::delete_user');
$routes->post('/group/simpan', 'Settings::simpan');

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

$routes->get('/peserta', 'PesertaController::index', ['filter' => 'login']);
$routes->post('/store-peserta', 'PesertaController::store');
$routes->post('/update-peserta', 'PesertaController::update');
$routes->post('/delete-peserta', 'PesertaController::delete');


$routes->get('/skema', 'SkemaController::index', ['filter' => 'login']);
$routes->post('/store-skema', 'SkemaController::store');
$routes->post('/import-skema', 'SkemaController::import');
$routes->post('/update-skema', 'SkemaController::update');
$routes->post('/delete-skema', 'SkemaController::delete');

$routes->get('/skema-siswa', 'SkemaSiswaController::index', ['filter' => 'login']);
$routes->post('/store-skema-siswa', 'SkemaSiswaController::store');

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

$routes->get('/apl1', 'APL1Controller::index', ['filter' => 'login']);
$routes->get('/validasi-apl1/(:any)', 'APL1Controller::detailAPL1/$1', ['filter' => 'login']);
$routes->post('/store-apl1', 'APL1Controller::store');
$routes->post('/store-dokumen-apl1', 'APL1Controller::storeDocument');
$routes->get('/edit-apl1', 'APL1Controller::edit', ['filter' => 'login']);
$routes->get('/apl1-pdf-(:any)', 'APL1Controller::pdf/$1', ['filter' => 'login']);
$routes->post('/update-apl1', 'APL1Controller::update');
$routes->post('/validasi-apl1', 'APL1Controller::validasi');

$routes->get('/asesmen-mandiri', 'APL2Controller::index', ['filter' => 'login']);
// $routes->get('/asesmen-mandiri/(:any)', 'APL2Controller::asesmen/$1', ['filter' => 'login']);
$routes->get('/apl2-pdf/(:any)', 'APL2Controller::pdf/$1', ['filter' => 'login']);
// $routes->post('/store-asesmen-mandiri', 'APL2Controller::store');

$routes->get('/settanggal', 'SettanggalController::index', ['filter' => 'login']);
$routes->post('/store-settanggal', 'SettanggalController::store');
$routes->post('/update-settanggal', 'SettanggalController::update');
$routes->post('/delete-settanggal', 'SettanggalController::delete');

// $routes->resource('persyaratan', ['controller' => 'PersyaratanController']);
$routes->get('/persyaratan', 'PersyaratanController::index', ['filter' => 'login']);
$routes->post('/store-persyaratan', 'PersyaratanController::store');
$routes->post('/update-persyaratan', 'PersyaratanController::update');
$routes->post('/delete-persyaratan', 'PersyaratanController::delete');

$routes->post('/getUnit', 'UnitController::getUnit');
$routes->post('/getElemen', 'ElemenController::getElemen');
$routes->post('/kabupaten', 'APL1Controller::kabupaten');
$routes->post('/kecamatan', 'APL1Controller::kecamatan');
$routes->post('/desa', 'APL1Controller::desa');
$routes->get('/OAuth/proses', '\Myth\Auth\Controllers\AuthController::proses');
