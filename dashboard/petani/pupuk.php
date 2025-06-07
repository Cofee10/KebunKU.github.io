<?php
require_once '../../includes/auth.php';
require_once '../../includes/layout.php';
require_once '../../config/database.php';

checkLogin();
checkRole(['petani']);

// Get all fertilizer usage for the farmer
$stmt = $pdo->prepare("
    SELECT p.*, dl.nama_lahan
    FROM penggunaan_pupuk p
    JOIN data_lahan dl ON p.id_lahan = dl.id_lahan
    WHERE dl.id_petani = ?
    ORDER BY p.tanggal_penggunaan DESC
");
$stmt->execute([$_SESSION['user_id']]);
$usages = $stmt->fetchAll();

// Calculate total usage
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(jumlah), 0) as total_usage
    FROM penggunaan_pupuk p
    JOIN data_lahan dl ON p.id_lahan = dl.id_lahan
    WHERE dl.id_petani = ?
");
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetch();
$total_stock = $result['total_usage'];

$content = '
<div class="container-fluid p-4">
    <h2 style="color: #43A047;">Pupuk</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" style="color: #43A047;">Dashboard</a></li>
            <li class="breadcrumb-item active">Pupuk</li>
        </ol>
    </nav>

        <div class="card border-0 shadow-sm h-100">
            <div class="card-body" style="background-color: #2E7D32; color: white; border-radius: 0.375rem;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-6 text-white-50">Total Stok Pupuk</div>
                        <div class="fs-2 fw-bold">' . number_format($total_stock, 0, ',', '.') . ' kg</div>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
            </div>
        </div>

<div class="card shadow-sm mb-4">
    <div class="card-header" style="background-color: #2E7D32; color: white;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-list me-2"></i>
                <span class="fs-5 fw-semibold">Data Pupuk</span>
            </div>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addFertilizerModal">
                <i class="fas fa-plus me-1"></i>Tambah
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="fertilizerTable">
                <thead class="table-success">
                    <tr>
                        <th>Tanggal</th>
                        <th>Lahan</th>
                        <th>Jenis Pupuk</th>
                        <th>Jumlah (kg)</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>';

foreach ($usages as $usage) {
    $content .= '
        <tr>
            <td data-label="Tanggal">' . date('d/m/Y', strtotime($usage['tanggal_penggunaan'])) . '</td>
            <td data-label="Lahan">' . htmlspecialchars($usage['nama_lahan']) . '</td>
            <td data-label="Jenis Pupuk">' . htmlspecialchars($usage['jenis_pupuk']) . '</td>
            <td data-label="Jumlah (kg)">' . number_format($usage['jumlah'], 0, ',', '.') . '</td>
            <td data-label="Catatan">' . htmlspecialchars($usage['catatan']) . '</td>
            <td data-label="Aksi">
                <button type="button" class="btn btn-warning btn-sm me-1" onclick="editUsage(' . $usage['id_penggunaan'] . ')">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteUsage(' . $usage['id_penggunaan'] . ')">
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

<!-- Add Usage Modal -->
<div class="modal fade" id="addUsageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Tambah Penggunaan Pupuk</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addUsageForm" action="actions/add_usage.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_lahan" class="form-label">Lahan</label>
                        <select class="form-select" id="id_lahan" name="id_lahan" required>
                            <option value="">Pilih Lahan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="jenis_pupuk" class="form-label">Jenis Pupuk</label>
                        <input type="text" class="form-control" id="jenis_pupuk" name="jenis_pupuk" required>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_penggunaan" class="form-label">Tanggal Penggunaan</label>
                        <input type="date" class="form-control" id="tanggal_penggunaan" name="tanggal_penggunaan" required>
                    </div>
                    <div class="mb-3">
                        <label for="jumlah" class="form-label">Jumlah (kg)</label>
                        <input type="number" class="form-control" id="jumlah" name="jumlah" required min="0" step="0.1">
                    </div>
                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3"></textarea>
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

<!-- Edit Usage Modal -->
<div class="modal fade" id="editUsageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Edit Penggunaan Pupuk</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUsageForm" action="actions/edit_usage.php" method="POST">
                <input type="hidden" id="edit_id_penggunaan" name="id_penggunaan">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_id_lahan" class="form-label">Lahan</label>
                        <select class="form-select" id="edit_id_lahan" name="id_lahan" required>
                            <option value="">Pilih Lahan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_jenis_pupuk" class="form-label">Jenis Pupuk</label>
                        <input type="text" class="form-control" id="edit_jenis_pupuk" name="jenis_pupuk" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tanggal_penggunaan" class="form-label">Tanggal Penggunaan</label>
                        <input type="date" class="form-control" id="edit_tanggal_penggunaan" name="tanggal_penggunaan" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_jumlah" class="form-label">Jumlah (kg)</label>
                        <input type="number" class="form-control" id="edit_jumlah" name="jumlah" required min="0" step="0.1">
                    </div>
                    <div class="mb-3">
                        <label for="edit_catatan" class="form-label">Catatan</label>
                        <textarea class="form-control" id="edit_catatan" name="catatan" rows="3"></textarea>
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
<div class="modal fade" id="deleteUsageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data penggunaan pupuk ini?</p>
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
    $("#fertilizerTable").DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
        }
    });

    // Load lands for the farmer
    loadLands();
});

function loadLands() {
    fetch("actions/get_lands.php")
        .then(response => response.json())
        .then(data => {
            const landSelects = document.querySelectorAll("#id_lahan, #edit_id_lahan");
            landSelects.forEach(select => {
                select.innerHTML = "<option value=\"\">Pilih Lahan</option>";
                data.forEach(land => {
                    select.innerHTML += `<option value="${land.id_lahan}">${land.nama_lahan}</option>`;
                });
            });
        });
}

function editUsage(id) {
    fetch(`actions/get_usage.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById("edit_id_penggunaan").value = data.id_penggunaan;
            document.getElementById("edit_id_lahan").value = data.id_lahan;
            document.getElementById("edit_jenis_pupuk").value = data.jenis_pupuk;
            document.getElementById("edit_tanggal_penggunaan").value = data.tanggal_penggunaan;
            document.getElementById("edit_jumlah").value = data.jumlah;
            document.getElementById("edit_catatan").value = data.catatan;
            
            new bootstrap.Modal(document.getElementById("editUsageModal")).show();
        });
}

function deleteUsage(id) {
    const modal = new bootstrap.Modal(document.getElementById("deleteUsageModal"));
    modal.show();
    
    document.getElementById("confirmDelete").onclick = function() {
        fetch(`actions/delete_usage.php?id=${id}`, { method: "DELETE" })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert("Gagal menghapus data penggunaan pupuk");
                }
            });
    };
}
</script>';

echo renderPage("Penggunaan Pupuk", "pupuk", $content);
?> 