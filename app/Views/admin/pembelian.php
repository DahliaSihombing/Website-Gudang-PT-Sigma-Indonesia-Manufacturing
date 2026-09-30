<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="p-3 bg-white rounded shadow-sm border-start border-primary border-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-dark mb-1">Transaksi Pembelian Material (Inbound Supplier)</h4>
                <p class="text-muted small mb-0">Kelola dan pantau seluruh data penerimaan bahan baku atau material masuk dari supplier.</p>
            </div>
            <button class="btn btn-primary btn-sm fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#addModal">+ Transaksi Baru</button>
        </div>
    </div>
</div>

<?php if(session()->getFlashdata('success')):?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif;?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold">
                Daftar Transaksi Pembelian Material Masuk
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-dark text-center small">
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 15%;">TANGGAL</th>
                                <th>NAMA MATERIAL</th>
                                <th style="width: 25%;">SUPPLIER</th>
                                <th style="width: 15%;">JUMLAH</th>
                                <th style="width: 18%;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php if(empty($purchases)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada data transaksi pembelian material.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach($purchases as $row): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td class="text-center"><?= date('d-m-Y', strtotime($row['date'])); ?></td>
                                        <td class="fw-bold text-primary"><?= esc($row['item_name']); ?></td>
                                        <td><?= esc($row['supplier']); ?></td>
                                        <td class="text-center"><span class="badge bg-success px-3 py-2"><?= esc($row['quantity']); ?></span></td>
                                        <td class="text-center">
                                            <button onclick="window.print()" class="btn btn-info btn-sm px-2 py-1 text-white fw-bold">Print</button>
                                            <button class="btn btn-warning btn-sm px-2 py-1 text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id']; ?>">Edit</button>
                                            <a href="/admin/pembelian/delete/<?= $row['id']; ?>" class="btn btn-danger btn-sm px-2 py-1 fw-bold" onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
                                        </td>
                                    </tr>

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="editModal<?= $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form action="/admin/pembelian/update/<?= $row['id']; ?>" method="post">
                                                    <?= csrf_field(); ?>
                                                    <div class="modal-header bg-warning text-dark py-2">
                                                        <h5 class="modal-title fs-6 fw-bold">Edit Transaksi Pembelian</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-start py-3">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label small fw-bold">Tanggal</label>
                                                                <input type="date" name="date" class="form-control form-control-sm" value="<?= $row['date']; ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label small fw-bold">Nama Material</label>
                                                                <select name="item_name" class="form-select form-select-sm" required>
                                                                    <?php foreach($items as $it): ?>
                                                                        <option value="<?= esc($it['item_name']); ?>" <?= ($row['item_name'] == $it['item_name']) ? 'selected' : ''; ?>>
                                                                            <?= esc($it['item_name']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label small fw-bold">Supplier</label>
                                                                <select name="supplier" class="form-select form-select-sm" required>
                                                                    <?php if(!empty($suppliers)): ?>
                                                                        <?php foreach($suppliers as $sup): ?>
                                                                            <option value="<?= esc($sup['name']); ?>" <?= ($row['supplier'] == $sup['name']) ? 'selected' : ''; ?>>
                                                                                <?= esc($sup['name']); ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    <?php else: ?>
                                                                        <option value="<?= esc($row['supplier']); ?>" selected><?= esc($row['supplier']); ?></option>
                                                                    <?php endif; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label small fw-bold">Jumlah (Beserta Satuan)</label>
                                                                <input type="text" name="quantity" class="form-control form-control-sm" value="<?= esc($row['quantity']); ?>" required>
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

<!-- Modal Tambah Transaksi Baru -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/admin/pembelian/store" method="post">
                <?= csrf_field(); ?>
                <div class="modal-header bg-primary text-white py-2">
                    <h5 class="modal-title fs-6 fw-bold">Catat Pembelian Material Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Tanggal</label>
                            <input type="date" name="date" class="form-control form-control-sm" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Pilih Nama Material</label>
                            <select name="item_name" class="form-select form-select-sm" required>
                                <option value="" disabled selected>-- Pilih Material --</option>
                                <?php foreach($items as $it): ?>
                                    <option value="<?= esc($it['item_name']); ?>"><?= esc($it['item_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Pilih Supplier</label>
                                <a href="/admin/partners?type=Supplier" class="text-decoration-none small text-primary fw-bold" target="_blank">+ Kelola Supplier</a>
                            </div>
                            <select name="supplier" class="form-select form-select-sm" required>
                                <option value="" disabled selected>-- Pilih Supplier --</option>
                                <?php if(!empty($suppliers)): ?>
                                    <?php foreach($suppliers as $sup): ?>
                                        <option value="<?= esc($sup['name']); ?>"><?= esc($sup['name']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Jumlah (Masukkan Angka Saja)</label>
                            <input type="number" name="quantity" class="form-control form-control-sm" placeholder="Contoh: 100" min="1" required>
                            <small class="text-muted" style="font-size: 11px;">*Satuan otomatis mengikuti satuan master material.</small>
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

<?= $this->endSection(); ?>