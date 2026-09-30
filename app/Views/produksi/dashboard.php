<?= $this->extend('produksi/layout'); ?>
<?= $this->section('content'); ?>

<!-- Header Welcome -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="p-4 bg-white rounded shadow-sm border-start border-primary border-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="fw-bold text-dark mb-1">Panel Kendali Shift Produksi & QC</h4>
            </div>
            <span class="badge bg-primary px-3 py-2 fs-6 fw-bold">📅 <?= date('d F Y'); ?></span>
        </div>
    </div>
</div>

<!-- 4 KARTU MONITORING BEBAN ANTREAN LANTAI KERJA -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-success border-4 h-100">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 11px;">Bahan Mentah Siap Potong</span>
                    <h3 class="fw-bold text-success mb-0 mt-1"><?= $antrean_material; ?> <span class="fs-6 text-muted">Item</span></h3>
                    <small class="text-muted" style="font-size: 11px;">Gudang Material</small>
                </div>
                <div class="fs-2 bg-success bg-opacity-10 text-success p-3 rounded-circle">✂️</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-primary border-4 h-100">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 11px;">Stok Siap OP 1</span>
                    <h3 class="fw-bold text-primary mb-0 mt-1"><?= $antrean_cutting; ?> <span class="fs-6 text-muted">Item</span></h3>
                    <small class="text-muted" style="font-size: 11px;">Hasil Cutting Siap Proses</small>
                </div>
                <div class="fs-2 bg-primary bg-opacity-10 text-primary p-3 rounded-circle">⚙️</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-info border-4 h-100">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 11px;">Antrean Subcon Siap QC</span>
                    <h3 class="fw-bold text-info mb-0 mt-1"><?= number_format($antrean_qc_subcon); ?> <span class="fs-6 text-muted">Pcs</span></h3>
                    <small class="text-muted" style="font-size: 11px;">Kiriman Vendor Masuk</small>
                </div>
                <div class="fs-2 bg-info bg-opacity-10 text-info p-3 rounded-circle">🔍</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-warning border-4 h-100">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 11px;">Part NG Siap Repair</span>
                    <h3 class="fw-bold text-warning-emphasis mb-0 mt-1"><?= number_format($antrean_rework); ?> <span class="fs-6 text-muted">Pcs</span></h3>
                    <small class="text-muted" style="font-size: 11px;">Menunggu Rework ke WIP/vendor</small>
                </div>
                <div class="fs-2 bg-warning bg-opacity-10 text-warning-emphasis p-3 rounded-circle">🔧</div>
            </div>
        </div>
    </div>
</div>

<!-- 5 TOMBOL AKSI STASIUN KERJA (STEP-BY-STEP WORKFLOW) -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="fw-bold text-dark mb-0">⚡ Form Input Cepat Berdasarkan Tahapan Kerja</h6>
            </div>
            <div class="card-body pt-0">
                <div class="row g-2">
                    <div class="col-md">
                        <a href="<?= base_url('/produksi/cutting'); ?>" class="btn btn-outline-success w-100 p-3 text-start h-100 rounded-3 border-2">
                            <div class="fs-4 mb-1">✂️</div>
                            <div class="fw-bold text-dark">1. Form OP 1</div>
                            <small class="text-muted d-block" style="font-size: 11px;">Potong bahan mentah</small>
                        </a>
                    </div>
                    <div class="col-md">
                        <a href="<?= base_url('/produksi/op1'); ?>" class="btn btn-outline-primary w-100 p-3 text-start h-100 rounded-3 border-2">
                            <div class="fs-4 mb-1">⚙️</div>
                            <div class="fw-bold text-dark">2. Form OP 2</div>
                            <small class="text-muted d-block" style="font-size: 11px;">Proses bubut / milling 1</small>
                        </a>
                    </div>
                    <div class="col-md">
                        <a href="<?= base_url('/produksi/final'); ?>" class="btn btn-outline-warning w-100 p-3 text-start h-100 rounded-3 border-2">
                            <div class="fs-4 mb-1">📦</div>
                            <div class="fw-bold text-dark">3. Form WIP</div>
                            <small class="text-muted d-block" style="font-size: 11px;">Finishing & perakitan internal</small>
                        </a>
                    </div>
                    <div class="col-md">
                        <a href="<?= base_url('/produksi/form-qc'); ?>" class="btn btn-outline-danger w-100 p-3 text-start h-100 rounded-3 border-2">
                            <div class="fs-4 mb-1">🔍</div>
                            <div class="fw-bold text-dark">4. Form QC</div>
                            <small class="text-muted d-block" style="font-size: 11px;">Inspeksi OK / Reject / Repair</small>
                        </a>
                    </div>
                    <div class="col-md">
                        <a href="<?= base_url('/produksi/repair'); ?>" class="btn btn-outline-dark w-100 p-3 text-start h-100 rounded-3 border-2">
                            <div class="fs-4 mb-1">🔧</div>
                            <div class="fw-bold text-dark">5. Rework Part NG</div>
                            <small class="text-muted d-block" style="font-size: 11px;">Perbaiki part cacat ke WIP/Vendor</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RINGKASAN OUTPUT SHIFT HARI INI & TABEL PEKERJAAN TERAKHIR -->
<div class="row g-4">
    <!-- Ringkasan Shift Hari Ini -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white py-3">
                <h6 class="fw-bold mb-0">📊 Ringkasan Shift Hari Ini</h6>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-around">
                <div class="p-3 bg-light rounded border text-center mb-3">
                    <span class="text-muted small fw-bold d-block">OUTPUT PRODUKSI HARI INI</span>
                    <h2 class="fw-bold text-primary mb-0 mt-1"><?= number_format($output_today); ?> <span class="fs-6">Pcs</span></h2>
                    <small class="text-muted" style="font-size: 11px;">Total dari seluruh mesin</small>
                </div>
                <div class="p-3 bg-light rounded border text-center">
                    <span class="text-muted small fw-bold d-block">LOLOS INSPEKSI QC HARI INI</span>
                    <h2 class="fw-bold text-success mb-0 mt-1"><?= number_format($qc_ok_today); ?> <span class="fs-6">Pcs</span></h2>
                    <small class="text-muted" style="font-size: 11px;">Masuk stok Finished Goods</small>
                </div>
                <div class="mt-3 text-center">
                    <a href="<?= base_url('/produksi/riwayat'); ?>" class="btn btn-outline-dark btn-sm fw-bold w-100 py-2">
                        📋 Buka Riwayat Produksi Lengkap →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Pengerjaan Terkini -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                <h6 class="fw-bold text-dark mb-0">⏱️ Riwayat Input Pekerjaan Terkini</h6>
                <span class="badge bg-light text-dark border">Live Feed 6 Terakhir</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%;">Tanggal</th>
                                <th style="width: 10%;">Shift</th>
                                <th style="width: 20%;">Tahapan</th>
                                <th>Nama Komponen</th>
                                <th style="width: 15%;" class="text-center">Output</th>
                                <th style="width: 15%;">PIC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($recent_activities)): ?>
                                <?php foreach($recent_activities as $ra): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($ra['date'])); ?></td>
                                    <td><span class="badge bg-secondary"><?= $ra['shift'] ?? 'Shift 1'; ?></span></td>
                                    <td>
                                        <?php if($ra['category'] === 'Cutting'): ?>
                                            <span class="badge bg-success">✂️ Cutting</span>
                                        <?php elseif($ra['category'] === 'WIP OP1'): ?>
                                            <span class="badge bg-primary">⚙️ Mesin OP1</span>
                                        <?php elseif($ra['category'] === 'WIP Final'): ?>
                                            <span class="badge bg-warning text-dark">📦 WIP Final</span>
                                        <?php elseif($ra['category'] === 'QC Pengecekan'): ?>
                                            <span class="badge bg-danger">🔍 QC Check</span>
                                        <?php else: ?>
                                            <span class="badge bg-dark"><?= $ra['category']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-primary"><?= $ra['item_name']; ?></td>
                                    <td class="text-center fw-bold text-success"><?= number_format((float)$ra['quantity']); ?> <?= $ra['unit'] ?? 'Pcs'; ?></td>
                                    <td class="fw-semibold text-dark"><?= $ra['operator_name'] ?? '-'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada aktivitas pengerjaan produksi yang tercatat.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>