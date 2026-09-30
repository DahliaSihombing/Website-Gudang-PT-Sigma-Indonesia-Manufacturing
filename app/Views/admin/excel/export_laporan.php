<?php
    $parseNum = function($val) {
        if (empty($val) || $val === '-') return 0;
        return (float) filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    };
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        body { font-family: Arial, sans-serif; }
        .kop-title { font-size: 14pt; font-weight: bold; color: #1e3a8a; text-align: center; }
        .kop-sub { font-size: 9pt; text-align: center; color: #555555; }
        .kop-doc { font-size: 11pt; font-weight: bold; text-align: center; margin-top: 5px; margin-bottom: 15px; color: #000; }
        .table-header { background-color: #1e293b; color: #ffffff; font-weight: bold; text-align: center; vertical-align: middle; }
        .table-data td, .table-header th { border: 0.5pt solid #94a3b8; padding: 6px; font-size: 10pt; vertical-align: middle; }
        .table-footer td { background-color: #e2e8f0; font-weight: bold; border: 0.5pt solid #475569; text-align: center; vertical-align: middle; }
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <table>
        <tr><td colspan="11" class="kop-title">PT SIGMA MANUFACTURING</td></tr>
        <tr><td colspan="11" class="kop-sub">Kawasan Industri Manufaktur, Blok C No. 12, Jawa Barat | Telp: (021) 8901234</td></tr>
        <tr><td colspan="11" class="kop-doc">LAPORAN <?= strtoupper($tipe_laporan); ?> <?= !empty($end_date) ? " (PER TANGGAL: " . date('d/m/Y', strtotime($end_date)) . ")" : ""; ?></td></tr>
        <tr><td></td></tr>
    </table>

    <table border="1" cellspacing="0" cellpadding="4">
    <!-- A. TABEL STOK GUDANG -->
    <?php if($tipe_laporan === 'stok'): ?>
        <?php $totMat = 0; $totCut = 0; $totOp1 = 0; $totWip = 0; $totPlat = 0; $totFc = 0; $totNg = 0; $totFg = 0; $totDel = 0; ?>
        <thead>
            <tr class="table-header">
                <th>NO</th>
                <th>NAMA PART / ITEM</th>
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
                <tr><td colspan="11" class="text-center">Tidak ada data stok.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach($report_data as $row): 
                    $totMat += $parseNum($row['material']); $totCut += $parseNum($row['cutting']);
                    $totOp1 += $parseNum($row['op1']); $totWip += $parseNum($row['wip_final']);
                    $totPlat += $parseNum($row['plating']); $totFc += $parseNum($row['fc']);
                    $totNg += $parseNum($row['ng']); $totFg += $parseNum($row['fg']);
                    $totDel += $parseNum($row['delivery']);
                ?>
                <tr class="table-data">
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-start" style="font-weight: bold; color: #1e3a8a;"><?= esc($row['item_name']); ?></td>
                    <td class="text-center"><?= $row['material']; ?></td>
                    <td class="text-center"><?= $row['cutting']; ?></td>
                    <td class="text-center"><?= $row['op1']; ?></td>
                    <td class="text-center"><?= $row['wip_final']; ?></td>
                    <td class="text-center"><?= $row['plating']; ?></td>
                    <td class="text-center"><?= $row['fc']; ?></td>
                    <td class="text-center" style="color:red; font-weight:bold;"><?= $row['ng']; ?></td>
                    <td class="text-center" style="color:green; font-weight:bold;"><?= $row['fg']; ?></td>
                    <td class="text-center"><?= $row['delivery']; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="table-footer">
                <td colspan="2" class="text-center">TOTAL SEMUA STOK</td>
                <td><?= number_format($totMat); ?></td>
                <td><?= number_format($totCut); ?></td>
                <td><?= number_format($totOp1); ?></td>
                <td><?= number_format($totWip); ?></td>
                <td><?= number_format($totPlat); ?></td>
                <td><?= number_format($totFc); ?></td>
                <td style="color:red;"><?= number_format($totNg); ?></td>
                <td style="color:green;"><?= number_format($totFg); ?></td>
                <td><?= number_format($totDel); ?></td>
            </tr>
        </tfoot>

    <!-- B. TABEL PEMBELIAN -->
    <?php elseif($tipe_laporan === 'pembelian'): ?>
        <?php $totBeli = 0; ?>
        <thead>
            <tr class="table-header">
                <th>NO</th><th>TANGGAL</th><th>NAMA MATERIAL</th><th>SUPPLIER</th><th>JUMLAH MASUK</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($report_data)): ?>
                <tr><td colspan="5" class="text-center">Tidak ada data pembelian.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach($report_data as $row): $totBeli += $parseNum($row['quantity']); ?>
                <tr class="table-data">
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['date'])); ?></td>
                    <td class="text-start" style="font-weight: bold;"><?= esc($row['item_name']); ?></td>
                    <td class="text-start"><?= esc($row['supplier']); ?></td>
                    <td class="text-center" style="font-weight:bold; color:green;"><?= esc($row['quantity']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="table-footer">
                <td colspan="4" class="text-end">TOTAL PEMBELIAN MATERIAL:</td>
                <td style="color:green;"><?= number_format($totBeli); ?></td>
            </tr>
        </tfoot>

    <!-- C. TABEL SUBCON -->
    <?php elseif($tipe_laporan === 'subcon'): ?>
        <?php $totOut = 0; $totIn = 0; ?>
        <thead>
            <tr class="table-header">
                <th>NO</th><th>TANGGAL</th><th>TIPE</th><th>NAMA BARANG</th><th>VENDOR</th><th>JUMLAH</th><th>MONITORING SISA</th><th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($report_data)): ?>
                <tr><td colspan="8" class="text-center">Tidak ada data subcon.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach($report_data as $row): 
                    $q = $parseNum($row['quantity']);
                    if ($row['type'] === 'OUT') $totOut += $q;
                    if ($row['type'] === 'IN')  $totIn  += $q;
                ?>
                <tr class="table-data">
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['date'])); ?></td>
                    <td class="text-center" style="font-weight: bold; color: <?= ($row['type'] == 'OUT') ? '#dc2626' : '#16a34a'; ?>;"><?= esc($row['type']); ?></td>
                    <td class="text-start" style="font-weight: bold;"><?= esc($row['item_name']); ?></td>
                    <td class="text-start"><?= esc($row['vendor_name']); ?></td>
                    <td class="text-center" style="font-weight:bold;"><?= esc($row['quantity']); ?></td>
                    <td class="text-start"><?= esc($row['monitoring']); ?></td>
                    <td class="text-center"><?= esc($row['status'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="table-footer">
                <td colspan="5" class="text-end">TOTAL (OUT: <?= number_format($totOut); ?> | IN: <?= number_format($totIn); ?>):</td>
                <td colspan="3">SELISIH OUTSTANDING: <?= number_format(max(0, $totOut - $totIn)); ?> Pcs</td>
            </tr>
        </tfoot>

    <!-- D. TABEL PRODUKSI -->
    <?php elseif($tipe_laporan === 'produksi'): ?>
        <?php $totOut = 0; $totOk = 0; $totNg = 0; ?>
        <thead>
            <tr class="table-header">
                <th>NO</th><th>TANGGAL</th><th>SHIFT</th><th>KATEGORI</th><th>NAMA BARANG HASIL</th><th>OUTPUT</th><th>QTY OK</th><th>QTY NG</th><th>PIC</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($report_data)): ?>
                <tr><td colspan="9" class="text-center">Tidak ada data produksi.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach($report_data as $row): 
                    $totOut += $parseNum($row['quantity']);
                    $totOk  += $parseNum($row['qty_ok'] ?? 0);
                    $totNg  += $parseNum($row['qty_ng'] ?? 0);
                ?>
                <tr class="table-data">
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['date'])); ?></td>
                    <td class="text-center"><?= esc($row['shift'] ?? 'Shift 1'); ?></td>
                    <td class="text-center"><?= esc($row['category']); ?></td>
                    <td class="text-start" style="font-weight: bold;"><?= esc($row['item_name']); ?></td>
                    <td class="text-center" style="font-weight:bold; color:green;"><?= esc($row['quantity'] . ' ' . ($row['unit'] ?? 'Pcs')); ?></td>
                    <td class="text-center" style="color:green;"><?= !empty($row['qty_ok']) ? number_format((float)$row['qty_ok']) : '-'; ?></td>
                    <td class="text-center" style="color:red;"><?= !empty($row['qty_ng']) ? number_format((float)$row['qty_ng']) : '-'; ?></td>
                    <td class="text-center"><?= esc($row['operator_name'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="table-footer">
                <td colspan="5" class="text-end">TOTAL PRODUKSI:</td>
                <td style="color:green;"><?= number_format($totOut); ?></td>
                <td style="color:green;"><?= number_format($totOk); ?></td>
                <td style="color:red;"><?= number_format($totNg); ?></td>
                <td>-</td>
            </tr>
        </tfoot>

    <!-- E. TABEL QC -->
    <?php elseif($tipe_laporan === 'qc'): ?>
        <?php $totCheck = 0; $totOk = 0; $totNg = 0; ?>
        <thead>
            <tr class="table-header">
                <th>NO</th><th>TANGGAL</th><th>NAMA BARANG</th><th>QTY CHECK</th><th>QTY OK</th><th>QTY NG</th><th>INSPECTOR</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($report_data)): ?>
                <tr><td colspan="7" class="text-center">Tidak ada data QC.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach($report_data as $row): 
                    $totCheck += $parseNum($row['quantity'] ?? 0);
                    $totOk    += $parseNum($row['qty_ok'] ?? 0);
                    $totNg    += $parseNum($row['qty_ng'] ?? 0);
                ?>
                <tr class="table-data">
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['date'] ?? $row['created_at'])); ?></td>
                    <td class="text-start" style="font-weight: bold;"><?= esc($row['item_name']); ?></td>
                    <td class="text-center"><?= number_format((float)($row['quantity'] ?? 0)); ?></td>
                    <td class="text-center" style="color:green; font-weight:bold;"><?= number_format((float)($row['qty_ok'] ?? 0)); ?></td>
                    <td class="text-center" style="color:red; font-weight:bold;"><?= number_format((float)($row['qty_ng'] ?? 0)); ?></td>
                    <td class="text-center"><?= esc($row['operator_name'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="table-footer">
                <td colspan="3" class="text-end">TOTAL QC INSPECTION:</td>
                <td><?= number_format($totCheck); ?></td>
                <td style="color:green;"><?= number_format($totOk); ?></td>
                <td style="color:red;"><?= number_format($totNg); ?></td>
                <td>-</td>
            </tr>
        </tfoot>

    <!-- F. TABEL NG -->
    <?php elseif($tipe_laporan === 'ng'): ?>
        <?php 
            $totNgCheck = 0; $totNgOk = 0; $totNgGrand = 0;
            $totBurry = 0; $totBelum = 0; $totDimensi = 0;
            $totGompal = 0; $totBaret = 0; $totHole = 0;
            $totReplating = 0; $totDacon = 0; $totPanjang = 0; $totPendek = 0;
        ?>
        <thead>
            <tr class="table-header">
                <th rowspan="2">DATE</th><th rowspan="2">QTY CHECK</th><th rowspan="2">QTY OK</th><th rowspan="2">GRAND TOTAL</th><th colspan="3">REPAIR</th><th colspan="7">REJECT</th><th rowspan="2">PIC</th>
            </tr>
            <tr class="table-header">
                <th>BURRY</th><th>BELUM</th><th>DIMENSI</th><th>GOMPAL</th><th>BARET</th><th>HOLE</th><th>REPLATING</th><th>DACON</th><th>PANJANG</th><th>PENDEK</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($report_data)): ?>
                <tr><td colspan="15" class="text-center">Tidak ada data part NG.</td></tr>
            <?php else: ?>
                <?php foreach($report_data as $row): 
                    $dOnly = isset($row['date']) ? date('d/m/Y', strtotime($row['date'])) : '-';
                    $qtyCheck = $row['quantity'] ?? 0;
                    $qtyOk = $row['qty_ok'] ?? 0;
                    
                    $burry = (float)($row['ng_repair_burry'] ?? 0);
                    $belum = (float)($row['ng_repair_belum'] ?? 0);
                    $dimensi = (float)($row['ng_repair_dimensi'] ?? 0);
                    
                    $gompal = (float)($row['ng_reject_gompal'] ?? 0);
                    $baret = (float)($row['ng_reject_baret'] ?? 0);
                    $hole = (float)($row['ng_reject_hole'] ?? 0);
                    $replating = (float)($row['ng_reject_replating'] ?? 0);
                    $dacon = (float)($row['ng_reject_dacon'] ?? 0);
                    $panjang = (float)($row['ng_reject_panjang'] ?? 0);
                    $pendek = (float)($row['ng_reject_pendek'] ?? 0);

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
                <tr class="table-data">
                    <td class="text-center"><?= $dOnly; ?></td>
                    <td class="text-center"><?= (float)$qtyCheck; ?></td>
                    <td class="text-center" style="color:green;"><?= (float)$qtyOk; ?></td>
                    <td class="text-center" style="color:red; font-weight:bold;"><?= $rowTotalNg; ?></td>
                    <td class="text-center"><?= $burry; ?></td><td class="text-center"><?= $belum; ?></td><td class="text-center"><?= $dimensi; ?></td>
                    <td class="text-center"><?= $gompal; ?></td><td class="text-center"><?= $baret; ?></td><td class="text-center"><?= $hole; ?></td>
                    <td class="text-center"><?= $replating; ?></td><td class="text-center"><?= $dacon; ?></td><td class="text-center"><?= $panjang; ?></td><td class="text-center"><?= $pendek; ?></td>
                    <td class="text-start"><?= esc($row['operator_name'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="table-footer">
                <td>TOTAL</td>
                <td><?= number_format($totNgCheck); ?></td>
                <td style="color:green;"><?= number_format($totNgOk); ?></td>
                <td style="color:red;"><?= number_format($totNgGrand); ?></td>
                <td><?= number_format($totBurry); ?></td><td><?= number_format($totBelum); ?></td><td><?= number_format($totDimensi); ?></td>
                <td><?= number_format($totGompal); ?></td><td><?= number_format($totBaret); ?></td><td><?= number_format($totHole); ?></td>
                <td><?= number_format($totReplating); ?></td><td><?= number_format($totDacon); ?></td><td><?= number_format($totPanjang); ?></td><td><?= number_format($totPendek); ?></td>
                <td>-</td>
            </tr>
        </tfoot>

    <!-- G. TABEL PENGIRIMAN -->
    <?php elseif($tipe_laporan === 'pengiriman'): ?>
        <?php $totKirim = 0; ?>
        <thead>
            <tr class="table-header">
                <th>NO</th><th>TANGGAL</th><th>NAMA BARANG (FG)</th><th>TUJUAN PT / CUSTOMER</th><th>JUMLAH</th><th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($report_data)): ?>
                <tr><td colspan="6" class="text-center">Tidak ada data pengiriman.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach($report_data as $row): $totKirim += $parseNum($row['quantity']); ?>
                <tr class="table-data">
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['date'])); ?></td>
                    <td class="text-start" style="font-weight: bold;"><?= esc($row['item_name']); ?></td>
                    <td class="text-start"><?= esc($row['destination']); ?></td>
                    <td class="text-center" style="font-weight:bold; color:green;"><?= esc($row['quantity']); ?></td>
                    <td class="text-center">Terkirim</td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="table-footer">
                <td colspan="4" class="text-end">TOTAL PENGIRIMAN FG:</td>
                <td style="color:green;"><?= number_format($totKirim); ?> Pcs</td>
                <td>-</td>
            </tr>
        </tfoot>
    <?php endif; ?>
    </table>
</body>
</html>