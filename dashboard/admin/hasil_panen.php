<?php
require_once '../../includes/auth.php';
require_once '../../includes/layout.php';
require_once '../../config/database.php';

checkLogin();
checkRole(['admin']);

$stmt = $pdo->prepare("SELECT nama_admin FROM admin WHERE id_admin = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin_name = $stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT dhp.*, t.nama_tanaman, p.nama_petani, dl.nama_lahan
    FROM data_hasil_panen dhp
    JOIN tanaman t ON dhp.id_tanaman = t.id_tanaman
    JOIN data_lahan dl ON dhp.id_lahan = dl.id_lahan
    JOIN petani p ON dl.id_petani = p.id_petani
    ORDER BY dhp.tanggal_panen DESC
");
$harvests = $stmt->fetchAll();

$stmt = $pdo->query("SELECT COALESCE(SUM(jumlah), 0) as total FROM data_hasil_panen");
$total_harvest = $stmt->fetchColumn();

$content = '
<div class="container-fluid p-4">
    <h2 style="color: #43A047;">Hasil Panen</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" style="color: #43A047;">Dashboard</a></li>
            <li class="breadcrumb-item active">Hasil Panen</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body" style="background: linear-gradient(45deg, #2E7D32, #43A047); border-radius: 0.5rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 mb-2">Total Hasil Panen</div>
                            <h3 class="text-white mb-0">' . number_format($total_harvest, 0, ",", ".") . ' kg</h3>
                        </div>
                        <i class="fas fa-leaf fa-2x" style="color: rgba(255,255,255,1);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0">
        <div class="card-header" style="background: #43A047; color: white;">
            <div class="d-flex align-items-center">
                <i class="fas fa-list me-2"></i>
                Data Hasil Panen
            </div>
        </div>
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center p-3">
                <div class="d-flex align-items-center">
                    <span>Show</span>
                    <select class="form-select form-select-sm mx-2" style="width: auto;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>entries</span>
                </div>
                <div class="search-box">
                    <input type="search" class="form-control" placeholder="Search...">
                </div>
            </div>

            <table class="table table-hover mb-0">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th>TANGGAL</th>
                        <th>PETANI</th>
                        <th>TANAMAN</th>
                        <th>LAHAN</th>
                        <th>JUMLAH</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>';

foreach ($harvests as $harvest) {
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
                            <td>' . htmlspecialchars($harvest["nama_petani"]) . '</td>
                            <td>' . htmlspecialchars($harvest["nama_tanaman"]) . '</td>
                            <td>' . htmlspecialchars($harvest["nama_lahan"]) . '</td>
                            <td>' . number_format($harvest["jumlah"], 0, ",", ".") . ' kg</td>
                            <td><span class="badge bg-' . $status_class . '">' . ucfirst($harvest["status"]) . '</span></td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>

                <div class="d-flex justify-content-between align-items-center p-3">
                    <div>Showing 0 to 0 of 0 entries</div>
                    <nav>
                        <ul class="pagination mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#">Previous</a>
                            </li>
                            <li class="page-item disabled">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>';

$content .= "
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#harvestTable').DataTable({
        'order': [[0, 'desc']],
        'pageLength': 10,
        'language': {
            'url': '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
        }
    });
});
</script>";

echo renderPage('Hasil Panen', 'hasil_panen', $content);
?> 