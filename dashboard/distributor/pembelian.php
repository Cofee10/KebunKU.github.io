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

// Get total pembelian statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(id_pembelian) as total_pembelian,
        COALESCE(SUM(jumlah_pembelian), 0) as total_berat,
        COALESCE(SUM(total_harga), 0) as total_pengeluaran
    FROM pembelian 
    WHERE id_distributor = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

// Get available harvest data
$stmt = $pdo->prepare("
    SELECT 
        hp.id_panen,
        t.nama_tanaman,
        pt.nama_petani,
        hp.jumlah,
        hp.harga_satuan
    FROM data_hasil_panen hp
    JOIN tanaman t ON hp.id_tanaman = t.id_tanaman
    JOIN data_lahan dl ON hp.id_lahan = dl.id_lahan
    JOIN petani pt ON dl.id_petani = pt.id_petani
    WHERE hp.status = 'tersedia'
");
$stmt->execute();
$available_harvests = $stmt->fetchAll();

// Get purchase history
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        t.nama_tanaman,
        pt.nama_petani
    FROM pembelian p
    JOIN data_hasil_panen hp ON p.id_panen = hp.id_panen
    JOIN tanaman t ON hp.id_tanaman = t.id_tanaman
    JOIN data_lahan dl ON hp.id_lahan = dl.id_lahan
    JOIN petani pt ON dl.id_petani = pt.id_petani
    WHERE p.id_distributor = ?
    ORDER BY p.tanggal_pembelian DESC
");
$stmt->execute([$_SESSION['user_id']]);
$purchase_history = $stmt->fetchAll();

$content = '
<div class="container-fluid p-4">
    <h2 style="color: #43A047;">Pembelian</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" style="color: #43A047;">Dashboard</a></li>
            <li class="breadcrumb-item active">Pembelian</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body" style="background-color: #2E7D32; color: white; border-radius: 0.375rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-6 text-white-50">Total Pembelian</div>
                            <div class="fs-2 fw-bold">' . number_format($stats["total_pembelian"], 0, ",", ".") . '</div>
                        </div>
                        <div class="fs-1 text-white-50">
                            <i class="fas fa-shopping-cart"></i>
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
                            <div class="fs-6 text-white-50">Total Pengeluaran</div>
                            <div class="fs-2 fw-bold">Rp ' . number_format($stats["total_pengeluaran"], 0, ",", ".") . '</div>
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
                <i class="fas fa-table me-2"></i>
                <span class="fs-5 fw-semibold">Hasil Panen Tersedia</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="availableHarvestTable" class="table table-striped table-hover">
                    <thead class="table-success">
                        <tr>
                            <th>Tanaman</th>
                            <th>Petani</th>
                            <th>Jumlah</th>
                            <th>Harga/Kg</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($available_harvests as $harvest) {
    $content .= '
                        <tr>
                            <td>' . htmlspecialchars($harvest["nama_tanaman"]) . '</td>
                            <td>' . htmlspecialchars($harvest["nama_petani"]) . '</td>
                            <td>' . number_format($harvest["jumlah"], 0, ",", ".") . ' kg</td>
                            <td>Rp ' . number_format($harvest["harga_satuan"], 0, ",", ".") . '</td>
                            <td>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#buyModal" 
                                    data-id="' . $harvest["id_panen"] . '" 
                                    data-tanaman="' . htmlspecialchars($harvest["nama_tanaman"]) . '"
                                    data-petani="' . htmlspecialchars($harvest["nama_petani"]) . '"
                                    data-jumlah="' . $harvest["jumlah"] . '"
                                    data-harga="' . $harvest["harga_satuan"] . '">
                                    <i class="fas fa-shopping-cart me-1"></i>Beli
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

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white py-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-history me-2"></i>
                <span class="fs-5 fw-semibold">Riwayat Pembelian</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="purchaseHistoryTable" class="table table-striped table-hover">
                    <thead class="table-success">
                        <tr>
                            <th>Tanggal</th>
                            <th>Tanaman</th>
                            <th>Petani</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($purchase_history as $purchase) {
    $status_class = "";
    $status_bg = "";
    switch ($purchase["status"]) {
        case "selesai":
            $status_class = "text-success";
            $status_bg = "bg-success";
            break;
        case "menunggu":
            $status_class = "text-warning";
            $status_bg = "bg-warning";
            break;
        case "ditolak":
            $status_class = "text-danger";
            $status_bg = "bg-danger";
            break;
    }
    
    $content .= '
                        <tr>
                            <td>' . date("d/m/Y", strtotime($purchase["tanggal_pembelian"])) . '</td>
                            <td>' . htmlspecialchars($purchase["nama_tanaman"]) . '</td>
                            <td>' . htmlspecialchars($purchase["nama_petani"]) . '</td>
                            <td>' . number_format($purchase["jumlah_pembelian"], 0, ",", ".") . ' kg</td>
                            <td>Rp ' . number_format($purchase["total_harga"], 0, ",", ".") . '</td>
                            <td><span class="badge ' . $status_bg . ' bg-gradient">' . ucfirst($purchase["status"]) . '</span></td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Buy Modal -->
<div class="modal fade" id="buyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success bg-gradient text-white">
                <h5 class="modal-title"><i class="fas fa-shopping-cart me-2"></i>Beli Hasil Panen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="buyForm" action="process_purchase.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_panen" id="id_panen">
                    <div class="mb-3">
                        <label class="form-label">Tanaman</label>
                        <input type="text" class="form-control bg-light" id="modal_tanaman" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Petani</label>
                        <input type="text" class="form-control bg-light" id="modal_petani" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Tersedia</label>
                        <input type="text" class="form-control bg-light" id="modal_jumlah" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga per Kg</label>
                        <input type="text" class="form-control bg-light" id="modal_harga" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Pembelian (kg)</label>
                        <input type="number" class="form-control" name="jumlah_pembelian" id="jumlah_pembelian" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi Pengiriman</label>
                        <textarea class="form-control" name="lokasi_pengiriman" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Harga</label>
                        <input type="text" class="form-control bg-light fw-bold" id="total_harga" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Beli
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize DataTables
    $("#availableHarvestTable").DataTable({
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });
    
    $("#purchaseHistoryTable").DataTable({
        responsive: true,
        order: [[0, "desc"]],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    // Handle Buy Modal
    const buyModal = document.getElementById("buyModal");
    buyModal.addEventListener("show.bs.modal", function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute("data-id");
        const tanaman = button.getAttribute("data-tanaman");
        const petani = button.getAttribute("data-petani");
        const jumlah = parseInt(button.getAttribute("data-jumlah"));
        const harga = parseInt(button.getAttribute("data-harga"));

        document.getElementById("id_panen").value = id;
        document.getElementById("modal_tanaman").value = tanaman;
        document.getElementById("modal_petani").value = petani;
        document.getElementById("modal_jumlah").value = jumlah.toLocaleString("id-ID") + " kg";
        document.getElementById("modal_harga").value = "Rp " + harga.toLocaleString("id-ID");

        const jumlahInput = document.getElementById("jumlah_pembelian");
        const totalHarga = document.getElementById("total_harga");

        function updateTotal() {
            const amount = parseInt(jumlahInput.value) || 0;
            if (amount > jumlah) {
                jumlahInput.value = jumlah;
                amount = jumlah;
            }
            const total = amount * harga;
            totalHarga.value = "Rp " + total.toLocaleString("id-ID");
        }

        jumlahInput.addEventListener("input", updateTotal);
        jumlahInput.max = jumlah;
        jumlahInput.value = "";
        totalHarga.value = "Rp 0";
    });
});
</script>';

echo renderPage('Pembelian', 'pembelian', $content); 