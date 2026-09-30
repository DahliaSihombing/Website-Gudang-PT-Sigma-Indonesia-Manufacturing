<?= $this->extend('produksi/layout'); ?>
<?= $this->section('content'); ?>
<div class="container-fluid">
    <!-- Header Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Riwayat Produksi & QC Harian</h4>
            <p class="text-muted small mb-0">Daftar laporan hasil kerja produksi berdasarkan tahapan proses dan pengecekan kualitas.</p>
        </div>
    </div>

    <!-- Form Filter Tanggal -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form method="GET" action="/produksi/riwayat" class="row g-2 align-items-end">
                <input type="hidden" name="kategori" value="<?= $active_tab; ?>">
                <div class="col-md-4">
                    <label class="form-label fw-bold small mb-1">Dari Tanggal</label>
                    <input type="date" class="form-control form-control-sm" name="start_date" value="<?= $start_date ?? ''; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small mb-1">Sampai Tanggal</label>
                    <input type="date" class="form-control form-control-sm" name="end_date" value="<?= $end_date ?? ''; ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold w-100">🔍 Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="/produksi/riwayat?kategori=<?= $active_tab; ?>" class="btn btn-secondary btn-sm fw-semibold w-100">🔄 Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tombol Navigasi Tab Kategori Horizontal (Termasuk Tab Repair) -->
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="<?= base_url('/produksi/riwayat?kategori=Semua'); ?>" class="btn btn-sm px-3 py-2 fw-bold <?= (!isset($active_tab) || $active_tab == 'Semua') ? 'btn-dark shadow-sm' : 'btn-light border text-dark'; ?>">
            📋 Semua Riwayat
        </a>
        <a href="<?= base_url('/produksi/riwayat?kategori=' . urlencode('Cutting')); ?>" class="btn btn-sm px-3 py-2 fw-bold <?= (isset($active_tab) && $active_tab == 'Cutting') ? 'btn-success text-white shadow-sm' : 'btn-light border text-dark'; ?>">
            ✂️ OP 1
        </a>
        <a href="<?= base_url('/produksi/riwayat?kategori=' . urlencode('WIP OP1')); ?>" class="btn btn-sm px-3 py-2 fw-bold <?= (isset($active_tab) && $active_tab == 'WIP OP1') ? 'btn-primary text-white shadow-sm' : 'btn-light border text-dark'; ?>">
            ⚙️ OP 2
        </a>
        <a href="<?= base_url('/produksi/riwayat?kategori=' . urlencode('WIP Final')); ?>" class="btn btn-sm px-3 py-2 fw-bold <?= (isset($active_tab) && $active_tab == 'WIP Final') ? 'btn-warning text-dark shadow-sm' : 'btn-light border text-dark'; ?>">
            📦 WIP
        </a>
        <a href="<?= base_url('/produksi/riwayat?kategori=QC'); ?>" class="btn btn-sm px-3 py-2 fw-bold <?= (isset($active_tab) && $active_tab == 'QC') ? 'btn-danger text-white shadow-sm' : 'btn-light border text-dark'; ?>">
            🔍 QC
        </a>
        <a href="<?= base_url('/produksi/riwayat?kategori=Repair'); ?>" class="btn btn-sm px-3 py-2 fw-bold <?= (isset($active_tab) && $active_tab == 'Repair') ? 'btn-info text-white shadow-sm' : 'btn-light border text-dark'; ?>">
            🔧 Rework
        </a>
    </div>

    <?php if(session()->getFlashdata('success')):?>
        <div class="alert alert-success py-2 small"><?= session()->getFlashdata('success'); ?></div>
    <?php endif;?>
    <?php if(session()->getFlashdata('error')):?>
        <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error'); ?></div>
    <?php endif;?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center py-2">
            <span>Menampilkan Data: [ <?= ($active_tab == 'QC') ? 'QC Pengecekan' : $active_tab; ?> ]</span>
            <small class="text-light" style="font-size: 11px;">Total: <?= count($productions); ?> Baris Data</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                
                <?php if (isset($active_tab) && $active_tab == 'QC'): ?>
                    <!-- TABEL KHUSUS QC -->
                    <table class="table table-hover table-bordered align-middle mb-0 small custom-table text-center">
                        <thead>
                            <tr>
                                <th rowspan="3" style="width: 3%; vertical-align: middle;">NO</th>
                                <th rowspan="3" style="width: 8%; vertical-align: middle;">DATE</th>
                                <th rowspan="3" style="width: 15%; vertical-align: middle;">PART NAME / BARANG</th>
                                <th rowspan="3" style="width: 6%; vertical-align: middle;">QTY CHECK</th>
                                <th rowspan="3" style="width: 6%; vertical-align: middle;">QTY OK</th>
                                <th rowspan="3" style="width: 6%; vertical-align: middle;" class="bg-danger text-white">GRAND TOTAL NG</th>
                                <th colspan="4" class="bg-warning text-dark fw-bold">REPAIR</th>
                                <th colspan="8" class="bg-danger text-white fw-bold">REJECT</th>
                                <th rowspan="3" style="width: 8%; vertical-align: middle;">PIC</th>
                                <th rowspan="3" style="width: 7%; vertical-align: middle;">AKSI</th>
                            </tr>
                            <tr>
                                <th colspan="2" class="bg-light text-dark py-1">VISUAL</th>
                                <th class="bg-light text-dark py-1">DIMENSI</th>
                                <th rowspan="2" class="bg-warning text-dark py-1" style="vertical-align: middle; width: 4%;">TOTAL REPAIR</th>
                                <th class="bg-light text-dark py-1" style="width: 4%;">HOLE</th>
                                <th class="bg-light text-dark py-1" style="width: 4%;">REPLATING</th>
                                <th class="bg-light text-dark py-1" style="width: 4%;">DACON</th>
                                <th class="bg-light text-dark py-1" style="width: 4%;">GOMPAL</th>
                                <th class="bg-light text-dark py-1" style="width: 4%;">BARET</th>
                                <th class="bg-light text-dark py-1" style="width: 4%;">PANJANG</th>
                                <th class="bg-light text-dark py-1" style="width: 4%;">PENDEK</th>
                                <th rowspan="2" class="bg-danger text-white py-1" style="vertical-align: middle; width: 4%;">TOTAL REJECT</th>
                            </tr>
                            <tr style="font-size: 10px;">
                                <th class="bg-light text-secondary py-1">BURRY GROOVING</th>
                                <th class="bg-light text-secondary py-1">BELUM PROSES</th>
                                <th class="bg-light text-secondary py-1">GROOVING</th>
                                <th class="bg-light text-secondary py-1">SEMPIT</th>
                                <th class="bg-light text-secondary py-1">-</th>
                                <th class="bg-light text-secondary py-1">-</th>
                                <th class="bg-light text-secondary py-1">VISUAL</th>
                                <th class="bg-light text-secondary py-1">VISUAL</th>
                                <th class="bg-light text-secondary py-1">DIMENSI</th>
                                <th class="bg-light text-secondary py-1">DIMENSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($productions)): ?>
                                <tr>
                                    <td colspan="20" class="text-center py-4 text-muted">Belum ada riwayat pengecekan QC.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no=1; foreach($productions as $row): 
                                    $repBurry = (int)($row['ng_repair_burry'] ?? 0);
                                    $repBelum = (int)($row['ng_repair_belum'] ?? 0);
                                    $repDim   = (int)($row['ng_repair_dimensi'] ?? 0);
                                    $totRep   = $repBurry + $repBelum + $repDim;

                                    $rejHole  = (int)($row['ng_reject_hole'] ?? 0);
                                    $rejRep   = (int)($row['ng_reject_replating'] ?? 0);
                                    $rejDac   = (int)($row['ng_reject_dacon'] ?? 0);
                                    $rejGomp  = (int)($row['ng_reject_gompal'] ?? 0);
                                    $rejBar   = (int)($row['ng_reject_baret'] ?? 0);
                                    $rejPanj  = (int)($row['ng_reject_panjang'] ?? 0);
                                    $rejPend  = (int)($row['ng_reject_pendek'] ?? 0);
                                    $totRej   = $rejHole + $rejRep + $rejDac + $rejGomp + $rejBar + $rejPanj + $rejPend;

                                    $grandNg  = $totRep + $totRej;
                                    if ($grandNg == 0 && (int)($row['qty_ng'] ?? 0) > 0) {
                                        $grandNg = (int)$row['qty_ng'];
                                    }

                                    $tglInput = !empty($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : '';
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['date'])); ?></td>
                                    <td class="text-start fw-bold text-primary">
                                        <?= $row['item_name']; ?>
                                        <small class="text-muted d-block" style="font-size: 10px;">
                                            Source: <?= $row['material_name'] ?? '-'; ?>
                                        </small>
                                    </td>
                                    <td class="fw-bold"><?= number_format((int)$row['quantity']); ?></td>
                                    <td class="fw-bold text-success"><?= number_format((int)($row['qty_ok'] ?? 0)); ?></td>
                                    <td class="fw-bold text-danger bg-light"><?= number_format($grandNg); ?></td>
                                    
                                    <td><?= $repBurry > 0 ? $repBurry : '-'; ?></td>
                                    <td><?= $repBelum > 0 ? $repBelum : '-'; ?></td>
                                    <td><?= $repDim > 0 ? $repDim : '-'; ?></td>
                                    <td class="fw-bold text-warning-emphasis bg-light"><?= $totRep > 0 ? $totRep : '-'; ?></td>

                                    <td><?= $rejHole > 0 ? $rejHole : '-'; ?></td>
                                    <td><?= $rejRep > 0 ? $rejRep : '-'; ?></td>
                                    <td><?= $rejDac > 0 ? $rejDac : '-'; ?></td>
                                    <td><?= $rejGomp > 0 ? $rejGomp : '-'; ?></td>
                                    <td><?= $rejBar > 0 ? $rejBar : '-'; ?></td>
                                    <td><?= $rejPanj > 0 ? $rejPanj : '-'; ?></td>
                                    <td><?= $rejPend > 0 ? $rejPend : '-'; ?></td>
                                    <td class="fw-bold text-danger bg-light"><?= $totRej > 0 ? $totRej : '-'; ?></td>

                                    <td class="fw-semibold text-dark"><?= $row['operator_name'] ?? '-'; ?></td>
                                    <td>
                                        <?php if ($tglInput === date('Y-m-d')): ?>
                                            <a href="<?= base_url('/produksi/edit/' . $row['id']); ?>" class="btn btn-warning btn-sm py-0 px-1 text-dark" title="Edit Data"><i class="fas fa-edit"></i> Edit</a>
                                            <a href="<?= base_url('/produksi/delete/' . $row['id']); ?>" class="btn btn-danger btn-sm py-0 px-1" onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus"><i class="fas fa-trash"></i> Hapus</a>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 10px;">Terkunci</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <?php else: ?>
                    <!-- TABEL STANDAR -->
                    <table class="table table-hover table-striped table-bordered align-middle mb-0 small custom-table">
                        <thead class="table-dark text-center">
                            <tr>
                                <th style="width: 4%;">NO</th>
                                <th style="width: 10%;">TANGGAL</th>
                                <th style="width: 9%;">SHIFT</th>
                                <th style="width: 20%;">MATERIAL / ASAL</th>
                                <th style="width: 11%;">JUMLAH PAKAI</th>
                                <th style="width: 20%;">HASIL PRODUKSI</th>
                                <th style="width: 10%;">OUTPUT</th>
                                <th style="width: 8%;">PIC</th>
                                <th style="width: 8%;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($productions)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">Belum ada riwayat produksi untuk kategori ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no=1; foreach($productions as $row): 
                                    $tglInput = !empty($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : '';
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($row['date'])); ?></td>
                                    <td class="text-center"><span class="badge bg-secondary"><?= $row['shift'] ?? 'Shift 1'; ?></span></td>
                                    
                                    <td>
                                        <div class="fw-semibold text-secondary"><?= $row['material_name'] ?? '-'; ?></div>
                                        <?php if(!empty($row['material_info'])): ?>
                                            <small class="text-muted" style="font-size: 10.5px;"><?= $row['material_info']; ?></small>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center fw-bold text-danger">
                                        <?= !empty($row['material_amount']) ? $row['material_amount'] : '-'; ?>
                                    </td>

                                    <td>
                                        <div class="fw-bold text-primary"><?= $row['item_name']; ?></div>
                                        <div>
                                            <?php if($row['status'] === 'Cutting'): ?>
                                                <span class="badge bg-success" style="font-size: 10px;">OP 1</span>
                                            <?php elseif($row['status'] === 'Proses Mesin OP1' || str_contains($row['status'], 'OP1')): ?>
                                                <span class="badge bg-primary" style="font-size: 10px;">OP 2</span>
                                            <?php elseif(str_contains($row['status'], 'Repair Selesai')): ?>
                                                <span class="badge bg-warning text-dark" style="font-size: 10px;">🔧 Rework NG ➔ WIP</span>
                                            <?php elseif(str_contains($row['status'], 'Kirim Repair') || str_contains($row['status'], 'Retur Material')): ?>
                                                <span class="badge bg-info text-dark" style="font-size: 10px;">🛠️ Vendor Repair/Retur</span>
                                            <?php elseif($row['status'] === 'Proses WIP Final' || str_contains($row['status'], 'WIP Final')): ?>
                                                <span class="badge bg-warning text-dark" style="font-size: 10px;">WIP</span>
                                            <?php elseif(str_contains(strtolower($row['status']), 'qc')): ?>
                                                <span class="badge bg-danger" style="font-size: 10px;">🔍 QC Check</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary" style="font-size: 10px;"><?= $row['status']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="text-center fw-bold text-success fs-6">
                                        <?= number_format((int)$row['quantity']); ?> <?= $row['unit'] ?? 'Pcs'; ?>
                                    </td>

                                    <td class="text-center fw-semibold text-dark">
                                        <?= $row['operator_name'] ?? '-'; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($tglInput === date('Y-m-d')): ?>
                                            <a href="<?= base_url('/produksi/edit/' . $row['id']); ?>" class="btn btn-warning btn-sm py-0 px-1 text-dark" title="Edit Data"><i class="fas fa-edit"></i> Edit</a>
                                            <a href="<?= base_url('/produksi/delete/' . $row['id']); ?>" class="btn btn-danger btn-sm py-0 px-1" onclick="return confirm('Yakin ingin menghapus data ini? Jumlah pakai akan dikembalikan ke master barang.')" title="Hapus"><i class="fas fa-trash"></i> Hapus</a>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 10px;">Terkunci</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<style>
    .custom-table thead th {
        background-color: #1e293b;
        color: #ffffff;
        vertical-align: middle;
        border-color: #334155 !important;
        padding: 8px 6px;
        font-size: 11px;
    }
    .custom-table tbody td {
        padding: 8px 6px;
        font-size: 12px;
    }
</style>

<?= $this->endSection(); ?>