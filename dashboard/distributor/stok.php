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

// Get stock statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT sg.id_stok) as total_item,
        COALESCE(SUM(sg.stok), 0) as total_berat,
        COALESCE(SUM(sg.stok * p.harga_satuan), 0) as total_nilai
    FROM stok_gudang sg
    JOIN data_hasil_panen p ON sg.id_panen = p.id_panen
    JOIN pembelian pb ON p.id_panen = pb.id_panen
    WHERE pb.id_distributor = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

// Get stock list
$stmt = $pdo->prepare("
    SELECT 
        sg.*,
        t.nama_tanaman,
        pt.nama_petani,
        p.harga_satuan
    FROM stok_gudang sg
    JOIN data_hasil_panen p ON sg.id_panen = p.id_panen
    JOIN pembelian pb ON p.id_panen = pb.id_panen
    JOIN tanaman t ON p.id_tanaman = t.id_tanaman
    JOIN data_lahan dl ON p.id_lahan = dl.id_lahan
    JOIN petani pt ON dl.id_petani = pt.id_petani
    WHERE pb.id_distributor = ?
    ORDER BY sg.update_terakhir DESC
");
$stmt->execute([$_SESSION['user_id']]);
$stocks = $stmt->fetchAll();

$content = '
<div class="container-fluid p-4">
    <h2 style="color: #43A047;">Stok Gudang</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" style="color: #43A047;">Dashboard</a></li>
            <li class="breadcrumb-item active">Stok Gudang</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background-color: #2E7D32; color: white; border-radius: 0.375rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-6 text-white-50">Total Item</div>
                            <div class="fs-2 fw-bold">' . number_format($stats["total_item"], 0, ",", ".") . '</div>
                        </div>
                        <div class="fs-1 text-white-50">
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background-color: #2E7D32; color: white; border-radius: 0.375rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-6 text-white-50">Total Berat</div>
                            <div class="fs-2 fw-bold">' . number_format($stats["total_berat"], 0, ",", ".") . ' kg</div>
                        </div>
                        <div class="fs-1 text-white-50">
                            <i class="fas fa-weight"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background-color: #2E7D32; color: white; border-radius: 0.375rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-6 text-white-50">Total Nilai</div>
                            <div class="fs-2 fw-bold">Rp ' . number_format($stats["total_nilai"], 0, ",", ".") . '</div>
                        </div>
                        <div class="fs-1 text-white-50">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header" style="background-color: #2E7D32; color: white;">
            <div class="d-flex align-items-center">
                <i class="fas fa-warehouse me-2"></i>
                <span class="fs-5 fw-semibold">Daftar Stok</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="stockTable" class="table table-striped table-hover">
                    <thead class="table-success">
                        <tr>
                            <th>Tanggal Masuk</th>
                            <th>Tanaman</th>
                            <th>Petani</th>
                            <th>Jumlah</th>
                            <th>Nilai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($stocks as $stock) {
    $status_class = "";
    $status_bg = "";
    switch ($stock["status"]) {
        case "tersedia":
            $status_class = "text-success";
            $status_bg = "bg-success";
            break;
        case "menipis":
            $status_class = "text-warning";
            $status_bg = "bg-warning";
            break;
        case "habis":
            $status_class = "text-danger";
            $status_bg = "bg-danger";
            break;
    }
    
    $nilai = $stock["stok"] * $stock["harga_satuan"];
    
    $content .= '
                        <tr>
                            <td>' . date("d/m/Y", strtotime($stock["update_terakhir"])) . '</td>
                            <td>' . htmlspecialchars($stock["nama_tanaman"]) . '</td>
                            <td>' . htmlspecialchars($stock["nama_petani"]) . '</td>
                            <td>' . number_format($stock["stok"], 0, ",", ".") . ' kg</td>
                            <td>Rp ' . number_format($nilai, 0, ",", ".") . '</td>
                            <td><span class="badge ' . $status_bg . ' bg-gradient">' . ucfirst($stock["status"]) . '</span></td>
                            <td>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#updateModal" 
                                    data-id="' . $stock["id_stok"] . '"
                                    data-tanaman="' . htmlspecialchars($stock["nama_tanaman"]) . '"
                                    data-stok="' . $stock["stok"] . '">
                                    <i class="fas fa-edit me-1"></i>Update
                                </button>
                            </td>
                        </tr>';
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
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Update Stok</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateForm" action="process_stock_update.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_stok" id="id_stok">
                    <div class="mb-3">
                        <label class="form-label">Tanaman</label>
                        <input type="text" class="form-control bg-light" id="modal_tanaman" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok Saat Ini</label>
                        <input type="text" class="form-control bg-light" id="modal_stok" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Update</label>
                        <input type="number" class="form-control" name="jumlah_update" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="menipis">Menipis</option>
                            <option value="habis">Habis</option>
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
    $("#stockTable").DataTable({
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
        const stok = parseInt(button.getAttribute("data-stok"));

        document.getElementById("id_stok").value = id;
        document.getElementById("modal_tanaman").value = tanaman;
        document.getElementById("modal_stok").value = stok.toLocaleString("id-ID") + " kg";
    });
});
</script>';

echo renderPage('Stok Gudang', 'stok', $content); 