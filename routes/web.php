<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
});

$router->group(['prefix' => 'dashboard', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/', 'DashboardController@index');
    $router->get('showUser', 'DashboardController@showUser');
    $router->post('logout', 'DashboardController@logout');

});

$router->group(['prefix' => 'permission',  'middleware' => 'auth'], function () use ($router) {
    $router->get('getPermission', 'PermissionController@getPermission');
    $router->post('requestPermission/{ID_ARSIP}', 'PermissionController@requestPermission');
    $router->post('requestScan/{ID_ARSIP}', 'PermissionController@requestScan');
    $router->post('requestInput', 'PermissionController@requestInput');
    $router->post('scanDokumen/{ID_PERMISSION}/{ID_ARSIP}', 'PermissionController@scanDokumen');
    $router->post('approvePermission/{ID_PERMISSION}', 'PermissionController@approvePermission');
    $router->post('rejectedPermission/{ID_PERMISSION}', 'PermissionController@rejectedPermission');
});

$router->group(['prefix' => 'pencarian', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/filter', 'PencarianController@pencarianFilter');
    $router->get('/getAllArsip', 'PencarianController@getAllArsip');
    $router->get('/getArsipById/{ID_ARSIP}', 'PencarianController@getArsipById');
    $router->get('/getArsipDokumen/{ID_ARSIP}', 'PencarianController@getArsipDokumenById');
});

$router->group(['prefix' => 'manajemen', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/getArsipById/{ID_PERMISSION}', 'ManajemenController@getArsipById');
    $router->post('editInput/{ID_PERMISSION}/{ID_ARSIP}', 'ManajemenController@editInput');
});

$router->group(['prefix' => 'operator', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/showOperator', 'OperatorController@showOperator');
    $router->post('/changeAkses/{ID_OPERATOR}', 'OperatorController@changeAkses');
    $router->delete('/{ID_OPERATOR}', 'OperatorController@deleteOperator');
});

$router->group(['prefix' => 'rekapitulasi', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/filterBaseKecamatan', 'RekapitulasiController@filterBaseKecamatan');
    $router->get('/filterBaseKelurahan', 'RekapitulasiController@filterBaseKelurahan');
    $router->get('/filterBaseTahun', 'RekapitulasiController@filterBaseTahun');
    $router->get('/filterBaseKelamin', 'RekapitulasiController@filterBaseKelamin');
});

$router->group(['prefix' => 'file', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/getDokumen', 'FileController@getDokumen');
});

//--------------------------------------------------info arsip controller------------------------------------------------


$router->group(['prefix' => 'arsipkelahiran', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/simpanKelahiran', 'InfoArsipKelahiranController@simpanKelahiran');
    $router->post('/updateKelahiran/{ID_ARSIP}', 'InfoArsipKelahiranController@updateKelahiran');
});

$router->group(['prefix' => 'arsipkematian', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/simpanKematian', 'InfoArsipKematianController@simpanKematian');
    $router->post('/updateKematian/{ID_ARSIP}', 'InfoArsipKematianController@updateKematian');
});

$router->group(['prefix' => 'arsipktp', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/simpanKtp', 'InfoArsipKtpController@simpanKtp');
    $router->post('/updateKtp/{ID_ARSIP}', 'InfoArsipKtpController@updateKtp');
});

$router->group(['prefix' => 'arsipkk', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/simpanKk', 'InfoArsipKkController@simpanKk');
    $router->post('/updateKk/{ID_ARSIP}', 'InfoArsipKkController@updateKk');
});

$router->group(['prefix' => 'arsippengangkatan', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/simpanPengangkatan', 'InfoArsipPengangkatanController@simpanPengangkatan');
    $router->post('/updatePengangkatan/{ID_ARSIP}', 'InfoArsipPengangkatanController@updatePengangkatan');
});

$router->group(['prefix' => 'arsippengesahan', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/simpanPengesahan', 'InfoArsipPengesahanController@simpanPengesahan');
    $router->post('/updatePengesahan/{ID_ARSIP}', 'InfoArsipPengesahanController@updatePengesahan');
});

$router->group(['prefix' => 'arsippengakuan', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/simpanPengakuan', 'InfoArsipPengakuanController@simpanPengakuan');
    $router->post('/updatePengakuan/{ID_ARSIP}', 'InfoArsipPengakuanController@updatePengakuan');
});

$router->group(['prefix' => 'arsipperkawinan', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/simpanPerkawinan', 'InfoArsipPerkawinanController@simpanPerkawinan');
    $router->post('/updatePerkawinan/{ID_ARSIP}', 'InfoArsipPerkawinanController@updatePerkawinan');
});

$router->group(['prefix' => 'arsipperceraian', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/simpanPerceraian', 'InfoArsipPerceraianController@simpanPerceraian');
    $router->post('/updatePerceraian/{ID_ARSIP}', 'InfoArsipPerceraianController@updatePerceraian');
});

$router->group(['prefix' => 'arsipskot', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/simpanSkot', 'InfoArsipSkotController@simpanSkot');
    $router->post('/updateSkot/{ID_ARSIP}', 'InfoArsipSkotController@updateSkot');
});

$router->group(['prefix' => 'arsipsktt', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/simpanSktt', 'InfoArsipSkttController@simpanSktt');
    $router->post('/updateSktt/{ID_ARSIP}', 'InfoArsipSkttController@updateSktt');
});

$router->group(['prefix' => 'arsipsuratpindah', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/simpanSuratPindah', 'InfoArsipSuratPindahController@simpanSuratPindah');
    $router->post('/updateSuratPindah/{ID_ARSIP}', 'InfoArsipSuratPindahController@updateSuratPindah');
});
