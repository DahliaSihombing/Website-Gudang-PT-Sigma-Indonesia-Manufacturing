<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="p-3 bg-white rounded shadow-sm border-start border-primary border-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="fw-bold text-dark mb-1">Master Data Mitra Rekanan</h4>
                <p class="text-muted small mb-0">Kelola daftar Supplier Material, Vendor Subcon Plating, dan PT Tujuan Pengiriman Barang.</p>
            </div>
            <button class="btn btn-primary btn-sm fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalTambahPartner">
                + Tambah Mitra Baru
            </button>
        </div>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Tab Filter Kategori Mitra -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap gap-2">
            <a href="/admin/partners" class="btn btn-sm fw-bold px-3 py-2 <?= ($active_filter === 'Semua') ? 'btn-dark' : 'btn-light border text-dark'; ?>">
                Semua Mitra
            </a>
            <a href="/admin/partners?type=Supplier" class="btn btn-sm fw-bold px-3 py-2 <?= ($active_filter === 'Supplier') ? 'btn-success text-white' : 'btn-light border text-dark'; ?>">
                🛒 Supplier Material
            </a>
            <a href="/admin/partners?type=Vendor" class="btn btn-sm fw-bold px-3 py-2 <?= ($active_filter === 'Vendor') ? 'btn-warning text-dark' : 'btn-light border text-dark'; ?>">
                ⚙️ Vendor Subcon (Plating)
            </a>
            <a href="/admin/partners?type=Customer" class="btn btn-sm fw-bold px-3 py-2 <?= ($active_filter === 'Customer') ? 'btn-info text-dark' : 'btn-light border text-dark'; ?>">
                🚚 PT Customer (Pengiriman)
            </a>
        </div>
    </div>
</div>

<!-- Tabel Data Mitra -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center py-2">
        <span>Daftar Mitra [ <?= $active_filter; ?> ]</span>
        <small class="text-light">Total: <?= count($partners); ?> Mitra</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle mb-0 small">
                <thead class="table-dark text-center">
                    <tr>
                        <th style="width: 5%;">NO</th>
                        <th style="width: 15%;">TIPE MITRA</th>
                        <th>NAMA PT / PERUSAHAAN</th>
                        <th style="width: 25%;">ALAMAT</th>
                        <th style="width: 15%;">NO. TELEPON</th>
                        <th style="width: 15%;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($partners)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data mitra pada kategori ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach($partners as $p): ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="text-center">
                                <?php if($p['type'] === 'Supplier'): ?>
                                    <span class="badge bg-success">Supplier</span>
                                <?php elseif($p['type'] === 'Vendor'): ?>
                                    <span class="badge bg-warning text-dark">Vendor</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">Customer</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-primary"><?= esc($p['name']); ?></td>
                            <td><?= esc($p['address'] ?: '-'); ?></td>
                            <td class="text-center"><?= esc($p['phone'] ?: '-'); ?></td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm px-2 py-1 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#modalEditPartner<?= $p['id']; ?>">
                                    Edit
                                </button>
                                <a href="/admin/partners/delete/<?= $p['id']; ?>" class="btn btn-danger btn-sm px-2 py-1 fw-bold" onclick="return confirm('Yakin ingin menghapus mitra <?= esc($p['name']); ?>?');">
                                    Hapus
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Edit Partner -->
                        <div class="modal fade" id="modalEditPartner<?= $p['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="/admin/partners/update/<?= $p['id']; ?>" method="post" class="modal-content">
                                    <?= csrf_field(); ?>
                                    <div class="modal-header bg-warning text-dark py-2">
                                        <h5 class="modal-title fs-6 fw-bold">Edit Data Mitra</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-start py-3">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Tipe Mitra</label>
                                            <select name="type" class="form-select form-select-sm" required>
                                                <option value="Supplier" <?= ($p['type'] === 'Supplier') ? 'selected' : ''; ?>>Supplier (Material Masuk)</option>
                                                <option value="Vendor" <?= ($p['type'] === 'Vendor') ? 'selected' : ''; ?>>Vendor Subcon (Plating / Cat)</option>
                                                <option value="Customer" <?= ($p['type'] === 'Customer') ? 'selected' : ''; ?>>Customer (Tujuan Pengiriman Barang)</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Nama PT / Mitra</label>
                                            <input type="text" name="name" class="form-control form-control-sm" value="<?= esc($p['name']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Alamat (Opsional)</label>
                                            <textarea name="address" rows="2" class="form-control form-control-sm"><?= esc($p['address']); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">No. Telepon / Kontak (Opsional)</label>
                                            <input type="text" name="phone" class="form-control form-control-sm" value="<?= esc($p['phone']); ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer py-2">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-warning btn-sm fw-bold">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Partner -->
<div class="modal fade" id="modalTambahPartner" tabindex="-1">
    <div class="modal-dialog">
        <form action="/admin/partners/store" method="post" class="modal-content">
            <?= csrf_field(); ?>
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title fs-6 fw-bold">Tambah Mitra / PT Rekanan Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Tipe Mitra</label>
                    <select name="type" class="form-select form-select-sm" required>
                        <option value="Supplier" <?= ($active_filter === 'Supplier') ? 'selected' : ''; ?>>Supplier (Material Masuk)</option>
                        <option value="Vendor" <?= ($active_filter === 'Vendor') ? 'selected' : ''; ?>>Vendor Subcon (Plating / Cat)</option>
                        <option value="Customer" <?= ($active_filter === 'Customer') ? 'selected' : ''; ?>>Customer (Tujuan Pengiriman Barang)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nama PT / Mitra</label>
                    <input type="text" name="name" class="form-control form-control-sm" placeholder="Contoh: PT. MAKMUR SEJAHTERA" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Alamat (Opsional)</label>
                    <textarea name="address" rows="2" class="form-control form-control-sm" placeholder="Alamat pabrik / kantor..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">No. Telepon / Kontak (Opsional)</label>
                    <input type="text" name="phone" class="form-control form-control-sm" placeholder="Contoh: 021-8901234">
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Mitra</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection(); ?>