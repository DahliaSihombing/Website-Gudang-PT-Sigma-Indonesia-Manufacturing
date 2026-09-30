<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Panel Admin - PT Sigma'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts & Bootstrap Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --primary-accent: #3b82f6;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        /* Sidebar Styling */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar-brand {
            padding: 24px 20px;
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-menu {
            padding: 15px 12px;
            overflow-y: auto;
            flex-grow: 1;
        }

        .menu-header {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #64748b;
            padding: 12px 12px 6px 12px;
        }

        .sidebar .nav-link {
            color: var(--text-muted);
            padding: 10px 14px;
            font-weight: 500;
            font-size: 13.5px;
            border-radius: 8px;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease-in-out;
        }

        .sidebar .nav-link i {
            font-size: 16px;
            transition: transform 0.2s ease;
        }

        .sidebar .nav-link:hover {
            color: #f8fafc;
            background-color: var(--sidebar-hover);
        }

        .sidebar .nav-link:hover i {
            transform: translateX(3px);
            color: var(--primary-accent);
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
            font-weight: 600;
        }

        .sidebar .nav-link.active i {
            color: #ffffff;
        }

        /* User Profile Area */
        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(15, 23, 42, 0.8);
        }

        .user-card {
            background: #1e293b;
            border-radius: 10px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        /* Main Content Layout */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .content-body {
            padding: 28px;
            flex-grow: 1;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar Admin -->
    <aside class="sidebar" id="sidebarMenu">
        <div>
            <!-- Brand Info -->
            <div class="sidebar-brand d-flex align-items-center gap-2">
                <div class="bg-primary rounded-3 p-2 d-flex align-items-center justify-content-center text-white" style="width: 38px; height: 38px;">
                    <i class="bi bi-gear-wide-connected fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-white mb-0 fs-6">PT SIGMA INDONESIA MANUFACTURING</h6>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="sidebar-menu">
                <div class="menu-header">Utama</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= (service('uri')->getSegment(2) == 'dashboard') ? 'active' : ''; ?>" href="/admin/dashboard">
                            <i class="bi bi-grid-1x2-fill"></i> Dashboard
                        </a>
                    </li>
                </ul>

                <div class="menu-header mt-2">Logistik & Material</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= (service('uri')->getSegment(2) == 'barang') ? 'active' : ''; ?>" href="/admin/barang">
                            <i class="bi bi-box-seam-fill"></i> Master Data Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (service('uri')->getSegment(2) == 'pembelian') ? 'active' : ''; ?>" href="/admin/pembelian">
                            <i class="bi bi-cart-check-fill"></i> Pembelian
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (service('uri')->getSegment(2) == 'subcon') ? 'active' : ''; ?>" href="/admin/subcon">
                            <i class="bi bi-truck-front-fill"></i> Vendor / Subcon
                        </a>
                    </li>
                </ul>

                <div class="menu-header mt-2">Monitoring & Distribusi</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= (service('uri')->getSegment(2) == 'produksi') ? 'active' : ''; ?>" href="/admin/produksi">
                            <i class="bi bi-cpu-fill"></i> Monitoring Produksi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (service('uri')->getSegment(2) == 'pengiriman') ? 'active' : ''; ?>" href="/admin/pengiriman">
                            <i class="bi bi-send-fill"></i> Pengiriman Barang
                        </a>
                    </li>
                </ul>

                <div class="menu-header mt-2">Manajemen & Sistem</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= (service('uri')->getSegment(2) == 'partners') ? 'active' : ''; ?>" href="/admin/partners">
                            <i class="bi bi-building-fill"></i> Penambahan Mitra
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (service('uri')->getSegment(2) == 'laporan') ? 'active' : ''; ?>" href="/admin/laporan">
                            <i class="bi bi-file-earmark-bar-graph-fill"></i> Pusat Laporan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (service('uri')->getSegment(2) == 'pengaturan') ? 'active' : ''; ?>" href="/admin/pengaturan">
                            <i class="bi bi-sliders"></i> Pengaturan
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- User Profile Footer -->
        <div class="sidebar-footer">
            <div class="user-card mb-2">
                <div class="avatar-circle">
                    <?= strtoupper(substr(session()->get('full_name') ?? 'A', 0, 1)); ?>
                </div>
                <div class="overflow-hidden me-auto">
                    <div class="fw-semibold text-white text-truncate style-name" style="font-size: 13px;">
                        <?= session()->get('full_name') ?? 'Administrator'; ?>
                    </div>
                    <small class="text-muted d-block text-capitalize" style="font-size: 11px;">
                        <?= session()->get('role') ?? 'Admin'; ?> Panel
                    </small>
                </div>
            </div>
            <a href="/auth/logout" class="btn btn-outline-danger btn-sm w-100 fw-semibold d-flex align-items-center justify-content-center gap-2" style="font-size: 12px; border-radius: 6px;">
                <i class="bi bi-box-arrow-right"></i> Keluar Sistem
            </a>
        </div>
    </aside>

    <!-- Content Wrap -->
    <div class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none p-1 px-2 border" type="button" onclick="document.getElementById('sidebarMenu').classList.toggle('show')">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <span class="fw-semibold text-slate-700" style="font-size: 14px;">
                    <i class="bi bi-calendar3 me-1 text-primary"></i> <?= date('d F Y'); ?>
                </span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill" style="font-size: 12px;">
                    <i class="bi bi-shield-check me-1"></i> Admin Access
                </span>
            </div>
        </header>

        <!-- Dynamic Body Content -->
        <main class="content-body">
            <?= $this->renderSection('content'); ?>
        </main>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>