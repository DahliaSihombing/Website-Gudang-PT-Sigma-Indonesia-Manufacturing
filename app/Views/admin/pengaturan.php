<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid px-0">
    <!-- Header Halaman -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="p-4 bg-white rounded shadow-sm border-start border-primary border-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Pengaturan & Manajemen Akun Pengguna</h4>
                    <p class="text-muted small mb-0">Kelola hak akses akun, tambah pengguna baru, ubah informasi profil, serta atur password.</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                    + Tambah Akun Baru
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Notifikasi Flashdata -->
    <?php if(session()->getFlashdata('success')):?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif;?>

    <?php if(session()->getFlashdata('error')):?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif;?>

    <!-- Tabel Daftar Pengguna -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white fw-bold py-2">
            📋 Daftar Akun Pengguna Sistem Pabrik
        </div>
        <div class="card-body p-3">
            <table class="table table-striped table-bordered align-middle mb-0 small">
                <thead class="table-dark text-center">
                    <tr>
                        <th style="width: 5%;">NO</th>
                        <th>NAMA LENGKAP</th>
                        <th>USERNAME / EMAIL</th>
                        <th style="width: 20%;">ROLE / HAK AKSES</th>
                        <th style="width: 30%;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada akun pengguna terdaftar.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach($users as $usr): ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="fw-bold text-primary"><?= esc($usr['full_name']); ?></td>
                            <td><?= esc($usr['username']); ?></td>
                            <td class="text-center">
                                <?php if(($usr['role'] ?? '') === 'admin'): ?>
                                    <span class="badge bg-danger px-2 py-1">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-success px-2 py-1">Operator Produksi</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm fw-semibold px-2 py-1" onclick="openEditModal('<?= $usr['id']; ?>', '<?= esc($usr['full_name'], 'js'); ?>', '<?= esc($usr['username'], 'js'); ?>', '<?= esc($usr['role'], 'js'); ?>')">
                                    ✏️ Edit
                                </button>
                                <button type="button" class="btn btn-info btn-sm fw-semibold text-white px-2 py-1" onclick="openPasswordModal('<?= $usr['id']; ?>', '<?= esc($usr['full_name'], 'js'); ?>')">
                                    🔑 Password
                                </button>
                                <a href="<?= base_url('/admin/pengaturan/delete/' . $usr['id']); ?>" class="btn btn-danger btn-sm fw-semibold px-2 py-1" onclick="return confirm('Yakin ingin menghapus akun ini?')">
                                    🗑️ Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Akun -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <form action="<?= base_url('/admin/pengaturan/store'); ?>" method="POST">
                <div class="modal-header bg-dark text-white py-2">
                    <h6 class="modal-title fw-bold">➕ Tambah Akun Pengguna Baru</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Lengkap</label>
                        <input type="text" name="full_name" class="form-control form-control-sm" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Username / Email Login</label>
                        <input type="text" name="username" class="form-control form-control-sm" placeholder="Contoh: budi@sigma.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Password Awal</label>
                        <input type="password" name="password" class="form-control form-control-sm" placeholder="Minimal 6 karakter" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Pilih Role / Hak Akses</label>
                        <select name="role" class="form-select form-select-sm" required>
                            <option value="" disabled selected>-- Pilih Hak Akses --</option>
                            <option value="admin">Admin (Akses Penuh Manajemen Pabrik)</option>
                            <option value="operator_produksi">Operator Produksi (Akses Input & Laporan Kerja)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Akun -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <form id="formEditUser" method="POST">
                <div class="modal-header bg-dark text-white py-2">
                    <h6 class="modal-title fw-bold">✏️ Edit Informasi Akun</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Lengkap</label>
                        <input type="text" id="edit_full_name" name="full_name" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Username / Email Login</label>
                        <input type="text" id="edit_username" name="username" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Pilih Role / Hak Akses</label>
                        <select id="edit_role" name="role" class="form-select form-select-sm" required>
                            <option value="admin">Admin</option>
                            <option value="operator_produksi">Operator Produksi</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm px-3 fw-bold text-dark">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ganti Password -->
<div class="modal fade" id="modalPasswordUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <form id="formPasswordUser" method="POST">
                <div class="modal-header bg-dark text-white py-2">
                    <h6 class="modal-title fw-bold">🔑 Ganti Password Pengguna: <span id="pass_user_name" class="text-warning"></span></h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Masukkan Password Baru</label>
                        <input type="password" name="new_password" class="form-control form-control-sm" placeholder="Password baru..." required>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info btn-sm px-3 fw-bold text-white">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript Helper untuk Modal -->
<script>
    function openEditModal(id, fullName, username, role) {
        document.getElementById('edit_full_name').value = fullName;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_role').value = role;
        
        let form = document.getElementById('formEditUser');
        form.action = '<?= base_url("/admin/pengaturan/update/"); ?>' + id;

        let modal = new bootstrap.Modal(document.getElementById('modalEditUser'));
        modal.show();
    }

    function openPasswordModal(id, fullName) {
        document.getElementById('pass_user_name').innerText = fullName;
        
        let form = document.getElementById('formPasswordUser');
        form.action = '<?= base_url("/admin/pengaturan/update-password/"); ?>' + id;

        let modal = new bootstrap.Modal(document.getElementById('modalPasswordUser'));
        modal.show();
    }
</script>

<?= $this->endSection(); ?>