<?php
require_once '../../includes/auth.php';
require_once '../../includes/layout.php';
require_once '../../config/database.php';

checkLogin();
checkRole(['petani']);

// Get farmer's data
$stmt = $pdo->prepare("SELECT * FROM petani WHERE id_petani = ?");
$stmt->execute([$_SESSION['user_id']]);
$petani = $stmt->fetch();

// Get total lahan
$stmt = $pdo->prepare("SELECT COUNT(*) as total_lahan FROM data_lahan WHERE id_petani = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_lahan = $stmt->fetch()['total_lahan'];

// Get total rencana tanam aktif
$stmt = $pdo->prepare("SELECT COUNT(*) as total_rencana FROM rencana_tanam rt 
                       JOIN data_lahan dl ON rt.id_lahan = dl.id_lahan 
                       WHERE dl.id_petani = ? AND rt.status = 'aktif'");
$stmt->execute([$_SESSION['user_id']]);
$total_rencana = $stmt->fetch()['total_rencana'];

// Get total hasil panen
$stmt = $pdo->prepare("SELECT COALESCE(SUM(dhp.jumlah), 0) as total_panen 
                       FROM data_hasil_panen dhp 
                       JOIN data_lahan dl ON dhp.id_lahan = dl.id_lahan 
                       WHERE dl.id_petani = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_panen = $stmt->fetch()['total_panen'];

// Get total penggunaan pupuk
$stmt = $pdo->prepare("SELECT COALESCE(SUM(pp.jumlah), 0) as total_pupuk 
                       FROM penggunaan_pupuk pp 
                       JOIN data_lahan dl ON pp.id_lahan = dl.id_lahan 
                       WHERE dl.id_petani = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_pupuk = $stmt->fetch()['total_pupuk'];

// Get recent harvests
$stmt = $pdo->prepare("
    SELECT dhp.*, t.nama_tanaman, dl.nama_lahan
    FROM data_hasil_panen dhp
    JOIN tanaman t ON dhp.id_tanaman = t.id_tanaman
    JOIN data_lahan dl ON dhp.id_lahan = dl.id_lahan
    WHERE dl.id_petani = ?
    ORDER BY dhp.tanggal_panen DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_harvests = $stmt->fetchAll();

// Get active planting plans
$stmt = $pdo->prepare("
    SELECT rt.*, t.nama_tanaman, dl.nama_lahan
    FROM rencana_tanam rt
    JOIN tanaman t ON rt.id_tanaman = t.id_tanaman
    JOIN data_lahan dl ON rt.id_lahan = dl.id_lahan
    WHERE dl.id_petani = ? AND rt.status = 'aktif'
    ORDER BY rt.tanggal_tanam DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$active_plans = $stmt->fetchAll();

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
                            <h3 class="mb-1 text-white">Selamat datang, ' . htmlspecialchars($petani["nama_petani"]) . '</h3>
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
                            <div class="fs-6 text-white-50">Total Lahan</div>
                            <div class="fs-2 fw-bold">' . number_format($total_lahan, 0, ",", ".") . '</div>
                        </div>
                        <div class="fs-1 text-white-50">
                            <i class="fas fa-map"></i>
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
                            <div class="fs-6 text-white-50">Rencana Tanam Aktif</div>
                            <div class="fs-2 fw-bold">' . number_format($total_rencana, 0, ",", ".") . '</div>
                        </div>
                        <div class="fs-1 text-white-50">
                            <i class="fas fa-seedling"></i>
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
                            <div class="fs-2 fw-bold">' . number_format($total_panen, 0, ",", ".") . ' kg</div>
                        </div>
                        <div class="fs-1 text-white-50">
                            <i class="fas fa-leaf"></i>
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
                            <div class="fs-6 text-white-50">Penggunaan Pupuk</div>
                            <div class="fs-2 fw-bold">' . number_format($total_pupuk, 0, ",", ".") . ' kg</div>
                        </div>
                        <div class="fs-1 text-white-50">
                            <i class="fas fa-flask"></i>
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
                            <i class="fas fa-calendar me-2"></i>
                            <span class="fs-5 fw-semibold">Rencana Tanam Terbaru</span>
                        </div>
                        <a href="rencana_tanam.php" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-success">
                                <tr>
                                    <th>Tanaman</th>
                                    <th>Lahan</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>';

foreach ($active_plans as $plan) {
    $status_class = "";
    switch ($plan["status"]) {
        case "aktif":
            $status_class = "success";
            break;
        case "selesai":
            $status_class = "secondary";
            break;
        case "gagal":
            $status_class = "danger";
            break;
    }
    
    $content .= '
                                <tr>
                                    <td>' . htmlspecialchars($plan["nama_tanaman"]) . '</td>
                                    <td>' . htmlspecialchars($plan["nama_lahan"]) . '</td>
                                    <td>' . date("d/m/Y", strtotime($plan["tanggal_tanam"])) . '</td>
                                    <td><span class="badge bg-' . $status_class . '">' . ucfirst($plan["status"]) . '</span></td>
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
                                    <th>Tanaman</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>';

foreach ($recent_harvests as $harvest) {
    $status_class = "";
    switch ($harvest["status"]) {
        case "tersedia":
            $status_class = "success";
            break;
        case "terjual":
            $status_class = "primary";
            break;
        case "gagal":
            $status_class = "danger";
            break;
    }
    
    $content .= '
                                <tr>
                                    <td>' . date("d/m/Y", strtotime($harvest["tanggal_panen"])) . '</td>
                                    <td>' . htmlspecialchars($harvest["nama_tanaman"]) . '</td>
                                    <td>' . number_format($harvest["jumlah"], 0, ",", ".") . ' kg</td>
                                    <td><span class="badge bg-' . $status_class . '">' . ucfirst($harvest["status"]) . '</span></td>
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

echo renderPage('Dashboard Petani', 'dashboard', $content);
?> 