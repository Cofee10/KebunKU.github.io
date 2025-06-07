<?php
require_once '../../includes/auth.php';
require_once '../../includes/layout.php';
require_once '../../config/database.php';

checkLogin();
checkRole(['distributor']);

// Get distributor's data
$stmt = $pdo->prepare("
    SELECT d.*,
           COALESCE(COUNT(DISTINCT p.id_pembelian), 0) as total_pembelian,
           COALESCE(SUM(p.jumlah_pembelian), 0) as total_stok,
           COALESCE(COUNT(DISTINCT CASE WHEN rp.status = 'dalam pengiriman' THEN rp.id_riwayat_pengiriman END), 0) as pengiriman_aktif,
           COALESCE(SUM(p.total_harga), 0) as total_transaksi
    FROM distributor d
    LEFT JOIN pembelian p ON d.id_distributor = p.id_distributor
    LEFT JOIN riwayat_pengiriman rp ON p.id_pembelian = rp.id_pembelian
    WHERE d.id_distributor = ?
    GROUP BY d.id_distributor
");
$stmt->execute([$_SESSION['user_id']]);
$distributor = $stmt->fetch();

// Get recent purchases
$stmt = $pdo->prepare("
    SELECT p.*, hp.jumlah as jumlah_tersedia, t.nama_tanaman, pt.nama_petani
    FROM pembelian p
    JOIN data_hasil_panen hp ON p.id_panen = hp.id_panen
    JOIN tanaman t ON hp.id_tanaman = t.id_tanaman
    JOIN data_lahan dl ON hp.id_lahan = dl.id_lahan
    JOIN petani pt ON dl.id_petani = pt.id_petani
    WHERE p.id_distributor = ?
    ORDER BY p.tanggal_pembelian DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_purchases = $stmt->fetchAll();

// Get recent shipments
$stmt = $pdo->prepare("
    SELECT rp.*, p.total_harga, p.jumlah_pembelian
    FROM riwayat_pengiriman rp
    JOIN pembelian p ON rp.id_pembelian = p.id_pembelian
    WHERE p.id_distributor = ?
    ORDER BY rp.tanggal_pengiriman DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_shipments = $stmt->fetchAll();

$content = '
<div class="container-fluid px-4">
    <!-- Welcome Card -->
    <div class="row mb-4 mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(45deg, #2E7D32, #43A047);">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-white rounded-circle p-3">
                                <i class="fas fa-user-circle fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-4">
                            <h3 class="mb-1 text-white">Selamat datang, ' . htmlspecialchars($distributor["nama_distributor"]) . '</h3>
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
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background: linear-gradient(45deg, #2E7D32, #43A047); border-radius: 0.5rem;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-white-50 mb-2">Total Pembelian</div>
                            <h3 class="text-white mb-0">' . number_format($distributor["total_pembelian"], 0, ",", ".") . '</h3>
                        </div>
                        <div class="p-3">
                            <i class="fas fa-shopping-cart fa-lg text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background: linear-gradient(45deg, #2E7D32, #43A047); border-radius: 0.5rem;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-white-50 mb-2">Total Stok</div>
                            <h3 class="text-white mb-0">' . number_format($distributor["total_stok"], 0, ",", ".") . ' kg</h3>
                        </div>
                        <div class="p-3">
                            <i class="fas fa-warehouse fa-lg text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background: linear-gradient(45deg, #2E7D32, #43A047); border-radius: 0.5rem;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-white-50 mb-2">Pengiriman Aktif</div>
                            <h3 class="text-white mb-0">' . number_format($distributor["pengiriman_aktif"], 0, ",", ".") . '</h3>
                        </div>
                        <div class="p-3">
                            <i class="fas fa-truck fa-lg text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background: linear-gradient(45deg, #2E7D32, #43A047); border-radius: 0.5rem;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-white-50 mb-2">Total Transaksi</div>
                            <h3 class="text-white mb-0">Rp ' . number_format($distributor["total_transaksi"], 0, ",", ".") . '</h3>
                        </div>
                        <div class="p-3">
                            <i class="fas fa-money-bill-wave fa-lg text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row g-4">
        <!-- Recent Purchases -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3" style="background: linear-gradient(45deg, #2E7D32, #43A047);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shopping-cart text-white me-2"></i>
                            <h5 class="text-white mb-0">Pembelian Terbaru</h5>
                        </div>
                        <a href="pembelian.php" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3">Tanaman</th>
                                    <th class="py-3">Petani</th>
                                    <th class="py-3">Jumlah</th>
                                    <th class="py-3">Total</th>
                                    <th class="py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>';

foreach ($recent_purchases as $purchase) {
    $status_class = "";
    switch ($purchase["status"]) {
        case "selesai":
            $status_class = "success";
            break;
        case "menunggu":
            $status_class = "warning";
            break;
        default:
            $status_class = "danger";
    }
    
    $content .= '
                                <tr>
                                    <td class="py-3">' . htmlspecialchars($purchase["nama_tanaman"]) . '</td>
                                    <td class="py-3">' . htmlspecialchars($purchase["nama_petani"]) . '</td>
                                    <td class="py-3">' . number_format($purchase["jumlah_pembelian"], 0, ",", ".") . ' kg</td>
                                    <td class="py-3">Rp ' . number_format($purchase["total_harga"], 0, ",", ".") . '</td>
                                    <td class="py-3">
                                        <span class="badge bg-' . $status_class . ' rounded-pill">
                                            ' . ucfirst($purchase["status"]) . '
                                        </span>
                                    </td>
                                </tr>';
}

if (empty($recent_purchases)) {
    $content .= '
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada data pembelian</td>
                                </tr>';
}

$content .= '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Shipments -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3" style="background: linear-gradient(45deg, #2E7D32, #43A047);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-truck text-white me-2"></i>
                            <h5 class="text-white mb-0">Pengiriman Terbaru</h5>
                        </div>
                        <a href="pengiriman.php" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3">Tanggal</th>
                                    <th class="py-3">Tujuan</th>
                                    <th class="py-3">Jumlah</th>
                                    <th class="py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>';

foreach ($recent_shipments as $shipment) {
    $status_class = "";
    switch ($shipment["status"]) {
        case "selesai":
            $status_class = "success";
            break;
        case "dalam_pengiriman":
            $status_class = "primary";
            break;
        case "menunggu":
            $status_class = "warning";
            break;
        default:
            $status_class = "danger";
    }
    
    $content .= '
                                <tr>
                                    <td class="py-3">' . date("d/m/Y", strtotime($shipment["tanggal_pengiriman"])) . '</td>
                                    <td class="py-3">' . htmlspecialchars($shipment["tujuan"]) . '</td>
                                    <td class="py-3">' . number_format($shipment["jumlah_pembelian"], 0, ",", ".") . ' kg</td>
                                    <td class="py-3">
                                        <span class="badge bg-' . $status_class . ' rounded-pill">
                                            ' . ucfirst(str_replace("_", " ", $shipment["status"])) . '
                                        </span>
                                    </td>
                                </tr>';
}

if (empty($recent_shipments)) {
    $content .= '
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada data pengiriman</td>
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

echo renderPage("Dashboard", "dashboard", $content);
?>

<style>
.btn-light {
    color: #2E7D32;
    border-color: #2E7D32;
}

.btn-light:hover {
    background-color: #2E7D32;
    color: #fff;
}

.table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #2E7D32;
    color: #2E7D32;
}
</style> 