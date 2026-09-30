<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="p-3 bg-white rounded shadow-sm border-start border-primary border-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-dark mb-1">Transaksi Subcon & Pewarnaan (Plating Outsource)</h4>
                <p class="text-muted small mb-0">Pantau proses pengeluaran (OUT) dan penerimaan (IN) barang plating ke vendor (Pcs & Berat Kg) termasuk kiriman repair dan retur material.</p>
            </div>
            <button class="btn btn-primary btn-sm fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#addModal">+ Transaksi Subcon Baru</button>
        </div>
    </div>
</div>

<?php if(session()->getFlashdata('success')):?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif;?>

<?php if(session()->getFlashdata('error')):?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif;?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle custom-table">
                        <thead class="text-center small">
                            <tr>
                                <th class="col-no">NO</th>
                                <th class="col-tgl">TANGGAL</th>
                                <th class="col-tipe">TIPE</th>
                                <th class="col-barang">NAMA BARANG / ITEM</th>
                                <th class="col-vendor">NAMA VENDOR</th>
                                <th class="col-jumlah">JUMLAH (PCS & KG)</th>
                                <th class="col-monitoring">MONITORING SISA DI VENDOR</th>
                                <th class="col-status">STATUS</th>
                                <th class="col-aksi">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if(empty($subcons)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Belum ada data transaksi subcon.</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no_out = 1; 
                                foreach($subcons as $row): 
                                    $pcsVal = (float) filter_var($row['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                                    $kgVal  = (float)($row['weight_kg'] ?? 0);
                                    $isReturMaterial = str_contains($row['status'], 'Retur') || str_contains($row['monitoring'], 'Retur');
                                    $isRepairVendor  = str_contains($row['status'], 'Repair') || str_contains($row['monitoring'], 'Repair');
                                ?>
                                    <tr>
                                        <td class="text-center fw-bold">
                                            <?= ($row['type'] == 'OUT') ? $no_out++ : ''; ?>
                                        </td>
                                        <td class="text-center"><?= date('d-m-Y', strtotime($row['date'])); ?></td>
                                        <td class="text-center">
                                            <?php if($row['type'] == 'OUT'): ?>
                                                <?php if($isReturMaterial): ?>
                                                    <span class="badge bg-danger">OUT (RETUR)</span>
                                                <?php elseif($isRepairVendor): ?>
                                                    <span class="badge bg-warning text-dark">OUT (REPAIR)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary">OUT (PLATING)</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-success">IN (MASUK)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold text-primary"><?= esc($row['item_name']); ?></td>
                                        <td><?= esc($row['vendor_name']); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-primary px-2 py-1 mb-1 d-inline-block"><?= number_format($pcsVal); ?> Pcs</span>
                                            <?php if($kgVal > 0): ?>
                                                <br><span class="badge bg-secondary px-2 py-1">⚖️ <?= number_format($kgVal, 2); ?> Kg</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <small class="text-muted fw-bold">
                                                <?php if($row['type'] == 'IN' && !empty($row['reference_id'])): ?>
                                                    Penerimaan In <br>
                                                    <span class="text-secondary" style="font-size: 11px;">
                                                        <?= $isReturMaterial ? '(Masuk ke Stok Material)' : ($isRepairVendor ? '(Selesai Repair)' : '(Dari Kiriman OUT)'); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <?php 
                                                        // Format string monitoring supaya bagian Sisa dicetak merah
                                                        $monText = esc($row['monitoring']);
                                                        // Mengubah format "Sisa: X Pcs" menjadi berwarna merah
                                                        $monTextFormatted = preg_replace('/(Sisa:\s*[^\|]+)/i', '<span class="text-danger fw-bold">$1</span>', $monText);
                                                        echo $monTextFormatted;
                                                    ?>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <?php if($row['type'] == 'OUT'): ?>
                                                <?php if($row['status'] == 'Selesai' || $row['status'] == 'Selesai Repair' || $row['status'] == 'Selesai Retur'): ?>
                                                    <span class="badge bg-light text-success border border-success px-2 py-1">&#10004; Selesai</span>
                                                <?php elseif(str_contains($row['status'], 'Sebagian')): ?>
                                                    <span class="badge bg-info text-dark border border-info px-2 py-1">Sebagian</span>
                                                <?php elseif($isReturMaterial): ?>
                                                    <span class="badge bg-danger text-white border border-danger px-2 py-1">Retur Material</span>
                                                <?php elseif($isRepairVendor): ?>
                                                    <span class="badge bg-warning text-dark border border-warning px-2 py-1">Repair Vendor</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary text-white px-2 py-1">Proses Vendor</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center align-items-center gap-1">
                                                <?php if($row['type'] == 'OUT'): ?>
                                                    <button class="btn btn-outline-warning btn-sm px-1 py-1 fw-bold text-dark" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id']; ?>" title="Edit">✏️</button>
                                                <?php endif; ?>
                                                <a href="/admin/subcon/delete/<?= $row['id']; ?>" class="btn btn-outline-danger btn-sm px-1 py-1 fw-bold" style="font-size: 11px;" onclick="return confirm('Yakin ingin menghapus data ini?');" title="Hapus">🗑️</a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal Edit Subcon -->
                                    <div class="modal fade" id="editModal<?= $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form action="/admin/subcon/update/<?= $row['id']; ?>" method="post">
                                                    <?= csrf_field(); ?>
                                                    <div class="modal-header bg-warning text-dark py-2">
                                                        <h5 class="modal-title fs-6 fw-bold">Edit Transaksi Subcon</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start py-3">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label small fw-bold">Tanggal</label>
                                                                <input type="date" name="date" class="form-control form-control-sm" value="<?= $row['date']; ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label small fw-bold">Tipe Transaksi</label>
                                                                <input type="text" class="form-control form-control-sm bg-light" value="<?= $row['type']; ?>" readonly>
                                                                <input type="hidden" name="type" value="<?= $row['type']; ?>">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label small fw-bold">Nama Barang</label>
                                                                <input type="text" name="item_name" class="form-control form-control-sm" value="<?= esc($row['item_name']); ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label small fw-bold">Nama Vendor</label>
                                                                <select name="vendor_name" class="form-select form-select-sm" required>
                                                                    <?php if(!empty($vendors)): ?>
                                                                        <?php foreach($vendors as $v): ?>
                                                                            <option value="<?= esc($v['name']); ?>" <?= ($row['vendor_name'] == $v['name']) ? 'selected' : ''; ?>><?= esc($v['name']); ?></option>
                                                                        <?php endforeach; ?>
                                                                    <?php else: ?>
                                                                        <option value="<?= esc($row['vendor_name']); ?>" selected><?= esc($row['vendor_name']); ?></option>
                                                                    <?php endif; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label small fw-bold">Jumlah (Pcs)</label>
                                                                <input type="number" name="quantity_pcs" class="form-control form-control-sm" value="<?= $pcsVal; ?>" min="1" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label small fw-bold">Berat Total (Kg) - Opsional</label>
                                                                <input type="number" step="0.01" name="weight_kg" class="form-control form-control-sm" value="<?= $kgVal; ?>" min="0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer py-2">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-warning btn-sm fw-bold">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Subcon Baru -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/admin/subcon/store" method="post" id="subconForm">
                <?= csrf_field(); ?>
                <div class="modal-header bg-primary text-white py-2">
                    <h5 class="modal-title fs-6 fw-bold">Tambah Transaksi Subcon</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tanggal Transaksi</label>
                            <input type="date" name="date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tipe Transaksi</label>
                            <select name="type" id="addType" class="form-select form-select-sm" required onchange="toggleFormType()">
                                <option value="OUT">OUT (Keluar ke Vendor)</option>
                                <option value="IN">IN (Masuk dari Vendor)</option>
                            </select>
                        </div>

                        <!-- Opsi Jenis Pengiriman OUT -->
                        <div class="col-md-12" id="groupSubconType">
                            <label class="form-label small fw-bold text-secondary">Tujuan Pengiriman OUT:</label>
                            <div class="d-flex flex-column flex-md-row gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="subcon_type" id="type_reguler" value="reguler" checked onchange="toggleSubconItemDropdown()">
                                    <label class="form-check-label small fw-semibold" for="type_reguler">
                                        📦 1. Plating Standar (WIP Final)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="subcon_type" id="type_repair" value="return_repair" onchange="toggleSubconItemDropdown()">
                                    <label class="form-check-label small fw-semibold text-warning-emphasis" for="type_repair">
                                        🔄 2. Kirim Repair Part NG (Replating Ulang)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="subcon_type" id="type_retur" value="return_material" onchange="toggleSubconItemDropdown()">
                                    <label class="form-check-label small fw-semibold text-danger" for="type_retur">
                                        ⚠️ 3. Kirim Retur Material Mentah (COLLAR Forging)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Kotak Referensi OUT Asal (Khusus IN) -->
                        <div class="col-md-12" id="refGroup" style="display: none;">
                            <div class="p-3 bg-light border rounded border-danger">
                                <label class="form-label small fw-bold text-danger">Pilih Kiriman OUT Asal (Barang & Vendor Otomatis Terisi)</label>
                                <select name="reference_id" id="refSelect" class="form-select form-select-sm" onchange="autofillFromRef(this)">
                                    <option value="">-- Pilih Transaksi OUT Sebelumnya --</option>
                                    <?php if(!empty($out_transactions)): ?>
                                        <?php foreach($out_transactions as $out): ?>
                                            <?php 
                                            $outPcs = (float) filter_var($out['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                                            $outKg  = (float) ($out['weight_kg'] ?? 0);
                                            ?>
                                            <option value="<?= $out['id']; ?>" 
                                                    data-item="<?= htmlspecialchars($out['item_name'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                    data-vendor="<?= htmlspecialchars($out['vendor_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-pcs="<?= $outPcs; ?>"
                                                    data-kg="<?= $outKg; ?>">
                                                [<?= $out['date']; ?>] | <?= $out['item_name']; ?> | <?= $out['vendor_name']; ?> | Qty: <?= number_format($outPcs); ?> Pcs (<?= $outKg; ?> Kg) | Status: <?= $out['status']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold" id="label_nama_barang">Pilih Nama Barang</label>
                            
                            <!-- Dropdown 1: Barang Plating / Repair (WIP Final) -->
                            <select name="item_name" id="select_item_reguler" class="form-select form-select-sm" required>
                                <option value="" disabled selected>-- Pilih Barang --</option>
                                <?php foreach($items_wip as $it): ?>
                                    <option value="<?= esc($it['item_name']); ?>"><?= esc($it['item_name']); ?> (<?= esc($it['category']); ?>)</option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Dropdown 2: Khusus Retur Material Mentah Forging -->
                            <select name="item_name" id="select_item_retur" class="form-select form-select-sm" style="display: none;" disabled>
                                <option value="" disabled selected>-- Pilih Material Forging Retur --</option>
                                <?php if(!empty($items_retur_material)): ?>
                                    <?php foreach($items_retur_material as $mat): ?>
                                        <option value="<?= esc($mat['item_name']); ?>"><?= esc($mat['item_name']); ?> (Material Mentah)</option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="COLLAR Ø 11.5 X Ø 6.5 FORGING">COLLAR Ø 11.5 X Ø 6.5 FORGING (Material Mentah)</option>
                                    <option value="COLLAR Ø 12.5 X Ø8.0 FORGING">COLLAR Ø 12.5 X Ø8.0 FORGING (Material Mentah)</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Pilih Nama Vendor</label>
                                <a href="/admin/partners?type=Vendor" class="text-decoration-none small text-primary fw-bold" target="_blank">+ Kelola Vendor</a>
                            </div>
                            <select name="vendor_name" id="addVendorName" class="form-select form-select-sm" required>
                                <option value="" disabled selected>-- Pilih Vendor --</option>
                                <?php if(!empty($vendors)): ?>
                                    <?php foreach($vendors as $v): ?>
                                        <option value="<?= esc($v['name']); ?>"><?= esc($v['name']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Jumlah (Pcs)</label>
                            <input type="number" name="quantity_pcs" id="addQtyPcs" class="form-control form-control-sm" placeholder="Contoh: 500" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Berat Total (Kg) - Opsional</label>
                            <input type="number" step="0.01" name="weight_kg" id="addWeightKg" class="form-control form-control-sm" placeholder="Contoh: 12.5" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleFormType() {
        var type = document.getElementById('addType').value;
        var refGroup = document.getElementById('refGroup');
        var refSelect = document.getElementById('refSelect');
        var groupSubconType = document.getElementById('groupSubconType');
        var itemReguler = document.getElementById('select_item_reguler');
        var itemRetur = document.getElementById('select_item_retur');
        var vendorSelect = document.getElementById('addVendorName');

        if (type === 'IN') {
            refGroup.style.display = 'block';
            groupSubconType.style.display = 'none';
            refSelect.setAttribute('required', 'required');
            
            itemReguler.value = "";
            itemRetur.value = "";
            vendorSelect.value = "";
        } else {
            refGroup.style.display = 'none';
            groupSubconType.style.display = 'block';
            refSelect.removeAttribute('required');
            refSelect.value = "";

            toggleSubconItemDropdown();

            vendorSelect.removeAttribute('readonly');
            vendorSelect.style.pointerEvents = 'auto';
            vendorSelect.style.backgroundColor = '#fff';
            vendorSelect.value = "";
        }
    }

    function toggleSubconItemDropdown() {
        var isRetur = document.getElementById('type_retur').checked;
        var itemReguler = document.getElementById('select_item_reguler');
        var itemRetur = document.getElementById('select_item_retur');
        var labelBarang = document.getElementById('label_nama_barang');

        if (isRetur) {
            itemReguler.style.display = 'none';
            itemReguler.setAttribute('disabled', 'disabled');

            itemRetur.style.display = 'block';
            itemRetur.removeAttribute('disabled');
            itemRetur.removeAttribute('readonly');
            itemRetur.style.pointerEvents = 'auto';
            itemRetur.style.backgroundColor = '#fff';
            itemRetur.value = "";

            labelBarang.className = 'form-label small fw-bold text-danger';
            labelBarang.innerText = 'Pilih Material Mentah Forging (Retur)';
        } else {
            itemRetur.style.display = 'none';
            itemRetur.setAttribute('disabled', 'disabled');

            itemReguler.style.display = 'block';
            itemReguler.removeAttribute('disabled');
            itemReguler.removeAttribute('readonly');
            itemReguler.style.pointerEvents = 'auto';
            itemReguler.style.backgroundColor = '#fff';
            itemReguler.value = "";

            labelBarang.className = 'form-label small fw-bold text-dark';
            labelBarang.innerText = 'Pilih Nama Barang';
        }
    }

    function autofillFromRef(selectElement) {
        var selectedOpt = selectElement.options[selectElement.selectedIndex];
        var itemName = selectedOpt.getAttribute('data-item');
        var vendorName = selectedOpt.getAttribute('data-vendor');
        var maxPcs = selectedOpt.getAttribute('data-pcs');
        var maxKg = selectedOpt.getAttribute('data-kg');

        var itemReguler = document.getElementById('select_item_reguler');
        var itemRetur = document.getElementById('select_item_retur');
        var vendorSelect = document.getElementById('addVendorName');
        var qtyInput = document.getElementById('addQtyPcs');
        var weightInput = document.getElementById('addWeightKg');

        if (itemName) {
            itemRetur.style.display = 'none';
            itemRetur.setAttribute('disabled', 'disabled');

            itemReguler.style.display = 'block';
            itemReguler.removeAttribute('disabled');
            
            let found = false;
            for (let i = 0; i < itemReguler.options.length; i++) {
                if (itemReguler.options[i].value === itemName) {
                    itemReguler.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found) {
                let opt = document.createElement('option');
                opt.value = itemName;
                opt.text = itemName;
                itemReguler.add(opt);
                itemReguler.value = itemName;
            }

            itemReguler.setAttribute('readonly', 'readonly');
            itemReguler.style.pointerEvents = 'none';
            itemReguler.style.backgroundColor = '#e9ecef';
        }

        if (vendorName) {
            vendorSelect.value = vendorName;
            vendorSelect.setAttribute('readonly', 'readonly');
            vendorSelect.style.pointerEvents = 'none';
            vendorSelect.style.backgroundColor = '#e9ecef';
        }

        if (maxPcs) {
            qtyInput.placeholder = "Maks kirim asal: " + Number(maxPcs).toLocaleString() + " Pcs";
        }
        if (maxKg && weightInput) {
            weightInput.placeholder = "Maks berat asal: " + maxKg + " Kg";
        }
    }

    document.getElementById('subconForm').addEventListener('submit', function() {
        var itemReguler = document.getElementById('select_item_reguler');
        var itemRetur = document.getElementById('select_item_retur');
        var vendorSelect = document.getElementById('addVendorName');
        
        if (itemReguler) {
            itemReguler.removeAttribute('readonly');
            itemReguler.style.pointerEvents = 'auto';
        }
        if (itemRetur) {
            itemRetur.removeAttribute('readonly');
            itemRetur.style.pointerEvents = 'auto';
        }
        if (vendorSelect) {
            vendorSelect.removeAttribute('readonly');
            vendorSelect.style.pointerEvents = 'auto';
        }
    });
</script>
<?= $this->endSection(); ?>