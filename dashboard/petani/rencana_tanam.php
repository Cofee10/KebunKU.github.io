<?php
require_once '../../includes/auth.php';
require_once '../../includes/layout.php';
require_once '../../config/database.php';

checkLogin();
checkRole(['petani']);

// Get all planting plans for the farmer
$stmt = $pdo->prepare("
    SELECT rt.*, t.nama_tanaman, dl.nama_lahan
    FROM rencana_tanam rt
    JOIN tanaman t ON rt.id_tanaman = t.id_tanaman
    JOIN data_lahan dl ON rt.id_lahan = dl.id_lahan
    WHERE dl.id_petani = ?
    ORDER BY rt.tanggal_tanam DESC
");
$stmt->execute([$_SESSION['user_id']]);
$plans = $stmt->fetchAll();

// Calculate total plans
$total_plans = count($plans);

// Count active plans
$active_plans = array_reduce($plans, function($carry, $item) {
    return $carry + ($item['status'] == 'aktif' ? 1 : 0);
}, 0);

$content = '
<div class="container-fluid p-4">
    <h2 style="color: #43A047;">Rencana Tanam</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" style="color: #43A047;">Dashboard</a></li>
            <li class="breadcrumb-item active">Rencana Tanam</li>
        </ol>
    </nav>

        <div class="card border-0 shadow-sm h-100">
            <div class="card-body" style="background-color: #2E7D32; color: white; border-radius: 0.375rem;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-6 text-white-50">Total Rencana</div>
                        <div class="fs-2 fw-bold">' . number_format($total_plans, 0, ',', '.') . '</div>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="fas fa-calendar"></i>
                    </div>
                </div>
            </div>
        </div>

<div class="card shadow-sm mb-4">
    <div class="card-header" style="background-color: #2E7D32; color: white;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-list me-2"></i>
                <span class="fs-5 fw-semibold">Data Rencana Tanam</span>
            </div>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                <i class="fas fa-plus me-1"></i>Tambah
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="planTable">
                <thead class="table-success">
                    <tr>
                        <th>Tanaman</th>
                        <th>Lahan</th>
                        <th>Tanggal Tanam</th>
                        <th>Perkiraan Panen</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>';

foreach ($plans as $plan) {
    $content .= '
        <tr>
            <td data-label="Tanaman">' . htmlspecialchars($plan['nama_tanaman']) . '</td>
            <td data-label="Lahan">' . htmlspecialchars($plan['nama_lahan']) . '</td>
            <td data-label="Tanggal Tanam">' . date('d/m/Y', strtotime($plan['tanggal_tanam'])) . '</td>
            <td data-label="Perkiraan Panen">' . date('d/m/Y', strtotime($plan['perkiraan_panen'])) . '</td>
            <td data-label="Status">
                <span class="badge bg-' . ($plan['status'] == 'aktif' ? 'success' : ($plan['status'] == 'selesai' ? 'primary' : 'secondary')) . '">
                    ' . ucfirst(htmlspecialchars($plan['status'])) . '
                </span>
            </td>
            <td data-label="Aksi">
                <button type="button" class="btn btn-warning btn-sm me-1" onclick="editPlan(' . $plan['id_rencana'] . ')">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="deletePlan(' . $plan['id_rencana'] . ')">
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

<!-- Add Plan Modal -->
<div class="modal fade" id="addPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Tambah Rencana Tanam</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPlanForm" action="actions/add_plan.php" method="POST">
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
                        <label for="tanggal_tanam" class="form-label">Tanggal Tanam</label>
                        <input type="date" class="form-control" id="tanggal_tanam" name="tanggal_tanam" required>
                    </div>
                    <div class="mb-3">
                        <label for="perkiraan_panen" class="form-label">Perkiraan Panen</label>
                        <input type="date" class="form-control" id="perkiraan_panen" name="perkiraan_panen" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="aktif">Aktif</option>
                            <option value="selesai">Selesai</option>
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

<!-- Edit Plan Modal -->
<div class="modal fade" id="editPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Edit Rencana Tanam</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPlanForm" action="actions/edit_plan.php" method="POST">
                <input type="hidden" id="edit_id_rencana" name="id_rencana">
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
                        <label for="edit_tanggal_tanam" class="form-label">Tanggal Tanam</label>
                        <input type="date" class="form-control" id="edit_tanggal_tanam" name="tanggal_tanam" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_perkiraan_panen" class="form-label">Perkiraan Panen</label>
                        <input type="date" class="form-control" id="edit_perkiraan_panen" name="perkiraan_panen" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="aktif">Aktif</option>
                            <option value="selesai">Selesai</option>
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
<div class="modal fade" id="deletePlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus rencana tanam ini?</p>
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
    $("#planTable").DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
        },
        order: [[2, "desc"]],
        responsive: true
    });

    // Load lands and crops for add form
    loadLands();
    loadCrops();

    // Form submission handlers
    $("#addPlanForm").on("submit", function(e) {
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

    $("#editPlanForm").on("submit", function(e) {
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

function editPlan(id) {
    $.ajax({
        url: "actions/get_plan.php",
        method: "GET",
        data: { id_rencana: id },
        success: function(response) {
            if (response.success) {
                const plan = response.data;
                $("#edit_id_rencana").val(plan.id_rencana);
                $("#edit_id_lahan").val(plan.id_lahan);
                $("#edit_id_tanaman").val(plan.id_tanaman);
                $("#edit_tanggal_tanam").val(plan.tanggal_tanam);
                $("#edit_perkiraan_panen").val(plan.perkiraan_panen);
                $("#edit_status").val(plan.status);
                $("#editPlanModal").modal("show");
            }
        }
    });
}

function deletePlan(id) {
    $("#deletePlanModal").modal("show");
    $("#confirmDelete").off("click").on("click", function() {
        $.ajax({
            url: "actions/delete_plan.php",
            method: "POST",
            data: { id_rencana: id },
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

echo renderPage('Rencana Tanam', 'rencana_tanam', $content);
?> 