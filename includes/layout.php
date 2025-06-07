<?php
function renderLayout($content, $page_title = null, $active_page = null) {
    ob_start();
    include 'header.php';
    echo $content;
    include 'footer.php';
    return ob_get_clean();
}

function renderPage($title, $active_menu, $content) {
    $menu_items = [
        'admin' => [
            'dashboard' => ['icon' => 'fas fa-home', 'text' => 'Dashboard', 'path' => '/KebunKU4TID/dashboard/admin/index.php'],
            'hasil_panen' => ['icon' => 'fas fa-leaf', 'text' => 'Hasil Panen', 'path' => '/KebunKU4TID/dashboard/admin/hasil_panen.php'],
            'transaksi' => ['icon' => 'fas fa-exchange-alt', 'text' => 'Transaksi', 'path' => '/KebunKU4TID/dashboard/admin/transaksi.php'],
            'kelola_admin' => ['icon' => 'fas fa-users-cog', 'text' => 'Kelola Admin', 'path' => '/KebunKU4TID/dashboard/admin/kelola_admin.php']
        ],
        'petani' => [
            'dashboard' => ['icon' => 'fas fa-home', 'text' => 'Dashboard', 'path' => '/KebunKU4TID/dashboard/petani/index.php'],
            'lahan' => ['icon' => 'fas fa-map-marked-alt', 'text' => 'Data Lahan', 'path' => '/KebunKU4TID/dashboard/petani/lahan.php'],
            'rencana_tanam' => ['icon' => 'fas fa-seedling', 'text' => 'Rencana Tanam', 'path' => '/KebunKU4TID/dashboard/petani/rencana_tanam.php'],
            'pupuk' => ['icon' => 'fas fa-flask', 'text' => 'Penggunaan Pupuk', 'path' => '/KebunKU4TID/dashboard/petani/pupuk.php'],
            'hasil_panen' => ['icon' => 'fas fa-leaf', 'text' => 'Hasil Panen', 'path' => '/KebunKU4TID/dashboard/petani/hasil_panen.php']
        ],
        'distributor' => [
            'dashboard' => ['icon' => 'fas fa-home', 'text' => 'Dashboard', 'path' => '/KebunKU4TID/dashboard/distributor/index.php'],
            'pembelian' => ['icon' => 'fas fa-shopping-cart', 'text' => 'Pembelian', 'path' => '/KebunKU4TID/dashboard/distributor/pembelian.php'],
            'pengiriman' => ['icon' => 'fas fa-truck', 'text' => 'Pengiriman', 'path' => '/KebunKU4TID/dashboard/distributor/pengiriman.php'],
            'stok' => ['icon' => 'fas fa-boxes', 'text' => 'Stok', 'path' => '/KebunKU4TID/dashboard/distributor/stok.php']
        ]
    ];

    $user_role = $_SESSION['role'];
    $current_menu = $menu_items[$user_role];

    $html = '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $title . ' - KebunKU</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="/KebunKU4TID/assets/css/' . $user_role . '.css" rel="stylesheet">
        <style>
            :root {
                --primary-color: #2E7D32;
                --secondary-color: #4CAF50;
                --accent-color: #81C784;
                --light-color: #E8F5E9;
            }
            
            .sidebar {
                background-color: var(--primary-color);
                min-height: 100vh;
                color: white;
                transition: all 0.3s ease;
                position: fixed;
                z-index: 100;
                width: 250px;
            }

            .sidebar.collapsed {
                width: 65px;
            }

            .sidebar.collapsed .nav-link span,
            .sidebar.collapsed .menu-section-title,
            .sidebar.collapsed .logo-text {
                display: none;
            }

            .sidebar.collapsed .nav-link {
                text-align: center;
                padding: 0.7rem;
                justify-content: center;
                margin: 0.2rem 0.5rem;
                gap: 0;
            }

            .sidebar.collapsed .nav-link i {
                margin: 0;
                width: auto;
                font-size: 1.25rem;
            }

            .sidebar.collapsed .logo-container {
                padding: 1.2rem 0.5rem;
                justify-content: center;
            }

            .sidebar.collapsed .logo-container img {
                margin: 0;
                width: 32px;
            }

            .main-content {
                padding: 0 1.5rem;
                background-color: #f8f9fa;
                margin-left: 250px;
                transition: all 0.3s ease;
                min-height: 100vh;
            }

            .main-content.expanded {
                margin-left: 65px;
            }
            
            .sidebar .nav-link {
                color: white;
                padding: 0.7rem 1rem;
                margin: 0.2rem 0.8rem;
                border-radius: 0.5rem;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                text-decoration: none;
                font-size: 0.95rem;
                gap: 12px;
            }

            .toggle-sidebar {
                position: fixed;
                left: 250px;
                top: 1.2rem;
                background: var(--primary-color);
                color: white;
                border: none;
                border-radius: 0 8px 8px 0;
                padding: 0.4rem 0.5rem;
                cursor: pointer;
                transition: all 0.3s ease;
                z-index: 101;
                width: 24px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .toggle-sidebar.collapsed {
                left: 65px;
            }

            .toggle-sidebar:hover {
                background: var(--secondary-color);
            }

            .toggle-sidebar i {
                font-size: 0.8rem;
            }
            
            .sidebar .nav-link:hover {
                background-color: var(--secondary-color);
                transform: translateX(5px);
            }
            
            .sidebar .nav-link.active {
                background-color: var(--secondary-color);
            }

            .sidebar .nav-link i {
                width: 20px;
                text-align: center;
                font-size: 1.1rem;
                flex-shrink: 0;
            }

            .sidebar .nav-link span {
                padding-left: 4px;
            }

            .logo-container {
                padding: 1rem 1.2rem;
                display: flex;
                align-items: center;
                color: white;
                text-decoration: none;
                margin-bottom: 0.8rem;
            }

            .logo-container:hover {
                color: white;
                text-decoration: none;
            }

            .logo-container img {
                width: 32px;
                height: auto;
                margin-right: 10px;
            }

            .logo-container h4 {
                margin: 0;
                font-size: 1.3rem;
                font-weight: 600;
            }

            .menu-section {
                margin-bottom: 0.8rem;
            }

            .menu-section-title {
                color: var(--light-color);
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 0.4rem;
                padding: 0 1.2rem;
                opacity: 0.8;
            }

            .welcome-card {
                background-color: white;
                border-radius: 15px;
                padding: 1.2rem;
                box-shadow: 0 0 15px rgba(0,0,0,0.1);
                margin-bottom: 1.5rem;
            }

            .welcome-card .welcome-icon {
                color: var(--primary-color);
            }

            .welcome-card h4 {
                color: var(--primary-color);
                font-weight: 600;
                font-size: 1.2rem;
                margin: 0;
            }

            .stats-box {
                background-color: var(--primary-color) !important;
                color: white;
                padding: 1.2rem;
                border-radius: 15px;
                transition: transform 0.3s ease;
                margin-bottom: 1rem;
            }

            .stats-box:hover {
                transform: translateY(-5px);
            }

            .stats-box i {
                font-size: 1.5rem;
                margin-right: 0.8rem;
            }

            .stats-box h6 {
                font-size: 0.9rem;
                margin: 0;
                opacity: 0.9;
                font-weight: 500;
            }

            .stats-box h2 {
                font-size: 1.8rem;
                margin: 0.5rem 0 0 0;
                font-weight: 600;
            }

            .card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 0 15px rgba(0,0,0,0.1);
                transition: transform 0.3s ease;
                overflow: hidden;
            }

            .card-header {
                padding: 1rem 1.2rem;
                background-color: var(--primary-color) !important;
                border-bottom: none;
                color: white;
            }

            .card-header h5 {
                font-size: 1rem;
                font-weight: 600;
            }

            .card-body {
                padding: 1.2rem;
            }

            .table th {
                background-color: var(--light-color) !important;
                color: var(--primary-color);
                font-weight: 600;
                padding: 0.8rem 1.2rem;
                font-size: 0.9rem;
            }

            .table td {
                padding: 0.8rem 1.2rem;
                vertical-align: middle;
            }

            .btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.85rem;
            }

            .badge {
                padding: 0.4rem 0.6rem;
                font-weight: 500;
            }

            .modal-header {
                padding: 1rem 1.2rem;
            }

            .modal-body {
                padding: 1.2rem;
            }

            .modal-footer {
                padding: 0.8rem 1.2rem;
            }

            .form-label {
                font-size: 0.9rem;
                margin-bottom: 0.4rem;
            }

            .form-control {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }

            .sidebar, .main-content, .toggle-sidebar {
                transition: none !important;
            }
            
            .sidebar.transition-enabled,
            .main-content.transition-enabled,
            .toggle-sidebar.transition-enabled {
                transition: all 0.3s ease !important;
            }

            .btn-success {
                background-color: var(--primary-color) !important;
                border-color: var(--primary-color) !important;
            }

            .btn-success:hover {
                background-color: var(--secondary-color) !important;
                border-color: var(--secondary-color) !important;
            }

            .section-header {
                background-color: var(--primary-color) !important;
                color: white;
                padding: 1rem;
                border-radius: 10px;
                margin-bottom: 1rem;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                background: var(--primary-color) !important;
                color: white !important;
                border-color: var(--primary-color) !important;
            }

            .export-btn {
                background-color: var(--primary-color) !important;
                color: white !important;
                border: none !important;
                padding: 0.5rem 1rem !important;
                border-radius: 5px !important;
            }

            .export-btn:hover {
                background-color: var(--secondary-color) !important;
            }

            .form-control:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.25);
            }

            .badge-success {
                background-color: var(--primary-color) !important;
            }

            .custom-title {
                color: var(--primary-color);
                border-bottom: 2px solid var(--primary-color);
                padding-bottom: 0.5rem;
                margin-bottom: 1.5rem;
            }

            .page-header {
                background-color: var(--primary-color) !important;
                color: white;
                padding: 1.5rem;
                border-radius: 10px;
                margin-bottom: 1.5rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .page-header h2 {
                margin: 0;
                font-size: 1.5rem;
                font-weight: 600;
            }

            .page-header .btn {
                background-color: white;
                color: var(--primary-color);
                border: none;
                padding: 0.5rem 1rem;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .page-header .btn:hover {
                background-color: var(--light-color);
                transform: translateY(-2px);
            }

            .page-header .btn i {
                margin-right: 0.5rem;
            }

            .table thead th {
                background-color: var(--light-color);
                color: var(--primary-color);
                font-weight: 600;
                border-bottom: none;
            }

            .table tbody td {
                vertical-align: middle;
            }

            .badge {
                background-color: var(--primary-color) !important;
                color: white;
                padding: 0.5rem 0.8rem;
                font-weight: 500;
                border-radius: 5px;
            }

            .btn-success {
                background-color: var(--primary-color) !important;
                border-color: var(--primary-color) !important;
            }

            .btn-success:hover {
                background-color: var(--secondary-color) !important;
                border-color: var(--secondary-color) !important;
            }

            .card {
                border: none;
                border-radius: 10px;
                box-shadow: 0 0 15px rgba(0,0,0,0.1);
                overflow: hidden;
            }

            .card-header {
                background-color: var(--primary-color) !important;
                color: white;
                padding: 1rem 1.2rem;
                border-bottom: none;
            }

            .card-body {
                padding: 1.2rem;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                background: var(--primary-color) !important;
                color: white !important;
                border-color: var(--primary-color) !important;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                background: var(--light-color) !important;
                color: var(--primary-color) !important;
                border-color: var(--light-color) !important;
            }

            .modal-header {
                background-color: var(--primary-color) !important;
                color: white;
                border-bottom: none;
            }

            .modal-header .btn-close {
                color: white;
                opacity: 0.8;
            }

            .modal-header .btn-close:hover {
                opacity: 1;
            }

            .modal-title {
                font-weight: 600;
            }

            .modal-footer {
                border-top: 1px solid #eee;
                padding: 1rem;
            }

            .modal-footer .btn-secondary {
                background-color: #6c757d;
                border: none;
            }

            .modal-footer .btn-success {
                background-color: var(--primary-color);
                border: none;
            }

            .modal-footer .btn-success:hover {
                background-color: var(--secondary-color);
            }

            .modal-body {
                padding: 1.5rem;
            }

            .modal-body .form-label {
                color: #495057;
                font-weight: 500;
            }

            .modal-body .form-control:focus,
            .modal-body .form-select:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.25);
            }

            .modal-body .form-control,
            .modal-body .form-select {
                border-radius: 6px;
                border: 1px solid #ced4da;
            }

            .page-header .btn {
                background-color: var(--primary-color);
                color: white;
                border: none;
                padding: 0.5rem 1rem;
                font-weight: 500;
                transition: all 0.3s ease;
                border-radius: 6px;
            }

            .page-header .btn:hover {
                background-color: var(--secondary-color);
                transform: translateY(-2px);
            }

            .page-header .btn i {
                margin-right: 0.5rem;
            }

            @media (max-width: 991.98px) {
                .sidebar {
                    width: 200px;
                }

                .sidebar.collapsed {
                    width: 0;
                    overflow: hidden;
                }

                .main-content {
                    margin-left: 200px;
                }

                .main-content.expanded {
                    margin-left: 0;
                }

                .toggle-sidebar {
                    left: 200px;
                }

                .toggle-sidebar.collapsed {
                    left: 0;
                }
            }

            @media (max-width: 767.98px) {
                .sidebar {
                    width: 100%;
                    position: fixed;
                    z-index: 1040;
                    transform: translateX(-100%);
                    transition: transform 0.3s ease;
                }

                .sidebar:not(.collapsed) {
                    transform: translateX(0);
                }

                .main-content {
                    margin-left: 0 !important;
                    padding: 1rem;
                }

                .toggle-sidebar {
                    left: 0;
                    z-index: 1050;
                }

                .page-header {
                    flex-direction: column;
                    gap: 1rem;
                    align-items: stretch !important;
                    text-align: center;
                }

                .page-header .btn {
                    width: 100%;
                }

                .table-responsive {
                    margin: 0 -1rem;
                    padding: 0 1rem;
                    width: calc(100% + 2rem);
                }

                .modal-dialog {
                    margin: 0.5rem;
                }
            }

            .card {
                height: 100%;
            }

            .card-body {
                height: 100%;
                display: flex;
                flex-direction: column;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            @media (max-width: 575.98px) {
                .table thead {
                    display: none;
                }

                .table tbody tr {
                    display: block;
                    margin-bottom: 1rem;
                    border: 1px solid #dee2e6;
                    border-radius: 6px;
                }

                .table tbody td {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border: none;
                    padding: 0.75rem 1rem;
                    text-align: right;
                }

                .table tbody td::before {
                    content: attr(data-label);
                    font-weight: 600;
                    margin-right: 1rem;
                    text-align: left;
                }

                .table tbody td:not(:last-child) {
                    border-bottom: 1px solid #dee2e6;
                }

                .btn-sm {
                    padding: 0.5rem 0.75rem;
                    font-size: 1rem;
                }
            }

            .form-group {
                margin-bottom: 1rem;
            }

            @media (max-width: 575.98px) {
                .form-control,
                .form-select {
                }

                .modal-footer {
                    flex-direction: column;
                    gap: 0.5rem;
                }

                .modal-footer .btn {
                    width: 100%;
                }
            }

            .dataTables_wrapper .row {
                margin: 0;
                width: 100%;
            }

            @media (max-width: 767.98px) {
                .dataTables_wrapper .dataTables_length,
                .dataTables_wrapper .dataTables_filter {
                    text-align: left;
                    margin-bottom: 0.5rem;
                }

                .dataTables_wrapper .dataTables_info,
                .dataTables_wrapper .dataTables_paginate {
                    text-align: left;
                    margin-top: 0.5rem;
                }

                .dataTables_wrapper .dataTables_paginate .paginate_button {
                    padding: 0.375rem 0.75rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-auto px-0">
                    <div class="sidebar" id="sidebar">
                        <a href="/KebunKU4TID/index.php" class="logo-container">
                            <img src="/KebunKU4TID/assets/img/logo.png" alt="KebunKU">
                            <h4 class="logo-text">KebunKU</h4>
                        </a>
                        <button class="toggle-sidebar" id="toggleSidebar">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="menu-section">
                            <div class="menu-section-title">Menu Utama</div>
                            <div class="nav flex-column">';

    foreach ($current_menu as $menu => $details) {
        $is_active = $menu === $active_menu ? 'active' : '';
        $path = isset($details['path']) ? $details['path'] : $menu . '.php';
        $html .= '
                                <a href="' . $path . '" class="nav-link ' . $is_active . '">
                                    <i class="' . $details['icon'] . '"></i>
                                    <span>' . $details['text'] . '</span>
                                </a>';
    }

    $html .= '
                            </div>
                        </div>
                        <div class="menu-section">
                            <div class="menu-section-title">Akun</div>
                            <div class="nav flex-column">
                                <a href="/KebunKU4TID/logout.php" class="nav-link text-white">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Keluar</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col main-content" id="mainContent">
                    ' . $content . '
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
        
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            var sidebar = document.querySelector(".sidebar");
            var mainContent = document.querySelector(".main-content");
            var toggleBtn = document.querySelector(".toggle-sidebar");
            
            var isSidebarCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
            if (isSidebarCollapsed) {
                sidebar.classList.add("collapsed");
                mainContent.classList.add("expanded");
                toggleBtn.classList.add("collapsed");
                toggleBtn.innerHTML = \'<i class="fas fa-chevron-right"></i>\';
            } else {
                sidebar.classList.remove("collapsed");
                mainContent.classList.remove("expanded");
                toggleBtn.classList.remove("collapsed");
                toggleBtn.innerHTML = \'<i class="fas fa-chevron-left"></i>\';
            }

            toggleBtn.addEventListener("click", function() {
                sidebar.classList.toggle("collapsed");
                mainContent.classList.toggle("expanded");
                toggleBtn.classList.toggle("collapsed");
                
                var isNowCollapsed = sidebar.classList.contains("collapsed");
                localStorage.setItem("sidebarCollapsed", isNowCollapsed);
                
                toggleBtn.innerHTML = isNowCollapsed ? 
                    \'<i class="fas fa-chevron-right"></i>\' : 
                    \'<i class="fas fa-chevron-left"></i>\';
            });

            setTimeout(function() {
                sidebar.classList.add("transition-enabled");
                mainContent.classList.add("transition-enabled");
                toggleBtn.classList.add("transition-enabled");
            }, 100);
        });
        </script>
    </body>
    </html>';

    return $html;
}
?> 