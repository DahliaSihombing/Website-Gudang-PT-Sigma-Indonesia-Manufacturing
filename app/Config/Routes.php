<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Root & Authentication
$routes->get('/', 'Home::index');
$routes->get('/login', 'AuthController::index');
$routes->get('/auth', 'AuthController::index');
$routes->post('/auth/login', 'AuthController::login');
$routes->get('/auth/logout', 'AuthController::logout');

// Rute Dashboard Multi-Role
$routes->get('/admin/dashboard', 'AdminController::dashboard');
$routes->get('/produksi/dashboard', 'ProduksiController::dashboard');
$routes->post('/produksi/store', 'ProduksiController::store');
$routes->get('/produksi/input-harian', 'ProduksiController::inputHarian');

// Rute Master Barang
$routes->get('/admin/barang', 'AdminController::barang');
$routes->post('/admin/barang/store', 'AdminController::storeBarang');
$routes->post('/admin/barang/update/(:num)', 'AdminController::updateBarang/$1');
$routes->get('/admin/barang/delete/(:num)', 'AdminController::deleteBarang/$1');

// Rute Pembelian
$routes->get('/admin/pembelian', 'AdminController::pembelian');
$routes->post('/admin/pembelian/store', 'AdminController::storePembelian');
$routes->post('/admin/pembelian/update/(:num)', 'AdminController::updatePembelian/$1');
$routes->get('/admin/pembelian/delete/(:num)', 'AdminController::deletePembelian/$1');

// Rute Subcon & Vendor
$routes->get('/admin/subcon', 'AdminController::subcon');
$routes->get('/admin/vendor', 'AdminController::subcon');
$routes->post('/admin/subcon/store', 'AdminController::storeSubcon');
$routes->post('/admin/subcon/update/(:num)', 'AdminController::updateSubcon/$1');
$routes->get('/admin/subcon/delete/(:num)', 'AdminController::deleteSubcon/$1');

// Rute Master Mitra / Rekanan (Vendor, Supplier, PT Customer)
$routes->get('/admin/partners', 'AdminController::partners');
$routes->post('/admin/partners/store', 'AdminController::storePartner');
$routes->post('/admin/partners/update/(:num)', 'AdminController::updatePartner/$1');
$routes->get('/admin/partners/delete/(:num)', 'AdminController::deletePartner/$1');

// Rute Monitoring Produksi & Aksi Edit/Hapus KHUSUS ADMIN
$routes->get('/admin/produksi', 'AdminController::produksi');
$routes->post('/admin/produksi/update/(:num)', 'AdminController::updateProduksi/$1');
$routes->get('/admin/produksi/delete/(:num)', 'AdminController::deleteProduksi/$1');

// Rute Pengiriman Admin
$routes->get('/admin/pengiriman', 'AdminController::pengiriman');
$routes->post('/admin/pengiriman/store', 'AdminController::storePengiriman');
$routes->post('/admin/pengiriman/update/(:num)', 'AdminController::updatePengiriman/$1');
$routes->get('/admin/pengiriman/delete/(:num)', 'AdminController::deletePengiriman/$1');

// Rute Laporan Pabrik & History Per Item
$routes->get('/admin/laporan', 'AdminController::laporan');
$routes->get('/admin/laporan/history', 'AdminController::laporanHistoryItem');
$routes->get('/admin/laporan/export-excel', 'AdminController::exportExcelLaporan');

// Route Pengaturan Akun Pengguna
$routes->get('admin/pengaturan', 'AdminController::pengaturan');
$routes->post('admin/pengaturan/store', 'AdminController::storeUser');
$routes->post('admin/pengaturan/update/(:num)', 'AdminController::updateUser/$1');
$routes->post('admin/pengaturan/update-password/(:num)', 'AdminController::updatePassword/$1');
$routes->get('admin/pengaturan/delete/(:num)', 'AdminController::deleteUser/$1');

// Rute Operasional & Riwayat Bagian Produksi
$routes->get('/produksi/riwayat', 'ProduksiController::riwayat');
$routes->get('/produksi/cutting', 'ProduksiController::formCutting');
$routes->post('/produksi/cutting/store', 'ProduksiController::storeCutting');

$routes->get('/produksi/op1', 'ProduksiController::formOp1');
$routes->post('/produksi/op1/store', 'ProduksiController::storeOp1');

$routes->get('/produksi/final', 'ProduksiController::formFinal');
$routes->post('/produksi/final/store', 'ProduksiController::storeFinal');

$routes->get('/produksi/form-qc', 'ProduksiController::formQc');
$routes->post('/produksi/store-qc', 'ProduksiController::storeQc');

$routes->get('/produksi/repair', 'ProduksiController::formRepair');
$routes->post('/produksi/repair/store', 'ProduksiController::storeRepair');

$routes->get('/produksi/edit/(:num)', 'ProduksiController::edit/$1');
$routes->post('/produksi/update/(:num)', 'ProduksiController::update/$1');
$routes->get('/produksi/delete/(:num)', 'ProduksiController::delete/$1');