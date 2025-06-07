<?php
require_once '../../includes/auth.php';
require_once '../../includes/layout.php';
require_once '../../config/database.php';

checkLogin();
checkRole(['admin']);

$stmt = $pdo->prepare("SELECT nama_admin FROM admin WHERE id_admin = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin_name = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'add') {
        $nama = trim($_POST['nama']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "Email sudah terdaftar!";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admin (nama_admin, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$nama, $email, $hashedPassword]);
                $_SESSION['success'] = "Admin berhasil ditambahkan!";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal menambahkan admin: " . $e->getMessage();
        }
    } elseif ($_POST['action'] == 'delete') {
        $id = $_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM admin WHERE id_admin = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Admin berhasil dihapus!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal menghapus admin: " . $e->getMessage();
        }
    }
}

$stmt = $pdo->query("SELECT * FROM admin ORDER BY nama_admin");
$admins = $stmt->fetchAll();

$total_admins = count($admins);

$content = '
<div class="container-fluid p-4">
    <h2 style="color: #43A047;">Kelola Admin</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" style="color: #43A047;">Dashboard</a></li>
            <li class="breadcrumb-item active">Kelola Admin</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body" style="background: linear-gradient(45deg, #2E7D32, #43A047); border-radius: 0.5rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 mb-2">Total Admin</div>
                            <h3 class="text-white mb-0">' . number_format($total_admins, 0, ",", ".") . '</h3>
                        </div>
                        <i class="fas fa-users fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header py-3" style="background: linear-gradient(45deg, #2E7D32, #43A047);">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-list text-white me-2"></i>
                    <h5 class="text-white mb-0">Data Admin</h5>
                </div>
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="fas fa-plus me-2"></i>Tambah Admin
                </button>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <span class="me-2">Show</span>
                        <select class="form-select form-select-sm w-auto" id="table-length">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-2">entries</span>
                    </div>
                    <div class="search-box">
                        <input type="search" class="form-control" placeholder="Search...">
                    </div>
                </div>

                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="py-3">NAMA</th>
                            <th class="py-3">EMAIL</th>
                            <th class="py-3 text-end">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($admins as $admin) {
    $content .= '
                        <tr>
                            <td class="py-3">' . htmlspecialchars($admin["nama_admin"]) . '</td>
                            <td class="py-3">' . htmlspecialchars($admin["email"]) . '</td>
                            <td class="py-3 text-end">
                                <button type="button" class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editAdminModal' . $admin["id_admin"] . '">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAdminModal' . $admin["id_admin"] . '">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>';

    $content .= '
    <div class="modal fade" id="editAdminModal' . $admin["id_admin"] . '" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #2E7D32; color: white;">
                    <h5 class="modal-title">Edit Admin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_admin" value="' . $admin["id_admin"] . '">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="nama" value="' . htmlspecialchars($admin["nama_admin"]) . '" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="' . htmlspecialchars($admin["email"]) . '" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>';

    $content .= '
    <div class="modal fade" id="deleteAdminModal' . $admin["id_admin"] . '" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #2E7D32; color: white;">
                    <h5 class="modal-title">Hapus Admin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus admin <strong>' . htmlspecialchars($admin["nama_admin"]) . '</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form action="" method="POST" class="d-inline">
                        <input type="hidden" name="id_admin" value="' . $admin["id_admin"] . '">
                        <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>';
}

$content .= '
                    </tbody>
                </table>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>Showing 1 to ' . $total_admins . ' of ' . $total_admins . ' entries</div>
                    <nav>
                        <ul class="pagination mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#">Previous</a>
                            </li>
                            <li class="page-item active">
                                <a class="page-link" href="#">1</a>
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
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #2E7D32; color: white;">
                <h5 class="modal-title">Tambah Admin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>';

$content .= "
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#adminTable').DataTable({
        'order': [[0, 'asc']],
        'pageLength': 10,
        'language': {
            'url': '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
        }
    });
});
</script>";

echo renderPage('Kelola Admin', 'kelola_admin', $content);
?> 