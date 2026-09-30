<?= $this->extend('produksi/layout'); ?>
<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 fw-bold">Form OP 2</h5>
                </div>
                <div class="card-body">
                    <?php if(session()->getFlashdata('error')):?>
                        <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error'); ?></div>
                    <?php endif;?>

                    <form action="<?= base_url('/produksi/op1/store'); ?>" method="post">
                        <?= csrf_field(); ?>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Tanggal</label>
                                <input type="date" name="date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Shift Kerja</label>
                                <select name="shift" class="form-select form-select-sm" required>
                                    <option value="Shift 1 (Pagi)">Shift 1 (Pagi)</option>
                                    <option value="Shift 2 (Malam)">Shift 2 (Malam)</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Nama Operator</label>
                                <input type="text" name="operator_name" class="form-control form-control-sm" placeholder="Nama operator" required>
                            </div>
                        </div>

                        <hr class="my-3">

                        <!-- Bagian Barang Asal dari Kategori Cutting -->
                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label class="form-label small fw-semibold">Pilih Barang Asal (Hasil OP 1 / Cutting)</label>
                                <select name="material_asal" id="material_asal" class="form-select form-select-sm fw-semibold" required>
                                    <option value="">-- Pilih Barang Asal --</option>
                                    <option value="" disabled class="bg-primary text-white fw-bold py-2">
                                        📁 === KATEGORI: PROSES OP 1 (CUTTING) ===
                                    </option>
                                    <?php if(!empty($items)): ?>
                                        <?php foreach($items as $item): ?>
                                            <option value="<?= esc($item['item_name']); ?>" class="text-dark bg-light ps-3">
                                                &nbsp;&nbsp;▪ <?= esc($item['item_name']); ?> &nbsp;(Stok: <?= esc($item['stock']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label small fw-semibold">Jumlah Pakai</label>
                                <input type="number" name="qty_material_pakai" class="form-control form-control-sm" min="1" placeholder="Jumlah" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label small fw-semibold">Satuan</label>
                                <input type="text" class="form-control form-control-sm bg-light" value="Pcs" readonly>
                                <input type="hidden" name="unit_pakai" value="Pcs">
                            </div>
                        </div>

                        <hr class="my-3">

                        <!-- Bagian Hasil / Output OP 2 -->
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label small fw-semibold">Hasil Nama Item (Otomatis)</label>
                                <input type="text" id="display_hasil" class="form-control form-control-sm bg-light" placeholder="Akan terisi otomatis..." disabled>
                                <input type="hidden" name="item_hasil_op1" id="item_hasil_op1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Jumlah Hasil (Pcs)</label>
                                <input type="number" name="quantity" class="form-control form-control-sm" min="1" placeholder="Contoh: 100" required>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary btn-sm fw-bold py-2">Simpan & Potong Stok Asal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('material_asal').addEventListener('change', function() {
        let selectedValue = this.value;
        let displayInput = document.getElementById('display_hasil');
        let hiddenInput = document.getElementById('item_hasil_op1');

        if (selectedValue) {
            let cleanVal = selectedValue.trim().toUpperCase();
            let hasil = "";

            if (cleanVal === 'COLLAR FAN CUTTING') {
                hasil = 'COLLAR FAN';
            } else if (cleanVal.includes('CUTTING')) {
                hasil = selectedValue.replace(/CUTTING/gi, 'OP2').trim();
            } else {
                hasil = selectedValue + ' OP 2';
            }

            displayInput.value = hasil;
            hiddenInput.value = hasil;
        } else {
            displayInput.value = '';
            hiddenInput.value = '';
        }
    });
</script>
<?= $this->endSection(); ?>