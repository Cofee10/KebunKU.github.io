<?php
require_once '../../includes/auth.php';
require_once '../../includes/layout.php';
require_once '../../config/database.php';

checkLogin();
checkRole(['distributor']);

// Get distributor's data
$stmt = $pdo->prepare("SELECT * FROM distributor WHERE id_distributor = ?");
$stmt->execute([$_SESSION['user_id']]);
$distributor = $stmt->fetch();

// Get shipping statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT rp.id_riwayat_pengiriman) as total_pengiriman,
        COALESCE(SUM(rp.jumlah), 0) as total_berat,
        COUNT(DISTINCT CASE WHEN rp.status = 'dalam pengiriman' THEN rp.id_riwayat_pengiriman END) as pengiriman_aktif
    FROM riwayat_pengiriman rp
    JOIN pembelian p ON rp.id_pembelian = p.id_pembelian
    WHERE p.id_distributor = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

// Get shipping history
$stmt = $pdo->prepare("
    SELECT 
        rp.*,
        p.total_harga,
        t.nama_tanaman,
        pt.nama_petani
    FROM riwayat_pengiriman rp
    JOIN pembelian p ON rp.id_pembelian = p.id_pembelian
    JOIN data_hasil_panen hp ON p.id_panen = hp.id_panen
    JOIN tanaman t ON hp.id_tanaman = t.id_tanaman
    JOIN data_lahan dl ON hp.id_lahan = dl.id_lahan
    JOIN petani pt ON dl.id_petani = pt.id_petani
    WHERE p.id_distributor = ?
    ORDER BY rp.tanggal_pengiriman DESC
");
$stmt->execute([$_SESSION['user_id']]);
$shipments = $stmt->fetchAll();

$content = '
<div class="container-fluid p-4">
    <h2 style="color: #43A047;">Pengiriman</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" style="color: #43A047;">Dashboard</a></li>
            <li class="breadcrumb-item active">Hasil Panen</li>
        </ol>
    </nav>

    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background: linear-gradient(45deg, #2E7D32, #43A047); border-radius: 0.5rem;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-white-50 mb-2">Total Pengiriman</div>
                            <h3 class="text-white mb-0">' . number_format($stats["total_pengiriman"], 0, ",", ".") . '</h3>
                        </div>
                        <i class="fas fa-truck fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background: linear-gradient(45deg, #2E7D32, #43A047); border-radius: 0.5rem;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-white-50 mb-2">Total Berat</div>
                            <h3 class="text-white mb-0">' . number_format($stats["total_berat"], 0, ",", ".") . ' kg</h3>
                        </div>
                        <i class="fas fa-weight fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background: linear-gradient(45deg, #2E7D32, #43A047); border-radius: 0.5rem;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-white-50 mb-2">Pengiriman Aktif</div>
                            <h3 class="text-white mb-0">' . number_format($stats["pengiriman_aktif"], 0, ",", ".") . '</h3>
                        </div>
                        <i class="fas fa-shipping-fast fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header py-3" style="background: linear-gradient(45deg, #2E7D32, #43A047);">
            <div class="d-flex align-items-center">
                <i class="fas fa-history text-white me-2"></i>
                <h5 class="text-white mb-0">Riwayat Pengiriman</h5>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="deliveryTable" class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3">Tanggal</th>
                            <th class="py-3">Tanaman</th>
                            <th class="py-3">Petani</th>
                            <th class="py-3">Jumlah</th>
                            <th class="py-3">Tujuan</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>';

if (empty($shipments)) {
    $content .= '
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada data pengiriman</td>
                        </tr>';
} else {
    foreach ($shipments as $shipment) {
        $status_class = "";
        switch ($shipment["status"]) {
            case "selesai":
                $status_class = "success";
                break;
            case "dalam_perjalanan":
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
                            <td class="py-3">' . htmlspecialchars($shipment["nama_tanaman"]) . '</td>
                            <td class="py-3">' . htmlspecialchars($shipment["nama_petani"]) . '</td>
                            <td class="py-3">' . number_format($shipment["jumlah"], 0, ",", ".") . ' kg</td>
                            <td class="py-3">' . htmlspecialchars($shipment["tujuan"]) . '</td>
                            <td class="py-3">
                                <span class="badge bg-' . $status_class . ' rounded-pill">
                                    ' . ucfirst(str_replace("_", " ", $shipment["status"])) . '
                                </span>
                            </td>
                            <td class="py-3">
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#updateModal" 
                                    data-id="' . $shipment["id_riwayat_pengiriman"] . '"
                                    data-tanaman="' . htmlspecialchars($shipment["nama_tanaman"]) . '"
                                    data-jumlah="' . $shipment["jumlah"] . '"
                                    data-tujuan="' . htmlspecialchars($shipment["tujuan"]) . '"
                                    data-status="' . $shipment["status"] . '">
                                    <i class="fas fa-edit me-1"></i>Update
                                </button>
                            </td>
                        </tr>';
    }
}

$content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success bg-gradient text-white">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Update Status Pengiriman</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateForm" action="process_delivery_update.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_pengiriman" id="id_pengiriman">
                    <div class="mb-3">
                        <label class="form-label">Tanaman</label>
                        <input type="text" class="form-control bg-light" id="modal_tanaman" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="text" class="form-control bg-light" id="modal_jumlah" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tujuan</label>
                        <input type="text" class="form-control bg-light" id="modal_tujuan" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="modal_status" required>
                            <option value="menunggu">Menunggu</option>
                            <option value="dalam_perjalanan">Dalam Perjalanan</option>
                            <option value="selesai">Selesai</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize DataTable
    $("#deliveryTable").DataTable({
        responsive: true,
        order: [[0, "desc"]],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    // Handle Update Modal
    const updateModal = document.getElementById("updateModal");
    updateModal.addEventListener("show.bs.modal", function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute("data-id");
        const tanaman = button.getAttribute("data-tanaman");
        const jumlah = parseInt(button.getAttribute("data-jumlah"));
        const tujuan = button.getAttribute("data-tujuan");
        const status = button.getAttribute("data-status");

        document.getElementById("id_pengiriman").value = id;
        document.getElementById("modal_tanaman").value = tanaman;
        document.getElementById("modal_jumlah").value = jumlah.toLocaleString("id-ID") + " kg";
        document.getElementById("modal_tujuan").value = tujuan;
        document.getElementById("modal_status").value = status;
    });
});
</script>';

echo renderPage('Pengiriman', 'pengiriman', $content); 