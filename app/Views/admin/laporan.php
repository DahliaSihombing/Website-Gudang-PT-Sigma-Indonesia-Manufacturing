<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-0">
    <!-- Header Halaman -->
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <div class="p-4 bg-white rounded shadow-sm border-start border-primary border-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Pusat Laporan & Rekapitulasi Pabrik</h4>
                    <p class="text-muted small mb-0">Pilih modul laporan operasional manufaktur untuk melihat riwayat data, memfilter berdasarkan item barang, mencetak dokumen resmi, atau export ke Excel.</p>
                </div>
                <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">📅 <?= date('d F Y'); ?></span>
            </div>
        </div>
    </div>

    <?php if(session()->getFlashdata('error')):?>
        <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
            <?= session()->getFlashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif;?>

    <!-- 1. KOTAK UTAMA: LAPORAN TRACEABILITY RIWAYAT ALUR PER ITEM -->
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-7 mb-3 mb-lg-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-3 bg-white bg-opacity-10 rounded-circle fs-2">📑</div>
                                <div>
                                    <h5 class="fw-bold mb-1 text-white">Laporan Riwayat Alur Produk (End-to-End Traceability)</h5>
                                    <p class="text-white-50 small mb-0">Lacak siklus hidup komponen: <strong>Material ➔ Cutting ➔ OP1 ➔ WIP Final ➔ Plating ➔ QC ➔ Delivery</strong>.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <form action="<?= base_url('/admin/laporan/history'); ?>" method="GET" class="d-flex gap-2">
                                <select name="item_name" class="form-select form-select-sm fw-semibold shadow-sm py-2" required>
                                    <option value="" disabled selected>-- Pilih Nama Barang / Part --</option>
                                    <?php if(!empty($item_list)): ?>
                                        <?php foreach($item_list as $it): ?>
                                            <?php if(stripos($it['item_name'], 'FINAL') === false): ?>
                                                <option value="<?= esc($it['item_name']); ?>"><?= esc($it['item_name']); ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <button type="submit" class="btn btn-warning btn-sm fw-bold px-3 text-dark text-nowrap shadow-sm">
                                    🔍 Buka History
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. GRID MODUL LAPORAN PABRIK LENGKAP -->
    <div class="row g-3 mb-4 no-print">
        
        <!-- A. LAPORAN STOK GUDANG -->
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-primary">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">📦 Stok Gudang</h6>
                            <span class="badge bg-primary">Stock Opname</span>
                        </div>
                        <p class="text-muted" style="font-size: 11px;">Rekapitulasi harian/bulanan pergerakan alur material hingga delivery.</p>
                        
                        <form id="form_stok" action="<?= base_url('/admin/laporan'); ?>" method="GET" class="mb-2">
                            <input type="hidden" name="tipe" value="stok">
                            
                            <div class="d-flex gap-3 mb-2 bg-light p-1 rounded border justify-content-center">
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="radio" name="stok_mode" id="stok_mode_hari" value="hari" <?= (empty($stok_mode) || $stok_mode === 'hari') ? 'checked' : ''; ?> onchange="toggleStokFilter('hari')">
                                    <label class="form-check-label small fw-semibold" for="stok_mode_hari" style="font-size: 11px;">Per-Hari</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="radio" name="stok_mode" id="stok_mode_bulan" value="bulan" <?= (($stok_mode ?? '') === 'bulan') ? 'checked' : ''; ?> onchange="toggleStokFilter('bulan')">
                                    <label class="form-check-label small fw-semibold" for="stok_mode_bulan" style="font-size: 11px;">Per-Bulan</label>
                                </div>
                            </div>

                            <div id="stok_input_hari" class="<?= (($stok_mode ?? 'hari') === 'bulan') ? 'd-none' : ''; ?> mb-1">
                                <label class="small text-secondary fw-semibold" style="font-size: 10px;">Per Tanggal:</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= (($tipe_laporan ?? '') === 'stok') ? ($end_date ?? date('Y-m-d')) : date('Y-m-d'); ?>">
                            </div>

                            <div id="stok_input_bulan" class="row g-1 mb-1 <?= (($stok_mode ?? '') === 'bulan') ? '' : 'd-none'; ?>">
                                <div class="col-7">
                                    <select name="stok_month" class="form-select form-select-sm">
                                        <?php 
                                            $monthsArr = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                                            $currM = $stok_month ?? date('m');
                                            foreach($monthsArr as $mNum => $mName):
                                        ?>
                                            <option value="<?= $mNum; ?>" <?= ($currM == $mNum) ? 'selected' : ''; ?>><?= $mName; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-5">
                                    <select name="stok_year" class="form-select form-select-sm">
                                        <?php for($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                                            <option value="<?= $y; ?>" <?= (isset($stok_year) && $stok_year == $y) ? 'selected' : ''; ?>><?= $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Filter Universal Item Barang -->
                            <div class="mb-1">
                                <select name="filter_item" class="form-select form-select-sm">
                                    <option value="all">-- Semua Item Barang --</option>
                                    <?php if(!empty($item_list)): ?>
                                        <?php foreach($item_list as $it): ?>
                                            <option value="<?= esc($it['item_name']); ?>" <?= (isset($filter_item) && $filter_item === $it['item_name']) ? 'selected' : ''; ?>>
                                                <?= esc($it['item_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="d-flex gap-1 mt-2">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold w-100" style="font-size: 11.5px;">🔍 Tampilkan</button>
                                <button type="button" class="btn btn-outline-dark btn-sm px-2" onclick="openPrintModal('stok', 'form_stok')" title="Cetak / Export">🖨️</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- B. LAPORAN PEMBELIAN -->
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-success">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">🛒 Pembelian</h6>
                            <span class="badge bg-success">Material Masuk</span>
                        </div>
                        <p class="text-muted" style="font-size: 11px;">Rekap pasokan bahan mentah berdasarkan item barang.</p>
                        
                        <form id="form_pembelian" action="<?= base_url('/admin/laporan'); ?>" method="GET" class="mb-2">
                            <input type="hidden" name="tipe" value="pembelian">
                            <div class="mb-1">
                                <input type="date" name="start_date" class="form-control form-control-sm mb-1" value="<?= (($tipe_laporan ?? '') === 'pembelian') ? ($start_date ?? date('Y-m-01')) : date('Y-m-01'); ?>" required>
                                <input type="date" name="end_date" class="form-control form-control-sm mb-1" value="<?= (($tipe_laporan ?? '') === 'pembelian') ? ($end_date ?? date('Y-m-d')) : date('Y-m-d'); ?>" required>
                                
                                <select name="filter_item" class="form-select form-select-sm">
                                    <option value="all">-- Semua Item Barang --</option>
                                    <?php if(!empty($item_list)): ?>
                                        <?php foreach($item_list as $it): ?>
                                            <option value="<?= esc($it['item_name']); ?>" <?= (isset($filter_item) && $filter_item === $it['item_name']) ? 'selected' : ''; ?>>
                                                <?= esc($it['item_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="d-flex gap-1 mt-2">
                                <button type="submit" class="btn btn-success btn-sm fw-bold w-100" style="font-size: 11.5px;">🔍 Tampilkan</button>
                                <button type="button" class="btn btn-outline-dark btn-sm px-2" onclick="openPrintModal('pembelian', 'form_pembelian')" title="Cetak / Export">🖨️</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- C. LAPORAN SUBCON -->
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-warning">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">⚙️ Subcon</h6>
                            <span class="badge bg-warning text-dark">Plating Outsource</span>
                        </div>
                        <p class="text-muted" style="font-size: 11px;">Rekap surat jalan kirim/kembali vendor per item barang.</p>
                        
                        <form id="form_subcon" action="<?= base_url('/admin/laporan'); ?>" method="GET" class="mb-2">
                            <input type="hidden" name="tipe" value="subcon">
                            <div class="mb-1">
                                <input type="date" name="start_date" class="form-control form-control-sm mb-1" value="<?= (($tipe_laporan ?? '') === 'subcon') ? ($start_date ?? date('Y-m-01')) : date('Y-m-01'); ?>" required>
                                <input type="date" name="end_date" class="form-control form-control-sm mb-1" value="<?= (($tipe_laporan ?? '') === 'subcon') ? ($end_date ?? date('Y-m-d')) : date('Y-m-d'); ?>" required>
                                
                                <select name="filter_item" class="form-select form-select-sm">
                                    <option value="all">-- Semua Item Barang --</option>
                                    <?php if(!empty($item_list)): ?>
                                        <?php foreach($item_list as $it): ?>
                                            <option value="<?= esc($it['item_name']); ?>" <?= (isset($filter_item) && $filter_item === $it['item_name']) ? 'selected' : ''; ?>>
                                                <?= esc($it['item_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="d-flex gap-1 mt-2">
                                <button type="submit" class="btn btn-warning btn-sm fw-bold w-100 text-dark" style="font-size: 11.5px;">🔍 Tampilkan</button>
                                <button type="button" class="btn btn-outline-dark btn-sm px-2" onclick="openPrintModal('subcon', 'form_subcon')" title="Cetak / Export">🖨️</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- D. LAPORAN PRODUKSI -->
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-info">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">🏭 Produksi</h6>
                            <span class="badge bg-info text-dark">Output Mesin</span>
                        </div>
                        <p class="text-muted" style="font-size: 11px;">Rekap hasil kerja operator mesin berdasarkan item barang.</p>
                        
                        <form id="form_produksi" action="<?= base_url('/admin/laporan'); ?>" method="GET" class="mb-2">
                            <input type="hidden" name="tipe" value="produksi">
                            <div class="mb-1">
                                <input type="date" name="start_date" class="form-control form-control-sm mb-1" value="<?= (($tipe_laporan ?? '') === 'produksi') ? ($start_date ?? date('Y-m-01')) : date('Y-m-01'); ?>" required>
                                <input type="date" name="end_date" class="form-control form-control-sm mb-1" value="<?= (($tipe_laporan ?? '') === 'produksi') ? ($end_date ?? date('Y-m-d')) : date('Y-m-d'); ?>" required>
                                
                                <select name="filter_item" class="form-select form-select-sm">
                                    <option value="all">-- Semua Item Barang --</option>
                                    <?php if(!empty($item_list)): ?>
                                        <?php foreach($item_list as $it): ?>
                                            <option value="<?= esc($it['item_name']); ?>" <?= (isset($filter_item) && $filter_item === $it['item_name']) ? 'selected' : ''; ?>>
                                                <?= esc($it['item_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="d-flex gap-1 mt-2">
                                <button type="submit" class="btn btn-info btn-sm fw-bold w-100 text-dark" style="font-size: 11.5px;">🔍 Tampilkan</button>
                                <button type="button" class="btn btn-outline-dark btn-sm px-2" onclick="openPrintModal('produksi', 'form_produksi')" title="Cetak / Export">🖨️</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- E. LAPORAN PENGECEKAN QC -->
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4" style="border-top-color: #06b6d4 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">🔍 Pengecekan QC</h6>
                            <span class="badge bg-secondary">Mutu Lolos</span>
                        </div>
                        <p class="text-muted" style="font-size: 11px;">Rekapitulasi hasil inspeksi mutu per item barang.</p>
                        
                        <form id="form_qc" action="<?= base_url('/admin/laporan'); ?>" method="GET" class="mb-2">
                            <input type="hidden" name="tipe" value="qc">
                            <div class="mb-1">
                                <input type="date" name="start_date" class="form-control form-control-sm mb-1" value="<?= (($tipe_laporan ?? '') === 'qc') ? ($start_date ?? date('Y-m-01')) : date('Y-m-01'); ?>" required>
                                <input type="date" name="end_date" class="form-control form-control-sm mb-1" value="<?= (($tipe_laporan ?? '') === 'qc') ? ($end_date ?? date('Y-m-d')) : date('Y-m-d'); ?>" required>
                                
                                <select name="filter_item" class="form-select form-select-sm">
                                    <option value="all">-- Semua Item Barang --</option>
                                    <?php if(!empty($item_list)): ?>
                                        <?php foreach($item_list as $it): ?>
                                            <option value="<?= esc($it['item_name']); ?>" <?= (isset($filter_item) && $filter_item === $it['item_name']) ? 'selected' : ''; ?>>
                                                <?= esc($it['item_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="d-flex gap-1 mt-2">
                                <button type="submit" class="btn btn-sm fw-bold w-100 text-white" style="background-color: #06b6d4; font-size: 11.5px;">🔍 Tampilkan</button>
                                <button type="button" class="btn btn-outline-dark btn-sm px-2" onclick="openPrintModal('qc', 'form_qc')" title="Cetak / Export">🖨️</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- F. LAPORAN PART NG -->
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-danger">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">⚠️ Laporan Part NG</h6>
                            <span class="badge bg-danger">Form Blangko</span>
                        </div>
                        <p class="text-muted" style="font-size: 11px;">Rekap detail cacat Repair & Reject per item barang.</p>
                        
                        <form id="form_ng" action="<?= base_url('/admin/laporan'); ?>" method="GET" class="mb-2">
                            <input type="hidden" name="tipe" value="ng">
                            <div class="mb-1">
                                <input type="date" name="start_date" class="form-control form-control-sm mb-1" value="<?= (($tipe_laporan ?? '') === 'ng') ? ($start_date ?? date('Y-m-01')) : date('Y-m-01'); ?>" required>
                                <input type="date" name="end_date" class="form-control form-control-sm mb-1" value="<?= (($tipe_laporan ?? '') === 'ng') ? ($end_date ?? date('Y-m-d')) : date('Y-m-d'); ?>" required>
                                
                                <select name="filter_item" class="form-select form-select-sm">
                                    <option value="all">-- Semua Item Barang --</option>
                                    <?php if(!empty($item_list)): ?>
                                        <?php foreach($item_list as $it): ?>
                                            <option value="<?= esc($it['item_name']); ?>" <?= (isset($filter_item) && $filter_item === $it['item_name']) ? 'selected' : ''; ?>>
                                                <?= esc($it['item_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="d-flex gap-1 mt-2">
                                <button type="submit" class="btn btn-danger btn-sm fw-bold w-100" style="font-size: 11.5px;">🔍 Tampilkan</button>
                                <button type="button" class="btn btn-outline-dark btn-sm px-2" onclick="openPrintModal('ng', 'form_ng')" title="Cetak / Export">🖨️</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- G. LAPORAN PENGIRIMAN FINISHED GOODS -->
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4" style="border-top-color: #8b5cf6 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">🚚 Pengiriman</h6>
                            <span class="badge" style="background-color: #8b5cf6;">Finished Goods</span>
                        </div>
                        <p class="text-muted" style="font-size: 11px;">Rekap produk terkirim berdasarkan item barang.</p>
                        
                        <form id="form_pengiriman" action="<?= base_url('/admin/laporan'); ?>" method="GET" class="mb-2">
                            <input type="hidden" name="tipe" value="pengiriman">
                            <div class="mb-1">
                                <input type="date" name="start_date" class="form-control form-control-sm mb-1" value="<?= (($tipe_laporan ?? '') === 'pengiriman') ? ($start_date ?? date('Y-m-01')) : date('Y-m-01'); ?>" required>
                                <input type="date" name="end_date" class="form-control form-control-sm mb-1" value="<?= (($tipe_laporan ?? '') === 'pengiriman') ? ($end_date ?? date('Y-m-d')) : date('Y-m-d'); ?>" required>
                                
                                <select name="filter_item" class="form-select form-select-sm">
                                    <option value="all">-- Semua Item Barang --</option>
                                    <?php if(!empty($item_list)): ?>
                                        <?php foreach($item_list as $it): ?>
                                            <option value="<?= esc($it['item_name']); ?>" <?= (isset($filter_item) && $filter_item === $it['item_name']) ? 'selected' : ''; ?>>
                                                <?= esc($it['item_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="d-flex gap-1 mt-2">
                                <button type="submit" class="btn btn-sm fw-bold w-100 text-white" style="background-color: #8b5cf6; font-size: 11.5px;">🔍 Tampilkan</button>
                                <button type="button" class="btn btn-outline-dark btn-sm px-2" onclick="openPrintModal('pengiriman', 'form_pengiriman')" title="Cetak / Export">🖨️</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- H. REKAP OPERATOR -->
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0 border-top border-4" style="border-top-color: #10b981 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">👷 Rekap Operator</h6>
                            <span class="badge bg-success text-white">Produktivitas</span>
                        </div>
                        <p class="text-muted" style="font-size: 11px;">Laporan total output dan kinerja per operator bulanan.</p>
                        
                        <form id="form_operator" action="<?= base_url('/admin/laporan'); ?>" method="GET" class="mb-2">
                            <input type="hidden" name="tipe" value="operator">
                            <div class="row g-1 mb-1">
                                <div class="col-6">
                                    <select name="month" class="form-select form-select-sm">
                                        <option value="all">Semua Bulan</option>
                                        <?php 
                                            $monthsArr = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                                            $currM = $selected_month ?? date('m');
                                            foreach($monthsArr as $mNum => $mName):
                                        ?>
                                            <option value="<?= $mNum; ?>" <?= ($currM == $mNum) ? 'selected' : ''; ?>><?= $mName; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select name="year" class="form-select form-select-sm">
                                        <?php for($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                                            <option value="<?= $y; ?>" <?= (isset($selected_year) && $selected_year == $y) ? 'selected' : ''; ?>><?= $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-12 mt-1">
                                    <select name="filter_item" class="form-select form-select-sm fw-semibold">
                                        <option value="all">-- Semua Item Barang --</option>
                                        <?php if(!empty($item_list)): ?>
                                            <?php foreach($item_list as $it): ?>
                                                <option value="<?= esc($it['item_name']); ?>" <?= (isset($filter_item) && $filter_item === $it['item_name']) ? 'selected' : ''; ?>>
                                                    <?= esc($it['item_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex gap-1 mt-2">
                                <button type="submit" class="btn btn-success btn-sm fw-bold w-100" style="font-size: 11.5px;">👷 Tampilkan Kinerja</button>
                                <button type="button" class="btn btn-outline-dark btn-sm px-2" onclick="openPrintModal('operator', 'form_operator')" title="Cetak / Export">🖨️</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. AREA TAMPILAN DATA HASIL FILTER -->
    <?php if(!empty($tipe_laporan)): ?>
        <div class="card shadow-sm border-0 report-print-area mb-4">
            
            <!-- KOP SURAT RESMI PABRIK (PRINT ONLY) -->
            <div class="print-header text-center pb-2 mb-2 border-bottom border-2 border-dark" style="display: none;">
                <h4 class="fw-bold text-uppercase mb-0 text-primary" style="letter-spacing: 1px; font-size: 15px;">PT SIGMA MANUFACTURING</h4>
                <p class="mb-0 text-muted" style="font-size: 9px;">Kawasan Industri Manufaktur, Blok C No. 12, Jawa Barat | Telp: (021) 8901234</p>
                <h6 class="fw-bold text-dark mt-1 mb-0 text-uppercase" style="font-size: 11px;">
                    <?= 'LAPORAN ' . strtoupper($tipe_laporan); ?>
                    <?php if($tipe_laporan === 'stok'): ?>
                        <?= ($stok_mode === 'bulan') ? '(PER-BULAN: ' . ($stok_month ?? date('m')) . '/' . ($stok_year ?? date('Y')) . ')' : '(PER-TANGGAL: ' . date('d/m/Y', strtotime($end_date ?? date('Y-m-d'))) . ')'; ?>
                    <?php endif; ?>
                </h6>
            </div>

            <!-- Header Hasil Laporan: Tambahan Dropdown Filter Item di Sebelah Kiri Tombol Cetak (Area Tanda Merah) -->
            <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center py-2 no-print flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-6">
                        📋 Hasil Laporan: [ <?= strtoupper($tipe_laporan); ?> ]
                    </span>
                    <?php if($tipe_laporan === 'stok'): ?>
                        <span class="badge bg-primary fs-6 px-3 py-2 ms-2">
                            📅 Periode: 
                            <?php 
                                if (($stok_mode ?? 'hari') === 'bulan') {
                                    $monthsArr = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                                    $mName = $monthsArr[$stok_month ?? date('m')] ?? date('m');
                                    echo "Bulan " . $mName . " " . ($stok_year ?? date('Y'));
                                } else {
                                    echo "Per-Tanggal " . date('d/m/Y', strtotime($end_date ?? date('Y-m-d')));
                                }
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- BAGIAN KANAN: FILTER ITEM & TOMBOL CETAK -->
                <div class="d-flex align-items-center gap-2">
                    <form action="<?= base_url('/admin/laporan'); ?>" method="GET" id="inlineFilterForm" class="d-flex align-items-center gap-1 m-0">
                        <!-- Pertahankan parameter query sebelumnya -->
                        <input type="hidden" name="tipe" value="<?= esc($tipe_laporan); ?>">
                        <input type="hidden" name="start_date" value="<?= esc($start_date ?? ''); ?>">
                        <input type="hidden" name="end_date" value="<?= esc($end_date ?? ''); ?>">
                        <input type="hidden" name="year" value="<?= esc($selected_year ?? ''); ?>">
                        <input type="hidden" name="month" value="<?= esc($selected_month ?? ''); ?>">
                        <input type="hidden" name="stok_mode" value="<?= esc($stok_mode ?? ''); ?>">
                        <input type="hidden" name="stok_month" value="<?= esc($stok_month ?? ''); ?>">
                        <input type="hidden" name="stok_year" value="<?= esc($stok_year ?? ''); ?>">

                        <select name="filter_item" class="form-select form-select-sm text-dark bg-light fw-semibold" style="width: 200px;" onchange="this.form.submit()">
                            <option value="all">-- Semua Item Barang --</option>
                            <?php if(!empty($item_list)): ?>
                                <?php foreach($item_list as $it): ?>
                                    <option value="<?= esc($it['item_name']); ?>" <?= (isset($filter_item) && $filter_item === $it['item_name']) ? 'selected' : ''; ?>>
                                        <?= esc($it['item_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </form>

                    <button type="button" onclick="window.print()" class="btn btn-warning btn-sm fw-bold text-dark px-3 shadow-sm text-nowrap">
                        🖨️ Cetak / Print (PDF)
                    </button>
                </div>
            </div>

            <div class="card-body p-3">
                
                <!-- A. LAPORAN OPERATOR -->
                <?php if($tipe_laporan === 'operator'): ?>
                    <div class="text-center mb-3">
                        <h5 class="fw-bold text-dark">Laporan Produktivitas Bulanan per Operator</h5>
                        <p class="text-muted small mb-0">
                            Periode: Bulan <?= $selected_month ?? date('m'); ?> Tahun <?= $selected_year ?? date('Y'); ?> 
                            <?php if(!empty($filter_item) && $filter_item !== 'all'): ?>
                                | Filter Item: <strong class="text-primary"><?= esc($filter_item); ?></strong>
                            <?php endif; ?>
                        </p>
                    </div>
                    <table class="table table-striped table-bordered align-middle mb-0 small">
                        <thead class="table-dark text-center">
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 15%;">TANGGAL</th>
                                <th>NAMA OPERATOR / PIC</th>
                                <th style="width: 20%;">TAHAPAN / PROSES</th>
                                <th style="width: 12%;">JUMLAH LOT</th>
                                <th style="width: 15%;">TOTAL OUTPUT</th>
                                <th style="width: 12%;">TOTAL OK</th>
                                <th style="width: 12%;">TOTAL NG</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($report_data)): ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data aktivitas operator pada periode dan filter item ini.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach($report_data as $row): ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($row['date'])); ?></td>
                                    <td class="fw-bold text-primary"><?= esc($row['operator_name']); ?></td>
                                    <td class="text-center"><span class="badge bg-info text-dark"><?= esc($row['category']); ?></span></td>
                                    <td class="text-center"><?= number_format($row['total_lot']); ?> Lot</td>
                                    <td class="text-center fw-bold text-success"><?= number_format($row['total_output']); ?> Pcs</td>
                                    <td class="text-center text-success"><?= number_format($row['total_ok'] ?? 0); ?></td>
                                    <td class="text-center text-danger"><?= number_format($row['total_ng'] ?? 0); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <!-- B. STOK GUDANG -->
                <?php elseif($tipe_laporan === 'stok'): ?>
                    <?php 
                        $totMat = 0; $totCut = 0; $totOp1 = 0; $totWip = 0;
                        $totPlat = 0; $totFc = 0; $totNg = 0; $totFg = 0; $totDel = 0;
                        $parseNum = function($val) {
                            if (empty($val) || $val === '-') return 0;
                            return (float) filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        };
                    ?>
                    <table class="table table-striped table-bordered align-middle mb-0 small" style="font-size: 11px !important;">
                        <thead class="table-dark text-center">
                            <tr>
                                <th style="width: 4%;">NO</th>
                                <th style="width: 20%;">NAMA PART / ITEM</th>
                                <th>MATERIAL</th>
                                <th>CUTTING</th>
                                <th>WIP OP 1</th>
                                <th>WIP FINAL</th>
                                <th>PLATING</th>
                                <th>FC</th>
                                <th>NG</th>
                                <th>FG</th>
                                <th>DELIVERY</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($report_data)): ?>
                                <tr><td colspan="11" class="text-center py-4 text-muted">Tidak ada data inventaris barang pada periode ini.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach($report_data as $row): 
                                    $totMat  += $parseNum($row['material']);
                                    $totCut  += $parseNum($row['cutting']);
                                    $totOp1  += $parseNum($row['op1']);
                                    $totWip  += $parseNum($row['wip_final']);
                                    $totPlat += $parseNum($row['plating']);
                                    $totFc   += $parseNum($row['fc']);
                                    $totNg   += $parseNum($row['ng']);
                                    $totFg   += $parseNum($row['fg']);
                                    $totDel  += $parseNum($row['delivery']);
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="fw-bold text-primary text-start ps-2"><?= esc($row['item_name']); ?></td>
                                    <td class="text-center"><?= $row['material']; ?></td>
                                    <td class="text-center"><?= $row['cutting']; ?></td>
                                    <td class="text-center"><?= $row['op1']; ?></td>
                                    <td class="text-center"><?= $row['wip_final']; ?></td>
                                    <td class="text-center"><?= $row['plating']; ?></td>
                                    <td class="text-center"><?= $row['fc']; ?></td>
                                    <td class="text-center text-danger fw-bold"><?= $row['ng']; ?></td>
                                    <td class="text-center text-success fw-bold"><?= $row['fg']; ?></td>
                                    <td class="text-center"><?= $row['delivery']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold text-center">
                            <tr>
                                <td colspan="2" class="text-center text-uppercase">TOTAL SEMUA STOK</td>
                                <td><?= number_format($totMat); ?></td>
                                <td><?= number_format($totCut); ?></td>
                                <td><?= number_format($totOp1); ?></td>
                                <td><?= number_format($totWip); ?></td>
                                <td><?= number_format($totPlat); ?></td>
                                <td><?= number_format($totFc); ?></td>
                                <td class="text-danger"><?= number_format($totNg); ?></td>
                                <td class="text-success"><?= number_format($totFg); ?></td>
                                <td><?= number_format($totDel); ?></td>
                            </tr>
                        </tfoot>
                    </table>

                <!-- C. PEMBELIAN -->
                <?php elseif($tipe_laporan === 'pembelian'): ?>
                    <?php 
                        $totBeli = 0; 
                        $parseNum = function($val) {
                            if (empty($val) || $val === '-') return 0;
                            return (float) filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        };
                    ?>
                    <table class="table table-striped table-bordered align-middle mb-0 small">
                        <thead class="table-dark text-center">
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 15%;">TANGGAL</th>
                                <th>NAMA MATERIAL</th>
                                <th style="width: 30%;">SUPPLIER</th>
                                <th style="width: 20%;">JUMLAH MASUK</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($report_data)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada transaksi pembelian pada rentang tanggal/item ini.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach($report_data as $row): 
                                    $totBeli += $parseNum($row['quantity']);
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($row['date'])); ?></td>
                                    <td class="fw-bold text-primary"><?= esc($row['item_name']); ?></td>
                                    <td><?= esc($row['supplier']); ?></td>
                                    <td class="text-center fw-bold text-success fs-6"><?= esc($row['quantity']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold text-center">
                            <tr>
                                <td colspan="4" class="text-end pe-3 text-uppercase">TOTAL PEMBELIAN MATERIAL:</td>
                                <td class="text-success fs-6"><?= number_format($totBeli); ?></td>
                            </tr>
                        </tfoot>
                    </table>

                <!-- D. SUBCON -->
                <?php elseif($tipe_laporan === 'subcon'): ?>
                    <?php 
                        $totOut = 0; $totIn = 0; 
                        $parseNum = function($val) {
                            if (empty($val) || $val === '-') return 0;
                            return (float) filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        };
                    ?>
                    <table class="table table-striped table-bordered align-middle mb-0 small">
                        <thead class="table-dark text-center">
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 12%;">TANGGAL</th>
                                <th style="width: 8%;">TIPE</th>
                                <th>NAMA BARANG</th>
                                <th style="width: 22%;">VENDOR</th>
                                <th style="width: 15%;">JUMLAH</th>
                                <th style="width: 18%;">MONITORING SISA</th>
                                <th style="width: 12%;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($report_data)): ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada transaksi subcon pada rentang tanggal/item ini.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach($report_data as $row): 
                                    $qtySc = $parseNum($row['quantity']);
                                    if ($row['type'] === 'OUT') $totOut += $qtySc;
                                    if ($row['type'] === 'IN')  $totIn  += $qtySc;
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($row['date'])); ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= ($row['type'] == 'OUT') ? 'bg-danger' : 'bg-success'; ?>">
                                            <?= $row['type']; ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-primary"><?= esc($row['item_name']); ?></td>
                                    <td><?= esc($row['vendor_name']); ?></td>
                                    <td class="text-center fw-bold"><?= esc($row['quantity']); ?></td>
                                    <td class="text-center"><small><?= esc($row['monitoring']); ?></small></td>
                                    <td class="text-center"><?= esc($row['status'] ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold text-center">
                            <tr>
                                <td colspan="5" class="text-end pe-3 text-uppercase">TOTAL SUBCON:</td>
                                <td>
                                    <span class="text-danger">OUT: <?= number_format($totOut); ?></span> | 
                                    <span class="text-success">IN: <?= number_format($totIn); ?></span>
                                </td>
                                <td colspan="2" class="text-warning-emphasis">
                                    SELISIH: <?= number_format(max(0, $totOut - $totIn)); ?> Pcs
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                <!-- E. PRODUKSI -->
                <?php elseif($tipe_laporan === 'produksi'): ?>
                    <?php 
                        $totProdOut = 0; $totProdOk = 0; $totProdNg = 0; 
                        $parseNum = function($val) {
                            if (empty($val) || $val === '-') return 0;
                            return (float) filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        };
                    ?>
                    <table class="table table-striped table-bordered align-middle mb-0 small">
                        <thead class="table-dark text-center">
                            <tr>
                                <th style="width: 4%;">NO</th>
                                <th style="width: 10%;">TANGGAL</th>
                                <th style="width: 8%;">SHIFT</th>
                                <th style="width: 14%;">KATEGORI/PROSES</th>
                                <th>NAMA BARANG HASIL</th>
                                <th style="width: 12%;">OUTPUT</th>
                                <th style="width: 10%;">QTY OK</th>
                                <th style="width: 10%;">QTY NG</th>
                                <th style="width: 12%;">PIC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($report_data)): ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data produksi pada rentang tanggal/item ini.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach($report_data as $row): 
                                    $totProdOut += $parseNum($row['quantity']);
                                    $totProdOk  += $parseNum($row['qty_ok'] ?? 0);
                                    $totProdNg  += $parseNum($row['qty_ng'] ?? 0);
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($row['date'])); ?></td>
                                    <td class="text-center"><span class="badge bg-secondary"><?= esc($row['shift'] ?? 'Shift 1'); ?></span></td>
                                    <td class="text-center"><span class="badge bg-info text-dark"><?= esc($row['category']); ?></span></td>
                                    <td class="fw-bold text-primary"><?= esc($row['item_name']); ?></td>
                                    <td class="text-center fw-bold text-success fs-6"><?= esc($row['quantity'] . ' ' . ($row['unit'] ?? 'Pcs')); ?></td>
                                    <td class="text-center fw-bold text-success"><?= !empty($row['qty_ok']) ? number_format((float)$row['qty_ok']) : '-'; ?></td>
                                    <td class="text-center fw-bold text-danger"><?= !empty($row['qty_ng']) ? number_format((float)$row['qty_ng']) : '-'; ?></td>
                                    <td class="text-center fw-semibold"><?= esc($row['operator_name'] ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold text-center">
                            <tr>
                                <td colspan="5" class="text-end pe-3 text-uppercase">TOTAL REKAP PRODUKSI:</td>
                                <td class="text-success fs-6"><?= number_format($totProdOut); ?></td>
                                <td class="text-success"><?= number_format($totProdOk); ?></td>
                                <td class="text-danger"><?= number_format($totProdNg); ?></td>
                                <td>-</td>
                            </tr>
                        </tfoot>
                    </table>

                <!-- F. QC -->
                <?php elseif($tipe_laporan === 'qc'): ?>
                    <?php 
                        $totQcCheck = 0; $totQcOk = 0; $totQcNg = 0; 
                        $parseNum = function($val) {
                            if (empty($val) || $val === '-') return 0;
                            return (float) filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        };
                    ?>
                    <table class="table table-striped table-bordered align-middle mb-0 small">
                        <thead class="table-dark text-center">
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 15%;">TANGGAL</th>
                                <th>NAMA BARANG / PART</th>
                                <th style="width: 15%;">QTY CHECK</th>
                                <th style="width: 15%;">QTY OK</th>
                                <th style="width: 15%;">QTY NG</th>
                                <th style="width: 20%;">INSPECTOR / PIC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($report_data)): ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data pengecekan QC pada rentang tanggal/item ini.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach($report_data as $row): 
                                    $totQcCheck += $parseNum($row['quantity'] ?? $row['qty_check'] ?? 0);
                                    $totQcOk    += $parseNum($row['qty_ok'] ?? 0);
                                    $totQcNg    += $parseNum($row['qty_ng'] ?? 0);
                                    $tglRow      = $row['date'] ?? $row['created_at'] ?? date('Y-m-d');
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($tglRow)); ?></td>
                                    <td class="fw-bold text-primary"><?= esc($row['item_name']); ?></td>
                                    <td class="text-center"><?= number_format((float)($row['quantity'] ?? $row['qty_check'] ?? 0)); ?></td>
                                    <td class="text-center fw-bold text-success"><?= number_format((float)($row['qty_ok'] ?? 0)); ?></td>
                                    <td class="text-center fw-bold text-danger"><?= number_format((float)($row['qty_ng'] ?? 0)); ?></td>
                                    <td class="text-center"><?= esc($row['operator_name'] ?? $row['inspector'] ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold text-center">
                            <tr>
                                <td colspan="3" class="text-end pe-3 text-uppercase">TOTAL PENGECEKAN QC:</td>
                                <td><?= number_format($totQcCheck); ?></td>
                                <td class="text-success"><?= number_format($totQcOk); ?></td>
                                <td class="text-danger"><?= number_format($totQcNg); ?></td>
                                <td>-</td>
                            </tr>
                        </tfoot>
                    </table>

                <!-- G. PART NG -->
                <?php elseif($tipe_laporan === 'ng'): ?>
                    <?php 
                        $totNgCheck = 0; $totNgOk = 0; $totNgGrand = 0;
                        $totBurry = 0; $totBelum = 0; $totDimensi = 0;
                        $totGompal = 0; $totBaret = 0; $totHole = 0;
                        $totReplating = 0; $totDacon = 0; $totPanjang = 0; $totPendek = 0;
                    ?>
                    <div class="p-2">
                        <table class="table table-bordered table-sm align-middle text-center mb-0" style="font-size: 9.5px !important;">
                            <thead class="table-dark">
                                <tr>
                                    <th rowspan="2" style="width: 5%;">DATE</th>
                                    <th rowspan="2" style="width: 14%;">PART / ITEM</th>
                                    <th rowspan="2" style="width: 6%;">QTY CHECK</th>
                                    <th rowspan="2" style="width: 6%;">QTY OK</th>
                                    <th rowspan="2" style="width: 6%;">GRAND TOTAL NG</th>
                                    <th colspan="3" class="bg-secondary text-white">REPAIR</th>
                                    <th colspan="7" class="bg-secondary text-white">REJECT</th>
                                    <th rowspan="2" style="width: 10%;">PIC</th>
                                </tr>
                                <tr>
                                    <th>BURRY GROOVING</th>
                                    <th>BELUM PROSES</th>
                                    <th>DIMENSI GROOVING</th>
                                    <th>GOMPAL</th>
                                    <th>BARET</th>
                                    <th>HOLE SEMPIT</th>
                                    <th>REPLATING</th>
                                    <th>VISUAL DACON</th>
                                    <th>PANJANG</th>
                                    <th>PENDEK</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($report_data)): ?>
                                    <tr><td colspan="16" class="text-center py-4 text-muted">Tidak ada data part NG pada rentang tanggal/item ini.</td></tr>
                                <?php else: ?>
                                    <?php foreach($report_data as $row): 
                                        $rawDate  = $row['date'] ?? $row['created_at'] ?? date('Y-m-d');
                                        $dateOnly = date('d/m', strtotime($rawDate));
                                        $qtyCheck = $row['quantity'] ?? $row['qty_check'] ?? 0;
                                        $qtyOk    = $row['qty_ok'] ?? 0;
                                        
                                        $burry   = (float)($row['ng_repair_burry'] ?? 0);
                                        $belum   = (float)($row['ng_repair_belum'] ?? 0);
                                        $dimensi = (float)($row['ng_repair_dimensi'] ?? 0);
                                        
                                        $gompal    = (float)($row['ng_reject_gompal'] ?? 0);
                                        $baret     = (float)($row['ng_reject_baret'] ?? 0);
                                        $hole      = (float)($row['ng_reject_hole'] ?? 0);
                                        $replating = (float)($row['ng_reject_replating'] ?? 0);
                                        $dacon     = (float)($row['ng_reject_dacon'] ?? 0);
                                        $panjang   = (float)($row['ng_reject_panjang'] ?? 0);
                                        $pendek    = (float)($row['ng_reject_pendek'] ?? 0);

                                        $rowTotalNg = $burry + $belum + $dimensi + $gompal + $baret + $hole + $replating + $dacon + $panjang + $pendek;
                                        if ($rowTotalNg == 0 && (float)($row['qty_ng'] ?? 0) > 0) {
                                            $rowTotalNg = (float)$row['qty_ng'];
                                        }

                                        $totNgCheck += (float)$qtyCheck;
                                        $totNgOk    += (float)$qtyOk;
                                        $totNgGrand += $rowTotalNg;

                                        $totBurry   += $burry;
                                        $totBelum   += $belum;
                                        $totDimensi += $dimensi;
                                        $totGompal  += $gompal;
                                        $totBaret   += $baret;
                                        $totHole    += $hole;
                                        $totReplating += $replating;
                                        $totDacon   += $dacon;
                                        $totPanjang += $panjang;
                                        $totPendek  += $pendek;
                                    ?>
                                    <tr>
                                        <td><b><?= $dateOnly; ?></b></td>
                                        <td class="text-start ps-1 fw-bold text-primary"><?= esc($row['item_name']); ?></td>
                                        <td><?= number_format((float)$qtyCheck); ?></td>
                                        <td><?= number_format((float)$qtyOk); ?></td>
                                        <td class="fw-bold text-danger bg-light"><?= $rowTotalNg > 0 ? number_format($rowTotalNg) : '-'; ?></td>
                                        <td><?= $burry > 0 ? number_format($burry) : '-'; ?></td>
                                        <td><?= $belum > 0 ? number_format($belum) : '-'; ?></td>
                                        <td><?= $dimensi > 0 ? number_format($dimensi) : '-'; ?></td>
                                        <td><?= $gompal > 0 ? number_format($gompal) : '-'; ?></td>
                                        <td><?= $baret > 0 ? number_format($baret) : '-'; ?></td>
                                        <td><?= $hole > 0 ? number_format($hole) : '-'; ?></td>
                                        <td><?= $replating > 0 ? number_format($replating) : '-'; ?></td>
                                        <td><?= $dacon > 0 ? number_format($dacon) : '-'; ?></td>
                                        <td><?= $panjang > 0 ? number_format($panjang) : '-'; ?></td>
                                        <td><?= $pendek > 0 ? number_format($pendek) : '-'; ?></td>
                                        <td class="text-start ps-1"><?= esc($row['operator_name'] ?? $row['inspector'] ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-secondary fw-bold text-center">
                                <tr>
                                    <td colspan="2">TOTAL REKAP NG</td>
                                    <td><?= number_format($totNgCheck); ?></td>
                                    <td class="text-success"><?= number_format($totNgOk); ?></td>
                                    <td class="text-danger"><?= number_format($totNgGrand); ?></td>
                                    <td><?= number_format($totBurry); ?></td>
                                    <td><?= number_format($totBelum); ?></td>
                                    <td><?= number_format($totDimensi); ?></td>
                                    <td><?= number_format($totGompal); ?></td>
                                    <td><?= number_format($totBaret); ?></td>
                                    <td><?= number_format($totHole); ?></td>
                                    <td><?= number_format($totReplating); ?></td>
                                    <td><?= number_format($totDacon); ?></td>
                                    <td><?= number_format($totPanjang); ?></td>
                                    <td><?= number_format($totPendek); ?></td>
                                    <td>-</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                <!-- H. PENGIRIMAN -->
                <?php elseif($tipe_laporan === 'pengiriman'): ?>
                    <?php 
                        $totKirim = 0; 
                        $parseNum = function($val) {
                            if (empty($val) || $val === '-') return 0;
                            return (float) filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        };
                    ?>
                    <table class="table table-striped table-bordered align-middle mb-0 small">
                        <thead class="table-dark text-center">
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 15%;">TANGGAL KIRIM</th>
                                <th>NAMA BARANG (FG)</th>
                                <th style="width: 30%;">TUJUAN (PT / CUSTOMER)</th>
                                <th style="width: 15%;">JUMLAH</th>
                                <th style="width: 12%;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($report_data)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data pengiriman pada rentang tanggal/item ini.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach($report_data as $row): 
                                    $totKirim += $parseNum($row['quantity']);
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($row['date'])); ?></td>
                                    <td class="fw-bold text-primary"><?= esc($row['item_name']); ?></td>
                                    <td><?= esc($row['destination']); ?></td>
                                    <td class="text-center fw-bold text-success fs-6"><?= esc($row['quantity']); ?></td>
                                    <td class="text-center"><span class="badge bg-success px-2 py-1">Terkirim</span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold text-center">
                            <tr>
                                <td colspan="4" class="text-end pe-3 text-uppercase">TOTAL PENGIRIMAN FINISHED GOODS:</td>
                                <td class="text-success fs-6"><?= number_format($totKirim); ?> Pcs</td>
                                <td>-</td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>

            </div>

            <!-- Tanda Tangan Cetak Laporan Resmi (Print Only) -->
            <div class="print-signatures row mt-4 pt-2 text-center" style="display: none;">
                <div class="col-4">
                    <p class="mb-4 fw-bold" style="font-size: 10px;">Dibuat Oleh,</p>
                    <p class="fw-bold text-decoration-underline mb-0" style="font-size: 10px;"><?= session()->get('full_name') ?? 'Staff Admin/PPIC'; ?></p>
                    <small class="text-muted" style="font-size: 8.5px;">Staff Administrasi</small>
                </div>
                <div class="col-4">
                    <p class="mb-4 fw-bold" style="font-size: 10px;">Diperiksa Oleh,</p>
                    <p class="fw-bold text-decoration-underline mb-0" style="font-size: 10px;">( ........................................ )</p>
                    <small class="text-muted" style="font-size: 8.5px;">Supervisor Terkait</small>
                </div>
                <div class="col-4">
                    <p class="mb-4 fw-bold" style="font-size: 10px;">Disetujui Oleh,</p>
                    <p class="fw-bold text-decoration-underline mb-0" style="font-size: 10px;">( ........................................ )</p>
                    <small class="text-muted" style="font-size: 8.5px;">Factory / Plant Manager</small>
                </div>
            </div>

        </div>
    <?php endif; ?>

</div>

<!-- Modal Pop-Up Choices -->
<div class="modal fade" id="modalPrintOptions" tabindex="-1" aria-labelledby="modalPrintOptionsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title fw-bold" id="modalPrintOptionsLabel">🖨️ Pilih Format & Distribusi Laporan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-muted small mb-4">Pilih aksi untuk modul laporan <strong id="modal_module_name" class="text-primary text-uppercase"></strong>:</p>
                
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" onclick="executeAction('pdf')" class="btn btn-danger w-100 py-3 fw-bold shadow-sm">
                            <div class="fs-4 mb-1">📄</div>
                            Cetak PDF
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" onclick="executeAction('excel')" class="btn btn-success w-100 py-3 fw-bold shadow-sm">
                            <div class="fs-4 mb-1">📊</div>
                            Download Excel
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" onclick="executeAction('wa_pdf')" class="btn btn-outline-danger w-100 py-3 fw-bold shadow-sm">
                            <div class="fs-4 mb-1">💬</div>
                            Kirim WA (PDF)
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" onclick="executeAction('wa_excel')" class="btn btn-outline-success w-100 py-3 fw-bold shadow-sm">
                            <div class="fs-4 mb-1">💬</div>
                            Kirim WA (Excel)
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 justify-content-center bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- CSS Styling Khusus Cetak -->
<style>
    @media print {
        @page {
            size: A4 landscape;
            margin: 0mm;
        }
        body { 
            background-color: #fff !important; 
            font-size: 9.5px !important; 
            margin: 10mm !important;
        }
        .no-print, .sidebar, nav, header, footer, .modal { 
            display: none !important; 
        }
        .main-content { 
            margin-left: 0 !important; 
            padding: 0 !important; 
        }
        .card { 
            border: none !important; 
            box-shadow: none !important; 
            margin-bottom: 0 !important;
        }
        .print-header { 
            display: block !important; 
            margin-top: 2px !important; 
            margin-bottom: 4px !important; 
        }
        .print-signatures { 
            display: flex !important; 
            margin-top: 15px !important; 
        }
        .table { 
            width: 100% !important; 
            border: 1px solid #000 !important; 
            margin-bottom: 0 !important; 
        }
        .table th, .table td { 
            border: 1px solid #000 !important;
            padding: 4px 6px !important;
        }
        .table-dark th {
            background-color: #333 !important;
            color: #fff !important;
            -webkit-print-color-adjust: exact;
        }
    }
</style>

<!-- JavaScript Handler untuk Filter & Cetak -->
<script>
let currentTipe = '';
let currentFormId = '';

function toggleStokFilter(mode) {
    const hariContainer = document.getElementById('stok_input_hari');
    const bulanContainer = document.getElementById('stok_input_bulan');
    
    if (mode === 'hari') {
        hariContainer.classList.remove('d-none');
        bulanContainer.classList.add('d-none');
    } else {
        hariContainer.classList.add('d-none');
        bulanContainer.classList.remove('d-none');
    }
}

function openPrintModal(tipe, formId) {
    currentTipe = tipe;
    currentFormId = formId;
    
    document.getElementById('modal_module_name').innerText = tipe;
    const modalEl = new bootstrap.Modal(document.getElementById('modalPrintOptions'));
    modalEl.show();
}

function executeAction(action) {
    const form = document.getElementById(currentFormId);
    if (!form) return;

    const formData = new FormData(form);
    const params = new URLSearchParams(formData).toString();

    if (action === 'pdf') {
        window.open('<?= base_url('/admin/laporan'); ?>?' + params + '&print=1', '_blank');
    } else if (action === 'excel') {
        window.location.href = '<?= base_url('/admin/laporan/export-excel'); ?>?' + params;
    } else if (action === 'wa_pdf') {
        const text = encodeURIComponent(`Halo, berikut link cetak Laporan ${currentTipe.toUpperCase()} PT Sigma Manufacturing:\n${window.location.origin}/admin/laporan?${params}&print=1`);
        window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
    } else if (action === 'wa_excel') {
        const text = encodeURIComponent(`Halo, berikut file export Laporan ${currentTipe.toUpperCase()} PT Sigma Manufacturing:\n${window.location.origin}/admin/laporan/export-excel?${params}`);
        window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('print') === '1') {
        window.print();
    }
});
</script>

<?= $this->endSection(); ?>