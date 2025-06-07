<?php
require_once '../../includes/auth.php';
require_once '../../includes/layout.php';
require_once '../../config/database.php';

checkLogin();
checkRole(['admin']);

// Get admin name
$stmt = $pdo->prepare("SELECT nama_admin FROM admin WHERE id_admin = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin_name = $stmt->fetchColumn();

// Get recent harvests
$stmt = $pdo->query("
    SELECT dhp.*, t.nama_tanaman, p.nama_petani 
    FROM data_hasil_panen dhp
    JOIN tanaman t ON dhp.id_tanaman = t.id_tanaman
    JOIN data_lahan dl ON dhp.id_lahan = dl.id_lahan
    JOIN petani p ON dl.id_petani = p.id_petani
    ORDER BY dhp.tanggal_panen DESC
    LIMIT 5
");
$recent_harvests = $stmt->fetchAll();

// Get recent purchases
$stmt = $pdo->query("
    SELECT p.*, d.nama_distributor 
    FROM pembelian p
    JOIN distributor d ON p.id_distributor = d.id_distributor
    ORDER BY p.id_pembelian DESC
    LIMIT 5
");
$recent_purchases = $stmt->fetchAll();

// Get total admins
$stmt = $pdo->query("SELECT COUNT(*) FROM admin");
$total_admins = $stmt->fetchColumn();

// Get total farmers
$stmt = $pdo->query("SELECT COUNT(*) FROM petani");
$total_farmers = $stmt->fetchColumn();

// Get total harvests
$stmt = $pdo->query("SELECT COUNT(*) FROM data_hasil_panen");
$total_harvests = $stmt->fetchColumn();

// Get total transactions
$stmt = $pdo->query("SELECT COUNT(*) FROM pembelian");
$total_transactions = $stmt->fetchColumn();

$content = '
<div class="container-fluid px-4">
    <!-- Welcome Card -->
    <div class="row mb-4 mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(45deg, #2E7D32, #43A047);">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-circle fa-3x text-white"></i>
                        </div>
                        <div class="flex-grow-1 ms-4">
                            <h3 class="mb-1 text-white">Selamat datang, ' . htmlspecialchars($admin_name) . '</h3>
                            <p class="text-white-50 mb-0">
                                <i class="fas fa-chart-line me-2"></i>Dashboard Overview
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background-color: #2E7D32; color: white; border-radius: 0.375rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-6 text-white-50">Total Petani</div>
                            <div class="fs-2 fw-bold">' . number_format($total_farmers, 0, ",", ".") . '</div>
                        </div>
                        <div class="fs-1 text-white-50">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background-color: #2E7D32; color: white; border-radius: 0.375rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-6 text-white-50">Total Distributor</div>
                            <div class="fs-2 fw-bold">' . number_format($total_admins, 0, ",", ".") . '</div>
                        </div>
                        <div class="fs-1 text-white-50">
                            <i class="fas fa-truck"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background-color: #2E7D32; color: white; border-radius: 0.375rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-6 text-white-50">Total Transaksi</div>
                            <div class="fs-2 fw-bold">' . number_format($total_transactions, 0, ",", ".") . '</div>
                        </div>
                        <div class="fs-1 text-white-50">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background-color: #2E7D32; color: white; border-radius: 0.375rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-6 text-white-50">Total Hasil Panen</div>
                            <div class="fs-2 fw-bold">' . number_format($total_harvests, 0, ",", ".") . ' kg</div>
                        </div>
                        <div class="fs-1 text-white-50">
                            <i class="fas fa-leaf"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #2E7D32; color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exchange-alt me-2"></i>
                            <span class="fs-5 fw-semibold">Transaksi Terbaru</span>
                        </div>
                        <a href="transaksi.php" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-success">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Distributor</th>
                                    <th>Petani</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>';

foreach ($recent_purchases as $purchase) {
    $status_class = $purchase['status'] == 'selesai' ? 'success' : ($purchase['status'] == 'menunggu' ? 'warning' : 'danger');
    $content .= '
                                <tr>
                                    <td>' . date("d/m/Y", strtotime($purchase['tanggal'])) . '</td>
                                    <td>' . htmlspecialchars($purchase['nama_distributor']) . '</td>
                                    <td>' . htmlspecialchars($purchase['nama_petani']) . '</td>
                                    <td>Rp ' . number_format($purchase['total_harga'], 0, ",", ".") . '</td>
                                    <td><span class="badge bg-' . $status_class . '">' . ucfirst($purchase['status']) . '</span></td>
                                </tr>';
}

$content .= '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #2E7D32; color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-leaf me-2"></i>
                            <span class="fs-5 fw-semibold">Hasil Panen Terbaru</span>
                        </div>
                        <a href="hasil_panen.php" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-success">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Petani</th>
                                    <th>Tanaman</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>';

foreach ($recent_harvests as $harvest) {
    $status_class = $harvest['status'] == 'tersedia' ? 'success' : ($harvest['status'] == 'terjual' ? 'primary' : 'danger');
    $content .= '
                                <tr>
                                    <td>' . date("d/m/Y", strtotime($harvest['tanggal_panen'])) . '</td>
                                    <td>' . htmlspecialchars($harvest['nama_petani']) . '</td>
                                    <td>' . htmlspecialchars($harvest['nama_tanaman']) . '</td>
                                    <td>' . number_format($harvest['jumlah'], 0, ",", ".") . ' kg</td>
                                    <td><span class="badge bg-' . $status_class . '">' . ucfirst($harvest['status']) . '</span></td>
                                </tr>';
}

$content .= '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

echo renderPage('Dashboard Admin', 'dashboard', $content);
?> 