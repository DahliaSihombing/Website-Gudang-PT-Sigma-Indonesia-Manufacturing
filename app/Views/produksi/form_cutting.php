<?= $this->extend('produksi/layout'); ?>
<?= $this->section('content'); ?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold py-2 small">Form OP 1 (Cutting Material)</div>
            <div class="card-body p-3">
                <?php if(session()->getFlashdata('error')):?>
                    <div class="alert alert-danger py-1 mb-2 small"><?= session()->getFlashdata('error'); ?></div>
                <?php endif;?>
                
                <form action="/produksi/cutting/store" method="post">
                    <?= csrf_field(); ?>
                    <!-- Baris 1: Tanggal, Shift, dan Nama Operator -->
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label fw-bold text-secondary" style="font-size: 11px;">Tanggal</label>
                            <input type="date" name="date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label fw-bold text-secondary" style="font-size: 11px;">Shift Kerja</label>
                            <select name="shift" class="form-select form-select-sm" required>
                                <option value="Shift 1">Shift 1 (Pagi)</option>
                                <option value="Shift 2">Shift 2 (Sore)</option>
                                <option value="Shift 3">Shift 3 (Malam)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label fw-bold text-secondary" style="font-size: 11px;">Nama Operator</label>
                            <input type="text" name="operator_name" class="form-control form-control-sm" placeholder="Nama operator..." required>
                        </div>
                    </div>

                    <!-- Baris 2: Material Asal, Jumlah Pakai, dan Satuan -->
                    <div class="row">
                        <div class="col-md-7 mb-2">
                            <label class="form-label fw-bold text-secondary" style="font-size: 11px;">Pilih Material Asal</label>
                            <select name="material_asal" class="form-select form-select-sm" required>
                                <option value="" disabled selected>-- Pilih Material --</option>
                                <?php foreach($items as $it): ?>
                                    <option value="<?= $it['item_name']; ?>"><?= $it['item_name']; ?> (Stok: <?= $it['stock']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label fw-bold text-secondary" style="font-size: 11px;">Jumlah Pakai</label>
                            <input type="number" name="qty_material_pakai" class="form-control form-control-sm" placeholder="0" min="1" required>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label fw-bold text-secondary" style="font-size: 11px;">Satuan</label>
                            <select name="unit_pakai" class="form-select form-select-sm" required>
                                <option value="Batang">Batang</option>
                                <option value="Kg">Kg</option>
                                <option value="Pcs">Pcs</option>
                                <option value="Meter">Meter</option>
                            </select>
                        </div>
                    </div>

                    <!-- Baris 3: Hasil Cutting & Jumlah Output Disejajarkan -->
                    <div class="row mb-3">
                        <div class="col-md-8 mb-2">
                            <label class="form-label fw-bold text-secondary" style="font-size: 11px;">Pilih Item Hasil OP 1 (Cutting)</label>
                            <select name="item_hasil_cutting" class="form-select form-select-sm" required>
                                <option value="" disabled selected>-- Pilih Hasil Potong --</option>
                                <option value="COLLAR FAN CUTTING">COLLAR FAN CUTTING</option>
                                <option value="COLLAR 62670 (T5) CUTTING">COLLAR 62670 (T5) CUTTING</option>
                                <option value="COLLAR 87180 (T6) CUTTING">COLLAR 87180 (T6) CUTTING</option>
                                <option value="COLLAR 87181 (T7) CUTTING">COLLAR 87181 (T7) CUTTING</option>
                                <option value="COLLAR RADIATOR 6.5MM">COLLAR RADIATOR 6.5MM</option>
                                <option value="COLLAR RADIATOR 8.0MM">COLLAR RADIATOR 8.0MM</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label fw-bold text-secondary" style="font-size: 11px;">Jumlah Hasil (Pcs)</label>
                            <input type="number" name="quantity" class="form-control form-control-sm" placeholder="Contoh: 100" min="1" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold py-2">Simpan & Potong Stok Material</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>