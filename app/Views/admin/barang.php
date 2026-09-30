<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="p-3 bg-white rounded shadow-sm border-start border-primary border-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-dark mb-1">Master Data Barang</h4>
                <p class="text-muted small mb-0">Kelola inventaris berdasarkan tahapan proses produksi dari material mentah hingga barang siap kirim.</p>
            </div>
            <button class="btn btn-primary btn-sm fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#addModal">+ Tambah Barang</button>
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

<!-- Tab Menu Kategori Horizontal -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body py-2 px-3">
                <div class="d-flex flex-wrap gap-2">
                    <?php 
                    $categories = ['Material', 'Cutting', 'WIP OP1', 'WIP Final', 'Plating', 'FC', 'NG', 'FG'];
                    $customLabels = [
                        'Material'  => 'Material',
                        'Cutting'   => 'OP 1',
                        'WIP OP1'   => 'OP 2',
                        'WIP Final' => 'WIP',
                        'Plating'   => 'Plating',
                        'FC'        => 'FC',
                        'NG'        => 'NG',
                        'FG'        => 'FG'
                    ];

                    foreach($categories as $cat): 
                        $isActive = (str_replace(['+', ' '], '', $selected_cat) == str_replace(' ', '', $cat));
                        $displayName = $customLabels[$cat] ?? $cat;
                    ?>
                        <a href="/admin/barang?cat=<?= urlencode($cat); ?>" class="btn btn-sm fw-bold px-3 py-2 <?= $isActive ? 'btn-primary shadow-sm' : 'btn-light text-dark border'; ?>">
                            <?= $displayName; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Data Barang -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                <span>
                    <?php $currentLabel = $customLabels[$selected_cat] ?? $selected_cat; ?>
                    Kategori: [ <?= $currentLabel; ?> ] 
                    <?php if($selected_cat === 'Plating'): ?>
                        — Monitoring Outstanding Barang di Vendor
                    <?php elseif($selected_cat === 'FC'): ?>
                        — Antrean Pengecekan Kualitas (Final Check)
                    <?php elseif($selected_cat === 'FG'): ?>
                        — Monitoring Stok Siap Kirim (Finished Goods)
                    <?php endif; ?>
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-dark text-center small">
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th>Nama Barang / Komponen</th>
                                
                                <?php if($selected_cat === 'Plating'): ?>
                                    <th style="width: 15%;">Total Kirim (OUT)</th>
                                    <th style="width: 15%;">Total Kembali (IN)</th>
                                    <th style="width: 20%;">Sisa di Vendor</th>
                                    <th style="width: 15%;">Status Vendor</th>
                                    <th style="width: 12%;">Aksi</th>
                                <?php elseif($selected_cat === 'FC'): ?>
                                    <th style="width: 15%;">Antrean In-House</th>
                                    <th style="width: 15%;">Antrean Subcon IN</th>
                                    <th style="width: 18%;">Total Siap Cek (FC)</th>
                                    <th style="width: 15%;">Status Kerja QC</th>
                                    <th style="width: 12%;">Aksi</th>
                                <?php else: ?>
                                    <th style="width: 25%;">Stok & Status</th>
                                    <th style="width: 18%;">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if(empty($items)): ?>
                                <tr>
                                    <td colspan="<?= ($selected_cat === 'Plating' || $selected_cat === 'FC') ? 7 : 4; ?>" class="text-center text-muted py-4">Belum ada data barang pada kategori ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach($items as $row): 
                                    $numericStock = (float) filter_var($row['stock'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                                    $rowId = $row['id'] ?? 0;
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td class="fw-bold text-primary"><?= $row['item_name']; ?></td>
                                        
                                        <?php if($selected_cat === 'Plating'): ?>
                                            <td class="text-center">
                                                <div class="mb-1"><span class="badge bg-danger px-2 py-1">📤 <?= number_format($row['total_out_pcs'] ?? 0); ?> Pcs</span></div>
                                                <div><span class="badge bg-secondary px-2 py-1">⚖️ <?= number_format($row['total_out_kg'] ?? 0, 2); ?> Kg</span></div>
                                            </td>
                                            <td class="text-center">
                                                <div class="mb-1"><span class="badge bg-success px-2 py-1">📥 <?= number_format($row['total_in_pcs'] ?? 0); ?> Pcs</span></div>
                                                <div><span class="badge bg-secondary px-2 py-1">⚖️ <?= number_format($row['total_in_kg'] ?? 0, 2); ?> Kg</span></div>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                    $sisaPcs = (float)($row['sisa_di_vendor_pcs'] ?? 0);
                                                    $sisaKg  = (float)($row['sisa_di_vendor_kg'] ?? 0);
                                                ?>
                                                <div class="mb-1">
                                                    <?php if($sisaPcs > 0): ?>
                                                        <span class="badge bg-warning text-dark px-2 py-1 fw-bold">⏳ <?= number_format($sisaPcs); ?> Pcs</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-success border border-success px-2 py-1">✓ 0 Pcs</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <?php if($sisaKg > 0): ?>
                                                        <span class="badge bg-warning text-dark px-2 py-1 fw-bold">⏳ <?= number_format($sisaKg, 2); ?> Kg</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-success border border-success px-2 py-1">✓ 0.00 Kg</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php if($sisaPcs > 0 || $sisaKg > 0): ?>
                                                    <span class="badge bg-warning text-dark border border-warning px-2 py-1">Proses di Vendor</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success text-white px-2 py-1">Selesai Semua</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if($rowId > 0): ?>
                                                    <button class="btn btn-warning btn-sm px-2 py-1 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#editModal<?= $rowId; ?>">Edit</button>
                                                    <a href="/admin/barang/delete/<?= $rowId; ?>" class="btn btn-danger btn-sm px-2 py-1 fw-bold" onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>

                                        <?php elseif($selected_cat === 'FC'): ?>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border px-2 py-1">🏭 <?= number_format($row['antrean_inhouse'] ?? 0); ?> Pcs</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border px-2 py-1">🔄 <?= number_format($row['antrean_subcon'] ?? 0); ?> Pcs</span>
                                            </td>
                                            <td class="text-center">
                                                <?php if($numericStock > 0): ?>
                                                    <span class="badge bg-warning text-dark px-3 py-2 fs-6 border border-warning">⏳ <?= number_format($numericStock); ?> Pcs</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success text-white px-3 py-2 fs-6">✓ 0 Pcs (Clear)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if($numericStock > 0): ?>
                                                    <span class="badge bg-danger text-white px-2 py-1">⚠️ Ada Antrean QC</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-success border border-success px-2 py-1">Semua Selesai</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if($rowId > 0): ?>
                                                    <button class="btn btn-warning btn-sm px-2 py-1 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#editModal<?= $rowId; ?>">Edit</button>
                                                    <a href="/admin/barang/delete/<?= $rowId; ?>" class="btn btn-danger btn-sm px-2 py-1 fw-bold" onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>

                                        <?php else: ?>
                                            <td class="text-center">
                                                <span class="badge bg-info text-dark px-3 py-2 fs-6">
                                                    <?= number_format($numericStock); ?> <?= $row['unit'] ?? 'Pcs'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if($rowId > 0): ?>
                                                    <button class="btn btn-warning btn-sm px-2 py-1 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#editModal<?= $rowId; ?>">Edit</button>
                                                    <a href="/admin/barang/delete/<?= $rowId; ?>" class="btn btn-danger btn-sm px-2 py-1 fw-bold" onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>

                                    <!-- Modal Edit Barang -->
                                    <?php if($rowId > 0): ?>
                                    <div class="modal fade" id="editModal<?= $rowId; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="/admin/barang/update/<?= $rowId; ?>" method="post">
                                                    <?= csrf_field(); ?>
                                                    <div class="modal-header bg-warning text-dark">
                                                        <h5 class="modal-title fs-6 fw-bold">Edit Data Barang (<?= $selected_cat; ?>)</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <input type="hidden" name="category" value="<?= $selected_cat; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">Nama Barang / Komponen</label>
                                                            <input type="text" name="item_name" class="form-control" value="<?= $row['item_name']; ?>" required>
                                                        </div>

                                                        <?php if($selected_cat === 'Plating'): ?>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">Tipe Alur Edit Plating</label>
                                                                <select name="plating_type" class="form-select">
                                                                    <option value="NONE">Update Direct Master Record Only</option>
                                                                    <option value="OUT">+ Tambah Kiriman ke Vendor (OUT)</option>
                                                                    <option value="IN">+ Tambah Penerimaan Vendor (IN)</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">Berat (Kg) - Opsional</label>
                                                                <input type="number" step="0.01" name="weight_kg" class="form-control" value="0">
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if($selected_cat === 'FC'): ?>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">Pilih Alur Pengecekan (FC)</label>
                                                                <select name="fc_type" class="form-select">
                                                                    <option value="inhouse">Update Antrean In-House (Gudang WIP Final)</option>
                                                                    <option value="subcon">+ Tambah Antrean Subcon IN (Kiriman Vendor)</option>
                                                                </select>
                                                            </div>
                                                        <?php endif; ?>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">
                                                                <?php 
                                                                    if($selected_cat === 'Plating') echo 'Jumlah Barang (Pcs)';
                                                                    elseif($selected_cat === 'FC') echo 'Jumlah Antrean (Pcs)';
                                                                    else echo 'Jumlah Stok';
                                                                ?>
                                                            </label>
                                                            <input type="number" name="stock" class="form-control" value="<?= $numericStock; ?>" min="0" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">Satuan</label>
                                                            <input type="text" name="unit" class="form-control" value="<?= $row['unit'] ?? 'Pcs'; ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-warning btn-sm fw-bold">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Barang -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/barang/store" method="post">
                <?= csrf_field(); ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fs-6 fw-bold">Tambah Data Barang Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Kategori / Tahapan</label>
                        <select name="category" id="modalCategorySelect" class="form-select" onchange="toggleFormFields(this.value)" required>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat; ?>" <?= ($selected_cat == $cat) ? 'selected' : ''; ?>><?= $customLabels[$cat] ?? $cat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Barang / Komponen</label>
                        <select name="item_name_select" id="itemNameSelect" class="form-select mb-2" onchange="handleItemNameSelect(this)">
                            <option value="">-- Pilih dari Master Barang --</option>
                            <?php if(!empty($all_master_items)): ?>
                                <?php foreach($all_master_items as $mItem): ?>
                                    <option value="<?= $mItem; ?>"><?= $mItem; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <option value="__NEW__">+ Input Nama Barang Baru...</option>
                        </select>
                        <input type="text" name="item_name" id="itemNameInput" class="form-control" placeholder="Ketik nama barang baru di sini" required>
                    </div>

                    <div id="platingFields" class="d-none">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Tipe Alur Plating</label>
                            <select name="plating_type" class="form-select">
                                <option value="OUT">Kirim ke Vendor (OUT)</option>
                                <option value="IN">Kembali dari Vendor (IN)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Berat (Kg) - Opsional</label>
                            <input type="number" step="0.01" name="weight_kg" class="form-control" placeholder="Contoh: 12.50" value="0">
                        </div>
                    </div>

                    <div id="fcFields" class="d-none mb-3">
                        <label class="form-label small fw-bold">Asal Antrean Pengecekan (FC)</label>
                        <select name="fc_type" class="form-select">
                            <option value="inhouse">Antrean In-House (Gudang WIP Final)</option>
                            <option value="subcon">Antrean Subcon IN (Kiriman Vendor)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold" id="stockLabel">Jumlah Stok (Pcs)</label>
                        <input type="number" name="stock" class="form-control" value="0" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Satuan</label>
                        <input type="text" name="unit" class="form-control" value="Pcs" placeholder="Contoh: Pcs, Batang, Kg" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleItemNameSelect(selectEl) {
    const inputEl = document.getElementById('itemNameInput');
    if (selectEl.value === '__NEW__') {
        inputEl.value = '';
        inputEl.readOnly = false;
        inputEl.focus();
    } else if (selectEl.value !== '') {
        inputEl.value = selectEl.value;
        inputEl.readOnly = true;
    } else {
        inputEl.value = '';
        inputEl.readOnly = false;
    }
}

function toggleFormFields(cat) {
    const platingFields = document.getElementById('platingFields');
    const fcFields = document.getElementById('fcFields');
    const stockLabel = document.getElementById('stockLabel');

    if (cat === 'Plating') {
        platingFields.classList.remove('d-none');
        fcFields.classList.add('d-none');
        stockLabel.innerText = 'Jumlah Barang (Pcs)';
    } else if (cat === 'FC') {
        fcFields.classList.remove('d-none');
        platingFields.classList.add('d-none');
        stockLabel.innerText = 'Jumlah Antrean (Pcs)';
    } else {
        platingFields.classList.add('d-none');
        fcFields.classList.add('d-none');
        stockLabel.innerText = 'Jumlah Stok';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    toggleFormFields(document.getElementById('modalCategorySelect').value);
});
</script>

<?= $this->endSection(); ?>