<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Laporan Riwayat Alur Produk - PT Sigma'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-size: 12px; }
        .timeline-card { border-left: 4px solid #0d6efd; margin-bottom: 20px; }
        .table-custom thead th {
            background-color: #1e293b !important;
            color: #ffffff !important;
            text-align: center;
            vertical-align: middle;
            font-size: 11px;
            padding: 6px 4px;
        }
        .table-custom tbody td {
            font-size: 11.5px;
            padding: 6px 4px;
        }
        .table-custom tfoot td {
            background-color: #e2e8f0 !important;
            font-weight: bold;
            font-size: 11.5px;
            padding: 6px 4px;
        }
        @media print {
            @page {
                size: A4 landscape;
                margin: 0.8cm;
            }
            body { background-color: #fff; font-size: 10px; }
            .no-print { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
            .print-header { display: block !important; }
        }
    </style>
</head>
<body class="p-3">

    <?php 
        $parseNum = function($val) {
            if (empty($val) || $val === '-') return 0;
            return (float) filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        };
    ?>

    <!-- Tombol Navigasi, Print & Download Excel Server-Side -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print flex-wrap gap-2">
        <a href="<?= base_url('/admin/laporan'); ?>" class="btn btn-secondary btn-sm fw-bold">← Kembali ke Pusat Laporan</a>
        <div class="d-flex gap-2">
            <a href="<?= base_url('/admin/laporan/export-history-excel?item_name=' . urlencode($selected_item)); ?>" class="btn btn-success btn-sm fw-bold px-3 shadow-sm">
                📥 Download Excel (.xls)
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold px-4 shadow-sm">
                🖨️ Cetak / Simpan PDF (Landscape)
            </button>
        </div>
    </div>

    <!-- Container Cetak & Tampilan -->
    <div>

        <!-- KOP RESMI LAPORAN MANUFAKTUR -->
        <div class="text-center pb-3 mb-3 border-bottom border-2 border-dark">
            <h3 class="fw-bold text-uppercase mb-1 text-primary" style="letter-spacing: 1.5px;">PT SIGMA MANUFACTURING</h3>
            <p class="mb-0 text-muted small">Kawasan Industri Manufaktur, Blok C No. 12, Jawa Barat | Telp: (021) 8901234</p>
            <h5 class="fw-bold text-dark mt-2 mb-1 text-uppercase">LAPORAN PENELUSURAN RIWAYAT ALUR PRODUK (END-TO-END TRACEABILITY)</h5>
            <span class="badge bg-dark px-3 py-2 fs-6">ITEM / KOMPONEN: <?= htmlspecialchars($selected_item, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <!-- 1. TAHAP INBOUND / PEMBELIAN BAHAN BAKU -->
        <div class="card shadow-sm mb-3 timeline-card">
            <div class="card-header bg-light py-2 fw-bold text-primary">
                1. Penerimaan Bahan Baku Mentah dari Supplier (Inbound Material)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 table-custom">
                        <thead>
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 15%;">TANGGAL</th>
                                <th>NAMA BAHAN BAKU</th>
                                <th style="width: 35%;">NAMA SUPPLIER</th>
                                <th style="width: 20%;">JUMLAH MASUK</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $totBeli = 0; ?>
                            <?php if(!empty($purchases)): ?>
                                <?php $no=1; foreach($purchases as $p): 
                                    $totBeli += $parseNum($p['quantity']);
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($p['date'])); ?></td>
                                    <td class="fw-bold text-primary"><?= esc($p['item_name']); ?></td>
                                    <td><?= esc($p['supplier']); ?></td>
                                    <td class="text-center fw-bold text-success"><?= esc($p['quantity']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-2">Tidak ada data pembelian langsung untuk item ini (Berasal dari stok material awal).</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if(!empty($purchases)): ?>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end pe-3">TOTAL MATERIAL MASUK:</td>
                                <td class="text-center text-success fw-bold"><?= number_format($totBeli); ?></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. TAHAP PROSES PRODUKSI INTERNAL -->
        <div class="card shadow-sm mb-3 timeline-card" style="border-left-color: #198754;">
            <div class="card-header bg-light py-2 fw-bold text-success">
                2. Eksekusi Proses Produksi Lantai Pabrik (Cutting ➔ Mesin OP1 ➔ WIP Final ➔ Rework)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 table-custom">
                        <thead>
                            <tr>
                                <th style="width: 4%;">NO</th>
                                <th style="width: 10%;">TANGGAL</th>
                                <th style="width: 8%;">SHIFT</th>
                                <th style="width: 12%;">TAHAPAN</th>
                                <th style="width: 20%;">MATERIAL ASAL</th>
                                <th style="width: 12%;">QTY PAKAI</th>
                                <th>HASIL PROSES</th>
                                <th style="width: 12%;">QTY HASIL</th>
                                <th style="width: 12%;">OPERATOR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $prodNonQc = array_filter($productions, fn($row) => $row['category'] !== 'QC Pengecekan' && !str_contains($row['status'], 'QC'));
                                $totPakai = 0; $totHasil = 0;
                            ?>
                            <?php if(!empty($prodNonQc)): ?>
                                <?php $no=1; foreach($prodNonQc as $pr): 
                                    $totPakai += $parseNum($pr['material_amount'] ?? 0);
                                    $totHasil += $parseNum($pr['quantity'] ?? 0);
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($pr['date'])); ?></td>
                                    <td class="text-center"><span class="badge bg-secondary"><?= esc($pr['shift'] ?? 'Shift 1'); ?></span></td>
                                    <td class="text-center"><span class="badge bg-success"><?= esc($pr['category']); ?></span></td>
                                    <td><?= esc($pr['material_name'] ?? '-'); ?></td>
                                    <td class="text-center fw-bold text-danger"><?= esc($pr['material_amount'] ?? '-'); ?></td>
                                    <td class="fw-bold text-primary"><?= esc($pr['item_name']); ?></td>
                                    <td class="text-center fw-bold text-success"><?= esc($pr['quantity'] . ' ' . $pr['unit']); ?></td>
                                    <td class="text-center fw-semibold"><?= esc($pr['operator_name'] ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center text-muted py-2">Belum ada riwayat proses permesinan internal untuk item ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if(!empty($prodNonQc)): ?>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end pe-3">TOTAL PRODUKSI:</td>
                                <td class="text-center text-danger fw-bold"><?= number_format($totPakai); ?></td>
                                <td></td>
                                <td class="text-center text-success fw-bold"><?= number_format($totHasil); ?> Pcs</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. TAHAP SUBCON / PLATING OUTSOURCE -->
        <div class="card shadow-sm mb-3 timeline-card" style="border-left-color: #ffc107;">
            <div class="card-header bg-light py-2 fw-bold text-dark">
                3. Pengiriman & Penerimaan Pewarnaan / Plating Vendor (Subcon Outsource)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 table-custom">
                        <thead>
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 12%;">TANGGAL</th>
                                <th style="width: 10%;">TIPE</th>
                                <th>NAMA BARANG</th>
                                <th style="width: 25%;">VENDOR EKSTERNAL</th>
                                <th style="width: 15%;">JUMLAH (PCS / KG)</th>
                                <th style="width: 20%;">STATUS MONITORING</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $totSubOut = 0; $totSubIn = 0; ?>
                            <?php if(!empty($subcons)): ?>
                                <?php $no=1; foreach($subcons as $s): 
                                    $qVal = $parseNum($s['quantity']);
                                    if ($s['type'] === 'OUT') $totSubOut += $qVal;
                                    if ($s['type'] === 'IN')  $totSubIn  += $qVal;
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($s['date'])); ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= ($s['type'] == 'OUT') ? 'bg-danger' : 'bg-success'; ?>">
                                            <?= $s['type']; ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-primary"><?= esc($s['item_name']); ?></td>
                                    <td><?= esc($s['vendor_name']); ?></td>
                                    <td class="text-center fw-bold"><?= esc($s['quantity']); ?></td>
                                    <td class="text-center"><small><?= esc($s['monitoring'] ?? '-'); ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center text-muted py-2">Item ini tidak melalui tahapan plating subcon (Tanpa Plating).</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if(!empty($subcons)): ?>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end pe-3">TOTAL SUBCON:</td>
                                <td class="text-center fw-bold">
                                    <span class="text-danger">OUT: <?= number_format($totSubOut); ?></span> | 
                                    <span class="text-success">IN: <?= number_format($totSubIn); ?></span>
                                </td>
                                <td class="text-center text-warning-emphasis">
                                    Sisa: <?= number_format(max(0, $totSubOut - $totSubIn)); ?> Pcs
                                </td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. TAHAP INSPEKSI KUALITAS QC & REKAP NG -->
        <div class="card shadow-sm mb-3 timeline-card" style="border-left-color: #dc3545;">
            <div class="card-header bg-light py-2 fw-bold text-danger">
                4. Hasil Pengecekan Kualitas (Final Inspection QC & Rincian Part NG)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 table-custom text-center">
                        <thead>
                            <tr>
                                <th style="width: 4%;">NO</th>
                                <th style="width: 10%;">TANGGAL</th>
                                <th style="width: 18%;">SUMBER BARANG</th>
                                <th style="width: 10%;">QTY CHECK</th>
                                <th style="width: 10%;">QTY OK (FG)</th>
                                <th style="width: 10%;">TOTAL NG</th>
                                <th style="width: 12%;">REPAIR</th>
                                <th style="width: 12%;">REJECT</th>
                                <th style="width: 14%;">INSPECTOR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $prodQc = array_filter($productions, fn($row) => $row['category'] === 'QC Pengecekan' || str_contains($row['status'], 'QC'));
                                $totCheck = 0; $totOk = 0; $totNg = 0; $totRep = 0; $totRej = 0;
                            ?>
                            <?php if(!empty($prodQc)): ?>
                                <?php $no=1; foreach($prodQc as $qc): 
                                    $rep = (int)($qc['ng_repair_burry'] ?? 0) + (int)($qc['ng_repair_belum'] ?? 0) + (int)($qc['ng_repair_dimensi'] ?? 0);
                                    $rej = (int)($qc['ng_reject_gompal'] ?? 0) + (int)($qc['ng_reject_baret'] ?? 0) + (int)($qc['ng_reject_hole'] ?? 0) + (int)($qc['ng_reject_replating'] ?? 0) + (int)($qc['ng_reject_dacon'] ?? 0) + (int)($qc['ng_reject_panjang'] ?? 0) + (int)($qc['ng_reject_pendek'] ?? 0);
                                    
                                    $cVal  = $parseNum($qc['quantity']);
                                    $okVal = (float)($qc['qty_ok'] ?? 0);
                                    $ngVal = (float)($qc['qty_ng'] ?? ($rep + $rej));

                                    $totCheck += $cVal;
                                    $totOk    += $okVal;
                                    $totNg    += $ngVal;
                                    $totRep   += $rep;
                                    $totRej   += $rej;
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d/m/Y', strtotime($qc['date'])); ?></td>
                                    <td><?= esc($qc['material_name'] ?? '-'); ?></td>
                                    <td class="fw-bold"><?= number_format((int)$qc['quantity']); ?></td>
                                    <td class="fw-bold text-success"><?= number_format((int)($qc['qty_ok'] ?? 0)); ?></td>
                                    <td class="fw-bold text-danger"><?= number_format((int)$ngVal); ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= $rep; ?> Pcs</span></td>
                                    <td><span class="badge bg-danger"><?= $rej; ?> Pcs</span></td>
                                    <td class="fw-semibold"><?= esc($qc['operator_name'] ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center text-muted py-2">Belum ada riwayat pengecekan QC untuk item ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if(!empty($prodQc)): ?>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end pe-3">TOTAL INSPEKSI QC:</td>
                                <td><?= number_format($totCheck); ?></td>
                                <td class="text-success"><?= number_format($totOk); ?></td>
                                <td class="text-danger"><?= number_format($totNg); ?></td>
                                <td><?= number_format($totRep); ?> Pcs</td>
                                <td><?= number_format($totRej); ?> Pcs</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- 5. TAHAP PENGIRIMAN PRODUK JADI KE CUSTOMER -->
        <div class="card shadow-sm mb-4 timeline-card" style="border-left-color: #0dcaf0;">
            <div class="card-header bg-light py-2 fw-bold text-info-emphasis">
                5. Riwayat Pengiriman Produk Jadi ke Customer (Delivery Finished Goods)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 table-custom">
                        <thead>
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 15%;">TANGGAL KIRIM</th>
                                <th>NAMA BARANG</th>
                                <th style="width: 35%;">PT / CUSTOMER TUJUAN</th>
                                <th style="width: 15%;">JUMLAH KIRIM</th>
                                <th style="width: 15%;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $totKirim = 0; ?>
                            <?php if(!empty($deliveries)): ?>
                                <?php $no=1; foreach($deliveries as $d): 
                                    $totKirim += $parseNum($d['quantity']);
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($d['date'])); ?></td>
                                    <td class="fw-bold text-primary"><?= esc($d['item_name']); ?></td>
                                    <td><?= esc($d['destination']); ?></td>
                                    <td class="text-center fw-bold text-success"><?= esc($d['quantity']); ?></td>
                                    <td class="text-center"><span class="badge bg-success px-2 py-1">Terkirim</span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted py-2">Belum ada transaksi pengiriman barang jadi untuk item ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if(!empty($deliveries)): ?>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end pe-3">TOTAL PENGIRIMAN FG:</td>
                                <td class="text-center text-success fw-bold"><?= number_format($totKirim); ?> Pcs</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- TANDA TANGAN RESMI PABRIK SAAT DICETAK -->
        <div class="row mt-4 pt-3 text-center print-signatures">
            <div class="col-4">
                <p class="mb-5 fw-bold">Dibuat Oleh (Admin/PPIC),</p>
                <p class="fw-bold text-decoration-underline mb-0"><?= session()->get('full_name') ?? 'Staff Admin'; ?></p>
                <small class="text-muted">PT Sigma Manufacturing</small>
            </div>
            <div class="col-4">
                <p class="mb-5 fw-bold">Diperiksa Oleh (QC Head),</p>
                <p class="fw-bold text-decoration-underline mb-0">( ........................................ )</p>
                <small class="text-muted">Quality Control Supervisor</small>
            </div>
            <div class="col-4">
                <p class="mb-5 fw-bold">Disetujui Oleh (Factory Manager),</p>
                <p class="fw-bold text-decoration-underline mb-0">( ........................................ )</p>
                <small class="text-muted">Plant / Operational Head</small>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>