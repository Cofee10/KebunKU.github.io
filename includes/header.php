<?php
if (!isset($page_title)) {
    $page_title = 'KebunKU';
}
if (!isset($active_page)) {
    $active_page = '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - KebunKU</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/KebunKU4TID/assets/css/admin.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/KebunKU4TID/assets/js/admin.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="/KebunKU4TID/assets/img/Logo.jpg" alt="KebunKU Logo" class="sidebar-logo">
            <h1 class="sidebar-title">KebunKU</h1>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <h6 class="nav-section-title">Menu Utama</h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= $active_page == 'dashboard' ? 'active' : '' ?>" href="/KebunKU4TID/dashboard/admin/index.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_page == 'hasil_panen' ? 'active' : '' ?>" href="/KebunKU4TID/dashboard/admin/hasil_panen.php">
                            <i class="fas fa-leaf"></i>
                            <span>Hasil Panen</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_page == 'transaksi' ? 'active' : '' ?>" href="/KebunKU4TID/dashboard/admin/transaksi.php">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Transaksi</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_page == 'kelola_admin' ? 'active' : '' ?>" href="/KebunKU4TID/dashboard/admin/kelola_admin.php">
                            <i class="fas fa-users-cog"></i>
                            <span>Kelola Admin</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="nav-section mt-auto">
                <h6 class="nav-section-title">Akun</h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/KebunKU4TID/logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Keluar</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <div class="main-content">
        <nav class="navbar navbar-expand-lg navbar-light topbar">
            <div class="container-fluid">
                <button class="btn btn-link sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i>
                            <span>Admin</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="/KebunKU4TID/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid py-4">
            <div class="page-header mb-4">
                <h1 class="page-title"><?= $page_title ?></h1>
            </div> 