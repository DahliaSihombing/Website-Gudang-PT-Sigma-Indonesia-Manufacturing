<?= $this->extend('produksi/layout'); ?>
<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center py-2">
                    <h5 class="mb-0 fw-bold fs-6">🔧 Form Penanganan Part NG</h5>
                </div>
                <div class="card-body p-4">
                    <?php if(session()->getFlashdata('error')):?>
                        <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error'); ?></div>
                    <?php endif;?>

                    <form action="<?= base_url('/produksi/repair/store'); ?>" method="post" id="formRework">
                        <?= csrf_field(); ?>

                        <!-- Baris 1: Informasi Umum -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tanggal Tindakan</label>
                                <input type="date" name="date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Shift Kerja</label>
                                <select name="shift" class="form-select form-select-sm" required>
                                    <option value="Shift 1">Shift 1 (Pagi)</option>
                                    <option value="Shift 2">Shift 2 (Sore)</option>
                                    <option value="Shift 3">Shift 3 (Malam)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Nama Operator / Teknisi</label>
                                <input type="text" name="operator_name" class="form-control form-control-sm" placeholder="Nama teknisi / PIC" required>
                            </div>
                        </div>

                        <!-- Baris 2: Pemilihan Jalur Penanganan -->
                        <div class="p-3 bg-light rounded border mb-3">
                            <label class="form-label small fw-bold text-dark d-block mb-2">Pilih Jalur Penanganan Part NG:</label>
                            <div class="d-flex flex-column flex-md-row gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_rework" id="tipe_inhouse" value="inhouse" checked onchange="toggleReworkType()">
                                    <label class="form-check-label small fw-bold text-success" for="tipe_inhouse">
                                        🏭 1. Perbaikan In-House (Masuk ke WIP Final)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_rework" id="tipe_vendor" value="vendor" onchange="toggleReworkType()">
                                    <label class="form-check-label small fw-bold text-primary" for="tipe_vendor">
                                        🔄 2. Kirim Repair ke Vendor (Subcon OUT Repair)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_rework" id="tipe_retur" value="retur" onchange="toggleReworkType()">
                                    <label class="form-check-label small fw-bold text-danger" for="tipe_retur">
                                        📦 3. Retur Material Mentah ke Vendor (Khusus 2 Item)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Box Info Khusus Retur Material -->
                        <div class="alert alert-warning py-2 small mb-3 border-warning" id="box_info_retur" style="display: none;">
                            <strong>ℹ️ Ketentuan Retur Material Mentah:</strong>
                            <ul class="mb-0 ps-3">
                                <li><strong>COLLAR RADIATOR 6.5MM</strong> dikonversi otomatis menjadi material <code>COLLAR Ø 11.5 X Ø 6.5 FORGING</code>.</li>
                                <li><strong>COLLAR RADIATOR 8.0MM</strong> dikonversi otomatis menjadi material <code>COLLAR Ø 12.5 X Ø8.0 FORGING</code>.</li>
                                <li>Barang dikirim keluar (OUT) sebagai Retur Material, dan saat diterima kembali (IN) dari vendor akan otomatis menambah stok Material Mentah.</li>
                            </ul>
                        </div>

                        <!-- Baris 3: Pemilihan Part NG & Jumlah -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Pilih Part NG yang Ditangani</label>
                                <select name="item_name" id="item_repair_select" class="form-select form-select-sm fw-semibold" onchange="updateMaxRepair(this)" required>
                                    <option value="" disabled selected>-- Pilih Part NG --</option>
                                    <?php if(!empty($ng_items)): ?>
                                        <?php foreach($ng_items as $ng): 
                                            $isReturSpecial = in_array(trim($ng['item_name']), ['COLLAR RADIATOR 6.5MM', 'COLLAR RADIATOR 8.0MM']);
                                            $hasStock = $ng['sisa_repair'] > 0;
                                        ?>
                                            <option value="<?= esc($ng['item_name']); ?>" 
                                                    data-max="<?= $ng['sisa_repair']; ?>"
                                                    data-retur="<?= $isReturSpecial ? '1' : '0'; ?>"
                                                    class="<?= $hasStock ? 'fw-bold text-dark' : 'text-muted'; ?>">
                                                <?= esc($ng['item_name']); ?> — (Stok Cacat Terakumulasi: <?= number_format($ng['sisa_repair']); ?> Pcs)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Jumlah (Pcs)</label>
                                <input type="number" name="quantity" id="qty_repair_input" class="form-control form-control-sm text-center fw-bold" placeholder="0" min="1" required>
                                <small class="fw-bold d-block mt-1" id="info_sisa" style="font-size: 11px;"></small>
                            </div>
                        </div>

                        <!-- Baris 4: Dropdown Vendor -->
                        <div class="row g-3 mb-3" id="group_vendor" style="display: none;">
                            <div class="col-md-12">
                                <div class="p-3 bg-white border border-primary rounded" id="vendor_border_box">
                                    <label class="form-label small fw-bold" id="label_vendor">Pilih Vendor Subcon Tujuan</label>
                                    <select name="vendor_name" id="select_vendor" class="form-select form-select-sm">
                                        <option value="" disabled selected>-- Pilih Vendor Subcon --</option>
                                        <?php if(!empty($vendors)): ?>
                                            <?php foreach($vendors as $v): ?>
                                                <option value="<?= esc($v['name']); ?>"><?= esc($v['name']); ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 5: Keterangan & Tindakan -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Tindakan / Jenis Perbaikan</label>
                                <input type="text" name="tindakan_repair" id="input_tindakan" class="form-control form-control-sm" placeholder="Contoh: Replating ulang / Polish cacat baret / Grinding burry" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Catatan Pengerjaan</label>
                                <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Catatan nomor lot, defect yang ditemukan, dsb.">
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" id="btn_submit" class="btn btn-warning btn-sm fw-bold py-2 text-dark">
                                🔁 Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleReworkType() {
    let isRetur = document.getElementById('tipe_retur').checked;
    let isRepairVendor = document.getElementById('tipe_vendor').checked;

    let groupVendor = document.getElementById('group_vendor');
    let selectVendor = document.getElementById('select_vendor');
    let vendorBox = document.getElementById('vendor_border_box');
    let labelVendor = document.getElementById('label_vendor');
    let btnSubmit = document.getElementById('btn_submit');
    let boxInfoRetur = document.getElementById('box_info_retur');
    let inputTindakan = document.getElementById('input_tindakan');

    let selectItem = document.getElementById('item_repair_select');
    let options = selectItem.querySelectorAll('option');

    options.forEach(opt => {
        if (!opt.value) return;
        let isReturSpecial = opt.getAttribute('data-retur') === '1';

        if (isRetur) {
            // Mode Retur: Hanya 2 part khusus yang aktif dan tampil
            if (isReturSpecial) {
                opt.hidden = false;
                opt.disabled = false;
                opt.style.display = '';
            } else {
                opt.hidden = true;
                opt.disabled = true;
                opt.style.display = 'none';
            }
        } else {
            // Mode In-house & Repair: Seluruh part aktif dan tampil
            opt.hidden = false;
            opt.disabled = false;
            opt.style.display = '';
        }
    });

    // Reset pilihan jika item yang sedang terpilih tidak sesuai dengan filter
    let curOpt = selectItem.options[selectItem.selectedIndex];
    if (curOpt && curOpt.disabled) {
        selectItem.value = '';
        document.getElementById('qty_repair_input').value = '';
        document.getElementById('info_sisa').innerText = '';
    }

    if (isRetur) {
        boxInfoRetur.style.display = 'block';
        groupVendor.style.display = 'block';
        selectVendor.setAttribute('required', 'required');
        vendorBox.className = 'p-3 bg-white border border-danger rounded';
        labelVendor.className = 'form-label small fw-bold text-danger';
        labelVendor.innerText = 'Pilih Vendor Tujuan Retur Material Mentah:';
        
        btnSubmit.className = 'btn btn-danger btn-sm fw-bold py-2 text-white';
        btnSubmit.innerText = '📦 Simpan';
        inputTindakan.value = 'Retur Cacat Forging ke Vendor (Konversi Material)';
    } else if (isRepairVendor) {
        boxInfoRetur.style.display = 'none';
        groupVendor.style.display = 'block';
        selectVendor.setAttribute('required', 'required');
        vendorBox.className = 'p-3 bg-white border border-primary rounded';
        labelVendor.className = 'form-label small fw-bold text-primary';
        labelVendor.innerText = 'Pilih Vendor Subcon Tujuan Pengiriman Repair:';
        
        btnSubmit.className = 'btn btn-primary btn-sm fw-bold py-2 text-white';
        btnSubmit.innerText = '🔄 Simpan';
        if (inputTindakan.value.includes('Retur Cacat Forging')) inputTindakan.value = '';
    } else {
        boxInfoRetur.style.display = 'none';
        groupVendor.style.display = 'none';
        selectVendor.removeAttribute('required');
        selectVendor.value = '';
        
        btnSubmit.className = 'btn btn-success btn-sm fw-bold py-2 text-white';
        btnSubmit.innerText = '🏭 Simpan';
        if (inputTindakan.value.includes('Retur Cacat Forging')) inputTindakan.value = '';
    }

    // Refresh validasi kuantitas saat tipe diubah
    updateMaxRepair(selectItem);
}

function updateMaxRepair(selectElement) {
    let opt = selectElement.options[selectElement.selectedIndex];
    if (!opt || !opt.value) {
        document.getElementById('info_sisa').innerText = '';
        return;
    }

    let maxQty = parseInt(opt.getAttribute('data-max')) || 0;
    let inputQty = document.getElementById('qty_repair_input');
    let infoSisa = document.getElementById('info_sisa');
    let btnSubmit = document.getElementById('btn_submit');

    inputQty.value = '';
    inputQty.setAttribute('max', maxQty);
    inputQty.setAttribute('placeholder', 'Maks: ' + maxQty);

    if (maxQty > 0) {
        infoSisa.className = 'text-success fw-bold d-block mt-1';
        infoSisa.innerText = '*Tersedia akumulasi cacat dari QC: ' + maxQty.toLocaleString() + ' Pcs';
        btnSubmit.disabled = false;
        btnSubmit.classList.remove('opacity-50');
    } else {
        infoSisa.className = 'text-danger fw-bold d-block mt-1';
        infoSisa.innerText = '⚠️ Stok cacat saat ini 0 Pcs. Part belum memiliki akumulasi cacat dari QC.';
        btnSubmit.disabled = true;
        btnSubmit.classList.add('opacity-50');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    toggleReworkType();
});
</script>
<?= $this->endSection(); ?>