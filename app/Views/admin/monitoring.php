<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-0">
    
    <div class="mb-3 border-bottom pb-3">
        <h3 class="fw-bold text-dark">Monitoring Produksi & QC</h3>
        <p class="text-muted mb-0">Kelola dan pantau seluruh data aktivitas proses produksi serta hasil pengecekan QC pabrik.</p>
    </div>

    <!-- Tombol Navigasi Tab Kategori & Filter Tanggal -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="/admin/produksi" class="row g-3 align-items-end">
                <input type="hidden" name="kategori" value="<?= $active_tab; ?>">
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Dari Tanggal</label>
                    <input type="date" class="form-control form-control-sm" name="start_date" value="<?= $start_date ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Sampai Tanggal</label>
                    <input type="date" class="form-control form-control-sm" name="end_date" value="<?= $end_date ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold w-100">🔍 Filter Tanggal</button>
                </div>
                <div class="col-md-3">
                    <a href="/admin/produksi?kategori=<?= $active_tab; ?>" class="btn btn-secondary btn-sm fw-semibold w-100">🔄 Reset Filter</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tombol Navigasi Tab Kategori -->
    <div class="mb-4 d-flex flex-wrap gap-2">
        <a href="/admin/produksi?kategori=Semua" class="btn btn-sm <?= ($active_tab == 'Semua') ? 'btn-dark' : 'btn-outline-dark fw-semibold'; ?>">
            📋 Semua Riwayat
        </a>
        <a href="/admin/produksi?kategori=Cutting" class="btn btn-sm <?= ($active_tab == 'Cutting') ? 'btn-success' : 'btn-outline-success fw-semibold'; ?>">
            ✂️ OP 1
        </a>
        <a href="/admin/produksi?kategori=WIP OP1" class="btn btn-sm <?= ($active_tab == 'WIP OP1') ? 'btn-primary' : 'btn-outline-primary fw-semibold'; ?>">
            ⚙️ OP 2
        </a>
        <a href="/admin/produksi?kategori=WIP Final" class="btn btn-sm <?= ($active_tab == 'WIP Final') ? 'btn-warning text-dark' : 'btn-outline-warning text-dark fw-semibold'; ?>">
            📦 WIP
        </a>
        <a href="/admin/produksi?kategori=QC Pengecekan" class="btn btn-sm <?= ($active_tab == 'QC Pengecekan') ? 'btn-danger' : 'btn-outline-danger fw-semibold'; ?>">
            🔍 QC
        </a>
    </div>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success'); ?></div>
    <?php endif; ?>

    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error'); ?></div>
    <?php endif; ?>

    <!-- Kotak Tabel Data -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white py-3">
            <h6 class="m-0 font-weight-bold">Menampilkan Data: <?= $active_tab; ?></h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                
                <?php if ($active_tab == 'QC Pengecekan'): ?>
                    <!-- TABEL KHUSUS MONITORING QC ADMIN DENGAN RINCIAN LENGKAP -->
                    <table class="table table-hover table-bordered align-middle mb-0 small custom-table text-center">
                        <thead>
                            <tr>
                                <th rowspan="3" style="width: 3%; vertical-align: middle;">NO</th>
                                <th rowspan="3" style="width: 7%; vertical-align: middle;">DATE</th>
                                <th rowspan="3" style="width: 14%; vertical-align: middle;">PART NAME / BARANG</th>
                                <th rowspan="3" style="width: 6%; vertical-align: middle;">QTY CHECK</th>
                                <th rowspan="3" style="width: 6%; vertical-align: middle;">QTY OK</th>
                                <th rowspan="3" style="width: 5%; vertical-align: middle;" class="bg-danger text-white">GRAND TOTAL NG</th>
                                <th colspan="4" class="bg-warning text-dark fw-bold">REPAIR</th>
                                <th colspan="8" class="bg-danger text-white fw-bold">REJECT</th>
                                <th rowspan="3" style="width: 7%; vertical-align: middle;">PIC</th>
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
                                    <td colspan="19" class="text-center py-4 text-muted">Belum ada riwayat pengecekan QC.</td>
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
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <?php else: ?>
                    <!-- TABEL STANDAR DENGAN INFORMASI MATERIAL / BARANG ASAL -->
                    <table class="table table-striped table-bordered mb-0 align-middle custom-table">
                        <thead class="table-dark text-center">
                            <tr>
                                <th class="py-3" style="width: 5%;">NO</th>
                                <th class="py-3" style="width: 11%;">TANGGAL</th>
                                <th class="py-3" style="width: 9%;">SHIFT</th>
                                <th class="py-3" style="width: 24%;">NAMA BARANG / ITEM</th>
                                <th class="py-3" style="width: 13%;">JUMLAH KELUAR</th>
                                <th class="py-3" style="width: 13%;">JUMLAH HASIL</th>
                                <th class="py-3" style="width: 13%;">PIC</th>
                                <th class="py-3" style="width: 12%;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($productions)): ?>
                                <?php $no = 1; foreach($productions as $row): ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= date('d-m-Y', strtotime($row['date'])); ?></td>
                                    <td class="text-center"><span class="badge bg-secondary"><?= $row['shift'] ?? 'Shift 1'; ?></span></td>
                                    <td class="text-start ps-3">
                                        <div class="fw-bold text-primary"><?= $row['item_name']; ?></div>
                                        <small class="text-muted" style="font-size: 11px;">
                                            📦 Asal: <span class="fw-semibold text-dark"><?= !empty($row['material_name']) ? $row['material_name'] : '-'; ?></span>
                                        </small>
                                    </td>
                                    <td class="text-center text-danger fw-bold"><?= $row['material_amount'] ?? '-'; ?></td>
                                    <td class="text-center text-success fw-bold"><?= $row['quantity'] . ' ' . ($row['unit'] ?? 'Pcs'); ?></td>
                                    <td class="text-center fw-semibold"><?= $row['operator_name']; ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-warning btn-sm text-dark fw-semibold px-2 py-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id']; ?>" title="Edit">
                                            ✏️ Edit
                                        </button>
                                        <a href="/admin/produksi/delete/<?= $row['id']; ?>" class="btn btn-danger btn-sm fw-semibold px-2 py-1" onclick="return confirm('Yakin ingin menghapus data ini? Jumlah keluar akan dikembalikan ke master barang dan stok hasil produksi akan disesuaikan.');" title="Hapus">
                                            🗑️ Hapus
                                        </a>
                                    </td>
                                </tr>

                                <!-- MODAL EDIT PRODUKSI -->
                                <div class="modal fade" id="editModal<?= $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $row['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="/admin/produksi/update/<?= $row['id']; ?>" method="POST">
                                            <?= csrf_field(); ?>
                                            <div class="modal-content">
                                                <div class="modal-header bg-dark text-white">
                                                    <h5 class="modal-title fs-6" id="editModalLabel<?= $row['id']; ?>">Edit Data Produksi - <?= $row['item_name']; ?></h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small">Jumlah Keluar (Material Amount)</label>
                                                        <input type="text" class="form-control" name="material_amount" value="<?= $row['material_amount']; ?>" required>
                                                        <small class="text-muted">Contoh: 15 Batang atau 2700 Pcs</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small">Jumlah Hasil (Quantity)</label>
                                                        <input type="number" step="any" class="form-control" name="quantity" value="<?= $row['quantity']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small">Satuan (Unit)</label>
                                                        <select name="unit" class="form-select" required>
                                                            <option value="Pcs" <?= (($row['unit'] ?? 'Pcs') === 'Pcs') ? 'selected' : ''; ?>>Pcs</option>
                                                            <option value="Batang" <?= (($row['unit'] ?? '') === 'Batang') ? 'selected' : ''; ?>>Batang</option>
                                                            <option value="Kg" <?= (($row['unit'] ?? '') === 'Kg') ? 'selected' : ''; ?>>Kg</option>
                                                            <option value="Set" <?= (($row['unit'] ?? '') === 'Set') ? 'selected' : ''; ?>>Set</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- END MODAL EDIT -->

                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data untuk kategori ini.</td>
                                </tr>
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
        padding: 6px 4px;
        font-size: 11px;
    }
    .custom-table tbody td {
        padding: 6px 4px;
        font-size: 11.5px;
        vertical-align: middle;
    }
</style>

<?= $this->endSection(); ?>