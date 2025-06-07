<?php
require_once '../../includes/auth.php';
require_once '../../includes/layout.php';
require_once '../../config/database.php';

checkLogin();
checkRole(['petani']);

// Get all harvests for the farmer
$stmt = $pdo->prepare("
    SELECT dhp.*, t.nama_tanaman, dl.nama_lahan
    FROM data_hasil_panen dhp
    JOIN tanaman t ON dhp.id_tanaman = t.id_tanaman
    JOIN data_lahan dl ON dhp.id_lahan = dl.id_lahan
    WHERE dl.id_petani = ?
    ORDER BY dhp.tanggal_panen DESC
");
$stmt->execute([$_SESSION['user_id']]);
$harvests = $stmt->fetchAll();

// Calculate total harvest
$total_harvest = array_reduce($harvests, function($carry, $item) {
    return $carry + $item['jumlah'];
}, 0);

$content = '
<div class="container-fluid p-4">
    <h2 style="color: #43A047;">Hasil Panen</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" style="color: #43A047;">Dashboard</a></li>
            <li class="breadcrumb-item active">Hasil Panen</li>
        </ol>
    </nav>

        <div class="card border-0 shadow-sm h-100">
            <div class="card-body" style="background-color: #2E7D32; color: white; border-radius: 0.375rem;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-6 text-white-50">Total Hasil Panen</div>
                        <div class="fs-2 fw-bold">' . number_format($total_harvest, 0, ',', '.') . ' kg</div>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="fas fa-seedling"></i>
                    </div>
                </div>
            </div>
        </div>

<div class="card shadow-sm mb-4">
    <div class="card-header" style="background-color: #2E7D32; color: white;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-list me-2"></i>
                <span class="fs-5 fw-semibold">Data Hasil Panen</span>
            </div>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addHarvestModal">
                <i class="fas fa-plus me-1"></i>Tambah
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="harvestTable">
                <thead class="table-success">
                    <tr>
                        <th>Tanggal Panen</th>
                        <th>Tanaman</th>
                        <th>Lahan</th>
                        <th>Jumlah (kg)</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>';

foreach ($harvests as $harvest) {
    $content .= '
        <tr>
            <td data-label="Tanggal Panen">' . date('d/m/Y', strtotime($harvest['tanggal_panen'])) . '</td>
            <td data-label="Tanaman">' . htmlspecialchars($harvest['nama_tanaman']) . '</td>
            <td data-label="Lahan">' . htmlspecialchars($harvest['nama_lahan']) . '</td>
            <td data-label="Jumlah (kg)">' . number_format($harvest['jumlah'], 0, ',', '.') . '</td>
            <td data-label="Status">
                <span class="badge bg-' . ($harvest['status'] == 'tersedia' ? 'success' : ($harvest['status'] == 'terjual' ? 'primary' : 'secondary')) . '">
                    ' . ucfirst(htmlspecialchars($harvest['status'])) . '
                </span>
            </td>
            <td data-label="Aksi">
                <button type="button" class="btn btn-warning btn-sm me-1" onclick="editHarvest(' . $harvest['id_hasil_panen'] . ')">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteHarvest(' . $harvest['id_hasil_panen'] . ')">
                    <i class="fas fa-trash"></i>
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
</div>

<!-- Add Harvest Modal -->
<div class="modal fade" id="addHarvestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Tambah Hasil Panen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addHarvestForm" action="actions/add_harvest.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_lahan" class="form-label">Lahan</label>
                        <select class="form-select" id="id_lahan" name="id_lahan" required>
                            <option value="">Pilih Lahan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="id_tanaman" class="form-label">Tanaman</label>
                        <select class="form-select" id="id_tanaman" name="id_tanaman" required>
                            <option value="">Pilih Tanaman</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_panen" class="form-label">Tanggal Panen</label>
                        <input type="date" class="form-control" id="tanggal_panen" name="tanggal_panen" required>
                    </div>
                    <div class="mb-3">
                        <label for="jumlah" class="form-label">Jumlah (kg)</label>
                        <input type="number" class="form-control" id="jumlah" name="jumlah" required min="0" step="0.1">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="terjual">Terjual</option>
                            <option value="gagal">Gagal</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Harvest Modal -->
<div class="modal fade" id="editHarvestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Edit Hasil Panen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editHarvestForm" action="actions/edit_harvest.php" method="POST">
                <input type="hidden" id="edit_id_hasil_panen" name="id_hasil_panen">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_id_lahan" class="form-label">Lahan</label>
                        <select class="form-select" id="edit_id_lahan" name="id_lahan" required>
                            <option value="">Pilih Lahan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_id_tanaman" class="form-label">Tanaman</label>
                        <select class="form-select" id="edit_id_tanaman" name="id_tanaman" required>
                            <option value="">Pilih Tanaman</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tanggal_panen" class="form-label">Tanggal Panen</label>
                        <input type="date" class="form-control" id="edit_tanggal_panen" name="tanggal_panen" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_jumlah" class="form-label">Jumlah (kg)</label>
                        <input type="number" class="form-control" id="edit_jumlah" name="jumlah" required min="0" step="0.1">
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="terjual">Terjual</option>
                            <option value="gagal">Gagal</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteHarvestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data hasil panen ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize DataTable
    $("#harvestTable").DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
        },
        order: [[0, "desc"]],
        responsive: true
    });

    // Load lands and crops for add form
    loadLands();
    loadCrops();

    // Form submission handlers
    $("#addHarvestForm").on("submit", function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr("action"),
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function() {
                alert("Terjadi kesalahan. Silakan coba lagi.");
            }
        });
    });

    $("#editHarvestForm").on("submit", function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr("action"),
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function() {
                alert("Terjadi kesalahan. Silakan coba lagi.");
            }
        });
    });
});

function loadLands() {
    $.ajax({
        url: "actions/get_lands.php",
        method: "GET",
        success: function(response) {
            const lands = response.data;
            const options = lands.map(land => 
                `<option value="${land.id_lahan}">${land.nama_lahan}</option>`
            ).join("");
            
            $("#id_lahan, #edit_id_lahan").html(`<option value="">Pilih Lahan</option>${options}`);
        }
    });
}

function loadCrops() {
    $.ajax({
        url: "actions/get_crops.php",
        method: "GET",
        success: function(response) {
            const crops = response.data;
            const options = crops.map(crop => 
                `<option value="${crop.id_tanaman}">${crop.nama_tanaman}</option>`
            ).join("");
            
            $("#id_tanaman, #edit_id_tanaman").html(`<option value="">Pilih Tanaman</option>${options}`);
        }
    });
}

function editHarvest(id) {
    $.ajax({
        url: "actions/get_harvest.php",
        method: "GET",
        data: { id_hasil_panen: id },
        success: function(response) {
            if (response.success) {
                const harvest = response.data;
                $("#edit_id_hasil_panen").val(harvest.id_hasil_panen);
                $("#edit_id_lahan").val(harvest.id_lahan);
                $("#edit_id_tanaman").val(harvest.id_tanaman);
                $("#edit_tanggal_panen").val(harvest.tanggal_panen);
                $("#edit_jumlah").val(harvest.jumlah);
                $("#edit_status").val(harvest.status);
                $("#editHarvestModal").modal("show");
            }
        }
    });
}

function deleteHarvest(id) {
    $("#deleteHarvestModal").modal("show");
    $("#confirmDelete").off("click").on("click", function() {
        $.ajax({
            url: "actions/delete_harvest.php",
            method: "POST",
            data: { id_hasil_panen: id },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function() {
                alert("Terjadi kesalahan. Silakan coba lagi.");
            }
        });
    });
}
</script>';

echo renderPage('Data Hasil Panen', 'hasil_panen', $content);
?> 