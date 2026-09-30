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
        .row-deleted {
            background-color: #fee2e2 !important;
            text-decoration: line-through;
            color: #991b1b;
        }
        .row-edited {
            background-color: #fef3c7 !important;
        }
        @media print {
            @page {
                size: A4 landscape;
                margin: 0.8cm;
            }
            body { background-color: #fff; font-size: 10.5px; }
            .no-print { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
            .print-header { display: block !important; }
        }
    </style>
</head>
<body class="p-3">

    <!-- Tombol Navigasi & Print -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="<?= base_url('/admin/laporan'); ?>" class="btn btn-secondary btn-sm fw-bold">← Kembali ke Pusat Laporan</a>
        <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold px-4 shadow-sm">🖨️ Cetak / Simpan PDF (Landscape)</button>
    </div>

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
                        <?php if(!empty($purchases)): ?>
                            <?php $no=1; foreach($purchases as $p): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($p['date'])); ?></td>
                                <td class="fw-bold text-primary"><?= $p['item_name']; ?></td>
                                <td><?= $p['supplier']; ?></td>
                                <td class="text-center fw-bold text-success"><?= $p['quantity']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-2">Tidak ada data pembelian langsung untuk item ini (Berasal dari stok material awal).</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. TAHAP PROSES PRODUKSI INTERNAL (CUTTING, MESIN OP1, WIP FINAL, REWORK) -->
    <div class="card shadow-sm mb-3 timeline-card" style="border-left-color: #198754;">
        <div class="card-header bg-light py-2 fw-bold text-success d-flex justify-content-between align-items-center">
            <span>2. Eksekusi Proses Produksi Lantai Pabrik (Cutting ➔ Mesin OP1 ➔ WIP Final ➔ Rework)</span>
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
                            <th style="width: 12%;">OPERATOR / STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $prodNonQc = array_filter($productions, fn($row) => $row['category'] !== 'QC Pengecekan' && !str_contains($row['status'], 'QC'));
                        ?>
                        <?php if(!empty($prodNonQc)): ?>
                            <?php $no=1; foreach($prodNonQc as $pr): 
                                $isDeleted = (!empty($pr['is_deleted']) && $pr['is_deleted'] == 1) || str_contains(strtolower($pr['status'] ?? ''), 'hapus');
                                $isEdited  = !empty($pr['is_edited']) && $pr['is_edited'] == 1;
                                $rowClass  = $isDeleted ? 'row-deleted' : ($isEdited ? 'row-edited' : '');
                            ?>
                            <tr class="<?= $rowClass; ?>">
                                <td class="text-center"><?= $no++; ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($pr['date'])); ?></td>
                                <td class="text-center"><span class="badge bg-secondary"><?= $pr['shift'] ?? 'Shift 1'; ?></span></td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= $pr['category']; ?></span>
                                    <?php if($isEdited): ?>
                                        <span class="badge bg-warning text-dark">Diedit</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $pr['material_name'] ?? '-'; ?></td>
                                <td class="text-center fw-bold text-danger"><?= $pr['material_amount'] ?? '-'; ?></td>
                                <td class="fw-bold text-primary"><?= $pr['item_name']; ?></td>
                                <td class="text-center fw-bold text-success"><?= $pr['quantity'] . ' ' . ($pr['unit'] ?? 'Pcs'); ?></td>
                                <td class="text-center fw-semibold">
                                    <?= $pr['operator_name'] ?? '-'; ?>
                                    <?php if($isDeleted): ?>
                                        <br><span class="badge bg-danger">DIHAPUS</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center text-muted py-2">Belum ada riwayat proses permesinan internal untuk item ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
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
                        <?php if(!empty($subcons)): ?>
                            <?php $no=1; foreach($subcons as $s): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($s['date'])); ?></td>
                                <td class="text-center">
                                    <span class="badge <?= ($s['type'] == 'OUT') ? 'bg-danger' : 'bg-success'; ?>">
                                        <?= $s['type']; ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-primary"><?= $s['item_name']; ?></td>
                                <td><?= $s['vendor_name']; ?></td>
                                <td class="text-center fw-bold"><?= $s['quantity']; ?></td>
                                <td class="text-center"><small><?= $s['monitoring'] ?? '-'; ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-2">Item ini tidak melalui tahapan plating subcon (Tanpa Plating).</td></tr>
                        <?php endif; ?>
                    </tbody>
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
                        ?>
                        <?php if(!empty($prodQc)): ?>
                            <?php $no=1; foreach($prodQc as $qc): 
                                $rep = (int)($qc['ng_repair_burry'] ?? 0) + (int)($qc['ng_repair_belum'] ?? 0) + (int)($qc['ng_repair_dimensi'] ?? 0);
                                $rej = (int)($qc['ng_reject_gompal'] ?? 0) + (int)($qc['ng_reject_baret'] ?? 0) + (int)($qc['ng_reject_hole'] ?? 0) + (int)($qc['ng_reject_replating'] ?? 0) + (int)($qc['ng_reject_dacon'] ?? 0) + (int)($qc['ng_reject_panjang'] ?? 0) + (int)($qc['ng_reject_pendek'] ?? 0);
                                $isDeleted = (!empty($qc['is_deleted']) && $qc['is_deleted'] == 1) || str_contains(strtolower($qc['status'] ?? ''), 'hapus');
                                $rowClass  = $isDeleted ? 'row-deleted' : '';
                            ?>
                            <tr class="<?= $rowClass; ?>">
                                <td><?= $no++; ?></td>
                                <td><?= date('d/m/Y', strtotime($qc['date'])); ?></td>
                                <td><?= $qc['material_name'] ?? '-'; ?></td>
                                <td class="fw-bold"><?= number_format((int)$qc['quantity']); ?></td>
                                <td class="fw-bold text-success"><?= number_format((int)($qc['qty_ok'] ?? 0)); ?></td>
                                <td class="fw-bold text-danger"><?= number_format((int)($qc['qty_ng'] ?? 0)); ?></td>
                                <td><span class="badge bg-warning text-dark"><?= $rep; ?> Pcs</span></td>
                                <td><span class="badge bg-danger"><?= $rej; ?> Pcs</span></td>
                                <td class="fw-semibold">
                                    <?= $qc['operator_name'] ?? '-'; ?>
                                    <?php if($isDeleted): ?>
                                        <br><span class="badge bg-danger">DIHAPUS</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center text-muted py-2">Belum ada riwayat pengecekan QC untuk item ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
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
                        <?php if(!empty($deliveries)): ?>
                            <?php $no=1; foreach($deliveries as $d): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($d['date'])); ?></td>
                                <td class="fw-bold text-primary"><?= $d['item_name']; ?></td>
                                <td><?= $d['destination']; ?></td>
                                <td class="text-center fw-bold text-success"><?= $d['quantity']; ?></td>
                                <td class="text-center"><span class="badge bg-success px-2 py-1">Terkirim</span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-2">Belum ada transaksi pengiriman barang jadi untuk item ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TANDA TANGAN RESMI PABRIK -->
    <div class="row mt-4 pt-3 text-center">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>