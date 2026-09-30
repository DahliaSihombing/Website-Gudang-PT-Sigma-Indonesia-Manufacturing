<?= $this->extend('produksi/layout'); ?>
<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 fw-bold">Form WIP</h5>
                </div>
                <div class="card-body">
                    <?php if(session()->getFlashdata('error')):?>
                        <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error'); ?></div>
                    <?php endif;?>

                    <form action="<?= base_url('/produksi/final/store'); ?>" method="post">
                        <?= csrf_field(); ?>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Tanggal</label>
                                <input type="date" name="date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Shift Kerja</label>
                                <select name="shift" class="form-select form-select-sm" required>
                                    <option value="Shift 1">Shift 1 (Pagi)</option>
                                    <option value="Shift 2">Shift 2 (Sore)</option>
                                    <option value="Shift 3">Shift 3 (Malam)</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Nama Operator</label>
                                <input type="text" name="operator_name" class="form-control form-control-sm" placeholder="Nama operator" required>
                            </div>
                        </div>

                        <hr class="my-3">

                        <!-- Bagian Barang Asal WIP -->
                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label class="form-label small fw-semibold">Pilih Barang Asal (Material, OP 1, atau OP 2)</label>
                                <select name="material_asal" class="form-select form-select-sm fw-semibold" required>
                                    <option value="">-- Pilih Barang Asal --</option>
                                    <?php 
                                        $categories = ['Material', 'Cutting', 'OP 1'];
                                        
                                        $categoryLabels = [
                                            'Material' => 'MATERIAL',
                                            'Cutting'  => 'PROSES OP 1',
                                            'OP 1'     => 'PROSES OP 2',
                                        ];

                                        foreach($categories as $cat): 
                                            $displayLabel = $categoryLabels[$cat] ?? strtoupper($cat);
                                    ?>
                                        <option value="" disabled class="bg-secondary text-white fw-bold py-2">
                                            📁 === KATEGORI: <?= $displayLabel; ?> ===
                                        </option>
                                        <?php if(!empty($items)): ?>
                                            <?php foreach($items as $item): ?>
                                                <?php 
                                                    $itemCat = trim($item['category']);
                                                    $isMatch = ($itemCat == $cat) || 
                                                             (($cat == 'OP 1' || $cat == 'WIP OP1') && in_array($itemCat, ['OP 1', 'WIP OP1', 'WIPFinal', 'WIP Final']));
                                                ?>
                                                <?php if($isMatch): ?>
                                                    <option value="<?= esc($item['item_name']); ?>" class="text-dark bg-light ps-3">
                                                        &nbsp;&nbsp;▪ <?= esc($item['item_name']); ?> &nbsp;(Stok: <?= esc($item['stock']); ?>)
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
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

                        <!-- Bagian Hasil WIP -->
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label small fw-semibold">Pilih Nama Item Hasil WIP</label>
                                <select name="item_hasil_final" class="form-select form-select-sm fw-semibold" required>
                                    <option value="">-- Pilih Item Hasil WIP --</option>
                                    <?php 
                                        $wipFinalList = [
                                            "COLLAR MUFF PROTECTOR",
                                            "COLLAR RADIATOR 6.5MM",
                                            "COLLAR RADIATOR 8.0MM",
                                            "COLLAR 62670",
                                            "COLLAR 87180",
                                            "COLLAR 87181",
                                            "COLLAR FAN",
                                            "SPACER FULL TANK",
                                            "ARM CLUTCH RELEASE",
                                            "BASE FJFN M30",
                                            "PIPE NUT ROLL-MTG",
                                            "COLLAR AIR/C SET 22",
                                            "PIN Ø10.0 X 16",
                                            "PIN Ø16.0"
                                        ];
                                        foreach($wipFinalList as $wipItem):
                                    ?>
                                        <option value="<?= $wipItem; ?>"><?= $wipItem; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold">Jumlah Hasil (Pcs)</label>
                                <input type="number" name="quantity" class="form-control form-control-sm" min="1" placeholder="Contoh: 100" required>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-dark btn-sm fw-bold py-2">Simpan & Selesaikan ke WIP</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>