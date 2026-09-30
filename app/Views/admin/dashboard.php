<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<!-- Header Welcome -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="p-4 bg-white rounded shadow-sm border-start border-primary border-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="fw-bold text-dark mb-1">Manufacturing Control Center</h4>
                <p class="text-muted small mb-0">Selamat datang kembali, <strong><?= session()->get('full_name') ?? 'Administrator Utama'; ?></strong>. Pantau metrik produksi, arus barang, dan pengiriman secara real-time.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= base_url('/admin/laporan'); ?>" class="btn btn-outline-primary btn-sm fw-bold px-3 py-2">📑 Pusat Laporan</a>
                <a href="<?= base_url('/admin/barang'); ?>" class="btn btn-primary btn-sm fw-bold px-3 py-2">+ Master Barang</a>
            </div>
        </div>
    </div>
</div>

<!-- 4 KARTU STATISTIK KPI UTAMA -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-primary text-white h-100">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase d-block" style="font-size: 11px;">Master Komponen</span>
                    <h3 class="fw-bold mb-0 mt-1"><?= number_format($total_items); ?></h3>
                    <small class="text-white-50" style="font-size: 11px;">Part terdaftar di sistem</small>
                </div>
                <div class="fs-1 bg-white bg-opacity-10 p-3 rounded-circle">📦</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-warning text-dark h-100">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-dark small fw-bold text-uppercase d-block" style="font-size: 11px;">Outstanding Vendor</span>
                    <h3 class="fw-bold mb-0 mt-1"><?= number_format($vendor_outstanding_pcs); ?> <span class="fs-6">Pcs</span></h3>
                    <small class="fw-semibold text-danger" style="font-size: 11px;">+ <?= number_format($vendor_outstanding_kg, 2); ?> Kg di Vendor</small>
                </div>
                <div class="fs-1 bg-dark bg-opacity-10 p-3 rounded-circle">⏳</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-success text-white h-100">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase d-block" style="font-size: 11px;">Stok FG Siap Kirim</span>
                    <h3 class="fw-bold mb-0 mt-1"><?= number_format($total_fg_ready); ?> <span class="fs-6">Pcs</span></h3>
                    <small class="text-white-50" style="font-size: 11px;">Finished Goods lolos QC</small>
                </div>
                <div class="fs-1 bg-white bg-opacity-10 p-3 rounded-circle">🏭</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-dark text-white h-100">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase d-block" style="font-size: 11px;">Total Terkirim</span>
                    <h3 class="fw-bold mb-0 mt-1"><?= number_format($total_delivered); ?> <span class="fs-6">Pcs</span></h3>
                    <small class="text-white-50" style="font-size: 11px;">Telah sampai di Customer</small>
                </div>
                <div class="fs-1 bg-white bg-opacity-10 p-3 rounded-circle">🚚</div>
            </div>
        </div>
    </div>
</div>

<!-- PIPELINE FLOWCHART PABRIK -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="fw-bold text-dark mb-0">🔄 Pipeline Alur Proses Produksi & Distribusi</h6>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 bg-light rounded border text-center">
                    <div class="flex-fill p-2 bg-white rounded shadow-sm border">
                        <small class="text-muted d-block" style="font-size: 10px;">1. MATERIAL</small>
                        <span class="fw-bold text-primary fs-6"><?= $count_material; ?> Item</span>
                    </div>
                    <span class="text-muted fw-bold">➔</span>
                    <div class="flex-fill p-2 bg-white rounded shadow-sm border">
                        <small class="text-muted d-block" style="font-size: 10px;">2. OP 1</small>
                        <span class="fw-bold text-success fs-6"><?= $count_cutting; ?> Item</span>
                    </div>
                    <span class="text-muted fw-bold">➔</span>
                    <div class="flex-fill p-2 bg-white rounded shadow-sm border">
                        <small class="text-muted d-block" style="font-size: 10px;">3. OP 2</small>
                        <span class="fw-bold text-info fs-6"><?= $count_op1; ?> Item</span>
                    </div>
                    <span class="text-muted fw-bold">➔</span>
                    <div class="flex-fill p-2 bg-white rounded shadow-sm border">
                        <small class="text-muted d-block" style="font-size: 10px;">4. WIP</small>
                        <span class="fw-bold text-warning-emphasis fs-6"><?= $count_wip; ?> Item</span>
                    </div>
                    <span class="text-muted fw-bold">➔</span>
                    <div class="flex-fill p-2 bg-white rounded shadow-sm border">
                        <small class="text-muted d-block" style="font-size: 10px;">5. PLATING</small>
                        <span class="fw-bold text-danger fs-6"><?= number_format($vendor_outstanding_pcs); ?> Pcs</span>
                    </div>
                    <span class="text-muted fw-bold">➔</span>
                    <div class="flex-fill p-2 bg-white rounded shadow-sm border">
                        <small class="text-muted d-block" style="font-size: 10px;">6. INSPEKSI QC</small>
                        <span class="fw-bold text-primary fs-6"><?= $count_qc; ?> Lot</span>
                    </div>
                    <span class="text-muted fw-bold">➔</span>
                    <div class="flex-fill p-2 bg-white rounded shadow-sm border">
                        <small class="text-muted d-block" style="font-size: 10px;">7. READY FG</small>
                        <span class="fw-bold text-success fs-6"><?= number_format($total_fg_ready); ?> Pcs</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- GRAFIK BATANG REKAPITULASI PART NG (BULANAN / TAHUNAN) -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-0">
                <div>
                    <h6 class="fw-bold text-dark mb-0">📊 Grafik Akumulasi Jenis Part NG (Defect Breakdown)</h6>
                    <small class="text-muted">Rincian jenis cacat: Burry Grooving, Belum Proses, Grooving, Hole Sempit, Replating, Dacon, Gompal, Baret, Panjang, Pendek</small>
                </div>
                
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- Tombol Toggle Mode Bulanan / Tahunan -->
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-dark fw-bold" id="btnModeBulan" onclick="switchViewMode('bulan')">📅 Bulanan</button>
                        <button type="button" class="btn btn-outline-dark fw-bold" id="btnModeTahun" onclick="switchViewMode('tahun')">📊 Tahunan</button>
                    </div>

                    <!-- Dropdown Filter Tahun (Aktif saat mode bulanan) -->
                    <form action="<?= base_url('/admin/dashboard'); ?>" method="GET" id="formFilterTahun" class="d-flex align-items-center gap-1">
                        <select name="year" class="form-select form-select-sm fw-semibold" style="width: 100px;" onchange="this.form.submit()">
                            <?php for($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                                <option value="<?= $y; ?>" <?= ($selected_year == $y) ? 'selected' : ''; ?>><?= $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </div>
            </div>
            
            <div class="card-body">
                <div style="height: 380px;">
                    <canvas id="ngBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABEL AKTIVITAS TERKINI (2 KOLOM: PRODUKSI & PENGIRIMAN) -->
<div class="row g-4">
    <!-- Aktivitas Produksi & QC -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                <h6 class="fw-bold text-dark mb-0">⚙️ Riwayat Produksi & QC Terbaru</h6>
                <a href="<?= base_url('/admin/produksi'); ?>" class="btn btn-sm btn-link p-0 text-decoration-none">Lihat Semua →</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 20%;">Tanggal</th>
                                <th>Nama Barang</th>
                                <th style="width: 20%;" class="text-center">Tahapan</th>
                                <th style="width: 20%;" class="text-center">Output</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($recent_productions)): ?>
                                <?php foreach($recent_productions as $rp): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($rp['date'])); ?></td>
                                    <td class="fw-bold text-primary"><?= $rp['item_name']; ?></td>
                                    <td class="text-center"><span class="badge bg-secondary"><?= $rp['category']; ?></span></td>
                                    <td class="text-center fw-bold text-success"><?= $rp['quantity'] . ' ' . $rp['unit']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada riwayat aktivitas produksi.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Aktivitas Pengiriman FG -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                <h6 class="fw-bold text-dark mb-0">🚚 Riwayat Pengiriman FG Terbaru</h6>
                <a href="<?= base_url('/admin/pengiriman'); ?>" class="btn btn-sm btn-link p-0 text-decoration-none">Lihat Semua →</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 20%;">Tanggal</th>
                                <th>Barang (FG)</th>
                                <th>Tujuan PT</th>
                                <th style="width: 20%;" class="text-center">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($recent_deliveries)): ?>
                                <?php foreach($recent_deliveries as $rd): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($rd['date'])); ?></td>
                                    <td class="fw-bold text-primary"><?= $rd['item_name']; ?></td>
                                    <td><?= $rd['destination']; ?></td>
                                    <td class="text-center fw-bold text-success"><?= $rd['quantity']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada data pengiriman Finished Goods.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT CHART.JS UNTUK GRAFIK BATANG NG (BULANAN / TAHUNAN) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let ngChartInstance = null;

    const monthlyLabels = <?= json_encode($chart_months); ?>;
    const monthlyDatasets = <?= json_encode($monthly_ng_datasets); ?>;

    const yearlyLabels = <?= json_encode($chart_years); ?>;
    const yearlyDatasets = <?= json_encode($yearly_ng_datasets); ?>;

    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('ngBarChart').getContext('2d');
        
        ngChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: monthlyDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top' },
                    title: { display: true, text: 'Akumulasi Kategori Defect NG Berdasarkan Waktu' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    });

    function switchViewMode(mode) {
        let btnBulan = document.getElementById('btnModeBulan');
        let btnTahun = document.getElementById('btnModeTahun');
        let formTahun = document.getElementById('formFilterTahun');

        if (mode === 'bulan') {
            btnBulan.className = 'btn btn-dark fw-bold';
            btnTahun.className = 'btn btn-outline-dark fw-bold';
            formTahun.style.display = 'flex';

            ngChartInstance.data.labels = monthlyLabels;
            ngChartInstance.data.datasets = monthlyDatasets;
        } else {
            btnTahun.className = 'btn btn-dark fw-bold';
            btnBulan.className = 'btn btn-outline-dark fw-bold';
            formTahun.style.display = 'none';

            ngChartInstance.data.labels = yearlyLabels;
            ngChartInstance.data.datasets = yearlyDatasets;
        }

        ngChartInstance.update();
    }
</script>

<?= $this->endSection(); ?>