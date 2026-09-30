<?= $this->extend('produksi/layout'); ?>
<?= $this->section('content'); ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center py-2">
                <span>Form Input Hasil Produksi / Proses Harian</span>
                <a href="/produksi/dashboard" class="btn btn-light btn-sm text-primary fw-bold">Kembali</a>
            </div>
            <div class="card-body p-4">
                <form action="/produksi/store" method="post">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tanggal Produksi</label>
                        <input type="date" name="date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Shift Kerja</label>
                        <select name="shift" class="form-select form-select-sm" required>
                            <option value="Shift 1">Shift 1 (Pagi)</option>
                            <option value="Shift 2">Shift 2 (Malam)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pilih Nama Barang / Material Asal</label>
                        <select name="item_name" class="form-select form-select-sm" required>
                            <option value="" disabled selected>-- Pilih Barang dari Master --</option>
                            <?php foreach($items as $it): ?>
                                <option value="<?= $it['item_name']; ?>"><?= $it['item_name']; ?> (Kategori: <?= $it['category']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Hasil Proses Masuk ke Kategori Mana?</label>
                        <select name="category" class="form-select form-select-sm" required>
                            <option value="Cutting">Cutting (Hasil Potong)</option>
                            <option value="WIP OP1">WIP OP1 (Proses Mesin 1)</option>
                            <option value="WIP Final">WIP Final (Setengah Jadi Akhir)</option>
                            <option value="Plating">Plating (Pewarnaan)</option>
                            <option value="FC">FC (Final Checking)</option>
                            <option value="FG">FG (Finish Good / Barang Jadi)</option>
                        </select>
                        <small class="text-muted" style="font-size: 11px;">*Pilih tahapan proses selanjutnya di alur pabrik.</small>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold small">Jumlah Output (Qty)</label>
                            <input type="number" name="quantity" class="form-control form-control-sm" placeholder="Contoh: 500" min="1" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Satuan</label>
                            <select name="unit" class="form-select form-select-sm" required>
                                <option value="Pcs">Pcs</option>
                                <option value="Batang">Batang</option>
                                <option value="Kg">Kg</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-primary btn-sm fw-bold py-2">Simpan & Update Stok Otomatis ke Master Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>