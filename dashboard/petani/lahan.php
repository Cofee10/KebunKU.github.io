<?php
require_once '../../includes/auth.php';
require_once '../../includes/layout.php';
require_once '../../config/database.php';

checkLogin();
checkRole(['petani']);

// Get all lands for the farmer
$stmt = $pdo->prepare("
    SELECT dl.*, COALESCE(SUM(dhp.jumlah), 0) as total_panen,
           COUNT(DISTINCT rt.id_rencana) as jumlah_rencana
    FROM data_lahan dl
    LEFT JOIN data_hasil_panen dhp ON dl.id_lahan = dhp.id_lahan
    LEFT JOIN rencana_tanam rt ON dl.id_lahan = rt.id_lahan AND rt.status = 'aktif'
    WHERE dl.id_petani = ?
    GROUP BY dl.id_lahan
    ORDER BY dl.nama_lahan ASC
");
$stmt->execute([$_SESSION['user_id']]);
$lands = $stmt->fetchAll();

// Calculate total area
$total_area = array_reduce($lands, function($carry, $item) {
    return $carry + $item['luas'];
}, 0);

$content = '
<div class="container-fluid p-4">
    <h2 style="color: #43A047;">Lahan</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" style="color: #43A047;">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Lahan</li>
        </ol>
    </nav>
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body" style="background-color: #2E7D32; color: white; border-radius: 0.375rem;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-6 text-white-50">Total Luas Lahan</div>
                        <div class="fs-2 fw-bold">' . number_format($total_area, 0, ',', '.') . ' m²</div>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="fas fa-map"></i>
                    </div>
                </div>
            </div>
        </div>

<div class="card shadow-sm mb-4">
    <div class="card-header" style="background-color: #2E7D32; color: white;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-list me-2"></i>
                <span class="fs-5 fw-semibold">Data Lahan</span>
            </div>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addLandModal">
                <i class="fas fa-plus me-1"></i>Tambah
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="landTable">
                <thead class="table-success">
                    <tr>
                        <th>Nama Lahan</th>
                        <th>Lokasi</th>
                        <th>Luas (m²)</th>
                        <th>Rencana Aktif</th>
                        <th>Total Panen (kg)</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>';


foreach ($lands as $land) {
    $content .= '
                            <tr>
                                <td>' . htmlspecialchars($land['nama_lahan']) . '</td>
                                <td>' . htmlspecialchars($land['lokasi']) . '</td>
                                <td>' . number_format($land['luas'], 0, ',', '.') . '</td>
                                <td>' . $land['jumlah_rencana'] . '</td>
                                <td>' . number_format($land['total_panen'], 0, ',', '.') . '</td>
                                <td>
                                    <span class="badge bg-' . ($land['status'] == 'aktif' ? 'success' : 'secondary') . '">
                                        ' . ucfirst(htmlspecialchars($land['status'])) . '
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm me-1" onclick="editLand(' . $land['id_lahan'] . ')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteLand(' . $land['id_lahan'] . ')">
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

<!-- Add Land Modal -->
<div class="modal fade" id="addLandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Tambah Lahan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addLandForm" action="actions/add_land.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_lahan" class="form-label">Nama Lahan</label>
                        <input type="text" class="form-control" id="nama_lahan" name="nama_lahan" required>
                    </div>
                    <div class="mb-3">
                        <label for="lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="lokasi" name="lokasi" required>
                    </div>
                    <div class="mb-3">
                        <label for="luas" class="form-label">Luas (m²)</label>
                        <input type="number" class="form-control" id="luas" name="luas" required min="1">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="aktif">Aktif</option>
                            <option value="tidak aktif">Tidak Aktif</option>
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

<!-- Edit Land Modal -->
<div class="modal fade" id="editLandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Edit Lahan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editLandForm" action="actions/edit_land.php" method="POST">
                <input type="hidden" id="edit_id_lahan" name="id_lahan">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nama_lahan" class="form-label">Nama Lahan</label>
                        <input type="text" class="form-control" id="edit_nama_lahan" name="nama_lahan" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="edit_lokasi" name="lokasi" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_luas" class="form-label">Luas (m²)</label>
                        <input type="number" class="form-control" id="edit_luas" name="luas" required min="1">
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="aktif">Aktif</option>
                            <option value="tidak aktif">Tidak Aktif</option>
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
<div class="modal fade" id="deleteLandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus lahan ini?</p>
                <p class="text-danger mb-0"><small>Semua data terkait lahan ini (hasil panen, rencana tanam, dll) juga akan terhapus.</small></p>
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
    $("#landTable").DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
        },
        order: [[0, "asc"]],
        responsive: true
    });

    // Form submission handlers
    $("#addLandForm").on("submit", function(e) {
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

    $("#editLandForm").on("submit", function(e) {
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

function editLand(id) {
    $.ajax({
        url: "actions/get_land.php",
        method: "GET",
        data: { id_lahan: id },
        success: function(response) {
            if (response.success) {
                const land = response.data;
                $("#edit_id_lahan").val(land.id_lahan);
                $("#edit_nama_lahan").val(land.nama_lahan);
                $("#edit_lokasi").val(land.lokasi);
                $("#edit_luas").val(land.luas);
                $("#edit_status").val(land.status);
                $("#editLandModal").modal("show");
            }
        }
    });
}

function deleteLand(id) {
    $("#deleteLandModal").modal("show");
    $("#confirmDelete").off("click").on("click", function() {
        $.ajax({
            url: "actions/delete_land.php",
            method: "POST",
            data: { id_lahan: id },
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

echo renderPage('Data Lahan', 'lahan', $content);
?> 