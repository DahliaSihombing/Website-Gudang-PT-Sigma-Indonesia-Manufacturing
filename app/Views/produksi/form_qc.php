<?= $this->extend('produksi/layout'); ?>
<?= $this->section('content'); ?>
<div class="container-fluid">
    <!-- Header Halaman -->
    <div class="row mb-3">
        <div class="col-md-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="fw-bold text-dark mb-1">Form Input Pengecekan QC</h4>
            </div>
            
            <div class="btn-group shadow-sm" role="group" aria-label="Sumber QC">
                <button type="button" class="btn btn-sm btn-primary fw-bold px-3 py-2" id="btn-inhouse" onclick="switchSumber('In-house')">📦 Dari WIP</button>
                <button type="button" class="btn btn-sm btn-outline-secondary fw-bold px-3 py-2" id="btn-subcon" onclick="switchSumber('Subcon')">🔄 Dari Subcon (Vendor)</button>
            </div>
        </div>
    </div>

    <?php if(session()->getFlashdata('error')):?>
        <div class="alert alert-danger py-2 small shadow-sm"><?= session()->getFlashdata('error'); ?></div>
    <?php endif;?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="<?= base_url('/produksi/store-qc'); ?>" method="post" onsubmit="return validasiForm(event)">
                <?= csrf_field(); ?>
                
                <input type="hidden" name="sumber_barang" id="sumber_barang" value="In-house">
                <input type="hidden" name="subcon_id" id="subcon_id" value="">

                <!-- BARIS 1: INFORMASI UMUM -->
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-secondary">Tanggal</label>
                        <input type="date" name="date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-secondary">Shift Kerja</label>
                        <select name="shift" class="form-select form-select-sm" required>
                            <option value="Shift 1">Shift 1 (Pagi)</option>
                            <option value="Shift 2">Shift 2 (Sore)</option>
                            <option value="Shift 3">Shift 3 (Malam)</option>
                        </select>
                    </div>

                    <!-- Container In-House -->
                    <div class="col-md-8" id="container-inhouse">
                        <label class="form-label small fw-bold text-secondary">Pilih Nama Part / Barang (WIP Final)</label>
                        <select name="item_name" class="form-select form-select-sm fw-semibold">
                            <option value="" disabled selected>-- Pilih Barang WIP Final --</option>
                            <?php if(!empty($wip_final)): ?>
                                <?php foreach($wip_final as $wf): ?>
                                    <option value="<?= $wf['item_name']; ?>"><?= $wf['item_name']; ?> (Stok Fisik: <?= $wf['stock']; ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Container Subcon -->
                    <div class="col-md-4" id="container-subcon" style="display: none;">
                        <label class="form-label small fw-bold text-secondary">Pilih Kiriman Subcon IN (Pending QC)</label>
                        <select id="subcon_select" class="form-select form-select-sm" onchange="updateSubconOtomatis(this)">
                            <option value="" disabled selected>-- Pilih Kiriman Masuk Subcon --</option>
                            <?php if(!empty($subcon_in)): ?>
                                <?php foreach($subcon_in as $si): ?>
                                    <option value="<?= $si['id']; ?>" 
                                            data-item="<?= htmlspecialchars($si['item_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-vendor="<?= htmlspecialchars($si['vendor_name'] ?? 'Vendor Eksternal', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-sisa="<?= $si['sisa_qty']; ?>"
                                            data-unit="<?= $si['unit_transaksi'] ?? 'Pcs'; ?>">
                                        [Tgl: <?= date('d/m/Y', strtotime($si['date'])); ?>] <?= $si['item_name']; ?> — <?= $si['vendor_name']; ?> (Sisa: <?= $si['sisa_qty']; ?> <?= $si['unit_transaksi'] ?? 'Pcs'; ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>-- Tidak ada kiriman Subcon pending --</option>
                            <?php endif; ?>
                        </select>
                        <input type="hidden" name="item_name" id="subcon_item_name" value="" disabled>
                    </div>

                    <div class="col-md-2" id="container-vendor" style="display: none;">
                        <label class="form-label small fw-bold text-secondary">Nama Vendor</label>
                        <input type="text" id="nama_vendor" name="nama_vendor" class="form-control form-control-sm bg-light fw-bold text-primary" readonly placeholder="Vendor...">
                    </div>

                    <div class="col-md-2" id="container-sisa" style="display: none;">
                        <label class="form-label small fw-bold text-danger">Sisa Belum Cek</label>
                        <input type="text" id="sisa_qty" class="form-control form-control-sm bg-light fw-bold text-danger text-center" readonly placeholder="0">
                    </div>
                </div>

                <!-- BARIS 2: KUANTITAS PEMERIKSAAN -->
                <div class="p-3 bg-light rounded border mb-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark mb-1">Total Qty Check</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="quantity" id="qty_check" class="form-control text-center fw-bold fs-6" placeholder="0" min="0.01" oninput="hitungOtomatis()" required>
                                <select name="unit" id="unit_check" class="form-select" style="max-width: 90px;" onchange="updateSatuanLabel()">
                                    <option value="Pcs">Pcs</option>
                                    <option value="Kg">Kg</option>
                                </select>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">*Jumlah sampel/lot yang diinspeksi</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-success mb-1">Total Qty OK (Lolos)</label>
                            <input type="number" step="0.01" name="qty_ok" id="qty_ok" class="form-control text-center text-success fw-bold fs-6 border-success" placeholder="0" min="0" oninput="hitungOtomatis()" required>
                            <small class="text-muted" style="font-size: 11px;">*Barang lolos standar (masuk ke FG)</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-danger mb-1">Total Qty NG (Cacat)</label>
                            <div class="form-control bg-white text-center text-danger fw-bold fs-6 border-danger d-flex justify-content-between align-items-center">
                                <span>Total NG:</span>
                                <span id="grand_total_ng" class="badge bg-danger fs-6">0 <span class="label-unit">Pcs</span></span>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">*(Total Repair + Total Reject)</small>
                        </div>
                    </div>
                </div>

                <!-- Alert Validasi Real-time -->
                <div id="alert_validasi" class="alert alert-danger py-2 px-3 small mb-3 shadow-sm fw-semibold" style="display: none;">
                    ⚠️ Perhatian: Jumlah (Qty OK + Total NG) harus sama persis dengan Qty Check!
                </div>

                <!-- BARIS 3: RINCIAN REPAIR & REJECT -->
                <div class="row g-3 mb-3">
                    <!-- KOTAK REPAIR -->
                    <div class="col-md-4">
                        <div class="card border-warning h-100 shadow-sm">
                            <div class="card-header bg-warning text-dark fw-bold text-center py-2 small">
                                🛠️ REPAIR (BISA DIPERBAIKI)
                            </div>
                            <div class="card-body bg-light p-3">
                                <div class="mb-3">
                                    <label class="form-label text-secondary fw-bold" style="font-size: 11px;">BURRY GROOVING (Visual)</label>
                                    <input type="number" step="0.01" name="ng_repair_burry" id="rep_burry" class="form-control form-control-sm text-center ng-input" value="0" min="0" oninput="hitungOtomatis()">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-secondary fw-bold" style="font-size: 11px;">BELUM PROSES (Visual)</label>
                                    <input type="number" step="0.01" name="ng_repair_belum" id="rep_belum" class="form-control form-control-sm text-center ng-input" value="0" min="0" oninput="hitungOtomatis()">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-secondary fw-bold" style="font-size: 11px;">GROOVING (Dimensi)</label>
                                    <input type="number" step="0.01" name="ng_repair_dimensi" id="rep_dim" class="form-control form-control-sm text-center ng-input" value="0" min="0" oninput="hitungOtomatis()">
                                </div>
                                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                                    <span class="small fw-bold text-secondary">Subtotal Repair:</span>
                                    <span id="total_repair" class="fw-bold text-warning-emphasis fs-6">0 <span class="label-unit">Pcs</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KOTAK REJECT -->
                    <div class="col-md-8">
                        <div class="card border-danger h-100 shadow-sm">
                            <div class="card-header bg-danger text-white fw-bold text-center py-2 small">
                                🚫 REJECT (RUSAK / AFKIR)
                            </div>
                            <div class="card-body bg-light p-3">
                                <div class="row g-2">
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label text-secondary fw-bold" style="font-size: 11px;">HOLE SEMPIT</label>
                                        <input type="number" step="0.01" name="ng_reject_hole" id="rej_hole" class="form-control form-control-sm text-center ng-input" value="0" min="0" oninput="hitungOtomatis()">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label text-secondary fw-bold" style="font-size: 11px;">REPLATING</label>
                                        <input type="number" step="0.01" name="ng_reject_replating" id="rej_replating" class="form-control form-control-sm text-center ng-input" value="0" min="0" oninput="hitungOtomatis()">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label text-secondary fw-bold" style="font-size: 11px;">DACON</label>
                                        <input type="number" step="0.01" name="ng_reject_dacon" id="rej_dacon" class="form-control form-control-sm text-center ng-input" value="0" min="0" oninput="hitungOtomatis()">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label text-secondary fw-bold" style="font-size: 11px;">GOMPAL (Visual)</label>
                                        <input type="number" step="0.01" name="ng_reject_gompal" id="rej_gompal" class="form-control form-control-sm text-center ng-input" value="0" min="0" oninput="hitungOtomatis()">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label text-secondary fw-bold" style="font-size: 11px;">BARET (Visual)</label>
                                        <input type="number" step="0.01" name="ng_reject_baret" id="rej_baret" class="form-control form-control-sm text-center ng-input" value="0" min="0" oninput="hitungOtomatis()">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label text-secondary fw-bold" style="font-size: 11px;">PANJANG (Dimensi)</label>
                                        <input type="number" step="0.01" name="ng_reject_panjang" id="rej_panjang" class="form-control form-control-sm text-center ng-input" value="0" min="0" oninput="hitungOtomatis()">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label text-secondary fw-bold" style="font-size: 11px;">PENDEK (Dimensi)</label>
                                        <input type="number" step="0.01" name="ng_reject_pendek" id="rej_pendek" class="form-control form-control-sm text-center ng-input" value="0" min="0" oninput="hitungOtomatis()">
                                    </div>
                                </div>
                                <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                                    <span class="small fw-bold text-secondary">Subtotal Reject:</span>
                                    <span id="total_reject" class="fw-bold text-danger fs-6">0 <span class="label-unit">Pcs</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BARIS 4: IDENTITAS INSPECTOR & CATATAN -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Nama PIC / Inspector QC</label>
                        <input type="text" name="operator_name" class="form-control form-control-sm" placeholder="Nama pemeriksa/inspector..." required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-bold text-secondary">Catatan / Keterangan Tambahan</label>
                        <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Catatan kondisi part, nomor lot, atau temuan lapangan...">
                    </div>
                </div>

                <!-- TOMBOL SUBMIT -->
                <div class="text-center mt-4 pt-2 border-top">
                    <button type="submit" id="btn_simpan" class="btn btn-danger fw-bold px-5 py-2 shadow-sm">
                        💾 SIMPAN LAPORAN QC DAN NG
                    </button>
                    <a href="<?= base_url('/produksi/riwayat?kategori=QC'); ?>" class="btn btn-secondary fw-semibold px-4 py-2 ms-2">Batal</a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
function switchSumber(sumber) {
    let btnInhouse = document.getElementById('btn-inhouse');
    let btnSubcon = document.getElementById('btn-subcon');
    let inputSumber = document.getElementById('sumber_barang');
    
    let containerInhouse = document.getElementById('container-inhouse');
    let containerSubcon = document.getElementById('container-subcon');
    let containerVendor = document.getElementById('container-vendor');
    let containerSisa = document.getElementById('container-sisa');

    let inhouseSelect = containerInhouse.querySelector('select');
    let subconItemName = document.getElementById('subcon_item_name');

    inputSumber.value = sumber;

    if (sumber === 'In-house') {
        btnInhouse.className = 'btn btn-sm btn-primary fw-bold px-3 py-2';
        btnSubcon.className = 'btn btn-sm btn-outline-secondary fw-bold px-3 py-2';
        
        containerInhouse.style.display = 'block';
        containerSubcon.style.display = 'none';
        containerVendor.style.display = 'none';
        containerSisa.style.display = 'none';

        inhouseSelect.removeAttribute('disabled');
        subconItemName.setAttribute('disabled', 'disabled');
        document.getElementById('subcon_id').value = '';
        document.getElementById('nama_vendor').value = '';
        document.getElementById('sisa_qty').value = '';
        document.getElementById('unit_check').value = 'Pcs';
        document.getElementById('unit_check').removeAttribute('disabled');
    } else {
        btnSubcon.className = 'btn btn-sm btn-primary fw-bold px-3 py-2';
        btnInhouse.className = 'btn btn-sm btn-outline-secondary fw-bold px-3 py-2';
        
        containerSubcon.style.display = 'block';
        containerVendor.style.display = 'block';
        containerSisa.style.display = 'block';
        containerInhouse.style.display = 'none';

        inhouseSelect.setAttribute('disabled', 'disabled');
        subconItemName.removeAttribute('disabled');
    }
    updateSatuanLabel();
}

function updateSubconOtomatis(selectElement) {
    let selectedOption = selectElement.options[selectElement.selectedIndex];
    let subconId = selectedOption.value;
    let itemName = selectedOption.getAttribute('data-item') || '';
    let vendorName = selectedOption.getAttribute('data-vendor') || 'Vendor Eksternal';
    let sisa = parseFloat(selectedOption.getAttribute('data-sisa')) || 0;
    let unit = selectedOption.getAttribute('data-unit') || 'Pcs';
    
    document.getElementById('subcon_id').value = subconId;
    document.getElementById('subcon_item_name').value = itemName;
    document.getElementById('nama_vendor').value = vendorName;
    document.getElementById('sisa_qty').value = sisa + ' ' + unit;
    
    let unitCheck = document.getElementById('unit_check');
    unitCheck.value = unit;

    let inputQty = document.getElementById('qty_check');
    inputQty.value = '';
    inputQty.setAttribute('max', sisa);
    inputQty.setAttribute('placeholder', 'Max: ' + sisa);

    updateSatuanLabel();
}

function updateSatuanLabel() {
    let unit = document.getElementById('unit_check').value;
    let labels = document.querySelectorAll('.label-unit');
    labels.forEach(el => el.innerText = unit);
}

function hitungOtomatis() {
    let qtyCheck = parseFloat(document.getElementById('qty_check').value) || 0;
    let qtyOk = parseFloat(document.getElementById('qty_ok').value) || 0;

    let repBurry = parseFloat(document.getElementById('rep_burry').value) || 0;
    let repBelum = parseFloat(document.getElementById('rep_belum').value) || 0;
    let repDim = parseFloat(document.getElementById('rep_dim').value) || 0;
    let totalRepair = repBurry + repBelum + repDim;

    let rejHole = parseFloat(document.getElementById('rej_hole').value) || 0;
    let rejReplating = parseFloat(document.getElementById('rej_replating').value) || 0;
    let rejDacon = parseFloat(document.getElementById('rej_dacon').value) || 0;
    let rejGompal = parseFloat(document.getElementById('rej_gompal').value) || 0;
    let rejBaret = parseFloat(document.getElementById('rej_baret').value) || 0;
    let rejPanjang = parseFloat(document.getElementById('rej_panjang').value) || 0;
    let rejPendek = parseFloat(document.getElementById('rej_pendek').value) || 0;

    let totalReject = rejHole + rejReplating + rejDacon + rejGompal + rejBaret + rejPanjang + rejPendek;
    let grandTotalNg = totalRepair + totalReject;

    let unit = document.getElementById('unit_check').value;

    document.getElementById('total_repair').innerText = totalRepair + ' ' + unit;
    document.getElementById('total_reject').innerText = totalReject + ' ' + unit;
    document.getElementById('grand_total_ng').innerText = grandTotalNg + ' ' + unit;

    let alertBox = document.getElementById('alert_validasi');
    let btnSimpan = document.getElementById('btn_simpan');

    // Validasi toleransi pembulatan desimal
    let selisih = Math.abs((qtyOk + grandTotalNg) - qtyCheck);
    if (qtyCheck > 0 && selisih > 0.001) {
        alertBox.style.display = 'block';
        alertBox.innerText = `⚠️ Peringatan: Qty OK (${qtyOk}) + Total NG (${grandTotalNg}) = ${(qtyOk + grandTotalNg)}, tetapi Qty Check adalah ${qtyCheck} ${unit}! Harap sesuaikan.`;
        btnSimpan.disabled = true;
        btnSimpan.classList.add('opacity-50');
    } else {
        alertBox.style.display = 'none';
        btnSimpan.disabled = false;
        btnSimpan.classList.remove('opacity-50');
    }
}

function validasiForm(event) {
    let qtyCheck = parseFloat(document.getElementById('qty_check').value) || 0;
    let qtyOk = parseFloat(document.getElementById('qty_ok').value) || 0;
    let unit = document.getElementById('unit_check').value;
    
    let grandTotalNg = (parseFloat(document.getElementById('rep_burry').value) || 0) +
                       (parseFloat(document.getElementById('rep_belum').value) || 0) +
                       (parseFloat(document.getElementById('rep_dim').value) || 0) +
                       (parseFloat(document.getElementById('rej_hole').value) || 0) +
                       (parseFloat(document.getElementById('rej_replating').value) || 0) +
                       (parseFloat(document.getElementById('rej_dacon').value) || 0) +
                       (parseFloat(document.getElementById('rej_gompal').value) || 0) +
                       (parseFloat(document.getElementById('rej_baret').value) || 0) +
                       (parseFloat(document.getElementById('rej_panjang').value) || 0) +
                       (parseFloat(document.getElementById('rej_pendek').value) || 0);

    let selisih = Math.abs((qtyOk + grandTotalNg) - qtyCheck);
    if (selisih > 0.001) {
        alert('Gagal Disimpan! Jumlah Qty OK dan Total NG harus sama persis dengan Qty Check (' + qtyCheck + ' ' + unit + ').');
        event.preventDefault();
        return false;
    }
    return true;
}
</script>

<?= $this->endSection(); ?>