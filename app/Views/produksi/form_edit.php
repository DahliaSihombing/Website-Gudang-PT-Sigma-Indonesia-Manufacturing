<?= $this->extend('produksi/layout'); ?>
<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">
                    Edit Data Produksi & Material
                </div>
                <div class="card-body">
                    <?php if(session()->getFlashdata('error')):?>
                        <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error'); ?></div>
                    <?php endif;?>

                    <?php 
                        // Memisahkan angka dan satuan dari field material_amount (Contoh: "2710 Pcs" -> angka: 2710, satuan: Pcs)
                        $rawMatAmount = $production['material_amount'] ?? '';
                        preg_match('/^([0-9\.]+)\s*(.*)$/', trim($rawMatAmount), $matches);
                        $matQty = $matches[1] ?? '';
                        $matUnit = !empty($matches[2]) ? $matches[2] : 'Pcs';
                    ?>

                    <form action="<?= base_url('/produksi/update/' . $production['id']); ?>" method="post">
                        <?= csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nama Barang / Hasil Produksi</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="<?= $production['item_name']; ?>" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Material / Asal Barang</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="<?= $production['material_name']; ?>" disabled>
                        </div>

                        <div class="row">
                            <!-- Edit Jumlah & Satuan Pakai -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Jumlah Pakai (Material)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="any" name="material_amount_qty" class="form-control" value="<?= $matQty; ?>" required>
                                    <select name="material_amount_unit" class="form-select" style="max-width: 100px;">
                                        <option value="Pcs" <?= ($matUnit == 'Pcs') ? 'selected' : ''; ?>>Pcs</option>
                                        <option value="Batang" <?= ($matUnit == 'Batang') ? 'selected' : ''; ?>>Batang</option>
                                        <option value="Kg" <?= ($matUnit == 'Kg') ? 'selected' : ''; ?>>Kg</option>
                                        <option value="Gram" <?= ($matUnit == 'Gram') ? 'selected' : ''; ?>>Gram</option>
                                        <option value="Meter" <?= ($matUnit == 'Meter') ? 'selected' : ''; ?>>Meter</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Edit Jumlah & Satuan Output -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Jumlah Output / Hasil</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="any" name="quantity" class="form-control" value="<?= $production['quantity']; ?>" required>
                                    <select name="unit" class="form-select" style="max-width: 100px;">
                                        <option value="Pcs" <?= (($production['unit'] ?? 'Pcs') == 'Pcs') ? 'selected' : ''; ?>>Pcs</option>
                                        <option value="Kg" <?= (($production['unit'] ?? '') == 'Kg') ? 'selected' : ''; ?>>Kg</option>
                                        <option value="Gram" <?= (($production['unit'] ?? '') == 'Gram') ? 'selected' : ''; ?>>Gram</option>
                                        <option value="Set" <?= (($production['unit'] ?? '') == 'Set') ? 'selected' : ''; ?>>Set</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Tanggal Produksi</label>
                                <input type="date" name="date" class="form-control form-control-sm" value="<?= $production['date']; ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Shift</label>
                                <select name="shift" class="form-select form-select-sm" required>
                                    <option value="Shift 1" <?= ($production['shift'] == 'Shift 1') ? 'selected' : ''; ?>>Shift 1</option>
                                    <option value="Shift 2" <?= ($production['shift'] == 'Shift 2') ? 'selected' : ''; ?>>Shift 2</option>
                                    <option value="Shift 3" <?= ($production['shift'] == 'Shift 3') ? 'selected' : ''; ?>>Shift 3</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nama PIC / Operator</label>
                            <input type="text" name="operator_name" class="form-control form-control-sm" value="<?= $production['operator_name']; ?>" required>
                        </div>

                        <div class="d-flex justify-content-between pt-2">
                            <a href="<?= base_url('/produksi/riwayat'); ?>" class="btn btn-secondary btn-sm">Kembali</a>
                            <button type="submit" class="btn btn-success btn-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>