<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <div>
        <h3 class="fw-bold text-dark mb-1">Pengiriman Barang (Finished Goods)</h3>
        <p class="text-muted small mb-0">Kelola dan pantau seluruh transaksi pengiriman produk jadi menuju ke PT / Customer tujuan.</p>
    </div>
    <button type="button" class="btn btn-primary fw-semibold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalPengiriman">
        + Transaksi Pengiriman Baru
    </button>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Tabel Daftar Pengiriman -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold">Daftar Riwayat Pengiriman Barang</h6>
        <small class="text-light">Total: <?= count($deliveries); ?> Transaksi</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-bordered mb-0 align-middle">
                <thead class="table-dark text-center small">
                    <tr>
                        <th class="py-3" style="width: 5%;">NO</th>
                        <th class="py-3" style="width: 15%;">TANGGAL</th>
                        <th class="py-3" style="width: 25%;">NAMA BARANG / ITEM</th>
                        <th class="py-3" style="width: 25%;">TUJUAN (PT / CUSTOMER)</th>
                        <th class="py-3" style="width: 15%;">JUMLAH</th>
                        <th class="py-3" style="width: 15%;">STATUS</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if(!empty($deliveries)): ?>
                        <?php $no = 1; foreach($deliveries as $row): ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($row['date'])); ?></td>
                            <td class="fw-bold text-primary"><?= esc($row['item_name']); ?></td>
                            <td><?= esc($row['destination']); ?></td>
                            <td class="text-center fw-bold text-success fs-6"><?= esc($row['quantity']); ?></td>
                            <td class="text-center"><span class="badge bg-success px-3 py-1">Terkirim</span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat transaksi pengiriman barang.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Tambah Pengiriman -->
<div class="modal fade" id="modalPengiriman" tabindex="-1" aria-labelledby="modalPengirimanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="<?= base_url('/admin/pengiriman/store'); ?>" method="POST" class="modal-content">
            <?= csrf_field(); ?>
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title fs-6 fw-bold" id="modalPengirimanLabel">Form Transaksi Pengiriman Barang (Finished Goods)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Tanggal Kirim</label>
                        <input type="date" name="date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold mb-0">Tujuan PT / Customer</label>
                            <a href="/admin/partners?type=Customer" class="text-decoration-none small text-primary fw-bold" target="_blank">+ Kelola PT Customer</a>
                        </div>
                        <select name="destination" class="form-select form-select-sm" required>
                            <option value="" disabled selected>-- Pilih PT / Customer Tujuan --</option>
                            <?php if(!empty($customers)): ?>
                                <?php foreach($customers as $c): ?>
                                    <option value="<?= esc($c['name']); ?>"><?= esc($c['name']); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Pilih Barang Jadi (Finished Goods Siap Kirim)</label>
                        <select name="item_name" id="item_fg_select" class="form-select form-select-sm fw-semibold" onchange="updateMaxKirim(this)" required>
                            <option value="" disabled selected>-- Pilih Barang FG --</option>
                            <?php if(!empty($fg_items)): ?>
                                <?php foreach($fg_items as $fg): ?>
                                    <option value="<?= esc($fg['item_name']); ?>" 
                                            data-max="<?= $fg['stock_ready']; ?>"
                                            <?= ($fg['stock_ready'] <= 0) ? 'class="text-muted bg-light"' : 'class="fw-bold text-primary"'; ?>>
                                        <?= esc($fg['item_name']); ?> — (Stok Siap Kirim: <?= number_format($fg['stock_ready']); ?> <?= esc($fg['unit']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Jumlah Kirim (Pcs)</label>
                        <input type="number" name="quantity" id="qty_kirim_input" class="form-control form-control-sm text-center fw-bold" placeholder="0" min="1" required>
                        <small class="text-danger fw-bold d-block mt-1" id="info_stok_fg" style="font-size: 11px;"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm fw-bold px-4">Simpan & Kirim Barang</button>
            </div>
        </form>
    </div>
</div>

<script>
    function updateMaxKirim(selectElement) {
        let selectedOption = selectElement.options[selectElement.selectedIndex];
        let maxStock = parseInt(selectedOption.getAttribute('data-max')) || 0;
        
        let inputQty = document.getElementById('qty_kirim_input');
        let infoBox = document.getElementById('info_stok_fg');

        inputQty.value = '';
        inputQty.setAttribute('max', maxStock);
        inputQty.setAttribute('placeholder', 'Maks: ' + maxStock);

        if (maxStock > 0) {
            infoBox.className = 'text-success fw-bold d-block mt-1';
            infoBox.innerText = '*Tersedia: ' + maxStock.toLocaleString() + ' Pcs';
        } else {
            infoBox.className = 'text-danger fw-bold d-block mt-1';
            infoBox.innerText = '⚠️ Stok FG Kosong (0 Pcs), belum bisa dikirim.';
        }
    }
</script>

<?= $this->endSection(); ?>